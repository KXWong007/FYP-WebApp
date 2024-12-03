<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="dashboard-title">DASHBOARD</h1>
    
    <!-- Status cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card custom-card bg-dark">
                <div class="card-body">
                    <h2 class="card-title">6</h2>
                    <p class="card-text">FREE TABLE</p>
                    <div class="card-icon">
                        <i class="fas fa-chair"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="#" class="text-white">MORE INFO →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card bg-info">
                <div class="card-body">
                    <h2 class="card-title"><?php echo e($pendingCount); ?></h2>
                    <p class="card-text">PENDING ORDERS</p>
                    <div class="card-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="#" class="text-white" data-bs-toggle="modal" data-bs-target="#pendingOrdersModal">MORE INFO →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card bg-warning">
                <div class="card-body">
                    <h2 class="card-title"><?php echo e($cookingCount); ?></h2>
                    <p class="card-text">DISH PREPARING</p>
                    <div class="card-icon">
                        <i class="fas fa-fire-burner"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="#" class="text-white" data-bs-toggle="modal" data-bs-target="#preparingOrdersModal">MORE INFO →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card bg-success">
                <div class="card-body">
                    <h2 class="card-title"><?php echo e($readytoserveCount); ?></h2>
                    <p class="card-text">DISH READY TO SERVE</p>
                    <div class="card-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="#" class="text-white" data-bs-toggle="modal" data-bs-target="#completedOrdersModal">MORE INFO →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Example table content (adjust as needed) -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">UNAVAILABLE DISH</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>NAME</th>
                                <th>CATEGORY</th>
                                <th>AREA</th>
                                <th>MORE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BEEF BURGER</td>
                                <td>BURGER</td>
                                <td>WESTERN</td>
                                <td><i class="fas fa-search"></i></td>
                            </tr>
                            <tr>
                                            <td>MI GORENG</td>
                                            <td>NOODLE</td>
                                            <td>MAIN HALL</td>
                                            <td><i class="fas fa-search"></i></td>
                                        </tr>
                                        <tr>
                                            <td>COCKTAIL</td>
                                            <td>BEER</td>
                                            <td>BAR</td>
                                            <td><i class="fas fa-search"></i></td>
                                        </tr>
                                        <tr>
                                            <td>SALMON</td>
                                            <td>FISH</td>
                                            <td>WESTERN</td>
                                            <td><i class="fas fa-search"></i></td>
                                        </tr>
                                        <tr>
                                            <td>NASI GORENG</td>
                                            <td>RICE</td>
                                            <td>CHINESE</td>
                                            <td><i class="fas fa-search"></i></td>
                                        </tr>
