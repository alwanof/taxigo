<!doctype html>
<html lang="en">

<head>
    <title>{{ env('APP_NAME') }}| @yield('title')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Premium Bootstrap 5 Landing Page Template" />
    <meta name="keywords" content="Saas, Software, multi-uses, HTML, Clean, Modern" />
    <meta name="author" content="Shreethemes" />
    <meta name="email" content="support@shreethemes.in" />
    <meta name="website" content="https://shreethemes.in" />
    <meta name="Version" content="v3.2.0" />

    <!-- favicon icon -->
    <link rel="shortcut icon" href="{{ asset('front/default/images/favicon.ico') }}">

    <!-- Bootstrap -->
    <link href="{{ asset('front/default/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- tobii css -->
    <link href="{{ asset('front/default/css/tobii.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons -->
    <link href="{{ asset('front/default/css/materialdesignicons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v3.0.6/css/line.css">
    <!-- Slider -->
    <link rel="stylesheet" href="{{ asset('front/default/css/tiny-slider.css') }}" />
    <!-- Main css -->
    @if ($lang == 'ar')
        @if ($data['generalSettings']['dark'])
            <link href="{{ asset('front/default/css/style-dark-rtl.css') }}" rel="stylesheet" type="text/css" />
        @else
            <link href="{{ asset('front/default/css/style-rtl.css') }}" rel="stylesheet" type="text/css" />
        @endif

    @else
        @if ($data['generalSettings']['dark'])
            <link href="{{ asset('front/default/css/style-dark.css') }}" rel="stylesheet" type="text/css" />
        @else
            <link href="{{ asset('front/default/css/style.css') }}" rel="stylesheet" type="text/css" />
        @endif

    @endif

    <link href="{{ asset('front/default/css/colors/' . $data['generalSettings']['color'] . '.css') }}" rel="stylesheet"
        id="color-opt" />



    @yield('css')
</head>

<body>
    <!-- Loader -->
    <div id="preloader">
        <div id="status">
            <div class="spinner">
                <div class="double-bounce1"></div>
                <div class="double-bounce2"></div>
            </div>
        </div>
    </div>
    <!-- Loader -->
    <!-- Navbar STart -->
    <header id="topnav" class="defaultscroll sticky">
        <div class="container">
            <!-- Logo container-->
            <a class="logo" href="index.html">
                <img src="{{ $data['home'][0]['topBar']['logoDark'] }}" height="24" class="logo-light-mode" alt="">
                <img src="{{ $data['home'][0]['topBar']['logoLight'] }}" height="24" class="logo-dark-mode" alt="">
            </a>
            @if ($data['home'][0]['topBar']['BTN']['show'])
                <div class="buy-button">
                    <a href="{{ $data['home'][0]['topBar']['BTN']['link'] }}" target="_blank">
                        <div class="btn btn-primary">{{ $data['home'][0]['topBar']['BTN']['caption'] }}</div>
                    </a>
                </div>
            @endif
            <!--end login button-->
            <!-- End Logo container-->
            <div class="menu-extras">
                <div class="menu-item">
                    <!-- Mobile menu toggle-->
                    <a class="navbar-toggle" id="isToggle" onclick="toggleMenu()">
                        <div class="lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </a>
                    <!-- End mobile menu toggle-->
                </div>
            </div>

            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu ">
                    @foreach ($data['home'][0]['topBar']['items'] as $nav)
                        @if ($nav['type'] == 'normal')
                            <li><a href="{{ $nav['link'] }}" class="sub-menu-item">{{ $nav['caption'] }}</a></li>
                        @else
                            <li class="has-submenu parent-menu-item">
                                <a href="javascript:void(0)">{{ $nav['caption'] }}</a><span
                                    class="menu-arrow"></span>
                                <ul class="submenu">
                                    @foreach ($nav['subItems'] as $subNav)
                                        <li><a href="{{ $subNav['link'] }}"
                                                class="sub-menu-item">{{ $subNav['caption'] }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach

                </ul>
                <!--end navigation menu-->
                <div class="buy-menu-btn d-none">
                    <a href="https://1.envato.market/4n73n" target="_blank" class="btn btn-primary">Buy Now</a>
                </div>
                <!--end login button-->
            </div>
            <!--end navigation-->
        </div>
        <!--end container-->
    </header>
    <!--end header-->
    <!-- Navbar End -->
    @yield('content')
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12 mb-0  pb-0 pb-md-2">
                    <a href="#" class="logo-footer">
                        <img src="{{ $data['home'][0]['preFooter']['imageSrc'] }}" height="24" alt="">
                    </a>
                    <ul class="list-unstyled social-icon foot-social-icon mb-0 mt-4">
                        @foreach ($data['home'][0]['preFooter']['items'] as $item)
                            <li class="list-inline-item"><a href="{{ $item['link'] }}" target="_blank"
                                    class="rounded"><i data-feather="{{ $item['icon'] }}"
                                        class="fea icon-sm fea-social"></i></a></li>
                        @endforeach
                    </ul>
                    <!--end icon-->


                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!--end container-->
    </footer>
    <!--end footer-->


    <!-- Footer -->
    <footer class="footer footer-bar">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <div class="text-sm-start">
                        <script>
                            document.write(new Date().getFullYear())
                        </script>
                        {{ $data['home'][0]['footer']['caption'] }}
                    </div>
                </div>
                <!--end col-->
                @if ($data['home'][0]['footer']['imageHolder']['show'])
                    <div class="col-sm-6 mt-4 mt-sm-0 pt-2 pt-sm-0">
                        <ul class="list-unstyled text-sm-end mb-0">
                            @foreach ($data['home'][0]['footer']['imageHolder']['items'] as $image)
                                <li class="list-inline-item">
                                    <a href="javascript:void(0)">
                                        <img src="{{ $image['src'] }}" class="avatar avatar-ex-sm"
                                            title="{{ $image['caption'] }}" alt="{{ $image['caption'] }}">
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <!--end col-->
                @endif
            </div>
            <!--end row-->
        </div>
        <!--end container-->
    </footer>
    <!--end footer-->

    <!-- Back to top -->
    <a href="#" onclick="topFunction()" id="back-to-top" class="btn btn-icon btn-primary back-to-top"><i
            data-feather="arrow-up" class="icons"></i></a>
    <!-- Back to top -->
    <!-- Javascript Start -->
    <script src="{{ asset('front/default/js/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ asset('front/default/js/bootstrap.bundle.min.js') }}"></script>
    <!-- SLIDER -->
    <script src="{{ asset('front/default/js/tiny-slider.js') }} "></script>
    <!-- tobii js -->
    <script src="{{ asset('front/default/js/tobii.min.js') }} "></script>
    <!-- Icons -->
    <script src="{{ asset('front/default/js/feather.min.js') }}"></script>
    <!-- Main Js -->
    <script src="{{ asset('front/default/js/plugins.init.js') }}"></script>
    <script src="{{ asset('front/default/js/app.js') }}"></script>
    @yield('js')
    <!-- Javascript End -->
</body>

</html>
