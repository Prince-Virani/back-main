<!-- BEGIN #header -->
<div id="header" class="app-header">
    <!-- BEGIN mobile-toggler -->
    <div class="mobile-toggler">
        <button type="button" class="menu-toggler" @if (!empty($appTopNav) && !empty($appSidebarHide)) data-toggle="top-nav-mobile" @else data-toggle="sidebar-mobile" @endif>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </div>
    <!-- END mobile-toggler -->

    <!-- BEGIN brand -->
    <div class="brand">
        <div class="desktop-toggler">
            <button type="button" class="menu-toggler" @if (empty($appSidebarHide))data-toggle="sidebar-minify"@endif>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>

        <a href="/dashboard" class="brand-logo">
            <img src="/assets/img/logo.png" class="invert-dark" alt="" height="200" />
        </a>
    </div>
    <!-- END brand -->

    <!-- BEGIN menu -->
    <div class="menu">
    <form class="menu-search d-none d-sm-flex align-items-center" onsubmit="redirectToCampaign(event)">
    <div class="menu-search-icon me-1">
        <i class="fa fa-search" onclick="redirectToCampaign(event)"></i>
    </div>
    <div class="menu-search-input flex-grow-1">
        <input type="text" id="header-search-input" class="form-control form-control-sm"
               placeholder="Search..." required>
    </div>
    </form>

        <div class="menu-item dropdown">
            <a href="#" data-bs-toggle="dropdown" data-display="static" class="menu-link">
                <div class="menu-icon"><i class="fa fa-bell nav-icon"></i></div>
                <div class="menu-label">3</div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-notification">
                <h6 class="dropdown-header text-body mb-1">Notifications</h6>
                <a href="#" class="dropdown-notification-item">
                    <div class="dropdown-notification-icon">
                        <i class="fa fa-receipt fa-lg fa-fw text-success"></i>
                    </div>
                    <div class="dropdown-notification-info">
                        <div class="title">Your store has a new order for 2 items totaling $1,299.00</div>
                        <div class="time">just now</div>
                    </div>
                    <div class="dropdown-notification-arrow">
                        <i class="fa fa-chevron-right"></i>
                    </div>
                </a>
                <a href="#" class="dropdown-notification-item">
                    <div class="dropdown-notification-icon">
                        <i class="far fa-user-circle fa-lg fa-fw text-muted"></i>
                    </div>
                    <div class="dropdown-notification-info">
                        <div class="title">3 new customer account is created</div>
                        <div class="time">2 minutes ago</div>
                    </div>
                    <div class="dropdown-notification-arrow">
                        <i class="fa fa-chevron-right"></i>
                    </div>
                </a>
                <a href="#" class="dropdown-notification-item">
                    <div class="dropdown-notification-icon">
                        <img src="/assets/img/icon/android.svg" alt="" width="26" />
                    </div>
                    <div class="dropdown-notification-info">
                        <div class="title">Your android application has been approved</div>
                        <div class="time">5 minutes ago</div>
                    </div>
                    <div class="dropdown-notification-arrow">
                        <i class="fa fa-chevron-right"></i>
                    </div>
                </a>
                <a href="#" class="dropdown-notification-item">
                    <div class="dropdown-notification-icon">
                        <img src="/assets/img/icon/paypal.svg" alt="" width="26" />
                    </div>
                    <div class="dropdown-notification-info">
                        <div class="title">Paypal payment method has been enabled for your store</div>
                        <div class="time">10 minutes ago</div>
                    </div>
                    <div class="dropdown-notification-arrow">
                        <i class="fa fa-chevron-right"></i>
                    </div>
                </a>
                <div class="p-2 text-center mb-n1">
                    <a href="#" class="text-body text-opacity-50 text-decoration-none">See all</a>
                </div>
            </div>
        </div>
        <div class="menu-item dropdown">
            <a href="#" data-bs-toggle="dropdown" data-display="static" class="menu-link">
                <div class="menu-img online">
                    <img src="/assets/img/user/user.jpg" alt="" class="ms-100 mh-100 rounded-circle" />
                </div>
                <div class="menu-text">{{ Auth::user()->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-end me-lg-3">
                <a class="dropdown-item d-flex align-items-center" href="/dashboard">Edit Profile <i class="fa fa-user-circle fa-fw ms-auto text-body text-opacity-50"></i></a>
                <!-- <a class="dropdown-item d-flex align-items-center" href="/email/inbox">Inbox <i class="fa fa-envelope fa-fw ms-auto text-body text-opacity-50"></i></a>
                <a class="dropdown-item d-flex align-items-center" href="/calendar">Calendar <i class="fa fa-calendar-alt fa-fw ms-auto text-body text-opacity-50"></i></a> -->
                <a class="dropdown-item d-flex align-items-center" href="/dashboard">Setting <i class="fa fa-wrench fa-fw ms-auto text-body text-opacity-50"></i></a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST" class="dropdown-item p-0 m-0">
                @csrf
                <button type="submit" class="d-flex align-items-center w-100 border-0 bg-transparent text-start px-3 py-2">
                    Log Out
                    <i class="fa fa-toggle-off fa-fw ms-auto text-body text-opacity-50"></i>
                </button>
                </form>
                  
            </div>
        </div>
    </div>
    <!-- END menu -->
</div>
<!-- END #header -->
<script>
    function redirectToCampaign(event) {
        event.preventDefault();

        const query = document.getElementById('header-search-input').value.trim().toLowerCase();

        const routeMap = {
            'dashboard': '{{ route('dashboard') }}',
            'websites': '{{ route('websites.index') }}',
            'manage pages': '{{ route('pages.index') }}',
            'themes': '{{ route('themes.index') }}',
            'manage commonpages': '{{ route('Commonpages.index') }}',
            'google analytics': '{{ route('analytics.index') }}',
            'tagmanagers': '{{ route('tagmanagers.index') }}',
            'adstxt': '{{ route('adstxt.index') }}',
            'adpositions': '{{ route('adpositions.index') }}',
            'categories': '{{ route('categories.index') }}',
            'ad units': '{{ route('ad-units.index') }}',
        };

        
        if (routeMap[query]) {
            window.location.href = routeMap[query];
            return;
        }

       
        const matchKey = Object.keys(routeMap).find(key => key.includes(query));

        if (matchKey) {
            window.location.href = routeMap[matchKey];
        } else {
            alert('No matching route found for "' + query + '"');
        }
    }
</script>
