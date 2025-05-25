<x-alumniadmin-dashboard>
    <!-- main content -->
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <!-- loader wrapper -->
                <div class="preloader-wrap p-3">
                    <div class="box shimmer">
                        <div class="lines">
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                        </div>
                    </div>
                    <div class="box shimmer mb-3">
                        <div class="lines">
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                        </div>
                    </div>
                    <div class="box shimmer">
                        <div class="lines">
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                            <div class="line s_shimmer"></div>
                        </div>
                    </div>
                </div>
                <!-- loader wrapper -->
                <div class="row feed-body">
                    <div class="col-xl-8 col-xxl-9 col-lg-8">


                        
                       


                        @livewire('createpost')
                        {{-- <livewire:counter /> --}}
                        
                        <livewire:returnpost />
                                                
                    
                        {{-- <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3">
                            <div class="card-body p-0 d-flex">
                                <figure class="avatar me-3"><img src="{{ auth()->user()->avatar ? auth()->user()->avatar: '/images/user-8.png' }}" alt="image" class="shadow-sm rounded-circle w45"></figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-1">Surfiya Zakizaki  <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">3 hour ago</span></h4>
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
                                <p class="fw-500 text-grey-500 lh-26 font-xssss w-100">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nulla dolor, ornare at commodo non, feugiat non nisi. Phasellus faucibus mollis pharetra. Proin blandit ac massa sed rhoncus <a href="#" class="fw-600 text-primary ms-2">See more</a></p>
                            </div>
                            <div class="card-body d-block p-0">
                                <div class="row ps-2 pe-2">
                                    <div class="col-xs-4 col-sm-4 p-1"><a href="images/t-10.jpg" data-lightbox="roadtrip"><img src="images/t-10.jpg" class="rounded-3 w-100" alt="image"></a></div>
                                    <div class="col-xs-4 col-sm-4 p-1"><a href="images/t-11.jpg" data-lightbox="roadtrip"><img src="images/t-11.jpg" class="rounded-3 w-100" alt="image"></a></div>
                                    <div class="col-xs-4 col-sm-4 p-1"><a href="images/t-12.jpg" data-lightbox="roadtrip" class="position-relative d-block"><img src="images/t-12.jpg" class="rounded-3 w-100" alt="image"><span class="img-count font-sm text-white ls-3 fw-600"><b>+2</b></span></a></div>
                                </div>
                            </div>
                            <div class="card-body d-flex p-0 mt-3">
                                <a href="#" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2"><i class="feather-thumbs-up text-white bg-primary-gradiant me-1 btn-round-xs font-xss"></i> <i class="feather-heart text-white bg-red-gradiant me-2 btn-round-xs font-xss"></i>2.8K Like</a>
                                <div class="emoji-wrap">
                                    <ul class="emojis list-inline mb-0">
                                        <li class="emoji list-inline-item"><i class="em em---1"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-angry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-anguished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-astonished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-blush"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-clap"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-cry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-full_moon_with_face"></i></li>
                                    </ul>
                                </div>
                                <a href="#" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg"></i><span class="d-none-xss">22 Comment</span></a>
                                <a href="#" id="dropdownMenu21" data-bs-toggle="dropdown" aria-expanded="false" class="ms-auto d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-share-2 text-grey-900 text-dark btn-round-sm font-lg"></i><span class="d-none-xs">Share</span></a>
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
                        </div> --}}
                    
                        
                        <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-0">
                            <div class="card-body p-0 d-flex">
                                <figure class="avatar me-3 m-0"><img src="images/user-8.png" alt="image" class="shadow-sm rounded-circle w45"></figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-1">Goria Coast  <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">2 hour ago</span></h4>
                                <a href="#" class="ms-auto" id="dropdownMenu6" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti-more-alt text-grey-900 btn-round-md bg-greylight font-xss"></i></a>
                                <div class="dropdown-menu dropdown-menu-end p-4 rounded-xxl border-0 shadow-lg" aria-labelledby="dropdownMenu6">
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
                                <p class="fw-500 text-grey-500 lh-26 font-xssss w-100">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nulla dolor, ornare at commodo non, feugiat non nisi. Phasellus faucibus mollis pharetra. Proin blandit ac massa sed rhoncus <a href="#" class="fw-600 text-primary ms-2">See more</a></p>
                            </div>
                            <div class="card-body d-flex p-0">
                                <a href="#" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2"><i class="feather-thumbs-up text-white bg-primary-gradiant me-1 btn-round-xs font-xss"></i> <i class="feather-heart text-white bg-red-gradiant me-2 btn-round-xs font-xss"></i>2.8K Like</a>
                                <div class="emoji-wrap">
                                    <ul class="emojis list-inline mb-0">
                                        <li class="emoji list-inline-item"><i class="em em---1"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-angry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-anguished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-astonished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-blush"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-clap"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-cry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-full_moon_with_face"></i></li>
                                    </ul>
                                </div>
                                <a href="#" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg"></i><span class="d-none-xss">22 Comment</span></a>
                                <a href="#" id="dropdownMenu31" data-bs-toggle="dropdown" aria-expanded="false" class="ms-auto d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-share-2 text-grey-900 text-dark btn-round-sm font-lg"></i><span class="d-none-xs">Share</span></a>
                                <div class="dropdown-menu dropdown-menu-end p-4 rounded-xxl border-0 shadow-lg" aria-labelledby="dropdownMenu31">
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
                        </div>

                        <div class="card w-100 shadow-none bg-transparent bg-transparent-card border-0 p-0 mb-0">
                            <div class="owl-carousel category-card owl-theme overflow-hidden nav-none">
                                <div class="item">
                                    <div class="card w200 d-block border-0 shadow-xss rounded-xxl overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body position-relative h100 bg-image-cover bg-image-center" style="background-image: url(images/u-bg.jpg);"></div>
                                        <div class="card-body d-block w-100 ps-4 pe-4 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 mt--6 position-relative w75 z-index-1"><img src="images/user-11.png" alt="image" class="float-right p-1 bg-white rounded-circle w-100"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xsss mt-2 mb-1">Aliqa Macale </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-2">support@gmail.com</p>
                                            <span class="live-tag mt-2 mb-0 bg-danger p-2 z-index-1 rounded-3 text-white font-xsssss text-uppersace fw-700 ls-3">LIVE</span>
                                            <div class="clearfix mb-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="item">
                                    <div class="card w200 d-block border-0 shadow-xss rounded-xxl overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body position-relative h100 bg-image-cover bg-image-center" style="background-image: url(images/s-2.jpg);"></div>
                                        <div class="card-body d-block w-100 ps-4 pe-4 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 mt--6 position-relative w75 z-index-1"><img src="images/user-2.png" alt="image" class="float-right p-1 bg-white rounded-circle w-100"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xsss mt-2 mb-1">Seary Victor </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-2">support@gmail.com</p>
                                            <span class="live-tag mt-2 mb-0 bg-danger p-2 z-index-1 rounded-3 text-white font-xsssss text-uppersace fw-700 ls-3">LIVE</span>
                                            <div class="clearfix mb-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="item">
                                    <div class="card w200 d-block border-0 shadow-xss rounded-xxl overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body position-relative h100 bg-image-cover bg-image-center" style="background-image: url(images/s-6.jpg);"></div>
                                        <div class="card-body d-block w-100 ps-4 pe-4 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 mt--6 position-relative w75 z-index-1"><img src="images/user-3.png" alt="image" class="float-right p-1 bg-white rounded-circle w-100"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xsss mt-2 mb-1">John Steere </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-2">support@gmail.com</p>
                                            <span class="live-tag mt-2 mb-0 bg-danger p-2 z-index-1 rounded-3 text-white font-xsssss text-uppersace fw-700 ls-3">LIVE</span>
                                            <div class="clearfix mb-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="item">
                                    <div class="card w200 d-block border-0 shadow-xss rounded-xxl overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body position-relative h100 bg-image-cover bg-image-center" style="background-image: url(images/bb-16.png);"></div>
                                        <div class="card-body d-block w-100 ps-4 pe-4 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 mt--6 position-relative w75 z-index-1"><img src="images/user-4.png" alt="image" class="float-right p-1 bg-white rounded-circle w-100"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xsss mt-2 mb-1">Mohannad Zitoun </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-2">support@gmail.com</p>
                                            <span class="live-tag mt-2 mb-0 bg-danger p-2 z-index-1 rounded-3 text-white font-xsssss text-uppersace fw-700 ls-3">LIVE</span>
                                            <div class="clearfix mb-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="item">
                                    <div class="card w200 d-block border-0 shadow-xss rounded-xxl overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body position-relative h100 bg-image-cover bg-image-center" style="background-image: url(images/e-4.jpg);"></div>
                                        <div class="card-body d-block w-100 ps-4 pe-4 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 mt--6 position-relative w75 z-index-1"><img src="images/user-7.png" alt="image" class="float-right p-1 bg-white rounded-circle w-100"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xsss mt-2 mb-1">Studio Express </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-2">support@gmail.com</p>
                                            <span class="live-tag mt-2 mb-0 bg-danger p-2 z-index-1 rounded-3 text-white font-xsssss text-uppersace fw-700 ls-3">LIVE</span>
                                            <div class="clearfix mb-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="item">
                                    <div class="card w200 d-block border-0 shadow-xss rounded-xxl overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body position-relative h100 bg-image-cover bg-image-center" style="background-image: url(images/coming-soon.png);"></div>
                                        <div class="card-body d-block w-100 ps-4 pe-4 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 mt--6 position-relative w75 z-index-1"><img src="images/user-5.png" alt="image" class="float-right p-1 bg-white rounded-circle w-100"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xsss mt-2 mb-1">Hendrix Stamp </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-2">support@gmail.com</p>
                                            <span class="live-tag mt-2 mb-0 bg-danger p-2 z-index-1 rounded-3 text-white font-xsssss text-uppersace fw-700 ls-3">LIVE</span>
                                            <div class="clearfix mb-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="item">
                                    <div class="card w200 d-block border-0 shadow-xss rounded-xxl overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body position-relative h100 bg-image-cover bg-image-center" style="background-image: url(images/bb-9.jpg);"></div>
                                        <div class="card-body d-block w-100 ps-4 pe-4 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 mt--6 position-relative w75 z-index-1"><img src="images/user-6.png" alt="image" class="float-right p-1 bg-white rounded-circle w-100"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xsss mt-2 mb-1">Mohannad Zitoun </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-2">support@gmail.com</p>
                                            <span class="live-tag mt-2 mb-0 bg-danger p-2 z-index-1 rounded-3 text-white font-xsssss text-uppersace fw-700 ls-3">LIVE</span>
                                            <div class="clearfix mb-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3">
                            <div class="card-body p-0 d-flex">
                                <figure class="avatar me-3"><img src="images/user-8.png" alt="image" class="shadow-sm rounded-circle w45"></figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-1">Anthony Daugloi <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">2 hour ago</span></h4>
                                <a href="#" class="ms-auto" id="dropdownMenu5" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti-more-alt text-grey-900 btn-round-md bg-greylight font-xss"></i></a>
                                <div class="dropdown-menu dropdown-menu-start p-4 rounded-xxl border-0 shadow-lg" aria-labelledby="dropdownMenu5">
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
                            <div class="card-body p-0 mb-3 rounded-3 overflow-hidden">
                                <a href="default-video.html" class="video-btn">
                                    <video autoplay loop class="float-right w-100">
                                        <source src="images/v-2.mp4" type="video/mp4">
                                    </video>
                                </a>
                            </div>
                            <div class="card-body p-0 me-lg-5">
                                <p class="fw-500 text-grey-500 lh-26 font-xssss w-100 mb-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nulla dolor, ornare at commodo non, feugiat non nisi. Phasellus faucibus mollis pharetra. Proin blandit ac massa sed rhoncus <a href="#" class="fw-600 text-primary ms-2">See more</a></p>
                            </div>
                            <div class="card-body d-flex p-0">
                                <a href="#" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2"><i class="feather-thumbs-up text-white bg-primary-gradiant me-1 btn-round-xs font-xss"></i> <i class="feather-heart text-white bg-red-gradiant me-2 btn-round-xs font-xss"></i>2.8K Like</a>
                                <div class="emoji-wrap">
                                    <ul class="emojis list-inline mb-0">
                                        <li class="emoji list-inline-item"><i class="em em---1"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-angry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-anguished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-astonished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-blush"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-clap"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-cry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-full_moon_with_face"></i></li>
                                    </ul>
                                </div>
                                <a href="#" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg"></i><span class="d-none-xss">22 Comment</span></a>
                                <a href="#" class="ms-auto d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-share-2 text-grey-900 text-dark btn-round-sm font-lg"></i><span class="d-none-xs">Share</span></a>
                            </div>
                        </div>

                        <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-0">
                            <div class="card-body p-0 d-flex">
                                <figure class="avatar me-3"><img src="images/user-8.png" alt="image" class="shadow-sm rounded-circle w45"></figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-1">Anthony Daugloi <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">2 hour ago</span></h4>
                                <a href="#" class="ms-auto"><i class="ti-more-alt text-grey-900 btn-round-md bg-greylight font-xss"></i></a>
                            </div>

                            <div class="card-body p-0 me-lg-5">
                                <p class="fw-500 text-grey-500 lh-26 font-xssss w-100">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nulla dolor, ornare at commodo non, feugiat non nisi. Phasellus faucibus mollis pharetra. Proin blandit ac massa sed rhoncus <a href="#" class="fw-600 text-primary ms-2">See more</a></p>
                            </div>
                            <div class="card-body d-block p-0 mb-3">
                                <div class="row ps-2 pe-2">
                                    <div class="col-xs-6 col-sm-6 p-1"><a href="images/t-36.jpg" data-lightbox="roadtri"><img src="images/t-21.jpg" class="rounded-3 w-100" alt="image"></a></div>
                                    <div class="col-xs-6 col-sm-6 p-1"><a href="images/t-32.jpg" data-lightbox="roadtri"><img src="images/t-22.jpg" class="rounded-3 w-100" alt="image"></a></div>
                                </div>
                                <div class="row ps-2 pe-2">
                                    <div class="col-xs-4 col-sm-4 p-1"><a href="images/t-33.jpg" data-lightbox="roadtri"><img src="images/t-23.jpg" class="rounded-3 w-100" alt="image"></a></div>
                                    <div class="col-xs-4 col-sm-4 p-1"><a href="images/t-34.jpg" data-lightbox="roadtri"><img src="images/t-24.jpg" class="rounded-3 w-100" alt="image"></a></div>
                                    <div class="col-xs-4 col-sm-4 p-1"><a href="images/t-35.jpg" data-lightbox="roadtri" class="position-relative d-block"><img src="images/t-25.jpg" class="rounded-3 w-100" alt="image"><span class="img-count font-sm text-white ls-3 fw-600"><b>+2</b></span></a></div>
                                </div>
                            </div>
                            <div class="card-body d-flex p-0">
                                <a href="#" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2"><i class="feather-thumbs-up text-white bg-primary-gradiant me-1 btn-round-xs font-xss"></i> <i class="feather-heart text-white bg-red-gradiant me-2 btn-round-xs font-xss"></i>2.8K Like</a>
                                <div class="emoji-wrap">
                                    <ul class="emojis list-inline mb-0">
                                        <li class="emoji list-inline-item"><i class="em em---1"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-angry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-anguished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-astonished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-blush"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-clap"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-cry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-full_moon_with_face"></i></li>
                                    </ul>
                                </div>
                                <a href="#" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg"></i><span class="d-none-xss">22 Comment</span></a>
                                <a href="#" class="ms-auto d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-share-2 text-grey-900 text-dark btn-round-sm font-lg"></i><span class="d-none-xs">Share</span></a>
                            </div>
                        </div>

                        <div class="card w-100 shadow-none bg-transparent bg-transparent-card border-0 p-0 mb-0">
                            <div class="owl-carousel category-card owl-theme overflow-hidden nav-none">
                                <div class="item">
                                    <div class="card w150 d-block border-0 shadow-xss rounded-3 overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body d-block w-100 ps-3 pe-3 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 position-relative w65 z-index-1"><img src="images/user-11.png" alt="image" class="float-right p-0 bg-white rounded-circle w-100 shadow-xss"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xssss mt-3 mb-1">Richard Bowers  </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">@macale343</p>
                                            <a href="#" class="text-center p-2 lh-20 w100 ms-1 ls-3 d-inline-block rounded-xl bg-success font-xsssss fw-700 ls-lg text-white">FOLLOW</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="card w150 d-block border-0 shadow-xss rounded-3 overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body d-block w-100 ps-3 pe-3 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 position-relative w65 z-index-1"><img src="images/user-9.png" alt="image" class="float-right p-0 bg-white rounded-circle w-100 shadow-xss"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xssss mt-3 mb-1">David Goria </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">@macale343</p>
                                            <a href="#" class="text-center p-2 lh-20 w100 ms-1 ls-3 d-inline-block rounded-xl bg-success font-xsssss fw-700 ls-lg text-white">FOLLOW</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="card w150 d-block border-0 shadow-xss rounded-3 overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body d-block w-100 ps-3 pe-3 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 position-relative w65 z-index-1"><img src="images/user-12.png" alt="image" class="float-right p-0 bg-white rounded-circle w-100 shadow-xss"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xssss mt-3 mb-1">Vincent Parks  </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">@macale343</p>
                                            <a href="#" class="text-center p-2 lh-20 w100 ms-1 ls-3 d-inline-block rounded-xl bg-success font-xsssss fw-700 ls-lg text-white">FOLLOW</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="card w150 d-block border-0 shadow-xss rounded-3 overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body d-block w-100 ps-3 pe-3 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 position-relative w65 z-index-1"><img src="images/user-8.png" alt="image" class="float-right p-0 bg-white rounded-circle w-100 shadow-xss"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xssss mt-3 mb-1">Studio Express </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">@macale343</p>
                                            <a href="#" class="text-center p-2 lh-20 w100 ms-1 ls-3 d-inline-block rounded-xl bg-success font-xsssss fw-700 ls-lg text-white">FOLLOW</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="card w150 d-block border-0 shadow-xss rounded-3 overflow-hidden mb-3 me-2 mt-3">
                                        <div class="card-body d-block w-100 ps-3 pe-3 pb-4 text-center">
                                            <figure class="avatar ms-auto me-auto mb-0 position-relative w65 z-index-1"><img src="images/user-7.png" alt="image" class="float-right p-0 bg-white rounded-circle w-100 shadow-xss"></figure>
                                            <div class="clearfix"></div>
                                            <h4 class="fw-700 font-xssss mt-3 mb-1">Aliqa Macale </h4>
                                            <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">@macale343</p>
                                            <a href="#" class="text-center p-2 lh-20 w100 ms-1 ls-3 d-inline-block rounded-xl bg-success font-xsssss fw-700 ls-lg text-white">FOLLOW</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        

                        <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3">
                            <div class="card-body p-0 d-flex">
                                <figure class="avatar me-3"><img src="images/user-8.png" alt="image" class="shadow-sm rounded-circle w45"></figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-1">Anthony Daugloi <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">2 hour ago</span></h4>
                                <a href="#" class="ms-auto"><i class="ti-more-alt text-grey-900 btn-round-md bg-greylight font-xss"></i></a>
                            </div>
                            <div class="card-body p-0 mb-3 rounded-3 overflow-hidden">
                                <a href="default-video.html" class="video-btn">
                                    <video autoplay loop class="float-right w-100">
                                        <source src="images/v-1.mp4" type="video/mp4">
                                    </video>
                                </a>
                            </div>
                            <div class="card-body p-0 me-lg-5">
                                <p class="fw-500 text-grey-500 lh-26 font-xssss w-100 mb-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nulla dolor, ornare at commodo non, feugiat non nisi. Phasellus faucibus mollis pharetra. Proin blandit ac massa sed rhoncus <a href="#" class="fw-600 text-primary ms-2">See more</a></p>
                            </div>
                            <div class="card-body d-flex p-0">
                                <a href="#" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2"><i class="feather-thumbs-up text-white bg-primary-gradiant me-1 btn-round-xs font-xss"></i> <i class="feather-heart text-white bg-red-gradiant me-2 btn-round-xs font-xss"></i>2.8K Like</a>
                                <div class="emoji-wrap">
                                    <ul class="emojis list-inline mb-0">
                                        <li class="emoji list-inline-item"><i class="em em---1"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-angry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-anguished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-astonished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-blush"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-clap"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-cry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-full_moon_with_face"></i></li>
                                    </ul>
                                </div>
                                <a href="#" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg"></i><span class="d-none-xss">22 Comment</span></a>
                                <a href="#" class="ms-auto d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-share-2 text-grey-900 text-dark btn-round-sm font-lg"></i><span class="d-none-xs">Share</span></a>
                            </div>
                        </div>

                        <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-0">
                            <div class="card-body p-0 d-flex">
                                <figure class="avatar me-3"><img src="images/user-8.png" alt="image" class="shadow-sm rounded-circle w45"></figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-1">Anthony Daugloi <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">2 hour ago</span></h4>
                                <a href="#" class="ms-auto"><i class="ti-more-alt text-grey-900 btn-round-md bg-greylight font-xss"></i></a>
                            </div>
                            <div class="card-body p-0 me-lg-5">
                                <p class="fw-500 text-grey-500 lh-26 font-xssss w-100 mb-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nulla dolor, ornare at commodo non, feugiat non nisi. Phasellus faucibus mollis pharetra. Proin blandit ac massa sed rhoncus <a href="#" class="fw-600 text-primary ms-2">See more</a></p>
                            </div>
                            <div class="card-body d-block p-0 mb-3">
                                <div class="row ps-2 pe-2">
                                    <div class="col-sm-12 p-1"><a href="images/t-30.jpg" data-lightbox="roadtr"><img src="images/t-31.jpg" class="rounded-3 w-100" alt="image"></a></div>                                        
                                </div>
                            </div>
                            <div class="card-body d-flex p-0">
                                <a href="#" class="emoji-bttn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2"><i class="feather-thumbs-up text-white bg-primary-gradiant me-1 btn-round-xs font-xss"></i> <i class="feather-heart text-white bg-red-gradiant me-2 btn-round-xs font-xss"></i>2.8K Like</a>
                                <div class="emoji-wrap">
                                    <ul class="emojis list-inline mb-0">
                                        <li class="emoji list-inline-item"><i class="em em---1"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-angry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-anguished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-astonished"></i> </li>
                                        <li class="emoji list-inline-item"><i class="em em-blush"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-clap"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-cry"></i></li>
                                        <li class="emoji list-inline-item"><i class="em em-full_moon_with_face"></i></li>
                                    </ul>
                                </div>
                                <a href="#" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg"></i><span class="d-none-xss">22 Comment</span></a>
                                <a href="#" class="ms-auto d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss"><i class="feather-share-2 text-grey-900 text-dark btn-round-sm font-lg"></i><span class="d-none-xs">Share</span></a>
                            </div>
                        </div>


                        <div class="card w-100 text-center shadow-xss rounded-xxl border-0 p-4 mb-3 mt-3">
                            <div class="snippet mt-2 ms-auto me-auto" data-title=".dot-typing">
                                <div class="stage">
                                    <div class="dot-typing"></div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <!--Upcoming Events-->               
                    <div class="col-xl-4 col-xxl-3 col-lg-4 ps-lg-0">
                        

                    
                        {{-- <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
                            <div class="card-body d-flex align-items-center  p-4">
                                <h4 class="fw-700 mb-0 font-xssss text-grey-900">Upcoming Events</h4>
                                <a href="default-event.html" class="fw-600 ms-auto font-xssss text-primary">See all</a>
                            </div>
                            <div class="card-body d-flex pt-0 ps-4 pe-4 pb-3 overflow-hidden">
                                <div class="bg-success me-2 p-3 rounded-xxl"><h4 class="fw-700 font-lg ls-3 lh-1 text-white mb-0"><span class="ls-1 d-block font-xsss text-white fw-600">FEB</span>22</h4></div>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-2">Meeting with clients <span class="d-block font-xsssss fw-500 mt-1 lh-4 text-grey-500">41 madison ave, floor 24 new work, NY 10010</span> </h4>
                            </div>

                            <div class="card-body d-flex pt-0 ps-4 pe-4 pb-3 overflow-hidden">
                                <div class="bg-warning me-2 p-3 rounded-xxl"><h4 class="fw-700 font-lg ls-3 lh-1 text-white mb-0"><span class="ls-1 d-block font-xsss text-white fw-600">APR</span>30</h4></div>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-2">Developer Programe <span class="d-block font-xsssss fw-500 mt-1 lh-4 text-grey-500">41 madison ave, floor 24 new work, NY 10010</span> </h4>
                            </div>

                            <div class="card-body d-flex pt-0 ps-4 pe-4 pb-3 overflow-hidden">
                                <div class="bg-primary me-2 p-3 rounded-xxl"><h4 class="fw-700 font-lg ls-3 lh-1 text-white mb-0"><span class="ls-1 d-block font-xsss text-white fw-600">APR</span>23</h4></div>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-2">Aniversary Event <span class="d-block font-xsssss fw-500 mt-1 lh-4 text-grey-500">41 madison ave, floor 24 new work, NY 10010</span> </h4>
                            </div>
                             
                        </div> --}}
                        @livewire('show-events')
                    </div>
                    

                </div>
            </div>
        </div>            
    </div>
    <!-- main content -->

