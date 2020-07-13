<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center"
       href="{{ route('dashboard.index', app()->getLocale()) }}">
        <div class="sidebar-brand-icon">
            <i class="fas fa-laugh-wink"></i>
            <i class="fas fa-truck"></i>
        </div>
        <div class="sidebar-brand-text mx-3">{{ env('APP_NAME') }} 0.1</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('dashboard.index', app()->getLocale()) }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>{{ __('dashboard.navigation.main') }}</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        TEST
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('cars.index', app()->getLocale()) }}">
            <i class="fas fa-fw fa-cog"></i>
            <span>{{ __('dashboard.navigation.cars') }}</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
</ul>
