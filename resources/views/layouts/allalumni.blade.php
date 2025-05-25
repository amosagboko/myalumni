<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> FuLafia | Alumni </title>

    <link rel="stylesheet" href="/css/themify-icons.css">
    <link rel="stylesheet" href="/css/feather.css">
    <!-- Favicon icon -->
    <link rel="icon" type="/image/png" sizes="16x16" href="/images/favicon.png">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/emoji.css">
    
    <link rel="stylesheet" href="/css/lightbox.css">
    @livewireStyles
    

</head>

<body class="color-theme-blue mont-font">

    <div class="preloader"></div>

    
    <div class="main-wrapper">

        <!-- navigation top-->
        <div class="nav-header bg-white shadow-xs border-0">
            <div class="nav-top">
                <a href="{{ route('dashboard') }}"><i class="text-success display1-size me-2 ms-0"></i><span class="d-inline-block fredoka-font ls-3 fw-600 text-current font-xxl logo-text mb-0">{{ config('app.name') }}</span> </a>
                <a href="#" class="mob-menu ms-auto me-2 chat-active-btn"><i class="feather-message-circle text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <a href="default-video.html" class="mob-menu me-2"><i class="feather-video text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <a href="#" class="me-2 menu-search-icon mob-menu"><i class="feather-search text-grey-900 font-sm btn-round-md bg-greylight"></i></a>
                <button class="nav-menu me-0 ms-2"></button>
            </div>
            
            <form action="#" class="float-right header-search">
                <div class="form-group mb-0 icon-input" style="float:right">
                    <i class="feather-search font-sm text-grey-400"></i>
                    <input type="text" placeholder="Start typing to search.." class="bg-grey border-0 lh-32 pt-2 pb-2 ps-5 pe-3 font-xssss fw-500 rounded-xl w350 theme-dark-bg">
                </div>
            </form>
            

            <a href="#" title="Notifications" class="p-2 text-center ms-auto menu-icon" id="dropdownMenu3" data-bs-toggle="dropdown" aria-expanded="false"><span class="dot-count bg-warning"></span>
                <i class=" font-lg bg-greylight btn-round-lg theme-dark-bg text-grey-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>    
                </i></a>
            <a href="{{ route('friends') }}" title="Find Friends" class="p-2 text-center ms-0 menu-icon center-menu-icon">
                <i class="font-lg bg-greylight btn-round-lg theme-dark-bg text-grey-500 ">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </i></a>

            <div class="dropdown-menu dropdown-menu-end p-4 rounded-3 border-0 shadow-lg" aria-labelledby="dropdownMenu3">
                
                <h4 class="fw-700 font-xss mb-4">Notification</h4>
                <div class="card bg-transparent-card w-100 border-0 ps-5 mb-3">
                    <img src="images/user-8.png" alt="user" class="w40 position-absolute left-0">
                    <h5 class="font-xsss text-grey-900 mb-1 mt-0 fw-700 d-block">Hendrix Stamp <span class="text-grey-400 font-xsssss fw-600 float-right mt-1"> 3 min</span></h5>
                    <h6 class="text-grey-500 fw-500 font-xssss lh-4">There are many variations of pass..</h6>
                </div>
                <div class="card bg-transparent-card w-100 border-0 ps-5 mb-3">
                    <img src="images/user-4.png" alt="user" class="w40 position-absolute left-0">
                    <h5 class="font-xsss text-grey-900 mb-1 mt-0 fw-700 d-block">Goria Coast <span class="text-grey-400 font-xsssss fw-600 float-right mt-1"> 2 min</span></h5>
                    <h6 class="text-grey-500 fw-500 font-xssss lh-4">Mobile Apps UI Designer is require..</h6>
                </div>

                <div class="card bg-transparent-card w-100 border-0 ps-5 mb-3">
                    <img src="images/user-7.png" alt="user" class="w40 position-absolute left-0">
                    <h5 class="font-xsss text-grey-900 mb-1 mt-0 fw-700 d-block">Surfiya Zakir <span class="text-grey-400 font-xsssss fw-600 float-right mt-1"> 1 min</span></h5>
                    <h6 class="text-grey-500 fw-500 font-xssss lh-4">Mobile Apps UI Designer is require..</h6>
                </div>
                <div class="card bg-transparent-card w-100 border-0 ps-5">
                    <img src="images/user-6.png" alt="user" class="w40 position-absolute left-0">
                    <h5 class="font-xsss text-grey-900 mb-1 mt-0 fw-700 d-block">Victor Exrixon <span class="text-grey-400 font-xsssss fw-600 float-right mt-1"> 30 sec</span></h5>
                    <h6 class="text-grey-500 fw-500 font-xssss lh-4">Mobile Apps UI Designer is require..</h6>
                </div>
            </div>
            <a href="#" title="Up Coming-events" class="p-2 text-center ms-3 menu-icon chat-active-btn">
                <i class="font-lg bg-greylight btn-round-lg theme-dark-bg text-grey-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </i></a>
            <div class="p-2 text-center ms-3 position-relative dropdown-menu-icon menu-icon cursor-pointer">
                <i class="font-lg bg-greylight btn-round-lg theme-dark-bg text-grey-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                </i>
                <div class="dropdown-menu-settings switchcolor-wrap">
                    <h4 class="fw-700 font-sm mb-4">Settings</h4>
                    <h6 class="font-xssss text-grey-500 fw-700 mb-3 d-block">Choose Color Theme</h6>
                    <ul>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="red" checked=""><i class="ti-check"></i>
                                <span class="circle-color bg-red" style="background-color: #ff3b30;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="green"><i class="ti-check"></i>
                                <span class="circle-color bg-green" style="background-color: #4cd964;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="blue" checked=""><i class="ti-check"></i>
                                <span class="circle-color bg-blue" style="background-color: #132977;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="pink"><i class="ti-check"></i>
                                <span class="circle-color bg-pink" style="background-color: #ff2d55;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="yellow"><i class="ti-check"></i>
                                <span class="circle-color bg-yellow" style="background-color: #ffcc00;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="orange"><i class="ti-check"></i>
                                <span class="circle-color bg-orange" style="background-color: #ff9500;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="gray"><i class="ti-check"></i>
                                <span class="circle-color bg-gray" style="background-color: #8e8e93;"></span>
                            </label>
                        </li>

                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="brown"><i class="ti-check"></i>
                                <span class="circle-color bg-brown" style="background-color: #D2691E;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="darkgreen"><i class="ti-check"></i>
                                <span class="circle-color bg-darkgreen" style="background-color: #228B22;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="deeppink"><i class="ti-check"></i>
                                <span class="circle-color bg-deeppink" style="background-color: #FFC0CB;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="cadetblue"><i class="ti-check"></i>
                                <span class="circle-color bg-cadetblue" style="background-color: #5f9ea0;"></span>
                            </label>
                        </li>
                        <li>
                            <label class="item-radio item-content">
                                <input type="radio" name="color-radio" value="darkorchid"><i class="ti-check"></i>
                                <span class="circle-color bg-darkorchid" style="background-color: #9932cc;"></span>
                            </label>
                        </li>
                    </ul>
                    
                    <div class="card bg-transparent-card border-0 d-block mt-3">
                        <h4 class="d-inline font-xssss mont-font fw-700">Header Background</h4>
                        <div class="d-inline float-right mt-1">
                            <label class="toggle toggle-menu-color"><input type="checkbox"><span class="toggle-icon"></span></label>
                        </div>
                    </div>
                    <div class="card bg-transparent-card border-0 d-block mt-3">
                        <h4 class="d-inline font-xssss mont-font fw-700">Menu Position</h4>
                        <div class="d-inline float-right mt-1">
                            <label class="toggle toggle-menu"><input type="checkbox"><span class="toggle-icon"></span></label>
                        </div>
                    </div>
                    <div class="card bg-transparent-card border-0 d-block mt-3">
                        <h4 class="d-inline font-xssss mont-font fw-700">Dark Mode</h4>
                        <div class="d-inline float-right mt-1">
                            <label class="toggle toggle-dark"><input type="checkbox"><span class="toggle-icon"></span></label>
                        </div>
                    </div>
                    
                </div>
            </div>
            

            <a href="{{ route('profile.update') }}" class="p-0 ms-3 menu-icon"><img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('/images/user-8.png') }}" alt="avatar" class="w40 mt--1"></a>
            
        </div>
        <!-- navigation top -->

        <!-- navigation left -->
        <nav class="navigation scroll-bar">
            <div class="container ps-0 pe-0">
                <div class="nav-content">
                    <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1 mb-2 mt-2">
                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span>My </span>Dashboard</div>
                        <ul class="mb-1 top-content">
                            <li class="logo d-none d-xl-block d-lg-block"></li>
                            <li><a href="default.html" class="nav-content-bttn open-font" ><i class="feather-tv btn-round-md bg-blue-gradiant me-3"></i><span>My Transcript</span></a></li>
                            <li><a href="default-badge.html" class="nav-content-bttn open-font" ><i class="feather-award btn-round-md bg-red-gradiant me-3"></i><span>Job Posts</span></a></li>
                            <li><a href="default-storie.html" class="nav-content-bttn open-font" ><i class="feather-globe btn-round-md bg-gold-gradiant me-3"></i><span>Explore Stories</span></a></li>
                            <li><a href="{{ route('alumni') }}" class="nav-content-bttn open-font" ><i class="feather-zap btn-round-md bg-mini-gradiant me-3"></i><span>Alumni</span></a></li>
                            <li><a href="user-page.html" class="nav-content-bttn open-font"><i class="feather-user btn-round-md bg-primary-gradiant me-3"></i><span>Membership </span></a></li>                        
                        </ul>
                    </div>

                    <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1 mb-2">
                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span>More </span>Pages</div>
                        <ul class="mb-3">
                            <li><a href="default-email-box.html" class="nav-content-bttn open-font"><i class="font-xl text-current feather-inbox me-3"></i><span>Elections</span><span class="circle-count bg-warning mt-1">584</span></a></li>
                            <li><a href="default-hotel.html" class="nav-content-bttn open-font"><i class="font-xl text-current feather-home me-3"></i><span>Messages</span></a></li>
                            <li><a href="default-event.html" class="nav-content-bttn open-font"><i class="font-xl text-current feather-map-pin me-3"></i><span>My Transactions</span></a></li>
                            <li><a href="default-live-stream.html" class="nav-content-bttn open-font"><i class="font-xl text-current feather-youtube me-3"></i><span>Live Stream</span></a></li>                        
                        </ul>
                    </div>
                    <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1">
                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span></span> Account</div>
                        <ul class="mb-1">
                            <li class="logo d-none d-xl-block d-lg-block"></li>
                            <li><a href="" class="nav-content-bttn open-font h-auto pt-2 pb-2"><i class="font-sm feather-settings me-3 text-grey-500"></i><span>My Profile</span></a></li>
                            <li><a href="default-analytics.html" class="nav-content-bttn open-font h-auto pt-2 pb-2"><i class="font-sm feather-pie-chart me-3 text-grey-500"></i><span>Donations</span></a></li>
                            <li><a href="default-message.html" class="nav-content-bttn open-font h-auto pt-2 pb-2"><i class="font-sm feather-message-square me-3 text-grey-500"></i><span>Chat</span><span class="circle-count bg-warning mt-0">23</span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        <!-- navigation left -->
     
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

    

        {{ $slot }}
    

    <script src="/js/plugin.js"></script>
    <script src="/js/lightbox.js"></script>
    <script src="/js/scripts.js"></script>
    
    
    </div>
    @livewireScripts
</body>

</html>