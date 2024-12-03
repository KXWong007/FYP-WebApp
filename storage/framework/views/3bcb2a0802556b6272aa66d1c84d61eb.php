

<?php $__env->startSection('title', 'Order Items'); ?>

<?php $__env->startSection('head'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="Content-Security-Policy" content="
        script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.datatables.net https://cdn.jsdelivr.net; 
        style-src 'self' 'unsafe-inline' https://cdn.datatables.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
        font-src 'self' https://cdnjs.cloudflare.com data:;">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="dashboard-title">ORDER ITEMS</h1>

    <!-- Order Actions -->
    <div class="orderitems-actions mb-3 d-flex justify-content-between align-items-center">
        <!-- Add Order Items Button -->
        <button type="button" class="btn btn-custom btn-success" id="addOrderItemsBtn" target="#addOrderItemsModal">
            <i class="fas fa-plus"></i> Add Order Items
        </button>
    </div>

    <!-- Add this right after your header or where you want to show messages -->
    <div id="alert-container">
        <div class="alert alert-success alert-fade" id="success-alert" style="display: none;">
            <span id="success-message"></span>
        </div>
        <div class="alert alert-warning alert-fade" id="warning-alert" style="display: none;">
            <span id="warning-message"></span>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-fade">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('warning')): ?>
        <div class="alert alert-warning alert-fade">
            <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    <!-- Display Order Details Above the Table -->
    <div class="orderitems-info mb-3 d-flex justify-content-between align-items-center">
        <span><strong>Order ID:</strong> <?php echo e($orderDetails->orderId ?? 'N/A'); ?></span> 
        <span><strong>Customer ID:</strong> <?php echo e($orderDetails->customerId ?? 'N/A'); ?></span> 
        <span><strong>Order Date:</strong> <?php echo e($orderDetails->orderDate ?? 'N/A'); ?></span> 
        <span><strong>Table Number:</strong> <?php echo e($orderDetails->tableNum ?? 'N/A'); ?></span>
        <span><strong>Total Amount: </strong>RM <?php echo e($orderDetails->totalAmount ?? 'N/A'); ?></span>
        <span><strong>Status:</strong> <?php echo e($orderDetails->orderStatus ?? 'N/A'); ?></span>
    </div>

    <div class="orderitems-info mb-3 d-flex justify-content-end align-items-center">
        <!-- Right side: Filters section -->
        <div class="filter-container d-flex align-items-center">
            <div class="d-flex align-items-center mr-2">
                <label for="filterDishName" class="mb-0"><strong>Dish: </strong></label>
                <input type="text" id="filterDishName" class="form-control form-control-sm mx-1" placeholder="Dish ID or Name" style="width: 180px;">
            </div>
    
            <div class="d-flex align-items-center">
                <label for="filterStaff" class="mb-0"><strong>Staff: </strong></label>
                <input type="text" id="filterStaff" class="form-control form-control-sm mx-1" placeholder="Staff ID or Name" style="width: 180px;">
            
                <label for="filterStatus" class="mb-0"><strong>Status: </strong></label>
                <select id="filterStatus" class="form-control form-control-sm mx-1" style="width: 160px;">
                    <option value="">All</option>
                    <option value="Pending" selected>Pending</option>
                    <option value="Cooking">Cooking</option>
                    <option value="Ready to serve">Ready to serve</option>
                    <option value="Served">Served</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Order Items Table -->
<div class="table-responsive">
    <table id="orderitems-table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Dish ID</th>
                <th>Dish Name</th>
                <th>Quantity</th>
                <th>Order Time</th>
                <th>Served By</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- DataTable will populate this tbody dynamically via AJAX -->
        </tbody>
    </table>        
</div>

    <!-- Add Order Items Modal -->
<div class="modal fade" id="addOrderItemsModal" tabindex="-1" aria-labelledby="addOrderItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 90%">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addOrderItemsModalLabel">Add Order Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addorderitems" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <!-- Hidden input for orderId -->
                    <input type="hidden" id="orderId" value="<?php echo e($orderDetails->orderId); ?>" />

                    <table id="orderitems-items" class="w-100" style="border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>Order Item Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                                <th>Served By (Staff ID)</th>
                                <th>
                                    <div class="d-flex align-items-center">
                                        Staff Name
                                        <span id="check-username" class="ms-2"></span>
                                    </div>
                                </th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th style="color: white;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="orderitems-item">
                                <td>
                                    <select class="form-select item-name">
                                        <option value="" disabled selected>Select an item</option>
                                        <!-- Options will be populated dynamically -->
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control quantity" value="1" min="1" />
                                </td>
                                <td class="unit-price">0.00</td>
                                <td class="total-price">0.00</td>
                                <td>
                                    <input type="text" class="form-control staff-id" id="staffId" />
                                </td>
                                <td>
                                    <input type="text" class="form-control staff-name" id="staffName" readonly style="background-color: #f0f0f0; border: 1px solid #ccc; color: #666; cursor: not-allowed;"/>
                                </td>
                                <td>
                                    <select id="status" class="form-select">
                                        <option value="Pending" selected>Pending</option>
                                        <option value="Cooking">Cooking</option>
                                        <option value="Ready to serve">Ready to serve</option>
                                        <option value="Served">Served</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control remarks" />
                                </td>
                                <td class="action-cell">
                                    <button type="button" class="btn btn-danger remove-btn" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <button type="button" id="addMoreBtn" class="btn btn-secondary">+ Add More</button>
                    <div class="mt-3">
                        <strong>Total Price: RM<span id="grandTotal">0</span></strong>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-dark" id="btncreate">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Order Items Modal -->
<div class="modal fade" id="editOrderItemsModal" tabindex="-1" aria-labelledby="editOrderItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 90%">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderItemsModalLabel">Edit Order Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editOrderItemForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <!-- Hidden input for orderId and orderItemId -->
                    <input type="hidden" id="orderId" name="orderId" value="<?php echo e($orderDetails->orderId); ?>"/>
                    <input type="hidden" id="orderItemId" name="orderItemId" value=""/> 

                    <table id="orderitems-table" class="w-100" style="border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>Order Item Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                                <th>Served By (Staff ID)</th>
                                <th>
                                    <div class="d-flex align-items-center">
                                        Staff Name
                                        <span id="check-edit-username" class="ms-2"></span>
                                    </div>
                                </th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="orderitems-item">
                                <td>
                                    <input type="hidden" id="edit_itemId" /> 
                                    <input type="text" class="form-control edit-item-name" id="edit_itemName" readonly style="background-color: #f0f0f0; border: 1px solid #ccc; color: #666; cursor: not-allowed;"/>
                                </td>
                                <td>
                                    <input type="number" class="form-control quantity" id="edit_quantity" min="1" />
                                </td>
                                <td class="unit-price" id="edit_unitPrice"></td>
                                <td class="total-price" id="edit_totalPrice"></td>
                                <td>
                                    <input type="text" class="form-control staff-id" id="edit_staffId" />
                                </td>
                                <td>
                                    <input type="text" class="form-control staff-name" id="edit_staffName" readonly style="background-color: #f0f0f0; border: 1px solid #ccc; color: #666; cursor: not-allowed;"/>
                                </td>
                                <td>
                                    <select id="edit_status" class="form-select">
                                        <option value="Pending" selected>Pending</option>
                                        <option value="Cooking">Cooking</option>
                                        <option value="Ready to serve">Ready to serve</option>
                                        <option value="Served">Served</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control remarks" id="edit_remarks" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-dark" id="btnupdate">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Just before closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Then Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Then DataTables -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>

    <!-- Your custom JS last -->
    <script>
        // Add this to verify jQuery is loaded
        console.log('jQuery version:', jQuery.fn.jquery);
        // Verify DataTables is loaded
        console.log('DataTables version:', $.fn.dataTable.version);

        // Pass the orderId dynamically from the Blade view to JavaScript
        window.orderId = "<?php echo e($orderDetails->orderId); ?>";  
    </script>
    <script src="<?php echo e(asset('js/orderitems.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\miche\Downloads\finalfyp\finalfyp\fypv28112024\fyptest-Integrate\resources\views/orders/details.blade.php ENDPATH**/ ?>