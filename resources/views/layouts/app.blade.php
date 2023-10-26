<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.14/moment-timezone-with-data-2012-2022.min.js"></script>
        {{--<script src={{ asset('build/assets/vendor.min.js') }}></script>--}}
        @livewireScripts
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/bootstrap.css', 'resources/css/icons.css', 'resources/css/ubold.css', 'resources/js/app.js', 'resources/js/ubold_vendor.min.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <!-- body start -->
    <body data-layout-mode="default" data-theme="light" data-layout-width="fluid" data-topbar-color="dark" data-menu-position="fixed" data-leftbar-color="light" data-leftbar-size='default' data-sidebar-user='false'>
        <!-- Begin page -->
        <div id="wrapper">
            <!-- Topbar Start -->
            <div class="navbar-custom">
                <div class="container-fluid">
                    <ul class="topnav-menu topnav-menu-left m-0 ml-4 search-position">
                        <li>
                            <livewire:keyword-search/>
                        </li>
                    </ul>
                    <ul class="list-unstyled topnav-menu float-end mb-0">
                        {{--@todo Style this correctly--}}
                        <li class="dropdown notification-list topbar-dropdown">
                            <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <span class="pro-user-name ms-1">
                                    {{ Auth::user()->name }}
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                                <!-- item-->
                                <div class="dropdown-header noti-title">
                                    <h6 class="text-overflow m-0">Welcome !</h6>
                                </div>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-user"></i>
                                    <span>My Account</span>
                                </a>
                                <!-- item-->
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('auth.logout') }}" class="dropdown-item notify-item">
                                    <i class="fe-log-out"></i>
                                    <span>Log out</span>
                                </a>
                            </div>
                        </li>
                        <li class="dropdown notification-list">
                            <a href="javascript:void(0);" class="nav-link right-bar-toggle waves-effect waves-light">
                                {{--<i class="fe-settings noti-icon"></i>--}}
                            </a>
                        </li>

                    </ul>

                    <!-- LOGO -->
                    <div class="clearfix"></div>
                </div>
            </div>
            <!-- end Topbar -->
            <div class="left-side-menu">

                <div class="h-100" data-simplebar>

                    <!-- User box -->
                    <div class="user-box text-center">
                        <img src="" alt="user-img" title="Mat Helme"
                             class="rounded-circle avatar-md">
                        <div class="dropdown">
                            <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block"
                               data-bs-toggle="dropdown">Geneva Kennedy</a>
                            <div class="dropdown-menu user-pro-dropdown">

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-user me-1"></i>
                                    <span>My Account</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-settings me-1"></i>
                                    <span>Settings</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-lock me-1"></i>
                                    <span>Lock Screen</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-log-out me-1"></i>
                                    <span>Logout</span>
                                </a>

                            </div>
                        </div>
                        <p class="text-muted">Admin Head</p>
                    </div>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">
                        <ul id="side-menu">
                            <a href="{{ route('dashboard') }}"><li class="menu-title mt-2">Dashboard</li></a>
                            <li class="menu-title mt-2">Keywords</li>
                            @foreach(\Illuminate\Support\Facades\Auth::user()->getUsersKeywords() as $keyword)
                                <li>
                                    <div style="flex-wrap: unset " class="row mt-2 mb-2">
                                        <div class="col-2 mt-2">
                                            <i class="bi bi-triangle-fill ml-2 mr-2"></i>
                                        </div>
                                        <div class="col-7 justify-center">
                                            <a style="all: unset; cursor: pointer" href="{{ route('keyword.metrics', $keyword->id) }}">
                                                <span> {{ $keyword->original_keyword }} near {{ str_replace('+', ' ', $keyword->location) }} </span>
                                            </a>
                                        </div>
                                        <div class="col-3 mt-2">
                                            <a href="{{ route('keyword.destroy', ['keyword' => $keyword->id]) }}"><img style="height: 20px; width: 20px; cursor: pointer" onclick="confirm('Are you sure you want to delete this keyword? This action is irreversible.') || event.stopImmediatePropagation();" alt="Delete Garbage Remove Icon" loading="lazy" src="https://cdn.iconscout.com/icon/premium/png-256-thumb/delete-52-103683.png?f=webp&amp;w=128" class="thumb_p6OvR"></a>
                                        </div>
                                    </div>
                                </li>
                                <hr style="color: rgba(73,73,73,0.16); border: 1px; border-top: 1px solid; margin: unset" >
                            @endforeach
                            <li class="menu-title mt-2">Reports</li>
                            <li>
                                <a href="{{ route('reports') }}">
                                    <i data-feather="calendar"></i>
                                    <span> Download reports </span>
                                </a>
                            </li>
                            <li class="menu-title mt-2">Billing</li>
                            <li>
                                <a href="{{ route('billing') }}">
                                    <i data-feather="calendar"></i>
                                    <span> Billing plans </span>
                                </a>
                            </li>
                            @if(\Illuminate\Support\Facades\Auth::user()->isAdmin())
                                <li class="menu-title mt-2">Admin</li>
                                <li>
                                    <a href="{{ route('admin') }}">
                                        <i data-feather="calendar"></i>
                                        <span> User management </span>
                                    </a>
                                </li>
                            @endif
                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>

            <div class="content-page">
                <div class="content">
                    <!-- Start Content-->
                    <div class="container-fluid">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>
<script>
    let userFullName = "{{ \Illuminate\Support\Facades\Auth::user()->name }}";
    let email = "{{ \Illuminate\Support\Facades\Auth::user()->email }}";
    let createdAt = "{{ \Illuminate\Support\Facades\Auth::user()->created_at->timestamp }}";

    window.intercomSettings = {
        api_base: "https://api-iam.intercom.io",
        app_id: "kawjniwg",
        name: userFullName, // Full name
        email: email, // Email address
        created_at: createdAt // Signup date as a Unix timestamp
    };

    // We pre-filled your app ID in the widget URL: 'https://widget.intercom.io/widget/kawjniwg'
    (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/kawjniwg';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
</script>
