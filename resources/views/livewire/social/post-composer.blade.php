<div>
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card w-100 shadow-xss rounded-xxl border-0 ps-4 pt-4 pe-4 pb-3 mb-3 post-composer-card">
        <div>
            <div class="card-body p-0">
                <span class="font-xssss fw-700 text-grey-900 card-body p-0 d-flex align-items-center">
                    <i class="btn-round-sm font-xs text-primary feather-edit-3 me-2 bg-greylight"></i>Create Post
                </span>
            </div>
            <div class="card-body p-0 mt-3 position-relative">
                <figure class="avatar position-absolute ms-2 mt-1 top-5">
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w30">
                </figure>
                <textarea wire:model.live="content"
                          wire:keydown.ctrl.enter.prevent="createPost"
                          class="h100 bor-0 w-100 rounded-xxl p-2 ps-5 font-xssss text-grey-900 fw-500 border-light-md theme-dark-bg post-composer-textarea"
                          cols="30"
                          rows="4"
                          placeholder="What's on your mind?"></textarea>
            </div>

            <div class="card-body d-flex flex-wrap p-0 mt-2 gap-2 align-items-center">
                @if($supportsVisibility ?? true)
                <select wire:model.live="visibility" class="form-select form-select-sm w-auto font-xssss fw-500 text-grey-900 rounded-xl border-0 bg-greylight post-composer-select">
                    @foreach($visibilityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @endif

                @if($shareableEvents->isNotEmpty())
                <select wire:model.live="sharedEventId" class="form-select form-select-sm font-xssss fw-500 text-grey-900 rounded-xl border-0 bg-greylight flex-grow-1 post-composer-select">
                    <option value="">Share an official event (optional)</option>
                    @foreach($shareableEvents as $event)
                        <option value="{{ $event->id }}">{{ $event->eventname }} — {{ $event->date?->format('M j, Y') }}</option>
                    @endforeach
                </select>
                @endif
            </div>

            <div class="card-body d-flex p-0 mt-3 align-items-center">
                <div class="position-relative">
                    <input type="file" wire:model="images" multiple accept="image/*" class="d-none" id="social-image-upload">
                    <label for="social-image-upload" class="d-flex align-items-center font-xssss fw-600 ls-1 text-grey-700 text-dark pe-4 mb-0 cursor-pointer">
                        <i class="font-md text-success feather-image me-2"></i><span class="d-none-xs">Photo</span>
                    </label>
                </div>
                <div class="position-relative">
                    <input type="file" wire:model="videos" multiple accept="video/mp4,video/quicktime,video/x-msvideo" class="d-none" id="social-video-upload">
                    <label for="social-video-upload" class="d-flex align-items-center font-xssss fw-600 ls-1 text-grey-700 text-dark pe-4 mb-0 cursor-pointer">
                        <i class="font-md text-danger feather-video me-2"></i><span class="d-none-xs">Video</span>
                    </label>
                </div>
                <button type="button"
                        wire:click.prevent="createPost"
                        class="ms-auto btn-round-md bg-primary-gradiant text-white font-xssss fw-600 px-3"
                        wire:loading.attr="disabled"
                        wire:target="createPost">
                    <span wire:loading.remove wire:target="createPost">Post</span>
                    <span wire:loading wire:target="createPost">Posting...</span>
                </button>
            </div>

            @error('content') <p class="text-danger font-xssss mt-2 mb-0">{{ $message }}</p> @enderror

            @if($isUploading)
            <div class="card-body p-0 mt-2">
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $uploadProgress }}%;"></div>
                </div>
            </div>
            @endif

            @if(count($images) > 0 || count($videos) > 0)
            <div class="card-body p-0 mt-2">
                @if(count($images) > 0)
                    @include('livewire.social.partials.post-composer-image-preview', [
                        'images' => $images,
                        'lightboxGroup' => 'composer-preview-'.auth()->id(),
                    ])
                @endif
                @if(count($videos) > 0)
                    <p class="font-xssss text-grey-500 mt-2 mb-0 px-2">{{ count($videos) }} video(s) selected</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
