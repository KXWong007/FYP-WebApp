<?php
    $firstItem = $orderItems->first();
    
    $allItemsServed = $orderItems->every(function($item) {
        return $item->status === 'Served' || $item->status === 'Cancelled';
    });
    
    $hasReadyItems = $orderItems->contains(function($item) {
        return $item->status === 'Ready to Serve';
    });
    
    $allItemsCancelled = $orderItems->every(function($item) {
        return $item->status === 'Cancelled';
    });
    
    $statusClass = match(true) {
        $allItemsCancelled => 'bg-secondary',
        $allItemsServed => 'bg-primary',
        $hasReadyItems => 'bg-success',
        default => 'bg-success'
    };
?>

<style>
    .bg-primary {
        background-color: #4c8fb5 !important;
    }
</style>

<div class="order-card" data-status="<?php echo e($firstItem->status); ?>" data-order-status="<?php echo e($firstItem->orderStatus); ?>">
    <div class="order-header <?php echo e($statusClass); ?>">
        <div class="d-flex justify-content-between align-items-center">
            <span class="time"><?php echo e($firstItem->created_at); ?></span>
            <span class="customer"><?php echo e($firstItem->customerName ?? 'Walk-in'); ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="table">TABLE: <?php echo e($firstItem->tableNum); ?></span>
            <div class="d-flex align-items-center gap-2">
                <span class="reference"><?php echo e($orderId); ?></span>
                <i class="fas fa-copy copy-icon" data-order-id="<?php echo e($orderId); ?>" style="cursor: pointer;"></i>
            </div>
        </div>
    </div>

    <div class="order-items">
        <?php $__currentLoopData = $orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="item">
                <div class="dish-line text-center 
                    <?php echo e($item->status === 'Served' ? 'completed' : ''); ?>

                    <?php echo e($item->status === 'Cancelled' ? 'cancelled' : ''); ?>"
                    <?php if($item->status === 'Ready to Serve'): ?>
                        data-bs-toggle="modal"
                        data-bs-target="#serveDishModal"
                        data-dish-id="<?php echo e($item->orderItemId); ?>"
                        data-dish-name="<?php echo e($item->dishName); ?>"
                        data-order-id="<?php echo e($orderId); ?>"
                        data-status="<?php echo e($item->status); ?>"
                        style="cursor: pointer;"
                    <?php elseif($item->status === 'Served'): ?>
                        data-bs-toggle="modal"
                        data-bs-target="#servedDetailsModal"
                        data-dish-id="<?php echo e($item->orderItemId); ?>"
                        data-dish-name="<?php echo e($item->dishName); ?>"
                        data-quantity="<?php echo e($item->quantity); ?>"
                        data-served-by="<?php echo e($item->servedBy); ?>"
                        data-served-time="<?php echo e($item->servedtime); ?>"
                        style="cursor: pointer;"
                    <?php endif; ?>
                >
                    <?php echo e($item->quantity); ?>X <?php echo e($item->dishName); ?>

                    <?php if($item->status === 'Served'): ?>
                        <i class="fas fa-check text-success ms-2"></i>
                    <?php elseif($item->status === 'Cancelled'): ?>
                        <i class="fas fa-times text-danger ms-2"></i>
                    <?php endif; ?>
                </div>
                <?php if($item->remark): ?>
                    <div class="item-remark text-center text-muted fst-italic">
                        <?php echo e($item->remark); ?>

                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div><?php /**PATH C:\Users\kxwon\Desktop\finalfyp\fyptest-Integrate\resources\views/pos/partials/order-card.blade.php ENDPATH**/ ?>