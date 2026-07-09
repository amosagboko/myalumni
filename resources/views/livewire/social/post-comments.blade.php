<div class="card-body p-0 mt-2 border-top-xs pt-3">
    <div class="d-flex align-items-center p-3 bg-greylight rounded-3 mb-3">
        <figure class="avatar me-2">
            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w35">
        </figure>
        <form wire:submit.prevent="addComment" class="d-flex align-items-center w-100">
            <input type="text" wire:model="body" class="form-control rounded-xxl bg-white border-0 ps-4 font-xssss text-grey-500 fw-500 p-2 w-100" placeholder="Write a comment...">
            <button type="submit" class="btn-round-sm bg-current text-white font-xssss ms-2 border-0">
                <i class="feather-send font-xss"></i>
            </button>
        </form>
    </div>
    @error('body') <p class="text-danger font-xssss">{{ $message }}</p> @enderror

    @foreach($comments as $comment)
        <div class="d-flex mb-3 p-2 bg-greylight rounded-3">
            <figure class="avatar me-2">
                <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : asset('/images/user-8.png') }}" alt="{{ $comment->user->name }}" class="shadow-sm rounded-circle w35">
            </figure>
            <div class="comment-content w-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-700 text-grey-900 font-xssss mt-0 mb-0">{{ $comment->user->name }}</h4>
                    <span class="font-xssss fw-500 text-grey-500">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="fw-500 text-grey-700 lh-26 font-xssss mt-1 mb-0">{{ $comment->comment }}</p>
            </div>
        </div>
    @endforeach
</div>
