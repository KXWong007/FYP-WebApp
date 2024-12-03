

<?php $__env->startSection('title', 'Kitchen Display'); ?>

<?php $__env->startSection('head'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="kitchen-display">
    <div class="header-container d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">DINE SIDE TO KITCHEN SIDE</h2>
        
        <div class="d-flex align-items-center gap-3">
            
            <div class="search-container">
                <input type="text" id="orderSearch" class="form-control" placeholder="Search Table/Order ID">
            </div>

            
            <div class="status-legend d-flex">
                <button class="badge bg-danger me-2 status-filter active" data-status="UPCOMING">UPCOMING</button>
                <button class="badge bg-warning text-dark me-2 status-filter" data-status="Cooking">COOKING</button>
                <button class="badge bg-success me-2 status-filter" data-status="Completed">READY TO SERVE</button>
                <button class="badge bg-secondary status-filter" data-status="Cancelled">CANCEL</button>
            </div>
        </div>
    </div>

    <div class="orders-grid">
        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orderId => $orderItems): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make('kitchen.partials.order-card', ['orderItems' => $orderItems, 'orderId' => $orderId], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<!-- Add this modal structure -->
<div class="modal fade" id="dishDetailModal" tabindex="-1" aria-labelledby="dishDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="dishDetailModalLabel">Dish Details: &nbsp;</h5>

                <h5><div id="modalDishName" class="form-control-plaintext"></div></h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="cookId" class="form-label">Staff ID</label>
                        <input type="text" class="form-control" id="cookId">
                    </div>
                    <div class="col-md-6">
                        <label for="staffName" class="form-label">Staff Name</label>
                        <input type="text" class="form-control" id="staffName" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="startTime" class="form-label">Start Time</label>
                    <input type="text" class="form-control" id="startTime" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="finishTime" class="form-label">Finish Time</label>
                        <input type="text" class="form-control" id="finishTime" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="startCookingBtn">Start Cooking</button>
                <button type="button" class="btn btn-success" id="finishCookingBtn" style="display: none;">Finish Cooking</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="cancelDishModal" tabindex="-1" aria-labelledby="cancelDishModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelDishModalLabel">Cancel Order Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Dish Name:</label>
                    <div id="cancelModalDishName" class="form-control-plaintext"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity:</label>
                    <div id="cancelModalQuantity" class="form-control-plaintext"></div>
                </div>
                <div class="alert alert-warning">
                    Are you sure you want to cancel this order item?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancelItem">Cancel Item</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
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
    transition: background-color 0.3s ease;
}

.order-header .time,
.order-header .staff,
.order-header .table,
.order-header .reference {
    font-size: 1.1em;
    font-weight: bold;
}

.order-items {
    padding: 15px;
    background: white;
}

.dish-line {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    font-size: 1.1em;
    text-align: center;
    transition: all 0.3s ease;
}

.dish-line:hover {
    background-color: rgba(0,0,0,0.05);
}

.dish-line.completed {
    text-decoration: line-through;
    color: #28a745;
}

.remark-section {
    padding: 15px;
    background: white;
    border-top: 1px solid #eee;
    color: #666;
    font-style: italic;
}

.order-footer {
    padding: 15px;
    background: white;
    border-top: 1px solid #eee;
}

.status-legend {
    padding: 10px;
}

.status-legend .badge {
    padding: 8px 15px;
    margin-right: 10px;
}

.btn-group .btn {
    border: 1px solid #333;
    font-weight: bold;
}

.btn-group .btn:hover {
    background-color: #333;
    color: white;
}

.header-container {

    border-bottom: 1px solid #eee;
}

.status-legend .badge {
    padding: 8px 15px;
    font-size: 0.9em;
    font-weight: 500;
}

.orders-separator {
    grid-column: 1 / -1; /* Span all columns */
    text-align: center;
    margin: 20px 0;
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
}

.orders-separator hr {
    flex: 1;
    margin: 0;
    border-color: #ddd;
}

.orders-separator span {
    color: #666;
    font-size: 0.9em;
    padding: 0 10px;
    background: white;
}

/* Reduce opacity for completed and cancelled orders */
.order-card.bg-success {
    opacity: 0.8;
}

.order-card.bg-secondary {
    opacity: 0.7;
}

.status-filter {
    border: none;
    cursor: pointer;
    padding: 8px 15px;
    font-size: 0.9em;
    font-weight: 500;
    opacity: 0.6;
    transition: opacity 0.3s;
}

.status-filter.active {
    opacity: 1;
}

