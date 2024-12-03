

<?php $__env->startSection('title', 'Table Management'); ?>

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
    <h1 class="dashboard-title">TABLE MANAGEMENT</h1>

    <!-- Table Management Actions -->
    <div class="tables-actions mb-3">
        <button type="button" class="btn btn-custom btn-success" id="addTablesBtn" data-bs-toggle="modal" data-bs-target="#addTablesModal">
            <i class="fas fa-plus"></i> Add Table
        </button>

        <!--<button type="button" class="btn btn-primary" id="import-btn" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import"></i> Import Data -->
        </button>

        <!--<button type="button" class="btn btn-danger dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
            <i class="fas fa-file-export"></i> EXPORT DATA -->
        </button>

            <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('reservations.export', 'xlsx')); ?>">Excel (.xlsx)</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('reservations.export', 'csv')); ?>">CSV</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('reservations.export', 'pdf')); ?>">PDF</a></li>
            </ul>     
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

    <!--Table Management Table -->
    <div class="table-responsive">
        <table id="tables-table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Table No.</th>
                    <th>Capacity</th>                   
                    <th>Area</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this tbody with JSON data -->
            </tbody>
        </table>
    </div>

    <!-- Add Table Management Modal -->
    <div class="modal fade" id="addTablesModal" tabindex="-1" aria-labelledby="addTablesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTablesModalLabel">Add Table</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addtables" form action="<?php echo e(route('table.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="tableNum">Table No.</label>
                                <input type="text" name="tableNum" id="tableNum" class="form-control" >
                             </div>

                             <div class="mb-3 single-column">
                                <label for="capacity">Capacity</label>
                                <input type="number" id="capacity" name="capacity" class="form-control" required>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="area">Area</label>
                                <select name="area" id="area" class="form-control" required>
                                    <option value="Main Hall">Main Hall</option>
                                    <option value="Badger Bar">Badger Bar</option>
                                    <option value="Hornbill Restaurant">Hornbill Restaurant</option>
                                    <option value="Rajah Room">Rajah Room</option>
                                </select>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="reserved">Reserved</option>
                                </select>
                            </div>
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

    <!-- Add this right after your opening content section -->
    
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
    <script src="<?php echo e(asset('js/table.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
<?php $__env->stopSection(); ?>

<!-- Edit Table Management Modal -->
<div class="modal fade" id="editTableModal" tabindex="-1" aria-labelledby="editTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTableModalLabel">Edit Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTableForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <!-- Editable fields -->
                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="edit_tableNum">Table No.</label>
                            <input type="text" name="tableNum" id="edit_tableNum" class="form-control" disabled>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_capacity">Capacity</label>
                            <input type="number" id="edit_capacity" name="capacity" class="form-control" required>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_area">Area</label>
                            <select name="area" id="edit_area" class="form-control" required>
                                <option value="Main Hall">Main Hall</option>
                                <option value="Badger Bar">Badger Bar</option>
                                <option value="Hornbill Restaurant">Hornbill Restaurant</option>
                                <option value="Rajah Room">Rajah Room</option>
                            </select>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_status">Status</label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="reserved">Reserved</option>
                            </select>
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

<!--QR code modal-->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrCodeModalLabel">QR Code for Table <span id="modalTableNum"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="qrCodeImageContainer" class="text-center mb-3" style="min-height: 200px;">
                    <!-- QR Code Image will be displayed here -->
                </div>
            </div>
            <div class="modal-footer">
                <button id="generateQRCodeBtn" type="button" class="btn btn-secondary" data-action="generate">Generate</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




<!-- Import Modal 
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"> -->
                <!-- Download template button 
                <div class="mb-3">
                    <a href="<?php echo e(route('reservations.template')); ?>" class="btn btn-dark">
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
</div> -->


<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\miche\Downloads\finalfyp\finalfyp\fypv28112024\fyptest-Integrate\resources\views/tables/table.blade.php ENDPATH**/ ?>