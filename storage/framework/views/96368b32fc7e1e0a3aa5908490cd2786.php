

<?php $__env->startSection('title', 'Orders'); ?>

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
    <h1 class="dashboard-title">ORDERS</h1>

    <!-- Order Actions -->
    <div class="order-actions mb-3">
        <button type="button" class="btn btn-custom btn-success" id="addOrderBtn" target="#addOrderModal">
            <i class="fas fa-plus"></i> Add Order
        </button>
        
        <!--
            <button type="button" class="btn btn-custom btn-primary" id="import-btn">
           <i class="fas fa-file-import"></i> Import Data
        </button>

        
            <button type="button" class="btn btn-danger dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-file-export"></i> EXPORT DATA
            </button>
            <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('orders.export', 'xlsx')); ?>">Excel (.xlsx)</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('orders.export', 'csv')); ?>">CSV</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('orders.export', 'pdf')); ?>">PDF</a></li>
            </ul>
        -->
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

    <!-- Orders Table -->
    <div class="table-responsive">
        <table id="orders-table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Table Number</th>
                    <th>Order Quantity</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this tbody with JSON data -->
            </tbody>
        </table>
    </div>

    <!-- Add Order Modal -->
    <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOrderModalLabel">Add Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addorder" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="customerId">Customer ID</label>
                                <input type="text" id="customerId" name="customerId" class="form-control" placeholder="Type to search..." required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="customer_name">Customer Name</label><span id="check-username"></span>
                                <input type="text" id="name" name="name" class="form-control" readonly style="background-color: #f0f0f0; border: 1px solid #ccc; color: #666; cursor: not-allowed;">
                            </div>
                        </div>


                            <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="tableNum">Table Number</label>
                                <select id="tableNum" class="form-select">
                                    <option value="" disabled selected>Select a table</option>
                                </select>
                            </div>
                        
                            <div class="mb-3 single-column">
                                <label for="status">Status</label>
                                <select id="status" class="form-select">
                                    <option value="Pending" selected>Pending</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="single-row">
                            <br>
                        </div>
                    </div>

                    <div class="modal-body">
                        <table id="order-items" class="w-100" style="border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th>Order Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                    <th>Remarks</th>
                                    <th style="color: white;">Action</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="order-item">
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
                        <button type="button" class="btn btn-dark" id="resetButton">Clear</button>
                        <button type="submit" class="btn btn-dark" id="btncreate">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderModalLabel">Edit Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="orderId">Order ID</label>
                            <input type="text" id="edit_orderId" class="form-control" readonly style="background-color: #f0f0f0; border: 1px solid #ccc; color: #666; cursor: not-allowed;">
                        </div>

                        <div class="mb-3 single-column">
                            <label for="customerId">Customer ID</label>
                            <input type="text" id="edit_customerId" name="customerId" class="form-control" placeholder="Type to search..." required>
                        </div>
                    </div>

                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="customer_name">Customer Name</label><span id="check-edit-username"></span>
                            <input type="text" id="edit_name" name="name" class="form-control" readonly style="background-color: #f0f0f0; border: 1px solid #ccc; color: #666; cursor: not-allowed;">
                        </div>
                        

                        <div class="mb-3 single-column">
                            <label for="tableNum">Table Number</label>
                            <select id="edit_tableNum" name="tableNum" class="form-select" required>
                                <option value="" disabled selected>Select a table</option>
                            </select>
                        </div>
                    </div>

                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="orderDate">Order Date</label>
                            <input type="datetime-local" id="edit_orderDate" name="orderDate" class="form-control" required>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="status">Status</label>
                            <select id="edit_status" name="status" class="form-select" required>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark" id="btnupdate">Update</button>
                    </div>
                </form>
            </div>
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
    </script>
    <script src="<?php echo e(asset('js/order.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\miche\Downloads\finalfypa\finalfyp\fyptest-Integrate\resources\views/orders/index.blade.php ENDPATH**/ ?>