.search-container {
    min-width: 250px;
}

.copy-icon {
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.2s ease;
}

.copy-icon:hover {
    color: white;
}

.fa-check {
    color: #28a745;
}

.btn-dark {
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-dark:hover {
    background-color: #343a40;
    border-color: #343a40;
}

#dishDetailModal .form-control {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

#dishDetailModal .form-control[readonly] {
    background-color: #e9ecef;
}

.modal-content.loading {
    opacity: 0.5;
    pointer-events: none;
}

/* Only show pointer cursor on clickable dish lines */
.dish-line[data-bs-toggle] {
    cursor: pointer;
}
.dish-line:not([data-bs-toggle]) {
    cursor: default;
}

.dish-line i {
    width: 16px;
    height: 16px;
}

.bg-primary {
    color: white !important;
}

.status-btn.btn-outline-success:hover {
    color: white;
}

.status-btn.btn-outline-primary:hover {
    color: white;
}

.status-btn.btn-outline-danger:hover {
    color: white;
}

.dish-line.cancelled {
    text-decoration: line-through;
    color: #dc3545;
    opacity: 0.7;
}

.dish-line.cancelled i {
    opacity: 1;
}

.countdown {
    font-weight: bold;
    font-size: 1.2em;
    margin-top: 5px;
}

.order-card.urgent .countdown {
    color: #ffffff;
    animation: pulse 1s infinite;
}

.order-card.very-urgent .countdown {
    color: #ffffff;
    animation: pulse 0.5s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

/* Add transition for smooth reordering */
.order-card {
    transition: all 0.3s ease-in-out;
}

.item-remark {
    font-size: 0.9em;
    padding: 2px 0;
    color: #666;
    border-bottom: 1px dashed #eee;
}

.item {
    margin-bottom: 8px;
}

.item:last-child {
    margin-bottom: 0;
}

.is-valid {
    border-color: #198754 !important;
    background-color: #f8fff9 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
    background-color: #fff8f8 !important;
}

#staffError {
    margin-top: 0.25rem;
    font-size: 0.875em;
}

.dish-line.served {
    text-decoration: line-through;
    color: #4c8fb5; /* Bootstrap primary color for served items */
}

.dish-line.served i {
    text-decoration: none; /* Keep the icon from being struck through */
}

