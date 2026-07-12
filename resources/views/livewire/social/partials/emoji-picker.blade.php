@php
    $field = $field ?? 'content';
    $label = $label ?? 'Add emoji';
    $emojis = app(\App\Support\Social\EmojiPicker::class)->items();
@endphp

@if(config('social.emoji_picker.enabled', true) && count($emojis) > 0)
    <details class="social-emoji-picker position-relative">
        <summary class="social-emoji-picker__trigger"
                 title="{{ $label }}"
                 aria-label="{{ $label }}">
            😊
        </summary>
        <div class="social-emoji-picker__menu" role="group" aria-label="Choose an emoji">
            @foreach($emojis as $emoji)
                <button type="button"
                        wire:click="insertEmoji('{{ $field }}', '{{ $emoji }}')"
                        class="social-emoji-picker__item"
                        title="{{ $emoji }}"
                        aria-label="Insert {{ $emoji }}">
                    {{ $emoji }}
                </button>
            @endforeach
        </div>
    </details>
@endif
