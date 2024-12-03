<!-- resources/views/layout.blade.php -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'A Renowned Club - Dashboard'); ?></title>
    <?php echo $__env->yieldContent('head'); ?>  <!-- Add this line -->
    <?php echo $__env->yieldContent('styles'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (included as a partial) -->
        <?php echo $__env->make('partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header (included as a partial) -->
            <?php echo $__env->make('partials.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Page-specific content -->
            <main class="container-fluid mt-3">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
    <script src="<?php echo e(asset('js/reservation-dashboard.js')); ?>"></script>
</body>
</html>
<?php /**PATH C:\Users\kxwon\Desktop\finalfyp\fyptest-Integrate\resources\views/layout.blade.php ENDPATH**/ ?>