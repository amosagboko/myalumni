@php
    $depth = $depth ?? 0;
    $maxNestingDepth = $maxNestingDepth ?? \App\Models\Comment::maxNestingDepth();
    $indentCap = $indentCap ?? \App\Models\Comment::indentCap();
    $supportsReplies = $supportsReplies ?? false;
    $replyingToId = $replyingToId ?? null;
    $visualDepth = min($depth, max(0, $indentCap));
    $canReply = $supportsReplies && ($depth + 1) < $maxNestingDepth;
    $children = $comment->relationLoaded('children') ? $comment->children : collect();
@endphp

<div class="comment-thread-item mb-3 {{ $visualDepth > 0 ? 'comment-thread-item--nested' : '' }}"
     @if($visualDepth > 0) style="--comment-depth: {{ $visualDepth }};" @endif>
    <div class="d-flex p-2 bg-greylight rounded-3">
        <figure class="avatar me-2">
            <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : asset('/images/user-8.png') }}"
                 alt="{{ $comment->user->name }}"
                 class="shadow-sm rounded-circle w35">
        </figure>
        <div class="comment-content w-100">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-700 text-grey-900 font-xssss mt-0 mb-0">{{ $comment->user->name }}</h4>
                <span class="font-xssss fw-500 text-grey-500">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p class="fw-500 text-grey-900 lh-26 font-xssss mt-1 mb-0">{{ $comment->comment }}</p>

            @if($canReply)
                <button type="button"
                        wire:click.prevent="startReply({{ $comment->id }})"
                        class="btn btn-link font-xsssss fw-600 text-primary p-0 mt-1 border-0 text-decoration-none">
                    Reply
                </button>
            @endif

            @if($replyingToId === $comment->id)
                <div class="d-flex align-items-center mt-2 gap-2">
                    <input type="text"
                           wire:model="replyBody"
                           wire:keydown.enter.prevent="addReply"
                           class="form-control rounded-xxl bg-white border-0 ps-3 font-xssss text-grey-900 fw-500 p-2 social-comment-input"
                           placeholder="Write a reply...">
                    @include('livewire.social.partials.emoji-picker', [
                        'field' => 'replyBody',
                        'label' => 'Add emoji to reply',
                    ])
                    <button type="button"
                            wire:click.prevent="addReply"
                            class="btn-round-sm bg-current text-white font-xssss border-0">
                        <i class="feather-send font-xss"></i>
                    </button>
                    <button type="button"
                            wire:click.prevent="cancelReply"
                            class="btn btn-link font-xsssss text-grey-500 p-0 border-0">
                        Cancel
                    </button>
                </div>
                @error('replyBody') <p class="text-danger font-xssss mt-1 mb-0">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>

    @foreach($children as $child)
        @include('livewire.social.partials.comment-item', [
            'comment' => $child,
            'depth' => $depth + 1,
            'supportsReplies' => $supportsReplies,
            'replyingToId' => $replyingToId,
            'maxNestingDepth' => $maxNestingDepth,
            'indentCap' => $indentCap,
        ])
    @endforeach
</div>
