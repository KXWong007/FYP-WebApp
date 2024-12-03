@extends('layout')

@section('title', 'Dine Side')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="pos-display">
    <div class="header-container d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">KITCHEN SIDE TO DINE SIDE</h2>
        
        <div class="d-flex align-items-center gap-3">
            {{-- Search Box --}}
            <div class="search-container">
                <input type="text" id="orderSearch" class="form-control" placeholder="Search Table/Order ID">
            </div>

            {{-- Status Filter Buttons --}}
            <div class="status-legend d-flex">
                <button class="badge me-2 status-filter active" data-status="Ready to Serve">READY TO SERVE</button>
                <button class="badge me-2 status-filter" data-status="Served">SERVED</button>
                <button class="badge status-filter" data-status="Cancelled">CANCELLED</button>
            </div>
        </div>
    </div>

    <div class="orders-grid">
        @foreach($orders as $orderId => $orderItems)
            @include('pos.partials.order-card', ['orderItems' => $orderItems, 'orderId' => $orderId])
        @endforeach
    </div>
</div>

@include('pos.partials.serve-modal')

@include('pos.partials.served-details-modal')

@endsection

@section('styles')
<style>
.kitchen-display {
    padding: 20px;
}

.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

.order-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-header {
    padding: 15px;
    color: white;
}

.order-items {
    padding: 15px;
    background: white;
}

.dish-line {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    font-size: 1.1em;
}

.dish-line:hover {
    background-color: rgba(0,0,0,0.05);
}

.dish-line.completed {
    text-decoration: line-through;
    color: #4c8fb5;
}

.dish-line.cancelled {
    text-decoration: line-through;
    color: #dc3545;
}

.item-remark {
    padding: 5px 0;
    font-size: 0.9em;
}

.status-legend .badge {
    cursor: pointer;
    padding: 16px 15px;
    border: none;
    color: white;
}

.status-legend .badge[data-status="Ready to Serve"] {
    background-color: #28a745;
}

.status-legend .badge[data-status="Served"] {
    background-color: #4c8fb5;
}

.status-legend .badge[data-status="Cancelled"] {
    background-color: #6c757d;
}

.status-legend .badge.active {
    box-shadow: 0 0 0 2px currentColor;
}

/* Remove hover effect */
.status-legend .badge:hover {
    opacity: 1;
}

/* Remove the black background on active */
.status-legend .badge:active,
.status-legend .badge:focus {
    background-color: inherit;
}

.status-legend .badge[data-status="Ready to Serve"]:active,
.status-legend .badge[data-status="Ready to Serve"]:focus {
    background-color: #28a745;
}

.status-legend .badge[data-status="Served"]:active,
.status-legend .badge[data-status="Served"]:focus {
    background-color: #4c8fb5;
}

.status-legend .badge[data-status="Cancelled"]:active,
.status-legend .badge[data-status="Cancelled"]:focus {
    background-color: #6c757d;
}

.search-container {
    min-width: 250px;
}

.badge {
    font-size: 0.8em;
    padding: 0.4em 0.6em;
}

.dish-line .badge {
    margin-left: 8px;
}

.dish-line.completed {
    color: #4c8fb5;
    transition: color 0.3s ease;
}

.dish-line .fa-check {
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
    color: #4c8fb5 !important;
}

