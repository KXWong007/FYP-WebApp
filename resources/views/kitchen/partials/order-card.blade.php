@php
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
@endphp

<div class="order-card" data-status="{{ $cardStatus }}">
    <div class="order-header {{ $statusClass }}">
        <div class="d-flex justify-content-between align-items-center">
            <span class="time">
                {{ $firstItem->created_at }}
                @if($firstItem->status === 'Pending')
                    <div class="countdown" data-created="{{ $firstItem->created_at }}" data-elapsed="{{ $firstItem->elapsed_seconds }}">
                        <span class="minutes"></span>:<span class="seconds"></span>
                    </div>
                @endif
            </span>
            <span class="customer">{{ $firstItem->customerName ?? 'Walk-in' }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="table">TABLE: {{ $firstItem->tableNum }}</span>
            <div class="d-flex align-items-center gap-2">
                <span class="reference">{{ $orderId }}</span>
                <i class="fas fa-copy copy-icon" data-order-id="{{ $orderId }}" style="cursor: pointer;"></i>
            </div>
        </div>
    </div>

    <div class="order-items">
    @foreach($orderItems as $item)
        <div class="item">
            <div class="dish-line text-center 
                {{ ($item->status === 'Ready to Serve' && isset($item->finishcook_time) && $item->finishcook_time) ? 'completed' : '' }}
                {{ $item->status === 'Served' ? 'served' : '' }}
                {{ $item->status === 'Cancelled' || $firstItem->status === 'Cancelled' ? 'cancelled' : '' }}" 
                @if($item->status === 'Pending')
                    data-bs-toggle="modal" 
                    data-bs-target="#cancelDishModal"
                    data-dish-id="{{ $item->orderItemId }}"
                    data-dish-name="{{ $item->dishName }}"
                    data-order-id="{{ $orderId }}"
                    data-quantity="{{ $item->quantity }}"
                    style="cursor: pointer;"
                @elseif($item->status === 'Cooking' || $item->status === 'Ready to Serve' || $item->status === 'Served')
                    data-bs-toggle="modal" 
                    data-bs-target="#dishDetailModal"
                    data-dish-id="{{ $item->orderItemId }}"
                    data-dish-name="{{ $item->dishName }}"
                    data-order-id="{{ $orderId }}"
                    data-status="{{ $item->status }}"
                    style="cursor: pointer;"
                @endif
                >
                {{ $item->quantity }}X {{ $item->dishName }}
                @if($item->status === 'Ready to Serve' && isset($item->finishcook_time) && $item->finishcook_time)
                    <i class="fas fa-check text-success ms-2"></i>
                @endif
                @if($item->status === 'Served')
                    <i class="fas fa-check text-primary ms-2"></i>
                @endif
                @if($item->status === 'Cancelled' || $firstItem->status === 'Cancelled')
                    <i class="fas fa-times text-danger ms-2"></i>
                @endif
            </div>
            @if($item->remark)
                <div class="item-remark text-center text-muted fst-italic">
                    {{ $item->remark }}
                </div>
            @endif
        </div>
    @endforeach
</div>

    <div class="order-footer">
        @if($firstItem->status === 'Pending')
            <div class="d-flex gap-2">
                <button class="btn btn-dark flex-grow-1 status-btn" 
                        data-order-item-id="{{ $firstItem->orderItemId }}"
                        data-status="Cooking">
                    COOKING
                </button>
                <button class="btn btn-danger cancel-order-btn" 
                        data-order-id="{{ $orderId }}">
                    CANCEL
                </button>
            </div>
        @endif
    </div>
</div>