@if(config('broadcasting.default') === 'reverb' && config('broadcasting.connections.reverb.key'))
    @php
        $reverb = config('broadcasting.connections.reverb');
        $useTls = (bool) ($reverb['options']['useTLS'] ?? false);
        $port = (int) ($reverb['options']['port'] ?? ($useTls ? 443 : 80));
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Echo === 'undefined' || typeof Pusher === 'undefined') {
                return;
            }

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
        });
    </script>
@endif
