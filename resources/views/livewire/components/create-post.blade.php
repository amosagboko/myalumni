<div>
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card w-100 shadow-xss rounded-xxl border-0 ps-4 pt-4 pe-4 pb-3 mb-3">
        <form wire:submit.prevent="createPost" class="card w-100 shadow-xss rounded-xxl border-0 ps-4 pt-4 pe-4 pb-3 mb-3">
            <div class="card-body p-0">
                <a href="#" class="font-xssss fw-600 text-grey-500 card-body p-0 d-flex">
                    <i class="btn-round-sm font-xs text-primary fa fa-pencil me-2 bg-greylight"></i>
                    Create Post
                </a>
                <textarea wire:model="content" class="h100 bor-0 w-100 rounded-xxl p-2 ps-5 font-xssss text-grey-500 fw-500 border-light-md theme-dark-bg" cols="30" rows="10" placeholder="What's on your mind?"></textarea>
            </div>
            <div class="card-body d-flex p-0 mt-0">
                <div class="d-flex align-items-center">
                    <div class="position-relative">
                        <input type="file" wire:model="images" multiple accept="image/*" class="d-none" id="image-upload">
                        <label for="image-upload" class="btn-round-md bg-greylight font-xss text-grey-500 cursor-pointer">
                            <i class="text-grey-900 btn-round-md bg-greylight font-xss">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            </i>
                        </label>
                    </div>
                </div>
                <div class="ms-auto">
                    <button type="submit" class="btn-round-md bg-primary text-white font-xssss" wire:loading.attr="disabled">
                        <span wire:loading.remove>Post</span>
                        <span wire:loading>Posting...</span>
                    </button>
                </div>
            </div>

            <!-- File size limits info -->
            <div class="card-body p-0 mt-2">
                <div class="d-flex align-items-center">
                    <i class="fa fa-info-circle text-primary me-2"></i>
                    <div class="d-flex align-items-center">
                        <i class="fa fa-expand text-grey-500 me-1"></i>
                        <span class="font-xssss text-grey-500"></span>
                        <div class="d-flex align-items-center ms-2">
                            <i class="fa fa-file-image-o text-grey-500 me-1" title="JPG"></i>
                            <i class="fa fa-file-image-o text-grey-500 me-1" title="PNG"></i>
                            <i class="fa fa-file-image-o text-grey-500 me-1" title="GIF"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload progress -->
            @if($isUploading)
            <div class="card-body p-0 mt-2">
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $uploadProgress }}%;" 
                         aria-valuenow="{{ $uploadProgress }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <p class="font-xssss text-grey-500 mt-1 text-center">
                    <i class="fa fa-upload me-1"></i> Uploading... {{ $uploadProgress }}%
                </p>
            </div>
            @endif

            <!-- Preview selected files -->
            @if(count($images) > 0)
            <div class="card-body p-0 mt-2">
                <div class="row">
                    @foreach($images as $index => $image)
                    <div class="col-4 mb-2">
                        <div class="position-relative">
                            <img src="{{ $image->temporaryUrl() }}" class="w-100 rounded-3" alt="Preview">
                            <button wire:click="removeImage({{ $index }})" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- Add Font Awesome and Material Icons CDN -->
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
    .btn-round-md {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }
    .material-icons {
        font-size: 20px;
        line-height: 1;
    }
    [title]:hover:after {
        content: attr(title);
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1;
    }
</style>
@endpush
