<header>
    <nav class="top-bar" data-topbar role="navigation">
        <ul class="title-area">
            <li class="name">
                <h1><a href="/">Tokenly</a></h1>
            </li>
            <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
            <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
        </ul>

        <section class="top-bar-section">
            <!-- Right Nav Section -->
            <ul class="right">
                <li class="divider"></li>
                @if (isset($currentUser) AND $currentUser)
                <li class="">
                    <span class="informational">
                        Welcome, user.
                        <a href="/auth/logout">Logout</a>
                    </span>
                </li>
                @else
                <li class="">
                    <a href="/auth/register">Sign Up</a>
                </li>
                <li class="divider"></li>
                <li class="">
                    <a href="/auth/login">Login</a>
                </li>
                @endif
            </ul>

            <!-- Left Nav Section -->
            @if (isset($currentUser) AND $currentUser)
            <ul class="left">
                <li class="{{{ (isset($currentPage) AND $currentPage == 'dashboard') ? 'active' : '' }}}"><a href="/user/dashboard">Dashboard</a></li>
            </ul>
            @endif
        </section>
    </nav>


</header>