@php
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
@endphp

<style>
    .bg-primary {
        background-color: #4c8fb5 !important;
    }
</style>

<div class="order-card" data-status="{{ $firstItem->status }}" data-order-status="{{ $firstItem->orderStatus }}">
    <div class="order-header {{ $statusClass }}">
        <div class="d-flex justify-content-between align-items-center">
            <span class="time">{{ $firstItem->created_at }}</span>
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
                    {{ $item->status === 'Served' ? 'completed' : '' }}
                    {{ $item->status === 'Cancelled' ? 'cancelled' : '' }}"
                    @if($item->status === 'Ready to Serve')
                        data-bs-toggle="modal"
                        data-bs-target="#serveDishModal"
                        data-dish-id="{{ $item->orderItemId }}"
                        data-dish-name="{{ $item->dishName }}"
                        data-order-id="{{ $orderId }}"
                        data-status="{{ $item->status }}"
                        style="cursor: pointer;"
                    @elseif($item->status === 'Served')
                        data-bs-toggle="modal"
                        data-bs-target="#servedDetailsModal"
                        data-dish-id="{{ $item->orderItemId }}"
                        data-dish-name="{{ $item->dishName }}"
                        data-quantity="{{ $item->quantity }}"
                        data-served-by="{{ $item->servedBy }}"
                        data-served-time="{{ $item->servedtime }}"
                        style="cursor: pointer;"
                    @endif
                >
                    {{ $item->quantity }}X {{ $item->dishName }}
                    @if($item->status === 'Served')
                        <i class="fas fa-check text-success ms-2"></i>
                    @elseif($item->status === 'Cancelled')
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
</div>