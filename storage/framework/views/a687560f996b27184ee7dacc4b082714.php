

<?php $__env->startSection('title', 'Payment'); ?>

<?php $__env->startSection('head'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="Content-Security-Policy" content="
        script-src 'self' 'unsafe-inline' 'unsafe-eval' 
            https://code.jquery.com 
            https://cdn.datatables.net 
            https://cdn.jsdelivr.net 
            https://js.pusher.com 
            https://cdnjs.cloudflare.com; 
        style-src 'self' 'unsafe-inline' 
            https://cdn.datatables.net 
            https://cdn.jsdelivr.net 
            https://cdnjs.cloudflare.com;
        font-src 'self' https://cdnjs.cloudflare.com data:;">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Make sure this is included before closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="dashboard-title">PAYMENT</h1>

    <!-- Payment Actions -->
    <div class="payment-actions mb-3">
        <button type="button" class="btn btn-custom btn-success" id="addPaymentBtn" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
            <i class="fas fa-plus"></i> Add Payment
        </button>

            <div class="dropdown d-inline-block">
                <button class="btn btn-custom btn-danger dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export"></i> EXPORT DATA
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="<?php echo e(route('payments.export', ['type' => 'xlsx'])); ?>">
                        <i class="fas fa-file-excel me-2"></i>Excel (.xlsx)
                    </a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('payments.export', ['type' => 'csv'])); ?>">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('payments.export', ['type' => 'pdf'])); ?>">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a></li>
                </ul>
            </div>
    </div>

    <!-- Alert Container -->
    <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Payments Table -->
    <div class="table-responsive">
        <table id="payments-table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Payment Code</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this -->
            </tbody>
        </table>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addPayment" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" id="paymentreservationcode" name="paymentreservationcode">
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="reservationId">Reservation ID</label>
                                <select id="reservationId" name="reservationId" class="form-control" required>
                                    <option value="">Search Reservation ID</option>
                                    <?php $__currentLoopData = $reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($reservation->reservationId); ?>" 
                                                data-customer-id="<?php echo e($reservation->customerId); ?>"
                                                data-customer-name="<?php echo e($reservation->name); ?>">
                                            <?php echo e($reservation->reservationId); ?> - <?php echo e($reservation->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="customerId">Customer ID</label>
                                <input type="text" id="customerId" name="customerId" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="customerName">Customer Name</label>
                                <input type="text" id="customerName" name="customerName" class="form-control" readonly>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="amount">Amount (RM)</label>
                                <input type="number" id="amount" name="amount" class="form-control" step="0.01" required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="paymentType">Payment Type</label>
                                <select name="paymentType" id="paymentType" class="form-control" required>
                                    <option value="">Select Payment Type</option>
                                    <option value="deposit">Deposit</option>
                                    <option value="full">Full Payment</option>
                                    <option value="partial">Partial Payment</option>
                                </select>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="paymentMethod">Payment Method</label>
                                <select name="paymentMethod" id="paymentMethod" class="form-control" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="paymentDate">Payment Date</label>
                                <input type="datetime-local" id="paymentDate" name="paymentDate" 
                                       class="form-control" required 
                                       value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>" 
                                       readonly>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="proofPayment">Proof of Payment</label>
                                <input type="file" id="proofPayment" name="proofPayment" class="form-control" accept="image/*">
                                <!-- Add image preview container -->
                                <div class="mt-2">
                                    <img id="imagePreview" src="" alt="Payment Proof Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
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

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPayment" enctype="multipart/form-data">
                        <input type="hidden" id="edit_paymentId" name="paymentId">
                        
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_reservationId" class="form-label">Reservation ID</label>
                                <input type="text" class="form-control" id="edit_reservationId" readonly>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="edit_amount" class="form-label">Amount (RM)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount" required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                            <label for="edit_paymentType" class="form-label">Payment Type</label>
                            <select class="form-select" id="edit_paymentType" name="paymentType" required>
                                <option value="deposit">Deposit</option>
                                <option value="full">Full Payment</option>
                                </select>
                            </div>

                            <div class="mb-3 single-column">
                            <label for="edit_paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select" id="edit_paymentMethod" name="paymentMethod" required>
                                <option value="online">Online</option>
                                <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                            <label for="edit_paymentDate" class="form-label">Payment Date</label>
                                <input type="datetime-local" class="form-control" id="edit_paymentDate" name="paymentDate" required>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="edit_proofPayment" class="form-label">Proof of Payment</label>
                                <input type="file" class="form-control" id="edit_proofPayment" name="proofPayment" accept="image/*">
                            </div>
                        </div>

                        <img id="edit_imagePreview" src="" alt="Payment Proof" style="max-width: 100%; margin-top: 10px; display: none;">

                        <div class="modal-footer">
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-dark">Update Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
 
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?php echo e(asset('js/payment.js')); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('<?php echo e(env('PUSHER_APP_KEY')); ?>', {
            cluster: '<?php echo e(env('PUSHER_APP_CLUSTER')); ?>'
        });

        var channel = pusher.subscribe('reservation-channel');
        channel.bind('reservation-event', function(data) {
            // Update notification count
            let count = parseInt($('#notification-count').text());
            $('#notification-count').text(count + 1);
            
            // Show notification
            toastr.info(data.message);
            
            // Add notification to dropdown
            let newNotification = `
                <a class="dropdown-item" href="#">
                    <i class="fas fa-bell pr-2"></i> ${data.message}
                    <span class="float-right text-muted text-sm">${moment().fromNow()}</span>
                </a>
            `;
            $('#notification-dropdown').prepend(newNotification);
        });

        // Clear notifications
        $('#clear-notifications').click(function() {
            $('#notification-count').text('0');
            $('#notification-dropdown').empty();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\kxwon\Desktop\finalfyp\fyptest-Integrate\resources\views/payment.blade.php ENDPATH**/ ?>