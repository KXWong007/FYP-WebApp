

<?php $__env->startSection('title', 'Customer'); ?>

<?php $__env->startSection('head'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="Content-Security-Policy" content="
        script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.datatables.net https://cdn.jsdelivr.net; 
        style-src 'self' 'unsafe-inline' https://cdn.datatables.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
        font-src 'self' https://cdnjs.cloudflare.com data:;">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="dashboard-title">CUSTOMER</h1>

    <!-- Customer Actions -->
    <div class="customer-actions mb-3">
        <button type="button" class="btn btn-custom btn-success" id="addCustomerBtn" target="#addCustomerModal">
            <i class="fas fa-plus"></i> Add Customer
        </button>
        <button type="button" class="btn btn-custom btn-primary" id="importDataBtn">
            <i class="fas fa-file-import"></i> Import Data
        </button>
        
        <div class="dropdown d-inline">
            <button class="btn btn-custom btn-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="exportDropdown">
                <i class="fas fa-file-export"></i> EXPORT DATA
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item export-item" href="<?php echo e(route('customers.export', ['type' => 'xlsx'])); ?>">
                        <i class="fas fa-file-excel me-2"></i>Excel (.xlsx)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item export-item" href="<?php echo e(route('customers.export', ['type' => 'csv'])); ?>">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item export-item" href="<?php echo e(route('customers.export', ['type' => 'pdf'])); ?>">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a>
                </li>
            </ul>
        </div>

    </div>

    <!-- Alert Containers -->
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

    <!-- Customers Table -->
    <div class="table-responsive">
        <table id="customers-table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Customer Type</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody> 
                <!-- DataTables will populate this tbody with JSON data -->
            </tbody>
        </table>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCustomerForm" method="POST" action="<?php echo e(route('customers.store')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="customerId">Customer ID</label>
                                <input type="text" id="customerId" name="customerId" class="form-control" required>
                                <span id="customerIdMessage" class="validation-message"></span>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="customerType">Customer Type</label>
                                <select id="customerType" name="customerType" class="form-control" required>
                                    <option value="">None Select</option>
                                    <option value="Ordinary">Ordinary</option>
                                    <option value="Associate">Associate</option>
                                    <option value="Junior">Junior</option>
                                </select>
                            </div>
                        </div>
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="name">Customer Name</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="nric">NRIC</label>
                                <input type="text" id="nric" name="nric" class="form-control" required>
                                <span id="nricMessage" class="validation-message"></span>
                            </div>
                            
                        </div>
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="religion">Religion</label>
                                <input type="text" id="religion" name="religion" class="form-control">
                            </div>
                            <div class="mb-3 single-column">
                                <label for="race">Race</label>
                                <input type="text" id="race" name="race" class="form-control">
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="dateOfBirth">Date of Birth</label>
                                <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-control" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="phoneNum">Phone Number</label>
                                <input type="text" id="phoneNum" name="phoneNum" class="form-control" required>
                            </div>
                        </div>
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="Active">Active</option>
                                    <option value="Pending Verification">Pending Verification</option>
                                    <option value="Frozen">Frozen</option>
                                    <option value="Disabled">Disabled</option>
                                    <option value="Banned">Banned</option>
                                </select>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="profilePicture">Profile</label>
                                <input type="file" id="profilePicture" name="profilePicture" class="form-control" accept="image/*">
                            </div>

                            <div class="mb-3 single-column">
                                <div class="mt-2">
                                    <img id="imagePreview" src="" alt="Profile Picture Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" id="resetButton">Clear</button>
                        <button type="submit" class="btn btn-dark" id="btnCreateCustomer">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCustomerForm" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="edit_customerId" name="customerId">
                    <div class="modal-body">
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_customerType">Customer Type</label>
                                <select id="edit_customerType" name="customerType" class="form-control" required>
                                    <option value="">None Select</option>
                                    <option value="Ordinary">Ordinary</option>
                                    <option value="Associate">Associate</option>
                                    <option value="Junior">Junior</option>
                                </select>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_name">Customer Name</label>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_email">Email</label>
                                <input type="email" id="edit_email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_nric">NRIC</label>
                                <input type="text" id="edit_nric" name="nric" class="form-control" required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_gender">Gender</label>
                                <select id="edit_gender" name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_dateOfBirth">Date of Birth</label>
                                <input type="date" id="edit_dateOfBirth" name="dateOfBirth" class="form-control" required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_religion">Religion</label>
                                <input type="text" id="edit_religion" name="religion" class="form-control">
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_race">Race</label>
                                <input type="text" id="edit_race" name="race" class="form-control">
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_phoneNum">Phone Number</label>
                                <input type="text" id="edit_phoneNum" name="phoneNum" class="form-control" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="edit_status">Status</label>
                                <select id="edit_status" name="status" class="form-control" required>
                                    <option value="Active">Active</option>
                                    <option value="Pending Verification">Pending Verification</option>
                                    <option value="Frozen">Frozen</option>
                                    <option value="Disabled">Disabled</option>
                                    <option value="Banned">Banned</option>
                                </select>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_address">Address</label>
                                <textarea id="edit_address" name="address" class="form-control" required></textarea>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_profilePicture">Profile Picture</label>
                                <input type="file" id="edit_profilePicture" name="profilePicture" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3 single-column">
                                <div class="mt-2">
                                    <img id="edit_imagePreview" src="" alt="Profile Picture Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-dark">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Your custom JS -->
    <script src="<?php echo e(asset('js/customer.js')); ?>"></script>

    
<?php $__env->stopSection(); ?>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Download template button -->
                <div class="mb-3">
                    <a href="<?php echo e(route('customers.template')); ?>" class="btn btn-dark">
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

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\kxwon\Desktop\finalfyp\fyptest-Integrate\resources\views/auth/customers.blade.php ENDPATH**/ ?>