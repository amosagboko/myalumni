<div class="card-body p-0 mt-2 border-top-xs pt-3">
    <div class="d-flex align-items-center p-3 bg-greylight rounded-3 mb-3">
        <figure class="avatar me-2">
            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w35">
        </figure>
        <form wire:submit.prevent="addComment" class="d-flex align-items-center w-100">
            <input type="text"
                   wire:model="body"
                   class="form-control rounded-xxl bg-white border-0 ps-4 font-xssss text-grey-900 fw-500 p-2 w-100 social-comment-input"
                   placeholder="Write a comment...">
            <button type="submit" class="btn-round-sm bg-current text-white font-xssss ms-2 border-0">
                <i class="feather-send font-xss"></i>
            </button>
        </form>
    </div>
    @error('body') <p class="text-danger font-xssss">{{ $message }}</p> @enderror

    @foreach($comments as $comment)
        @include('livewire.social.partials.comment-item', [
            'comment' => $comment,
            'depth' => 0,
            'supportsReplies' => $supportsReplies,
        ])
    @endforeach
</div>

@once
    @push('styles')
    <style>
        .social-comment-input::placeholder {
            color: #8a94a6;
            opacity: 1;
        }
    </style>
    @endpush
@endonce