.dish-line.completed .fa-check {
    opacity: 1;
    transform: scale(1);
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentDishId = null;
    let currentOrderId = null;

    // Function to update orders
    function refreshOrders() {
        window.location.reload();
    }

    // Poll for updates every 30 seconds
    setInterval(refreshOrders, 30000);

    // Status filter handling
    $('.status-filter').click(function() {
        $('.status-filter').removeClass('active');
        $(this).addClass('active');
        
        const selectedStatus = $(this).data('status');
        const searchTerm = $('#orderSearch').val().toLowerCase();
        
        $('.order-card').each(function() {
            const $card = $(this);
            const orderItems = $card.find('.dish-line');
            let shouldShow = false;
            
            // Check status match
            if (selectedStatus === 'Ready to Serve') {
                shouldShow = Array.from(orderItems).some(item => 
                    $(item).text().includes('Ready to Serve') || 
                    (!$(item).hasClass('completed') && !$(item).hasClass('cancelled'))
                );
            } 
            else if (selectedStatus === 'Served') {
                shouldShow = Array.from(orderItems).some(item => 
                    $(item).hasClass('completed')
                );
            }
            else if (selectedStatus === 'Cancelled') {
                shouldShow = $card.data('order-status') === 'Cancelled';
            }
            
            // Check search term match if there is a search term
            if (searchTerm) {
                const tableNum = $card.find('.table').text().toLowerCase();
                const orderId = $card.find('.reference').text().toLowerCase();
                const searchMatches = tableNum.includes(searchTerm) || orderId.includes(searchTerm);
                shouldShow = shouldShow && searchMatches;
            }
            
            $card.toggle(shouldShow);
        });
    });

    // Search functionality
    $('#orderSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const activeStatus = $('.status-filter.active').data('status');
        
        $('.order-card').each(function() {
            const $card = $(this);
            const tableNum = $card.find('.table').text().toLowerCase();
            const orderId = $card.find('.reference').text().toLowerCase();
            const matches = tableNum.includes(searchTerm) || orderId.includes(searchTerm);
            
            // Check if card matches current filter status
            let statusMatches = false;
            const orderItems = $card.find('.dish-line');
            
            if (activeStatus === 'Ready to Serve') {
                statusMatches = Array.from(orderItems).some(item => 
                    $(item).text().includes('Ready to Serve') || 
                    (!$(item).hasClass('completed') && !$(item).hasClass('cancelled'))
                );
            } 
            else if (activeStatus === 'Served') {
                statusMatches = Array.from(orderItems).some(item => 
                    $(item).hasClass('completed')
                );
            }
            else if (activeStatus === 'Cancelled') {
                statusMatches = $card.data('order-status') === 'Cancelled';
            }
            
            // Show card only if it matches both search term and status filter
            $card.toggle(matches && statusMatches);
        });
    });

    // Copy icon handler
    $('.copy-icon').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Try multiple ways to get the order ID
        let orderId = $(this).prev('.reference').text().trim();
        if (!orderId) {
            orderId = $(this).closest('.order-header').find('.reference').text().trim();
        }
        
        console.log('Found OrderId:', orderId);
        
        if (!orderId) {
            console.error('No order ID found');
            return;
        }
        
        // Use the newer Clipboard API if available
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(orderId).then(() => {
                console.log('Successfully copied:', orderId);
                // Visual feedback
                const $icon = $(this);
                $icon.removeClass('fa-copy').addClass('fa-check');
                setTimeout(() => {
                    $icon.removeClass('fa-check').addClass('fa-copy');
                }, 1000);
            }).catch((err) => {
                console.error('Failed to copy:', err);
            });
        } else {
            // Fallback to older method
            const textarea = document.createElement('textarea');
            textarea.value = orderId;
            textarea.style.position = 'fixed';  // Prevent scrolling to bottom
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    console.log('Successfully copied:', orderId);
                    // Visual feedback
                    const $icon = $(this);
                    $icon.removeClass('fa-copy').addClass('fa-check');
                    setTimeout(() => {
                        $icon.removeClass('fa-check').addClass('fa-copy');
                    }, 1000);
                } else {
                    console.error('Copy command failed');
                }
            } catch (err) {
                console.error('Failed to copy text:', err);
            } finally {
                document.body.removeChild(textarea);
            }
        }
    });

    // Serve dish modal handling
    $('#serveDishModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        currentDishId = button.data('dish-id');
        currentOrderId = button.data('order-id');
        const dishName = button.data('dish-name');
        
        $('#modalDishName').text(dishName);
        $('#serveStaffId').val('');
        $('#staffName').val('');
        $('#finishTime').val('');
        $('#currentTime').val('');
        
        // Get dish status including finishcook_time
        $.get(`/dine-side/dish-status/${currentDishId}`)
            .done(function(response) {
                if (response.success) {
                    // Set finish time from finishcook_time
                    if (response.finishcook_time) {
                        $('#finishTime').val(response.finishcook_time);
                    }
                    
                    // Set current time
                    const now = new Date();
                    $('#currentTime').val(now.toLocaleString('en-US', { 
                        year: 'numeric', 
                        month: '2-digit', 
                        day: '2-digit',
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit',
                        hour12: false 
                    }));
                }
            })
            .fail(function(xhr) {
                console.error('Error fetching dish status:', xhr);
            });
    });

    // Staff ID validation
    $('#serveStaffId').on('keyup', function() {
        const staffId = $(this).val();
        const $staffName = $('#staffName');
        const $serveDishBtn = $('#serveDishBtn');
        
        if (!staffId) {
            $staffName.val('');
            $staffName.removeClass('is-valid is-invalid');
            $serveDishBtn.prop('disabled', true);
            return;
        }

        // Check if staff exists
        $.get(`/kitchen/check-staff/${staffId}`, function(response) {
            if (response.success) {
                $staffName.val(response.name);
                $staffName.removeClass('is-invalid').addClass('is-valid');
                $serveDishBtn.prop('disabled', false);
                $('#staffError').remove();
            } else {
                $staffName.val('');
                $staffName.removeClass('is-valid').addClass('is-invalid');
                $serveDishBtn.prop('disabled', true);
                if (!$('#staffError').length) {
                    $staffName.after('<div id="staffError" class="text-danger small">Staff ID does not exist</div>');
                }
            }
        });
    });

    // Serve dish button handler
    $('#serveDishBtn').on('click', function() {
        const staffId = $('#serveStaffId').val();
        const currentTime = $('#currentTime').val();
        
        if (!staffId) {
            alert('Please enter Staff ID');
            return;
        }

        $.ajax({
            url: '/dine-side/serve-dish',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                dishId: currentDishId,
                staffId: staffId,
                servedtime: currentTime
            },
            success: function(response) {
                if (response.success) {
                    // Add success animation
                    const $dishLine = $(`.dish-line[data-dish-id="${currentDishId}"]`);
                    $dishLine.addClass('completed');
                    $dishLine.append('<i class="fas fa-check ms-2"></i>');
                    
                    // Close modal and refresh
                    $('#serveDishModal').modal('hide');
                    setTimeout(refreshOrders, 500);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error serving dish');
            }
        });
    });

    // Trigger the active filter on page load
    $('.status-filter.active').trigger('click');

    // Add this after your existing modal handlers
    $('#servedDetailsModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const dishId = button.data('dish-id');
        const dishName = button.data('dish-name');
        const quantity = button.data('quantity');
        
        // Set initial values
        $('#servedDishName').text(dishName);
        $('#servedQuantity').text(quantity + 'X');
        
        // Fetch served details
        $.get(`/dine-side/dish-status/${dishId}`)
            .done(function(response) {
                if (response.success) {
                    $('#servedStaffId').text(response.staffid || '-');
                    $('#servedStaffName').text(response.name || '-');
                    $('#servedTime').text(response.servedtime || '-');
                    $('#finishcook_time').text(response.finishcook_time || '-');
                }
            })
            .fail(function(xhr) {
                console.error('Error fetching served details:', xhr);
            });
    });
});
</script>
@endsection