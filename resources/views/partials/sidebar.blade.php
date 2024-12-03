@php
    // Retrieve the user data from the session
    $user = session('user');
@endphp

<div class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <h5>A RENOWNED CLUB</h5>
    </div>

    <!-- Sidebar Navigation Links -->
    <ul class="nav flex-column">
        
        {{-- DASHBOARD (Visible to F&B Manager only) --}}
        @if(isset($user['staffType']) && $user['staffType'] === 'F&B Manager')
            <li class="nav-item">
                <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> DASHBOARD
                </a>
            </li>
        @endif

        {{-- CUSTOMER SECTION (Visible to F&B Manager only) --}}
        @if(isset($user['staffType']) && $user['staffType'] === 'F&B Manager')
            <li class="nav-item">
                <a href="{{ url('/customers') }}" class="nav-link {{ request()->is('customers') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> CUSTOMER
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/staff') }}" class="nav-link {{ request()->is('staff') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i> STAFF
                </a>
            </li>
        @endif

        {{-- DINE AREA MANAGEMENT SECTION (Visible to F&B Manager and Dining Area Staff) --}}
        @if(isset($user['staffType']) && in_array($user['staffType'], ['F&B Manager', 'Dining Area Staff']))
            <li class="nav-item sidebar-header">DINE AREA MANAGEMENT</li>
            
            <li class="nav-item">
                <a href="{{ url('/menu') }}" class="nav-link {{ request()->is('menu') ? 'active' : '' }}">
                    <i class="fas fa-utensils"></i> MENU
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/orders') }}" class="nav-link {{ request()->is('orders') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> ORDER
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/reservations') }}" class="nav-link {{ request()->is('reservations') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> RESERVATION
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/dine-side') }}" class="nav-link {{ request()->is('dine-side') ? 'active' : '' }}">
                    <i class="fas fa-utensils"></i> DINE SIDE
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/table') }}" class="nav-link {{ request()->is('table') ? 'active' : '' }}">
                    <i class="fas fa-chair"></i> TABLE
                </a>
            </li>
        @endif

        {{-- KITCHEN MANAGEMENT SECTION (Visible to F&B Manager and Kitchen Area Staff) --}}
        @if(isset($user['staffType']) && in_array($user['staffType'], ['F&B Manager', 'Kitchen Area Staff','Inventory Manager']))
            <li class="nav-item sidebar-header">KITCHEN MANAGEMENT</li>
            
            <li class="nav-item">
                <a href="{{ url('/dish') }}" class="nav-link {{ request()->is('dish') ? 'active' : '' }}">
                    <i class="fas fa-drumstick-bite"></i> DISH
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/inventory') }}" class="nav-link {{ request()->is('inventory') ? 'active' : '' }}">
                    <i class="fas fa-boxes-stacked"></i> INVENTORY
                </a>
            </li>
<li class="nav-item">
            <a href="{{ url('/forecast') }}" class="nav-link {{ request()->is('forecast.index') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked"></i> STOCK FORECAST
            </a>
        </li>
            <li class="nav-item">
                <a href="{{ url('/kitchen') }}" class="nav-link {{ request()->is('kitchen') ? 'active' : '' }}">
                    <i class="fas fa-kitchen-set"></i> KITCHEN SIDE
                </a>
            </li>
        @endif

        {{-- OTHER SECTION (Visible to F&B Manager only) --}}
        @if(isset($user['staffType']) && $user['staffType'] === 'F&B Manager')
            <li class="nav-item sidebar-header">OTHER</li>
            
            <li class="nav-item">
                <a href="{{ url('/analytics') }}" class="nav-link {{ request()->is('analytics') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> ANALYTICS
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/payment') }}" class="nav-link {{ request()->is('payment') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> PAYMENT
                </a>
            </li>
        @endif

        {{-- If `staffType` is not set, show a message or a general link --}}
        @if(empty($user['staffType']))
            <li class="nav-item">
                <a href="{{ url('/') }}" class="nav-link">
                    <i class="fas fa-info-circle"></i> User type not set, please contact support.
                </a>
            </li>
        @endif
    </ul>
</div>
