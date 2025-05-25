
<div wire:poll.2s class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3">
    @foreach ($posts as $data )
    <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3">
    <div class="card-body p-0 d-flex">
        <figure class="avatar me-3"><img src="{{ $data->user->avatar ? asset('storage/' . $data->user->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w45"></figure>
        <h4 class="fw-700 text-grey-900 font-xssss mt-1">{{ $data->user->name }} <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">{{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }}</span></h4>
        <a href="#" class="ms-auto" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti-more-alt text-grey-900 btn-round-md bg-greylight font-xss"></i></a>
        <div class="dropdown-menu dropdown-menu-end p-4 rounded-xxl border-0 shadow-lg" aria-labelledby="dropdownMenu2">
            <div class="card-body p-0 d-flex">
                <i class="feather-bookmark text-grey-500 me-3 font-lg"></i>
                <h4 class="fw-600 text-grey-900 font-xssss mt-0 me-4">Save Link <span class="d-block font-xsssss fw-500 mt-1 lh-3 text-grey-500">Add this to your saved items</span></h4>
            </div>
            <div class="card-body p-0 d-flex mt-2">
                <i class="feather-alert-circle text-grey-500 me-3 font-lg"></i>
                <h4 class="fw-600 text-grey-900 font-xssss mt-0 me-4">Hide Post <span class="d-block font-xsssss fw-500 mt-1 lh-3 text-grey-500">Save to your saved items</span></h4>
            </div>
            <div class="card-body p-0 d-flex mt-2">
                <i class="feather-alert-octagon text-grey-500 me-3 font-lg"></i>
                <h4 class="fw-600 text-grey-900 font-xssss mt-0 me-4">Hide all from Group <span class="d-block font-xsssss fw-500 mt-1 lh-3 text-grey-500">Save to your saved items</span></h4>
            </div>
            <div class="card-body p-0 d-flex mt-2">
                <i class="feather-lock text-grey-500 me-3 font-lg"></i>
                <h4 class="fw-600 mb-0 text-grey-900 font-xssss mt-0 me-4">Unfollow Group <span class="d-block font-xsssss fw-500 mt-1 lh-3 text-grey-500">Save to your saved items</span></h4>
            </div>
        </div>
    </div>
    
    
    <div class="card-body p-0 me-lg-5">
        <p class="fw-500 text-grey-700 lh-26 font-xssss w-100"> {{ $data->content }} </a></p>
    </div>
    


    @if(optional($data->media)->isNotEmpty())  
    <div class="row ps-2 pe-2">
        @foreach($data->media as $media)
            @php
                $mediaData = json_decode($media->file, true);
                $mediaPath = $mediaData['media_path'] ?? null;
                $mediaType = $mediaData['media_type'] ?? null;
            @endphp
            @if($mediaType == 'image' && $mediaPath)
                <div class="col-xs-4 col-sm-4 p-1">
                    <a href="{{ asset('storage/' . $mediaPath) }}" 
                       data-lightbox="post-{{ $data->id }}" 
                       data-title="{{ $data->user->name }}'s post"
                       class="position-relative d-block">
                        <img src="{{ asset('storage/' . $mediaPath) }}" 
                             class="rounded-3 w-100" 
                             alt="Post image">
                    </a>
                </div>
            @elseif($mediaType == 'video' && $mediaPath)
                <div class="col-xs-12 col-sm-6 p-1">
                    <video width="100%" height="auto" controls class="rounded-3 w-100">
                        <source src="{{ asset('storage/' . $mediaPath) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            @endif
        @endforeach
    </div>
    @endif



    
    {{-- <div class="card-body d-block p-0">
        <div class="row ps-2 pe-2">

           @if($data->media)
           @foreach(json_decode($data->media->file) as $file)
            
           @if($data->media->filetype == 'image')
            
            <div class="col-xs-4 col-sm-4 p-1"><a href="{{ asset('storage/' . $file) }}" data-lightbox="roadtrip" class="position-relative d-block"><img src="{{ asset('storage/' . $fiel) }}" class="rounded-3 w-100" alt="image"><span class="img-count font-sm text-white ls-3 fw-600"><b></b></span></a></div>
            @elseif($post->media->filetype == 'video')
            <video width="320" height="240" controls>
                <source src="{{ asset('storage/' . $file) }}" type="video/mp4">
            </video>
            @endif
            @endforeach
            @endif
        </div>
    </div> --}}
    





    <div class="card-body d-flex p-0 mt-3">
        <a href="#" wire:click.prevent="like({{ $data->id }})" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2">
            <x-feather-icon name="heart" class="me-1" />
            {{ $data->likes ?? 0 }} Like
        </a>
        <a href="#" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2">
            <x-feather-icon name="message-circle" class="me-1" />
            {{ $data->comments()->count() }} Comments
        </a>
        {{-- <a href="#" id="dropdownMenu21" data-bs-toggle="dropdown" aria-expanded="false" class="ms-auto d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss">
            <x-feather-icon name="share-2" class="me-1" />
            <span class="d-none-xs">Share</span>
        </a> --}}
        <div class="dropdown-menu dropdown-menu-end p-4 rounded-xxl border-0 shadow-lg" aria-labelledby="dropdownMenu21">
            <h4 class="fw-700 font-xss text-grey-900 d-flex align-items-center">Share <i class="feather-x ms-auto font-xssss btn-round-xs bg-greylight text-grey-900 me-2"></i></h4>
            <div class="card-body p-0 d-flex">
                <ul class="d-flex align-items-center justify-content-between mt-2">
                    <li class="me-1"><a href="#" class="btn-round-lg bg-facebook"><i class="font-xs ti-facebook text-white"></i></a></li>
                    <li class="me-1"><a href="#" class="btn-round-lg bg-twiiter"><i class="font-xs ti-twitter-alt text-white"></i></a></li>
                    <li class="me-1"><a href="#" class="btn-round-lg bg-linkedin"><i class="font-xs ti-linkedin text-white"></i></a></li>
                    <li class="me-1"><a href="#" class="btn-round-lg bg-instagram"><i class="font-xs ti-instagram text-white"></i></a></li>
                    <li><a href="#" class="btn-round-lg bg-pinterest"><i class="font-xs ti-pinterest text-white"></i></a></li>
                </ul>
            </div>
            
            <div class="card-body p-0 d-flex">
                <ul class="d-flex align-items-center justify-content-between mt-2">
                    <li class="me-1"><a href="#" class="btn-round-lg bg-tumblr"><i class="font-xs ti-tumblr text-white"></i></a></li>
                    <li class="me-1"><a href="#" class="btn-round-lg bg-youtube"><i class="font-xs ti-youtube text-white"></i></a></li>
                    <li class="me-1"><a href="#" class="btn-round-lg bg-flicker"><i class="font-xs ti-flickr text-white"></i></a></li>
                    <li class="me-1"><a href="#" class="btn-round-lg bg-black"><i class="font-xs ti-vimeo-alt text-white"></i></a></li>
                    <li><a href="#" class="btn-round-lg bg-whatsup"><i class="font-xs feather-phone text-white"></i></a></li>
                </ul>
            </div>
            <h4 class="fw-700 font-xssss mt-4 text-grey-500 d-flex align-items-center mb-3">Copy Link</h4>
            <i class="feather-copy position-absolute right-35 mt-3 font-xs text-grey-500"></i>
            <input type="text" value="https://socia.be/1rGxjoJKVF0" class="bg-grey text-grey-500 font-xssss border-0 lh-32 p-2 font-xssss fw-600 rounded-3 w-100 theme-dark-bg">
        </div>
    </div>

    <!-- Comments Section -->
    <div class="card-body p-0 mt-3">
        <!-- Comment Form -->
        <div class="d-flex align-items-center p-3 bg-greylight rounded-3">
            <figure class="avatar me-2">
                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w35">
            </figure>
            <form wire:submit.prevent="addComment({{ $data->id }})" class="d-flex align-items-center w-100">
                <input type="text" wire:model="newComment" class="form-control rounded-xxl bg-white border-0 ps-4 font-xssss text-grey-500 fw-500 p-2 w-100" placeholder="Write a comment...">
                <button type="submit" class="btn-round-sm bg-current text-white font-xssss ms-2">
                    <x-feather-icon name="send" size="sm" />
                </button>
            </form>
        </div>

        <!-- Comments List -->
        @if($data->comments()->exists())
            <div class="comments-list p-3">
                @foreach($data->comments()->get() as $comment)
                    <div class="d-flex mb-3 p-2 bg-greylight rounded-3">
                        <figure class="avatar me-2">
                            <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w35">
                        </figure>
                        <div class="comment-content w-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="fw-700 text-grey-900 font-xssss mt-0">{{ $comment->user->name }}</h4>
                                <span class="font-xssss fw-500 text-grey-500">{{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}</span>
                            </div>
                            <p class="fw-500 text-grey-700 lh-26 font-xssss mt-1 mb-0">{{ $comment->comment }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    </div>
    @endforeach
</div>
