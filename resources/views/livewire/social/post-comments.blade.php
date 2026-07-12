<div
    class="card-body p-0 mt-2 border-top-xs pt-3 social-comments-panel"
    @if(! config('social.realtime_enabled') && config('social.poll_interval_seconds', 10) > 0)
        wire:poll.visible.{{ config('social.poll_interval_seconds', 10) }}s
    @endif
    wire:loading.delay.longest.class="social-comments-syncing"
>
    <div class="d-flex align-items-center p-3 bg-greylight rounded-3 mb-3">
        <figure class="avatar me-2">
            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w35">
        </figure>
        <div class="d-flex align-items-center w-100">
            <input type="text"
                   wire:model.live="body"
                   wire:keydown.enter.prevent="addComment"
                   class="form-control rounded-xxl bg-white border-0 ps-4 font-xssss text-grey-900 fw-500 p-2 w-100 social-comment-input"
                   placeholder="Write a comment...">
            @include('livewire.social.partials.emoji-picker', [
                'field' => 'body',
                'label' => 'Add emoji to comment',
            ])
            <button type="button"
                    wire:click.prevent="addComment"
                    class="btn-round-sm bg-current text-white font-xssss ms-2 border-0"
                    wire:loading.attr="disabled"
                    wire:target="addComment">
                <i class="feather-send font-xss" wire:loading.remove wire:target="addComment"></i>
                <span class="spinner-border spinner-border-sm" wire:loading wire:target="addComment" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>
    @error('body') <p class="text-danger font-xssss">{{ $message }}</p> @enderror

    @foreach($comments as $comment)
        @include('livewire.social.partials.comment-item', [
            'comment' => $comment,
            'depth' => 0,
            'supportsReplies' => $supportsReplies,
            'replyingToId' => $replyingToId,
            'maxNestingDepth' => $maxNestingDepth,
            'indentCap' => $indentCap,
        ])
    @endforeach
</div>
