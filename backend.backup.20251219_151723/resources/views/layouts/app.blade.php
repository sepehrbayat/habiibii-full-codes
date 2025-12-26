<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Favicon -->
    @php
        $icon = \App\Models\BusinessSetting::where(['key' => 'icon'])->first();
    @endphp
    @if($icon)
        <link rel="icon" type="image/x-icon" href="{{ \App\CentralLogics\Helpers::get_full_url('business', $icon->value ?? '', ($icon->storage->isNotEmpty() ? $icon->storage[0]->value : 'public'), 'favicon') }}">
    @endif

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
    
    <!-- Font -->
    <link href="{{ asset('assets/admin/css/fonts.css') }}" rel="stylesheet">
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/theme.minc619.css?v=1.0') }}">
    
    <!-- Admin Style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/toastr.css') }}">
    
    <!-- Custom Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    @stack('css_or_js')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ translate('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            @php
                                $customerLoginUrl = \App\Models\DataSetting::where('key', 'customer_login_url')->value('value') ?? 'customer';
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login', ['tab' => $customerLoginUrl]) }}">{{ translate('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ translate('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ translate('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- jQuery (must be loaded before Bootstrap and theme.min.js) -->
    <script src="{{ asset('assets/admin/js/vendor.min.js') }}"></script>
    
    <!-- Ensure jQuery is loaded before theme.min.js -->
    <script>
        (function() {
            function loadThemeScript() {
                if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
                    console.error('jQuery is not available. Cannot load theme.min.js');
                    return false;
                }
                
                var script = document.createElement('script');
                script.src = '{{ asset('assets/admin/js/theme.min.js') }}';
                script.async = false;
                script.defer = false;
                
                script.onerror = function() {
                    console.error('Failed to load theme.min.js');
                };
                
                (document.head || document.getElementsByTagName('head')[0]).appendChild(script);
                return true;
            }
            
            if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
                loadThemeScript();
            } else {
                requestAnimationFrame(function() {
                    if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
                        loadThemeScript();
                    } else {
                        setTimeout(function() {
                            if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
                                loadThemeScript();
                            } else {
                                console.error('jQuery failed to load from vendor.min.js after timeout');
                            }
                        }, 100);
                    }
                });
            }
        })();
    </script>
    
    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/admin/js/bootstrap.min.js') }}"></script>
    
    <!-- Toastr JS -->
    <script src="{{ asset('assets/admin/js/toastr.js') }}"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <!-- Toastr Messages -->
    {!! Toastr::message() !!}
    
    @stack('script')
</body>
</html>
