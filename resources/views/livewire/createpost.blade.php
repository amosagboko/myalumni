
<div>
    <form wire:submit.prevent="createPost" class="card w-100 shadow-xss rounded-xxl border-0 ps-4 pt-4 pe-4 pb-3 mb-3">
        <div class="card-body p-0">
            <a href="#" class=" font-xssss fw-600 text-grey-500 card-body p-0 d-flex align-items-center"><i class="btn-round-sm font-xs text-primary me-2 bg-greylight">
                <svg width="24px" height="24px" viewBox="0 -4.5 151 151" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0)">
                    <path d="M117.311 13.5735L121.577 9.53334C119.566 7.46382 117.073 7.35342 114.623 7.38215C111.424 7.46318 108.328 8.53109 105.76 10.4396C100.975 13.866 96.3053 17.4493 91.7491 21.1895C85.8416 26.0373 80.2482 31.2784 74.2246 35.9704C71.5297 38.0693 68.1289 39.2692 65.0159 40.8107C64.5937 41.0195 63.9797 40.8394 62.8671 40.8394C63.1863 39.4595 63.1563 38.0834 63.7856 37.1543C65.3031 34.913 66.8497 32.551 68.8776 30.8225C77.3587 23.5993 85.8965 16.4291 94.6802 9.58264C98.4531 6.6136 102.59 4.13862 106.99 2.2177C114.117 -0.833911 121.077 -0.164135 127.124 5.25328C129.88 4.31207 132.583 3.15627 135.401 2.4743C137.746 1.88374 140.216 2.02811 142.476 2.88773C144.737 3.74736 146.678 5.28083 148.039 7.28069C151.071 11.7843 151.402 17.6851 148.127 21.708C144.704 25.9128 140.693 29.6386 136.555 33.9763C137.666 37.0815 136.132 39.987 133.403 42.575C123.337 52.1238 113.564 61.9995 103.212 71.2255C88.8614 84.0162 74.0689 96.3095 59.5071 108.864C54.2093 113.428 48.8763 117.964 43.8522 122.817C38.7691 127.766 32.7171 131.61 26.0769 134.105C20.6718 136.148 15.2725 138.202 9.84181 140.171C8.52705 140.702 7.13225 141.009 5.71586 141.077C2.47649 141.094 0.578865 138.413 1.3844 135.233C1.49197 134.81 1.64319 134.399 1.83566 134.007C6.5165 124.511 11.2099 115.021 15.9159 105.537C16.4166 104.572 17.0726 103.696 17.8576 102.943C31.1546 89.9732 44.4267 76.9763 57.8099 64.0955C72.0595 50.3811 86.3714 36.732 100.747 23.1485C103.439 20.5943 106.428 18.3517 109.101 15.7796C111.527 13.4521 114.135 12.3916 117.311 13.5735ZM64.5995 94.8147C86.2419 76.4431 107.25 57.9243 126.736 37.6153L114.104 20.6881L50.9954 80.3599L64.5995 94.8147ZM45.979 85.3291L23.7797 106.056C27.6733 111.067 31.1725 116.254 35.1905 120.941L57.5801 100.515C53.7337 95.4807 49.9652 90.5453 45.979 85.3291ZM130.166 27.914C132.434 26.1995 134.634 24.7487 136.574 23.0125C138.509 21.2662 140.306 19.3718 141.947 17.3466C143.486 15.4629 143.356 13.2115 142.182 11.2128C140.999 9.20143 138.981 8.76837 136.77 9.16107C136.123 9.24453 135.484 9.38092 134.86 9.56859C129.836 11.3682 125.287 14.2839 121.552 18.0964C124.734 21.7195 127.807 25.2257 130.166 27.914ZM18.0932 112.29C15.7353 118.653 13.5471 124.56 11.3584 130.466L11.9597 130.883L26.7242 125.279L18.0932 112.29Z" fill="#000000"/>
                    </g>
                    <defs>
                    <clipPath id="clip0">
                    <rect width="150" height="141.118" fill="white" transform="translate(0.777344)"/>
                    </clipPath>
                    </defs>
                    </svg>
            </i>Create Post</a>
        </div>
        <div class="card-body p-0 mt-3 position-relative">
            <figure class="avatar position-absolute ms-2 mt-1 top-5"><img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="shadow-sm rounded-circle w30"></figure>
            <textarea wire:model.lazy="content" required name="content" class="h100 bor-0 w-100 rounded-xxl p-2 ps-5 font-xssss text-grey-900 fw-500 border-light-md theme-dark-bg" cols="30" rows="10" placeholder="What's on your mind?"></textarea>
        </div>
    
        @error('content')
        ('content') <span class="error">{{ $message }}</span>
        @enderror
    
        <div wire:loading wire:target="images">Uploading ....</div>
        <div wire:loading wire:target="videos">Uploading ....</div>
    
        @if ($images)
            @foreach ($images as $image)
                <img src="{{ $image->temporaryUrl() }}" alt="image" width="width:25px">
            @endforeach
        @endif
    
        @if ($videos)
                @foreach ($videos as $video)
                <video src="{{ $video->temporaryUrl() }}" alt="" width="width:100px"></video>
                @endforeach
        @endif
    
        <style>
            .upload-btn-wrapper{
                position: relative;
                overflow: hidden;
                display: inline-block;
                
            }
    
            .upload-btn-wrapper input[type=file]{
                font-size: 100px;
                position: absolute;
                left: 0;
                top: 0;
                opacity: 0;
            }

            
        </style>
    
        <div class="card-body d-flex p-0 mt-0">
            <a href="#" class="d-flex align-items-center font-xssss fw-600 ls-1 text-grey-700 text-dark pe-4 upload-btn-wrapper"><i class="font-md text-danger me-2">
                <svg fill="#000000" width="24px" height="24px" viewBox="-0.5 0 19 19" xmlns="http://www.w3.org/2000/svg" class="cf-icon-svg"><path d="M16.205 6.813v7.29a1.118 1.118 0 0 1-1.115 1.115H2.91a1.118 1.118 0 0 1-1.115-1.115v-7.29A1.118 1.118 0 0 1 2.91 5.698h2.902v-.365a1.118 1.118 0 0 1 1.115-1.115h4.146a1.118 1.118 0 0 1 1.115 1.115v.365h2.902a1.118 1.118 0 0 1 1.115 1.115zm-3.789 3.645A3.416 3.416 0 1 0 9 13.874a3.416 3.416 0 0 0 3.416-3.416zM10.627 8.83A2.301 2.301 0 1 1 9 8.157a2.286 2.286 0 0 1 1.627.674zm-.643-3.262a.558.558 0 1 0 .558-.557.558.558 0 0 0-.558.557z"/></svg>    
            </i><span class="d-none-xs">Photo
            <input type="file" multiple id="file" wire:model='images'>    
            </span></a>
            {{-- <a href="#" class="d-flex align-items-center font-xssss fw-600 ls-1 text-grey-700 text-dark pe-4 upload-btn-wrapper"><i class="font-md text-success me-2">
                <svg width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none"><path fill="#000000" fill-rule="evenodd" d="M5 5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1.586l2.293 2.293A1 1 0 0 0 22 16V8a1 1 0 0 0-1.707-.707L18 9.586V8a3 3 0 0 0-3-3H5z" clip-rule="evenodd"/></svg>    
            </i><span class="d-none-xs">Video
                <input type="file" multiple id="file" wire:model='videos'>    
            </span></a> --}}
            {{-- <a href="#" class="d-flex align-items-center font-xssss fw-600 ls-1 text-grey-700 text-dark pe-4"><i class="font-md text-warning me-2">
                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="9.5" stroke="#222222" stroke-linecap="round"/>
                    <path d="M8.20857 15.378C8.63044 15.7433 9.20751 16.0237 9.86133 16.2124C10.5191 16.4023 11.256 16.5 12 16.5C12.744 16.5 13.4809 16.4023 14.1387 16.2124C14.7925 16.0237 15.3696 15.7433 15.7914 15.378" stroke="#222222" stroke-linecap="round"/>
                    <circle cx="9" cy="10" r="1" fill="#222222" stroke="#222222" stroke-linecap="round"/>
                    <circle cx="15" cy="10" r="1" fill="#222222" stroke="#222222" stroke-linecap="round"/>
                    </svg>    
            </i><span class="d-none-xs">Feeling/Activity</span></a> --}}
            <button style="outline: none; border: none; border-radius: 43px;" type="submit" class="outline-none ms-auto botder-none bg-none">
                <i class=" text-grey-900 btn-round-md bg-greylight font-xss">
                    <svg width="24px" height="24px" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.9541 0.709802C14.93 0.761862 14.8965 0.810638 14.8536 0.853553L5.40076 10.3064L8.07126 14.7573C8.16786 14.9183 8.34653 15.0116 8.53386 14.9989C8.72119 14.9862 8.88561 14.8696 8.95958 14.697L14.9541 0.709802Z" fill="#000000"/><path d="M4.69366 9.59931L0.242756 6.92876C0.0817496 6.83216 -0.0115621 6.65349 0.00115182 6.46616C0.0138657 6.27883 0.130462 6.11441 0.303045 6.04044L14.293 0.0447451C14.2399 0.0688812 14.1902 0.102782 14.1465 0.146447L4.69366 9.59931Z" fill="#000000"/></svg>
                </i>
            </button>
            
            {{-- <div class="dropdown-menu dropdown-menu-start p-4 rounded-xxl border-0 shadow-lg" aria-labelledby="dropdownMenu4">
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
            </div> --}}
        </div>
    </form>
</div>
