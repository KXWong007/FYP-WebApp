<?php
    $firstItem = $orderItems->first(function($item) {
        return $item->status !== 'Cancelled';
    }) ?? $orderItems->first();
    
    $allItemsCompleted = $orderItems->every(function($item) {
        return $item->status === 'Ready to Serve' || 
               $item->status === 'Served' || 
               $item->status === 'Cancelled';
    });
    
    $hasReadyItems = $orderItems->contains(function($item) {
        return $item->status === 'Ready to Serve';
    });

    $hasServedItems = $orderItems->contains(function($item) {
        return $item->status === 'Served';
    });
    
    $hasCookingItems = $orderItems->contains(function($item) {
        return $item->status === 'Cooking';
    });
    
    $allItemsCancelled = $orderItems->every(function($item) {
        return $item->status === 'Cancelled';
    });
    
    $statusClass = match(true) {
        $allItemsCancelled => 'bg-secondary',
        $allItemsCompleted && ($hasReadyItems || $hasServedItems) => 'bg-success',
        $hasCookingItems => 'bg-warning text-dark',
        $firstItem && $firstItem->status === 'Pending' => 'bg-danger',
        default => 'bg-danger'
    };

    // Determine if this card should be shown based on its status
    $cardStatus = match(true) {
        $allItemsCancelled => 'Cancelled',
        $allItemsCompleted && ($hasReadyItems || $hasServedItems) => 'Completed',
        $hasCookingItems => 'Cooking',
        default => 'UPCOMING'
    };
?>

<div class="order-card" data-status="<?php echo e($cardStatus); ?>">
    <div class="order-header <?php echo e($statusClass); ?>">
        <div class="d-flex justify-content-between align-items-center">
            <span class="time">
                <?php echo e($firstItem->created_at); ?>

                <?php if($firstItem->status === 'Pending'): ?>
                    <div class="countdown" data-created="<?php echo e($firstItem->created_at); ?>" data-elapsed="<?php echo e($firstItem->elapsed_seconds); ?>">
                        <span class="minutes"></span>:<span class="seconds"></span>
                    </div>
                <?php endif; ?>
            </span>
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
                <?php echo e(($item->status === 'Ready to Serve' && isset($item->finishcook_time) && $item->finishcook_time) ? 'completed' : ''); ?>

                <?php echo e($item->status === 'Served' ? 'served' : ''); ?>

                <?php echo e($item->status === 'Cancelled' || $firstItem->status === 'Cancelled' ? 'cancelled' : ''); ?>" 
                <?php if($item->status === 'Pending'): ?>
                    data-bs-toggle="modal" 
                    data-bs-target="#cancelDishModal"
                    data-dish-id="<?php echo e($item->orderItemId); ?>"
                    data-dish-name="<?php echo e($item->dishName); ?>"
                    data-order-id="<?php echo e($orderId); ?>"
                    data-quantity="<?php echo e($item->quantity); ?>"
                    style="cursor: pointer;"
                <?php elseif($item->status === 'Cooking' || $item->status === 'Ready to Serve' || $item->status === 'Served'): ?>
                    data-bs-toggle="modal" 
                    data-bs-target="#dishDetailModal"
                    data-dish-id="<?php echo e($item->orderItemId); ?>"
                    data-dish-name="<?php echo e($item->dishName); ?>"
                    data-order-id="<?php echo e($orderId); ?>"
                    data-status="<?php echo e($item->status); ?>"
                    style="cursor: pointer;"
                <?php endif; ?>
                >
                <?php echo e($item->quantity); ?>X <?php echo e($item->dishName); ?>

                <?php if($item->status === 'Ready to Serve' && isset($item->finishcook_time) && $item->finishcook_time): ?>
                    <i class="fas fa-check text-success ms-2"></i>
                <?php endif; ?>
                <?php if($item->status === 'Served'): ?>
                    <i class="fas fa-check text-primary ms-2"></i>
                <?php endif; ?>
                <?php if($item->status === 'Cancelled' || $firstItem->status === 'Cancelled'): ?>
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

    <div class="order-footer">
        <?php if($firstItem->status === 'Pending'): ?>
            <div class="d-flex gap-2">
                <button class="btn btn-dark flex-grow-1 status-btn" 
                        data-order-item-id="<?php echo e($firstItem->orderItemId); ?>"
                        data-status="Cooking">
                    COOKING
                </button>
                <button class="btn btn-danger cancel-order-btn" 
                        data-order-id="<?php echo e($orderId); ?>">
                    CANCEL
                </button>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH C:\Users\kxwon\Desktop\finalfyp\fyptest-Integrate\resources\views/kitchen/partials/order-card.blade.php ENDPATH**/ ?>