<!-- right chat -->
<div class="right-chat nav-wrap mt-2 right-scroll-bar">
    <div class="middle-sidebar-right-content bg-white shadow-xss rounded-xxl">

<!-- loader wrapper -->
        <div class="preloader-wrap p-3">
            <div class="box shimmer">
                <div class="lines">
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                </div>
            </div>
            <div class="box shimmer mb-3">
                <div class="lines">
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                </div>
            </div>
            <div class="box shimmer">
                <div class="lines">
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                    <div class="line s_shimmer"></div>
                </div>
            </div>
        </div>
    {{-- <!-- loader wrapper -->

        <div class="section full pe-3 ps-4 pt-4 position-relative feed-body">
            <h4 class="font-xsssss text-grey-500 text-uppercase fw-700 ls-3">CONTACTS</h4>
            <ul class="list-group list-group-flush">
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-8.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Hurin Seary</a>
                    </h3>
                    <span class="badge badge-primary text-white badge-pill fw-500 mt-0">2</span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-7.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Victor Exrixon</a>
                    </h3>
                    <span class="bg-success ms-auto btn-round-xss"></span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-6.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Surfiya Zakir</a>
                    </h3>
                    <span class="bg-warning ms-auto btn-round-xss"></span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-5.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Goria Coast</a>
                    </h3>
                    <span class="bg-success ms-auto btn-round-xss"></span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-4.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Hurin Seary</a>
                    </h3>
                    <span class="badge mt-0 text-grey-500 badge-pill pe-0 font-xsssss">4:09 pm</span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-3.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">David Goria</a>
                    </h3>
                    <span class="badge mt-0 text-grey-500 badge-pill pe-0 font-xsssss">2 days</span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-2.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Seary Victor</a>
                    </h3>
                    <span class="bg-success ms-auto btn-round-xss"></span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    <figure class="avatar float-left mb-0 me-2">
                        <img src="images/user-12.png" alt="image" class="w35">
                    </figure>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Ana Seary</a>
                    </h3>
                    <span class="bg-success ms-auto btn-round-xss"></span>
                </li>
                
            </ul>
        </div>
        <div class="section full pe-3 ps-4 pt-4 pb-4 position-relative feed-body">
            <h4 class="font-xsssss text-grey-500 text-uppercase fw-700 ls-3">GROUPS</h4>
            <ul class="list-group list-group-flush">
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    
                    <span class="btn-round-sm bg-primary-gradiant me-3 ls-3 text-white font-xssss fw-700">UD</span>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Studio Express</a>
                    </h3>
                    <span class="badge mt-0 text-grey-500 badge-pill pe-0 font-xsssss">2 min</span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    
                    <span class="btn-round-sm bg-gold-gradiant me-3 ls-3 text-white font-xssss fw-700">AR</span>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Armany Design</a>
                    </h3>
                    <span class="bg-warning ms-auto btn-round-xss"></span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    
                    <span class="btn-round-sm bg-mini-gradiant me-3 ls-3 text-white font-xssss fw-700">UD</span>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">De fabous</a>
                    </h3>
                    <span class="bg-success ms-auto btn-round-xss"></span>
                </li>
            </ul>
        </div>
        <div class="section full pe-3 ps-4 pt-0 pb-4 position-relative feed-body">
            <h4 class="font-xsssss text-grey-500 text-uppercase fw-700 ls-3">Pages</h4>
            <ul class="list-group list-group-flush">
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    
                    <span class="btn-round-sm bg-primary-gradiant me-3 ls-3 text-white font-xssss fw-700">AB</span>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Armany Seary</a>
                    </h3>
                    <span class="bg-success ms-auto btn-round-xss"></span>
                </li>
                <li class="bg-transparent list-group-item no-icon pe-0 ps-0 pt-2 pb-2 border-0 d-flex align-items-center">
                    
                    <span class="btn-round-sm bg-gold-gradiant me-3 ls-3 text-white font-xssss fw-700">SD</span>
                    <h3 class="fw-700 mb-0 mt-0">
                        <a class="font-xssss text-grey-600 d-block text-dark model-popup-chat" href="#">Entropio Inc</a>
                    </h3>
                    <span class="bg-success ms-auto btn-round-xss"></span>
                </li>
                
            </ul>
        </div>

    </div>
 </div> --}}


  <!-- right chat -->

    <div class="app-footer border-0 shadow-lg bg-primary-gradiant">
    <a href="default.html" class="nav-content-bttn nav-center"><i class="feather-home"></i></a>
    <a href="default-video.html" class="nav-content-bttn"><i class="feather-package"></i></a>
    <a href="default-live-stream.html" class="nav-content-bttn" data-tab="chats"><i class="feather-layout"></i></a>            
    <a href="shop-2.html" class="nav-content-bttn"><i class="feather-layers"></i></a>
    <a href="default-settings.html" class="nav-content-bttn"><img src="images/female-profile.png" alt="user" class="w30 shadow-xss"></a>
    </div>

    <div class="app-header-search">
    <form class="search-form">
        <div class="form-group searchbox mb-0 border-0 p-1">
            <input type="text" class="form-control border-0" placeholder="Search...">
            <i class="input-icon">
                <ion-icon name="search-outline" role="img" class="md hydrated" aria-label="search outline"></ion-icon>
            </i>
            <a href="#" class="ms-1 mt-1 d-inline-block close searchbox-close">
                <i class="ti-close font-xs"></i>
            </a>
        </div>
    </form>
    </div>
</x-alumniadmin-dashboard>