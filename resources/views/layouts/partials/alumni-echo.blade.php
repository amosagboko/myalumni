@if(config('social.realtime_enabled') && config('broadcasting.default') === 'reverb' && config('broadcasting.connections.reverb.key'))
    @php
        $reverb = config('broadcasting.connections.reverb');
        $useTls = (bool) ($reverb['options']['useTLS'] ?? false);
        $port = (int) ($reverb['options']['port'] ?? ($useTls ? 443 : 80));
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        (function () {
            function bootEcho() {
                if (window.Echo || typeof Echo === 'undefined' || typeof Pusher === 'undefined') {
                    return false;
                }

                try {
                    window.Pusher = Pusher;
                    window.Echo = new Echo({
                        broadcaster: 'reverb',
                        key: @json($reverb['key']),
                        wsHost: @json($reverb['options']['host']),
                        wsPort: {{ $port }},
                        wssPort: {{ $port }},
                        forceTLS: @json($useTls),
                        enabledTransports: ['ws', 'wss'],
                        authEndpoint: @json(url('/broadcasting/auth')),
                        auth: {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                        },
                    });

                    return true;
                } catch (error) {
                    console.warn('Alumni Echo failed to start:', error);

                    return false;
                }
            }

            function subscribeRealtimeChannels() {
                if (!window.Echo || typeof Livewire === 'undefined') {
                    return;
                }

                try {
                    window.Echo.channel('alumni.social')
                        .listen('.feed.updated', function (payload) {
                            Livewire.dispatch('background-feed-sync', payload);
                        });
                } catch (error) {
                    console.warn('Alumni social feed channel failed:', error);
                }

                @auth
                try {
                    window.Echo.private(@json('App.Models.User.'.auth()->id()))
                        .listen('.notification.created', function () {
                            Livewire.dispatch('background-notification-sync');
                        });
                } catch (error) {
                    console.warn('Alumni notification channel failed:', error);
                }
                @endauth
            }

            document.addEventListener('livewire:init', function () {
                if (bootEcho()) {
                    subscribeRealtimeChannels();
                }
            });
        })();
    </script>
@endif
<script>
    document.addEventListener('livewire:init', function () {
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', function () {
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }

                if (typeof lightbox !== 'undefined' && typeof lightbox.init === 'function') {
                    lightbox.init();
                }
            });
        }
    });
</script>
