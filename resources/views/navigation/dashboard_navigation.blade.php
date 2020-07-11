<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <ul class="navbar-nav ml-auto">

        {{-- TODO        @include('navigation.dashboard.notification')--}}

        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <button class="nav-link dropdown-toggle bg-transparent border-0 text-gray-600"
                    type="button" id="language-switcher"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ Str::upper(app()->getLocale()) }}
            </button>
            <div class="dropdown-menu align-items-center" style="max-width: 3rem; min-width: 3rem;"
                 aria-labelledby="language-switcher">
                @include('partials.language_switcher')
            </div>
        </li>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <h3>{{ auth()->user()->avatar }}</h3>
                {{--      TODO          <img class="img-profile rounded-circle">--}}
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                 aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('user.profile.settings', app()->getLocale()) }}">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
