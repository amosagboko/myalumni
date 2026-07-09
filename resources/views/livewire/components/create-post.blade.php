<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card w-100 shadow-xss rounded-xxl border-0 ps-4 pt-4 pe-4 pb-3 mb-3">
        <form wire:submit.prevent="createPost">
            <div class="card-body p-0">
                <a href="#" class="font-xssss fw-600 text-grey-500 card-body p-0 d-flex align-items-center" onclick="return false;">
                    <i class="btn-round-sm font-xs text-primary feather-edit-3 me-2 bg-greylight"></i>Create Post
                </a>
            </div>
            <div class="card-body p-0 mt-3 position-relative">
                <figure class="avatar position-absolute ms-2 mt-1 top-5">
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w30">
                </figure>
                <textarea wire:model="content" name="message" class="h100 bor-0 w-100 rounded-xxl p-2 ps-5 font-xssss text-grey-500 fw-500 border-light-md theme-dark-bg" cols="30" rows="4" placeholder="What's on your mind?"></textarea>
            </div>
            <div class="card-body d-flex p-0 mt-0 align-items-center">
                <div class="position-relative">
                    <input type="file" wire:model="images" multiple accept="image/*" class="d-none" id="image-upload">
                    <label for="image-upload" class="d-flex align-items-center font-xssss fw-600 ls-1 text-grey-700 text-dark pe-4 mb-0 cursor-pointer">
                        <i class="font-md text-success feather-image me-2"></i><span class="d-none-xs">Photo/Video</span>
                    </label>
                </div>
                <button type="submit" class="ms-auto btn-round-md bg-primary-gradiant text-white font-xssss fw-600" wire:loading.attr="disabled">
                    <span wire:loading.remove>Post</span>
                    <span wire:loading>Posting...</span>
                </button>
            </div>

            @if($isUploading)
            <div class="card-body p-0 mt-2">
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $uploadProgress }}%;" aria-valuenow="{{ $uploadProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="font-xssss text-grey-500 mt-1 mb-0">Uploading... {{ $uploadProgress }}%</p>
            </div>
            @endif

            @if(count($images) > 0)
            <div class="card-body p-0 mt-2">
                <div class="row ps-2 pe-2">
                    @foreach($images as $index => $image)
                    <div class="col-xs-4 col-sm-4 p-1">
                        <div class="position-relative">
                            <img src="{{ $image->temporaryUrl() }}" class="rounded-3 w-100" alt="Preview">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

@push('styles')
<style>
    .cursor-pointer { cursor: pointer; }
</style>
@endpush
