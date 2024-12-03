<header class="navbar navbar-expand navbar-light custom-header">
    <div class="container-fluid">
        <div class="navbar-nav me-auto">
            <div class="nav-item text-nowrap">
                <span class="nav-link px-3">
                    <i class="fas fa-user"></i>
                    {{-- Define $user as an object from session data --}}
                    @php
    // Retrieve the user data from the session
    $user = session('user');
@endphp

{{-- Check if the staffType is set --}}
@if(isset($user['staffType']) && $user['staffType'] === 'F&B Manager')
    ADMIN
@else
    {{-- Display user's name if it exists, otherwise display 'Guest' --}}
    {{ isset($user['name']) ? $user['name'] : 'Guest' }}
@endif

                </span>
            </div>
        </div>
        <div class="navbar-nav ms-auto">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="#"><i class="fas fa-bars"></i></a>
            </div>
            <div class="nav-item text-nowrap dropdown">
                <a class="nav-link px-3 position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell" style="font-size: 20px;"></i>
                    <span class="notification-badge position-absolute top-0 start-100 translate-middle">
                        0
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                    <div class="notification-list">
                        <!-- Notifications will be inserted here -->
                    </div>
                </div>
            </div>
            <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="{{ route('logout') }}"><i class="fas fa-power-off"></i> LOGOUT</a>

            </div>
        </div>
    </div>
</header>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/header.js') }}"></script>
