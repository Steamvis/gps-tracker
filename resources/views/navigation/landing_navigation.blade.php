<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand js-scroll-trigger"
           href="/">{{ config('app.name') }}</a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
                data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false"
                aria-label="Toggle navigation">
            Menu
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <div class="dropdown nav-link">
                    <button class="border-0 bg-transparent" type="button" id="language-switcher"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Str::upper(app()->getLocale()) }}
                    </button>
                    <div class="dropdown-menu align-items-center" style="max-width: 3rem; min-width: 3rem;"
                         aria-labelledby="language-switcher">
                        @foreach($locales as $locale)
                            @empty(Route::current()->token)
                                @php($data = ['locale' => $locale, 'token' => Route::current()->token])
                            @else
                                @php($data = ['locale' => $locale])
                            @endempty
                            <a href="{{ route(Route::currentRouteName(), $data) }}"
                               class="dropdown-item p-0 text-center py-2">{{ Str::upper($locale) }}</a>
                        @endforeach
                    </div>
                </div>
                @guest
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('login', app()->getLocale()) }}">{{ __('landing.login') }}
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('register', app()->getLocale()) }}">{{ __('landing.register') }}
                            </a>
                        </li>
                    @endif
                @else
                    <li class=" nav-item">
                        <a class="nav-link"
                           href="{{ route('dashboard.index', app()->getLocale()) }}">{{ __('dashboard.navigation.main') }}
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout', app()->getLocale()) }}"
                               onclick="event.preventDefault();
                                                                         document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout', app()->getLocale()) }}" method="POST"
                                  style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