<!-- Repeat rows as needed -->
                        </tbody>
                    </table>
                    <div class="text-center">
                        <a href="#" class="btn btn-link">CHECK MORE <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">UPCOMING RESERVATION</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>TYPE</th>
                                <th>AREA</th>
                                <th>TIME</th>
                                <th>PAX</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $upcomingReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="reservation-row">
                                    <td><?php echo e($reservation->reservationId); ?></td>
                                    <td>
                                        <i class="fas <?php echo e($reservation->type_icon); ?>"></i> 
                                        <?php echo e(strtoupper($reservation->eventType)); ?>

                                    </td>
                                    <td><?php echo e($reservation->rarea === 'W' ? 'Rajah Room' : 'Hornbill Room'); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($reservation->reservationDate)->format('d/m/Y H:i')); ?></td>
                                    <td><?php echo e($reservation->pax); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    <div class="text-center">
                        <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#reservationModal">
                            CHECK MORE <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservationModalLabel">All Upcoming Reservations</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="allReservationsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>TYPE</th>
                                <th>AREA</th>
                                <th>TIME</th>
                                <th>PAX</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this at the bottom of your file, after the existing modal -->
    <div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-labelledby="reservationDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservationDetailsModalLabel">Reservation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="reservationDetailsContent">
                        <!-- Details will be inserted here via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preparing Orders Modal -->
    <div class="modal fade" id="preparingOrdersModal" tabindex="-1" aria-labelledby="preparingOrdersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="preparingOrdersModalLabel">Orders Being Prepared</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="preparingOrdersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Table</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Start Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $preparingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($order->order_id); ?></td>
                                    <td><?php echo e($order->table_number); ?></td>
                                    <td><?php echo e($order->item_name); ?></td>
                                    <td><?php echo e($order->quantity); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($order->started_at)->format('H:i:s')); ?></td>
                                    <td>
                                        <span class="badge bg-warning">Cooking</span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Orders Modal -->
    <div class="modal fade" id="completedOrdersModal" tabindex="-1" aria-labelledby="completedOrdersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completedOrdersModalLabel">Ready to Serve Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="completedOrdersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Table</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Completed Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $readyToServeOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($order->order_id); ?></td>
                                    <td><?php echo e($order->table_number); ?></td>
                                    <td><?php echo e($order->item_name); ?></td>
                                    <td><?php echo e($order->quantity); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($order->started_at)->format('H:i:s')); ?></td>
                                    <td>
                                        <span class="badge bg-success">Ready to Serve</span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Orders Modal -->
    <div class="modal fade" id="pendingOrdersModal" tabindex="-1" aria-labelledby="pendingOrdersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pendingOrdersModalLabel">Pending Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="pendingOrdersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>customerId</th>
                                <th>Table</th>
                                <th>Amount</th>
                                <th>Order Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr style="cursor: pointer;" 
                                    onclick="showOrderDetails('<?php echo e($order->order_id); ?>')" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#orderDetailsModal">
                                    <td><?php echo e($order->order_id); ?></td>
                                    <td><?php echo e($order->customerId); ?></td>
                                    <td><?php echo e($order->tableNum); ?></td>
                                    <td>RM <?php echo e($order->totalAmount); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($order->created_at)->format('H:i:s')); ?></td>
                                    <td>
                                        <span class="badge bg-info">Pending</span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Order Details - #<span id="currentOrderId"></span></h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Table: <span id="currentTableNum"></span></span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="orderDetailsTable">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Will be populated via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this JavaScript at the bottom of your file -->
    <script>
    function showOrderDetails(orderId) {
        // Update the modal title with the order ID
        document.getElementById('currentOrderId').textContent = orderId;
        
        // Get the table number from the row and update it in the modal
        const tableNum = event.currentTarget.querySelector('td:nth-child(3)').textContent;
        document.getElementById('currentTableNum').textContent = tableNum;
        
        // Hide the pending orders modal but don't remove it from DOM
        $('#pendingOrdersModal').modal('hide');
        
        // Fetch order details
        fetch(`/api/orders/${orderId}/details`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#orderDetailsTable tbody');
                tbody.innerHTML = ''; // Clear existing rows
                
                data.forEach(item => {
                    // Set badge color based on status
                    let badgeClass = '';
                    switch(item.status) {
                        case 'Cancelled':
                            badgeClass = 'bg-secondary'; // Grey
                            break;
                        case 'Ready to Serve':
                            badgeClass = 'bg-success';   // Green
                            break;
                        case 'Cooking':
                            badgeClass = 'bg-warning';   // Yellow/Orange
                            break;
                        default:
                            badgeClass = 'bg-info';      // Blue (for Pending)
                    }

                    const row = `
                        <tr>
                            <td>${item.dishName}</td>
                            <td>${item.quantity}</td>
                            <td>RM ${item.price}</td>
                            <td>RM ${(item.price * item.quantity).toFixed(2)}</td>
                            <td><span class="badge ${badgeClass}">${item.status}</span></td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            })
            .catch(error => console.error('Error:', error));
    }

    // Add this event listener for the order details modal
    $('#orderDetailsModal').on('hidden.bs.modal', function (e) {
        // Show the pending orders modal again when order details modal is closed
        $('#pendingOrdersModal').modal('show');
    });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LAU\Desktop\finalfyp\fypv28112024\fyptest-Integrate\resources\views/welcome.blade.php ENDPATH**/ ?>