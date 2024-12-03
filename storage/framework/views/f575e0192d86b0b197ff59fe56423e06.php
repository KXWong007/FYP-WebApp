<?php
    // Retrieve the user data from the session
    $user = session('user');
?>

<div class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <h5>A RENOWNED CLUB</h5>
    </div>

    <!-- Sidebar Navigation Links -->
    <ul class="nav flex-column">
        
        
        <?php if(isset($user['staffType']) && $user['staffType'] === 'F&B Manager'): ?>
            <li class="nav-item">
                <a href="<?php echo e(url('/dashboard')); ?>" class="nav-link <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-tachometer-alt"></i> DASHBOARD
                </a>
            </li>
        <?php endif; ?>

        
        <?php if(isset($user['staffType']) && $user['staffType'] === 'F&B Manager'): ?>
            <li class="nav-item">
                <a href="<?php echo e(url('/customers')); ?>" class="nav-link <?php echo e(request()->is('customers') ? 'active' : ''); ?>">
                    <i class="fas fa-users"></i> CUSTOMER
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/staff')); ?>" class="nav-link <?php echo e(request()->is('staff') ? 'active' : ''); ?>">
                    <i class="fas fa-user-tie"></i> STAFF
                </a>
            </li>
        <?php endif; ?>

        
        <?php if(isset($user['staffType']) && in_array($user['staffType'], ['F&B Manager', 'Dining Area Staff'])): ?>
            <li class="nav-item sidebar-header">DINE AREA MANAGEMENT</li>
            
            <li class="nav-item">
                <a href="<?php echo e(url('/menu')); ?>" class="nav-link <?php echo e(request()->is('menu') ? 'active' : ''); ?>">
                    <i class="fas fa-utensils"></i> MENU
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/orders')); ?>" class="nav-link <?php echo e(request()->is('orders') ? 'active' : ''); ?>">
                    <i class="fas fa-clipboard-list"></i> ORDER
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/reservations')); ?>" class="nav-link <?php echo e(request()->is('reservations') ? 'active' : ''); ?>">
                    <i class="fas fa-calendar-alt"></i> RESERVATION
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/dine-side')); ?>" class="nav-link <?php echo e(request()->is('dine-side') ? 'active' : ''); ?>">
                    <i class="fas fa-utensils"></i> DINE SIDE
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/table')); ?>" class="nav-link <?php echo e(request()->is('table') ? 'active' : ''); ?>">
                    <i class="fas fa-chair"></i> TABLE
                </a>
            </li>
        <?php endif; ?>

        
        <?php if(isset($user['staffType']) && in_array($user['staffType'], ['F&B Manager', 'Kitchen Area Staff','Inventory Manager'])): ?>
            <li class="nav-item sidebar-header">KITCHEN MANAGEMENT</li>
            
            <li class="nav-item">
                <a href="<?php echo e(url('/dish')); ?>" class="nav-link <?php echo e(request()->is('dish') ? 'active' : ''); ?>">
                    <i class="fas fa-drumstick-bite"></i> DISH
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/inventory')); ?>" class="nav-link <?php echo e(request()->is('inventory') ? 'active' : ''); ?>">
                    <i class="fas fa-boxes-stacked"></i> INVENTORY
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/kitchen')); ?>" class="nav-link <?php echo e(request()->is('kitchen') ? 'active' : ''); ?>">
                    <i class="fas fa-kitchen-set"></i> KITCHEN SIDE
                </a>
            </li>
        <?php endif; ?>

        
        <?php if(isset($user['staffType']) && $user['staffType'] === 'F&B Manager'): ?>
            <li class="nav-item sidebar-header">OTHER</li>
            
            <li class="nav-item">
                <a href="<?php echo e(url('/analytics')); ?>" class="nav-link <?php echo e(request()->is('analytics') ? 'active' : ''); ?>">
                    <i class="fas fa-chart-line"></i> ANALYTICS
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(url('/payment')); ?>" class="nav-link <?php echo e(request()->is('payment') ? 'active' : ''); ?>">
                    <i class="fas fa-credit-card"></i> PAYMENT
                </a>
            </li>
        <?php endif; ?>

        
        <?php if(empty($user['staffType'])): ?>
            <li class="nav-item">
                <a href="<?php echo e(url('/')); ?>" class="nav-link">
                    <i class="fas fa-info-circle"></i> User type not set, please contact support.
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<?php /**PATH C:\Users\limru\Downloads\finalfypMich\fyptest-Integrate\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>