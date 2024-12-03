jQuery(document).ready(function($) {
    // Initialize DataTable
    const paymentsTable = $('#payments-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/payment/data',
            type: 'GET',
            dataSrc: function(json) {
                return json.data || [];
            }
        },
        columns: [
            { data: 'reservationId' },
            { data: 'paymentreservationcode' },
            { 
                data: null,
                render: function(data) {
                    return `${data.customerId} - ${data.name}`;
                }
            },
            { 
                data: 'amount',
                render: function(data) {
                    return data ? 'RM ' + parseFloat(data).toFixed(2) : 'RM 0.00';
                }
            },
            { data: 'paymentType' },
            { 
                data: 'paymentDate',
                render: function(data) {
                    return data ? new Date(data).toLocaleString() : '';
                }
            },
            { data: 'paymentMethod' },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="action-buttons">
                            <button class="btn btn-dark btn-sm edit-btn" data-id="${data.paymentId}">
                                <i class="fas fa-edit fa-sm"></i>
                            </button>
                            <button class="btn btn-dark btn-sm delete-btn" data-id="${data.paymentId}">
                                <i class="fas fa-trash fa-sm"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[4, 'desc']],
        responsive: true,
        language: {
            processing: "Loading...",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
        }
    });

    // Function to set current datetime
    function setCurrentDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        $('#paymentDate').val(currentDateTime);
    }

    // Set current datetime when modal opens
    $('#addPaymentModal').on('shown.bs.modal', function() {
        setCurrentDateTime();
        initializeSelect2();
        generatePaymentCode();
    });

    // Initialize Select2 with custom formatting
    function initializeSelect2() {
        $('#reservationId').select2({
            placeholder: 'Search Reservation ID',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addPaymentModal'),
            templateResult: formatReservation,
            templateSelection: formatReservation
        });
    }

    // Custom formatting for Select2 options
    function formatReservation(reservation) {
        if (!reservation.id) return reservation.text;
        return $(`<span>${reservation.id} - ${$(reservation.element).data('customer-name')}</span>`);
    }

    // Handle reservation selection
    $('#reservationId').on('select2:select', function(e) {
        const selectedOption = $(this).find(':selected');
        const customerId = selectedOption.data('customer-id');
        const customerName = selectedOption.data('customer-name');

        $('#customerId').val(customerId);
        $('#customerName').val(customerName);
    });

    // Clear customer details when reservation is cleared
    $('#reservationId').on('select2:clear', function(e) {
        $('#customerId').val('');
        $('#customerName').val('');
    });

    // Add Payment Form Submit with additional validation
    $('#addPayment').on('submit', function(e) {
        e.preventDefault();
        const reservationId = $('#reservationId').val();
        const amount = parseFloat($('#amount').val());

        if (!reservationId) {
            showAlert('error', "Please select a valid reservation");
            return;
        }

        if (isNaN(amount) || amount <= 0) {
            showAlert('error', "Please enter a valid amount");
            return;
        }

        const formData = new FormData(this);
        formData.set('paymentDate', $('#paymentDate').val());
        formData.set('paymentreservationcode', $('#paymentreservationcode').val());

        $.ajax({
            url: '/payments',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Close modal first
                    const modal = bootstrap.Modal.getInstance($('#addPaymentModal'));
                    modal.hide();
                    
                    // Remove modal backdrop and body class
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    
                    // Show success message
                    showAlert('success', "Payment added successfully!");
                    
                    // Reset form and reload table
                    $("#resetButton").click();
                    $("#payments-table").DataTable().ajax.reload();
                    
                    // Remove the paid reservation from the select options
                    const option = $(`#reservationId option[value="${reservationId}"]`);
                    option.remove();
                    $('#reservationId').trigger('change');
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON.error || "Error adding payment");
            }
        });
    });

    // Edit Payment
    $(document).on('click', '.edit-btn', function() {
        const paymentId = $(this).data('id');
        
        // Fetch payment details
        $.ajax({
            url: `/payments/${paymentId}/edit`,
            method: 'GET',
            success: function(response) {
                const payment = response.payment;
                
                // Populate edit modal fields
                $('#edit_paymentId').val(payment.paymentId);
                $('#edit_reservationId').val(payment.reservationId);
                $('#edit_amount').val(payment.amount);
                $('#edit_paymentType').val(payment.paymentType);
                $('#edit_paymentMethod').val(payment.paymentMethod);
                $('#edit_paymentDate').val(payment.paymentDate);
                
                // Show existing proof of payment if available
                const preview = document.getElementById('edit_imagePreview');
                if (payment.proofPayment) {
                    preview.src = `/storage/${payment.proofPayment}`;
                    preview.style.display = 'block';
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                }
                
                // Show edit modal
                $('#editPaymentModal').modal('show');
            },
            error: function(xhr) {
                showAlert('error', 'Error fetching payment details');
            }
        });
    });

    // Handle edit form submission
    $('#editPayment').on('submit', function(e) {
        e.preventDefault();
        const paymentId = $('#edit_paymentId').val();
        const formData = new FormData(this);
        formData.append('_method', 'PUT'); // Laravel method spoofing for PUT request

        $.ajax({
            url: `/payments/${paymentId}`,
            method: 'POST', // Actually sends as PUT due to _method field
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editPaymentModal').modal('hide');
                    paymentsTable.ajax.reload();
                    showAlert('success', 'Payment updated successfully');
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON.error || 'Error updating payment');
            }
        });
    });

    // Handle image preview in edit modal
    document.getElementById('edit_proofPayment').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('edit_imagePreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });

    // Delete Payment
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this payment?')) {
            const paymentId = $(this).data('id');
            
            $.ajax({
                url: `/payments/${paymentId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    paymentsTable.ajax.reload();
                    showAlert('success', response.success);
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON.error);
                }
            });
        }
    });

    // Optional: Add validation to ensure reservation exists
    $('#addPayment').on('submit', function(e) {
        e.preventDefault();
        const reservationId = $('#reservationId').val();
        if (!reservationId) {
            showAlert('error', 'Please select a valid reservation');
            return;
        }
        // ... rest of your submit code
    });

    // Helper function for alerts
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove any existing alerts
        $('#alert-container').children().remove();
        
        // Add new alert
        $('#alert-container').append(alertHtml);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Add this to your existing JavaScript
    document.getElementById('proofPayment').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });

    // For edit modal, add this function to show existing image
    function populateEditModal(payment) {
        
        // Show existing proof of payment if available
        const preview = document.getElementById('imagePreview');
        if (payment.proofPayment) {
            preview.src = `/storage/${payment.proofPayment}`;
            preview.style.display = 'block';
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }

    // Reset form when modal is hidden
    $('#addPaymentModal').on('hidden.bs.modal', function() {
        // Remove modal backdrop if it persists
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        
        // Reset form
        $('#addPayment')[0].reset();
        $('#reservationId').val('').trigger('change');
        $('#imagePreview').attr('src', '').hide();
    });

    // Reset button functionality
    $('#resetButton').on('click', function() {
        $('#addPayment')[0].reset();
        $('#reservationId').val('').trigger('change');
        $('#imagePreview').attr('src', '').hide();
    });

    // Add this inside your jQuery ready function
    $(document).ready(function() {
        // Initialize all dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });
    });

    // Add this new function
    function generatePaymentCode() {
        const code = Math.floor(100000 + Math.random() * 900000); // Generates 6-digit number
        $('#paymentreservationcode').val(code);
    }
}); 