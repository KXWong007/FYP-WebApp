


<?php $__env->startSection('title', 'Inventory Management'); ?>

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
    <h1 class="dashboard-title">INVENTORY MANAGEMENT</h1>

    <!-- Inventory Actions -->
    <div class="inventory-actions mb-3">
        <button type="button" class="btn btn-success" id="addInventoryBtn" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
            <i class="fas fa-plus"></i> Add Inventory
        </button>
        <!-- button type="button" class="btn btn-custom btn-primary" id="import-btn" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import"></i> Import Data
        </button-->

        <div class="dropdown d-inline-block">
                <button class="btn btn-custom btn-danger dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export"></i> EXPORT DATA
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="<?php echo e(route('inventory.export', ['type' => 'xlsx'])); ?>">
                        <i class="fas fa-file-excel me-2"></i>Excel (.xlsx)
                    </a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('inventory.export', ['type' => 'csv'])); ?>">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('inventory.export', ['type' => 'pdf'])); ?>">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a></li>
                </ul>
            </div>
    </div>

    <!-- Alert Messages -->
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

    <!-- Inventory Table -->
    <div class="table-responsive">
        <table id="inventory-table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Minimum</th>
                    <th>Maximum</th>
                    <th>Unit Price</th>
                    <th>Measurement Unit</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this tbody with JSON data -->
            </tbody>
        </table>
    </div>

    <!-- Add Inventory Modal -->
    <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInventoryModalLabel">Add Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addinventory" method="POST" action="<?php echo e(route('inventory.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <!-- Inventory fields -->
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="inventoryId" class="form-label">Inventory ID</label>
                                <input type="text" class="form-control" id="inventoryId" name="inventoryId" maxlength="50" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="itemName" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="itemName" name="itemName" maxlength="50" required>
                            </div>
                        </div>
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="measurementUnit" class="form-label">Measurement Unit</label>
                                <input type="text" class="form-control" id="measurementUnit" name="measurementUnit" maxlength="10" required>
                            </div>
                        </div>
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="minimum" class="form-label">Minimum Quantity</label>
                                <input type="number" class="form-control" id="minimum" name="minimum" min="0" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="maximum" class="form-label">Maximum Quantity</label>
                                <input type="number" class="form-control" id="maximum" name="maximum" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="unitPrice" class="form-label">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" id="unitPrice" name="unitPrice" min="0" required>
                        </div>
                        <!-- Buttons directly below Unit Price -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-dark me-2" id="resetButton">Clear</button>
                            <button type="submit" class="btn btn-dark" id="btncreate">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ediInventoryModalLabel">Edit Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editInventoryForm" method="POST">
                        <?php echo csrf_field(); ?>
                        
                        <div class="single-row">
                            <!-- Read-only fields -->
                            <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_inventoryId" class="form-label">Inventory ID</label>
                                <input type="text" class="form-control" id="edit_inventoryId" readonly>
                            </div>
                            <!-- Editable fields -->
                            <div class="mb-3 single-column">
                                <label for="edit_itemName" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="edit_itemName" name="itemName" maxlength="50" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_measurementUnit" class="form-label">Measurement Unit</label>
                                <input type="text" class="form-control" id="edit_measurementUnit" name="measurementUnit" maxlength="10" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_minimum" class="form-label">Minimum Quantity</label>
                                <input type="number" class="form-control" id="edit_minimum" name="minimum" min="0" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_maximum" class="form-label">Maximum Quantity</label>
                                <input type="number" cedit_lass="form-control" id="edit_maximum" name="maximum" min="0" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_unitPrice" class="form-label">Unit Price</label>
                                <input type="number" step="0.01" class="form-control" id="edit_unitPrice" name="unitPrice" min="0" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-dark" id="updateButton">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <!-- Download template button -->
                    <div class="mb-3">
                        <a href="<?php echo e(route('inventory.template')); ?>" class="btn btn-dark">
                            <i class="fas fa-download"></i> Download CSV Template
                        </a>
                    </div>
                    
                    <form id="importForm" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Choose CSV File</label>
                            <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-custom btn-primary">Import</button>
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
    <script src="<?php echo e(asset('js/inventory.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
<?php $__env->stopSection(); ?>
        

           
          

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\LATESTF&B\finalfyp\fyptest-Integrate\resources\views/inventory/inventory.blade.php ENDPATH**/ ?>