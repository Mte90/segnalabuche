<header class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
            </svg>
            {{ config('city.name') }}
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link @if(request()->path() == '/') active @endif" aria-current="page" href="{{ url('/') }}">Segnala</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->path() == 'mappa' || request()->path() == 'mappa/') active @endif" aria-current="page" href="{{ url('/mappa') }}">Mappa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->is('admin/*')) active @endif" href="{{ url('/admin/segnalazioni') }}">Admin</a>
                </li>
                @if(auth()->check())
                    <li class="nav-item">
                        <a class="nav-link @if(request()->path() == 'logout') active @endif" href="{{ url('/') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</header>