</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
$(document).ready(function() {
    // Function to update orders
    function refreshOrders() {
        console.log('Refreshing orders...'); // Debug log
        window.location.reload();
    }

    // Poll for updates every 30 seconds
    setInterval(refreshOrders, 30000);

    // Function to attach all event handlers
    function attachEventHandlers() {
        attachDishLineHandlers();
        
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

        // Status button click handler
        $('.status-btn').on('click', function() {
            const orderItemId = $(this).data('order-item-id');
            const status = $(this).data('status');
            const staffId = $('#cookId').val(); // Get the staff ID from the modal

            $.ajax({
                url: '/kitchen/update-status',
                method: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    orderItemId: orderItemId,
                    status: status,
                    staffId: staffId  // Add this line to send the staff ID
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error updating status');
                }
            });
        });
    }

    // Initial attachment of event handlers
    attachEventHandlers();

    // Set initial status to Pending
    let currentStatus = 'Pending';
    filterOrders(currentStatus);

    // Status filter click handler
    $('.status-filter').on('click', function() {
        const status = $(this).data('status');
        $('.status-filter').removeClass('active');
        $(this).addClass('active');

        $('.order-card').each(function() {
            const $card = $(this);
            const $items = $card.find('.dish-line');
            
            if (status === 'UPCOMING') {
                // Show cards with Pending items
                const hasPending = $items.toArray().some(item => 
                    $(item).data('status') === 'pending'
                );
                $card.toggle(hasPending);
            } else if (status === 'Completed') {
                // Check if all items are Ready to Serve
                const allReady = $items.toArray().every(item => 
                    $(item).data('status') === 'Ready to Serve' || 
                    $(item).data('status') === 'Cancelled'
                );
                $card.toggle(allReady);
            } else if (status === 'Cancelled') {
                // Show cards where all items are cancelled
                const allCancelled = $items.toArray().every(item => 
                    $(item).hasClass('cancelled')
                );
                $card.toggle(allCancelled);
            } else {
                // For other statuses (Cooking)
                const hasStatus = $items.toArray().some(item => 
                    $(item).data('status') === status
                );
                $card.toggle(hasStatus);
            }
        });
    });

    // Search input handler
    $('#orderSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const selectedStatus = $('.status-filter.active').data('status');
        
        $('.order-card').each(function() {
            const $card = $(this);
            const tableNum = $card.find('.table').text().toLowerCase();
            const orderId = $card.find('.reference').text().toLowerCase();
            const cardStatus = $card.data('status');
            
            // First check if the card matches the current status filter
            let statusMatch = false;
            if (selectedStatus === 'UPCOMING' && cardStatus === 'UPCOMING') {
                statusMatch = true;
            } else if (selectedStatus === 'Cooking' && cardStatus === 'Cooking') {
                statusMatch = true;
            } else if (selectedStatus === 'Completed' && cardStatus === 'Completed') {
                statusMatch = true;
            } else if (selectedStatus === 'Cancelled' && cardStatus === 'Cancelled') {
                statusMatch = true;
            }
            
            // Then check if it matches the search term
            const searchMatch = searchTerm === '' || 
                              tableNum.includes(searchTerm) || 
                              orderId.includes(searchTerm);
            
            // Show the card only if both conditions are met
            $card.toggle(statusMatch && searchMatch);
        });
    });

    function filterOrders(status, searchTerm = '') {
        currentStatus = status;
        
        $('.order-card').each(function() {
            const $card = $(this);
            const tableNum = $card.find('.table').text().toLowerCase();
            const orderId = $card.find('.reference').text().toLowerCase();
            const cardStatus = getCardStatus($card);
            
            const matchesStatus = status === 'All' || cardStatus === status;
            const matchesSearch = searchTerm === '' || 
                                tableNum.includes(searchTerm) || 
                                orderId.includes(searchTerm);

            $card.toggle(matchesStatus && matchesSearch);
        });
    }

    function getCardStatus($card) {
        if ($card.find('.order-header').hasClass('bg-danger')) return 'Pending';
        if ($card.find('.order-header').hasClass('bg-warning')) return 'Cooking';
        if ($card.find('.order-header').hasClass('bg-success')) return 'Completed';
        if ($card.find('.order-header').hasClass('bg-secondary')) return 'Cancelled';
        return '';
    }

    function getStatusClass(status) {
        switch (status) {
            case 'Pending': return 'bg-danger';
            case 'Cooking': return 'bg-warning text-dark';
            case 'Ready to Serve': return 'bg-success';
            case 'Served': return 'bg-primary';
            case 'Cancelled': return 'bg-secondary';
            default: return 'bg-light';
        }
    }

    // Add staff ID input handler
    $('#cookId').on('keyup', function() {
        const staffId = $(this).val();
        const $staffName = $('#staffName');
        const $finishCookingBtn = $('#finishCookingBtn');
        const $startCookingBtn = $('#startCookingBtn');
        
        if (!staffId) {
            $staffName.val('');
            $staffName.removeClass('is-valid is-invalid');
            $finishCookingBtn.prop('disabled', true);
            $startCookingBtn.prop('disabled', true);
            return;
        }

        // Check if staff exists
        $.get(`/kitchen/check-staff/${staffId}`, function(response) {
            if (response.success) {
                $staffName.val(response.name);
                $staffName.removeClass('is-invalid').addClass('is-valid');
                $finishCookingBtn.prop('disabled', false);
                $startCookingBtn.prop('disabled', false);
                // Remove error message if it exists
                $('#staffError').remove();
            } else {
                $staffName.val('');
                $staffName.removeClass('is-valid').addClass('is-invalid');
                $finishCookingBtn.prop('disabled', true);
                $startCookingBtn.prop('disabled', true);
                // Add error message
                if (!$('#staffError').length) {
                    $staffName.after('<div id="staffError" class="text-danger small">Staff ID does not exist</div>');
                }
            }
        });
    });

    // Start cooking button
    $('#startCookingBtn').on('click', function() {
        const staffId = $('#cookId').val();
        if (!staffId) {
            alert('Please enter Staff ID');
            return;
        }
        
        $.post('/kitchen/start-cooking', {
            _token: '<?php echo e(csrf_token()); ?>',
            dishId: currentDishId,
            orderId: currentOrderId,
            staffId: staffId
        }, function(response) {
            if (response.success) {
                $('#startTime').val(response.start_time);
                $('#staffName').val(response.name);
                $('#startCookingBtn').hide();
                $('#finishCookingBtn').show();
                refreshOrders(); // Refresh the orders display
            }
        }).fail(function(xhr) {
            alert('Error starting cooking process');
        });
    });

    // Finish cooking button
    $('#finishCookingBtn').on('click', function() {
        $.post('/kitchen/finish-cooking', {
            _token: '<?php echo e(csrf_token()); ?>',
            dishId: currentDishId,
            orderId: currentOrderId
        }, function(response) {
            if (response.success) {
                $('#finishTime').val(response.finishcook_time);
                refreshOrders(); // Refresh the orders display
                setTimeout(() => {
                    $('#dishDetailModal').modal('hide');
                }, 1000);
            }
        }).fail(function(xhr) {
            alert('Error finishing cooking process');
        });
    });

    function attachDishLineHandlers() {
        // Only attach click handlers to dish lines that have data-bs-toggle attribute
        $('.dish-line[data-bs-toggle]').off('click').on('click', function() {
            currentDishId = $(this).data('dish-id');
            currentOrderId = $(this).data('order-id');
            const dishName = $(this).data('dish-name');
            const status = $(this).data('status');
            
            // Reset modal fields
            $('#modalDishName').text(dishName);
            $('#startTime').val('');
            $('#finishTime').val('');
            $('#cookId').val('');
            $('#staffName').val('');
            
            // Only fetch status for non-pending items
            if (status !== 'Pending') {
                // Check dish status
                $.get(`/kitchen/dish-status/${currentDishId}`)
                    .done(function(response) {
                        if (response.success) {
                            $('#startTime').val(response.start_time || '');
                            $('#cookId').val(response.staffid || '');
                            $('#staffName').val(response.name || '');
                            $('#finishTime').val(response.finishcook_time || '');
                            
                            // Hide both buttons by default
                            $('#startCookingBtn').hide();
                            $('#finishCookingBtn').hide();
                            
                            // Show appropriate button based on status
                            if (response.status === 'Cooking') {
                                $('#finishCookingBtn').show();
                            } else if (response.status === 'Pending') {
                                $('#startCookingBtn').show();
                            }
                            
                            // Make fields readonly for Ready to Serve status
                            const isReadyToServe = response.status === 'Ready to Serve';
                            $('#cookId').prop('readonly', isReadyToServe);
                            
                            // Show the modal
                            $('#dishDetailModal').modal('show');
                        }
                    })
                    .fail(function(xhr) {
                        console.error('Error fetching dish status:', xhr);
                        alert('Error loading dish details');
                    });
            }
        });
    }

    // Cancel order button handler
    $('.orders-grid').on('click', '.cancel-order-btn', function() {
        const orderId = $(this).data('order-id');
        
        if (confirm('Are you sure you want to cancel this order?')) {
            $.ajax({
                url: '/kitchen/cancel-order',
                method: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    orderId: orderId
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload(); // Refresh the page
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Cancel order error:', xhr);
                    alert('Error cancelling order');
                }
            });
        }
    });

    let currentCancelItemId = null;

    $('#cancelDishModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const dishName = button.data('dish-name');
        const quantity = button.data('quantity');
        currentCancelItemId = button.data('dish-id');

        $('#cancelModalDishName').text(dishName);
        $('#cancelModalQuantity').text(quantity + 'X');
    });

    $('#confirmCancelItem').on('click', function() {
        if (!currentCancelItemId) return;

        $.ajax({
            url: '/kitchen/cancel-item',
            method: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                orderItemId: currentCancelItemId
            },
            success: function(response) {
                if (response.success) {
                    $('#cancelDishModal').modal('hide');
                    window.location.reload(); // Refresh to show updated status
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error cancelling item');
            }
        });
    });

    // Initialize countdown timers
    function initializeCountdowns() {
        $('.countdown').each(function() {
            const $countdown = $(this);
            const $orderCard = $countdown.closest('.order-card');
            const $items = $orderCard.find('.dish-line');
            
            // Check if any items are pending
            const hasPendingItems = $items.toArray().some(item => 
                $(item).data('status') === 'pending'
            );

            // Only initialize countdown for orders with pending items
            if (hasPendingItems) {
                const elapsedSeconds = parseInt($countdown.data('elapsed'));
                const targetSeconds = 600; // 10 minutes in seconds
                let remainingSeconds = Math.max(targetSeconds - elapsedSeconds, 0);

                function updateTimer() {
                    const minutes = Math.floor(remainingSeconds / 60);
                    const seconds = remainingSeconds % 60;
                    
                    $countdown.find('.minutes').text(String(minutes).padStart(2, '0'));
                    $countdown.find('.seconds').text(String(seconds).padStart(2, '0'));
                    
                    // Add urgency classes only for pending orders
                    if (remainingSeconds <= 120) { // Last 2 minutes
                        $orderCard.addClass('very-urgent');
                    } else if (remainingSeconds <= 300) { // Last 5 minutes
                        $orderCard.addClass('urgent');
                    }

                    if (remainingSeconds > 0) {
                        remainingSeconds--;
                    }
                }

                // Initial update
                updateTimer();
                
                // Update every second
                setInterval(updateTimer, 1000);
            } else {
                // Hide countdown for non-pending orders
                $countdown.hide();
            }
        });
    }

    // Sort orders by urgency
    function sortOrders() {
        const $ordersGrid = $('.orders-grid');
        const $orders = $ordersGrid.children('.order-card').get();
        
        $orders.sort(function(a, b) {
            const aSeconds = parseInt($(a).find('.countdown').data('elapsed'));
            const bSeconds = parseInt($(b).find('.countdown').data('elapsed'));
            return bSeconds - aSeconds; // Sort by elapsed time (most urgent first)
        });
        
        $ordersGrid.append($orders);
    }

    // Initialize timers and sorting
    initializeCountdowns();
    sortOrders();
    
    // Resort orders every 30 seconds
    setInterval(sortOrders, 30000);

    // Prevent dish detail modal from showing on pending items
    $('.dish-line').on('click', function(e) {
        if ($(this).data('status') === 'pending') {
            e.stopPropagation();
            $('#dishDetailModal').modal('hide');
            return;
        }
    });

    $('#dishDetailModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const dishId = button.data('dish-id');
        const dishName = button.data('dish-name');
        currentDishId = dishId;

        // Reset form state
        $('#cookId').val('');
        $('#staffName').val('');
        $('#staffName').removeClass('is-valid is-invalid');
        $('#staffError').remove();
        $('#finishCookingBtn').prop('disabled', true);
        $('#startCookingBtn').prop('disabled', true);

        $('#modalDishName').text(dishName);

        // Get the current status of the dish
        $.get(`/kitchen/dish-status/${dishId}`, function(response) {
            if (response.success) {
                $('#cookId').val(response.staffid || '');
                $('#staffName').val(response.name || '');
                $('#startTime').val(response.start_time || '');
                $('#finishTime').val(response.finishcook_time || '');
                
                // If staff ID exists, validate it
                if (response.staffid) {
                    $('#cookId').trigger('keyup');
                }
                
                // Update button visibility based on status
                if (response.status === 'Cooking') {
                    $('#startCookingBtn').hide();
                    $('#finishCookingBtn').show();
                } else {
                    $('#startCookingBtn').show();
                    $('#finishCookingBtn').hide();
                }
            }
        });
    });

    // Add this after your existing scripts
    $('#cookId').on('change', function() {
        const staffId = $(this).val();
        
        // Check if staff exists
        $.get(`/kitchen/check-staff/${staffId}`, function(response) {
            if (response.success) {
                $('#staffName').val(response.name);
            } else {
                $('#staffName').val('');
                // alert('Staff not found');
            }
        });
    });

    // Update the start cooking button handler
    $('#startCookingBtn').on('click', function() {
        const staffId = $('#cookId').val();
        if (!staffId) {
            alert('Please enter a Staff ID');
            return;
        }
        
        $.ajax({
            url: '/kitchen/start-cooking',
            method: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                dishId: currentDishId,
                staffId: staffId
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });

    // Update the finish cooking button handler
    $('#finishCookingBtn').on('click', function() {
        const staffId = $('#cookId').val();
        if (!staffId) {
            alert('Please enter a Staff ID');
            return;
        }

        $.ajax({
            url: '/kitchen/finish-cooking',
            method: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                dishId: currentDishId,
                staffId: staffId
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });

    // Status filter handling
    $('.status-filter').click(function() {
        $('.status-filter').removeClass('active');
        $(this).addClass('active');
        
        const selectedStatus = $(this).data('status');
        
        $('.order-card').each(function() {
            const cardStatus = $(this).data('status');
            if (selectedStatus === 'Completed' && cardStatus === 'Completed') {
                $(this).show();
            } else if (selectedStatus === 'Cooking' && cardStatus === 'Cooking') {
                $(this).show();
            } else if (selectedStatus === 'UPCOMING' && cardStatus === 'UPCOMING') {
                $(this).show();
            } else if (selectedStatus === 'Cancelled' && cardStatus === 'Cancelled') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Trigger the active filter on page load
    $('.status-filter.active').trigger('click');
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\kxwon\Desktop\finalfyp\fyptest-Integrate\resources\views/kitchen/index.blade.php ENDPATH**/ ?>