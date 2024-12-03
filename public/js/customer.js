jQuery(document).ready(function ($) {
    $("#customers-table").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        ajax: {
            url: "/customers",
            type: "GET",
            dataSrc: 'data'
        },
        columns: [
            { data: "customerId", name: "customerId" },
            { data: "customerType", name: "customerType" },
            { data: "name", name: "name" },
            { data: "email", name: "email" },
            { data: "phoneNum", name: "phoneNum" },
            { data: "status", name: "status" },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="action-buttons">
                            <button class="btn btn-dark btn-sm edit-btn" data-id="${data.customerId}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-dark btn-sm delete-btn" data-id="${data.customerId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                },
            },
        ],
        responsive: true,
        drawCallback: function (settings) {
            var tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    placement: "bottom",
                    boundary: "viewport",
                });
            });
        },
    });

    function reloadPage() {
        window.location.reload();
    }

    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll("[title]")
    );

    var tooltipTriggerList = [].slice.call(document.querySelectorAll("[title]"));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: "bottom",
            boundary: "viewport",
        });
    });

    $("#addCustomerBtn").on("click", function () {
        const myModal = new bootstrap.Modal(document.getElementById("addCustomerModal"));
        myModal.show();
    });

    $("#importDataBtn").on("click", function () {
        const importModal = new bootstrap.Modal(document.getElementById("importModal"));
        importModal.show();
    });

    $("#exportDataBtn").on("click", function () {
        // Implement export data functionality
    });

    $("#reservations-table").on("click", ".view-btn", function() {
        var id = $(this).data("id");
        // Implement view reservation functionality
    });
    
    $("#customers-table").on("click", ".delete-btn", function () {
        var id = $(this).data("id");
        // Implement delete customer functionality
    });

    $("#customerId").on("input", function () {
        checkUsername();
    });

    $("#customerId").on("keyup", function () {
        getCustomerDetail(this.value);
    });

    function checkUsername() {
        $.ajax({
            url: "/check-customer",
            data: { customerId: $("#customerId").val() },
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                $("#check-username").html(data.message);
                $("#btncreate").prop("disabled", !data.exists);
            },
            error: function () {
                $("#check-username").html('<span style="color:red">Error checking customer ID</span>');
            },
        });
    }

    function getCustomerDetail(customerId) {
        if (customerId.length === 0) {
            $("#cName").val("");
            return;
        }

        $.ajax({
            url: "/get-customer-detail",
            data: { customerId: customerId },
            type: "GET",
            success: function (response) {
                console.log("Response:", response);
                if (response && response.name) {
                    $("#cName").val(response.name);
                } else {
                    $("#cName").val("");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                $("#cName").val("");
            },
        });
    }

    $("#resetButton").on("click", function () {
        $("#addCustomerForm")[0].reset();
        $("#customerId").val("");
        $("#name").val("");
        $("#email").val("");
        $("#gender").val("Select");
        $("#phoneNum").val("");
        $("#customerIdMessage, #nricMessage").html("");
        $("#imagePreview").attr("src", "").hide();
        customerIdValid = true;
        nricValid = true;
        updateSubmitButton();
    });

    $(document).on("click", ".edit-btn", function () {
        const customerId = $(this).data("id");
        console.log("Fetching customer details for ID:", customerId);

        $.ajax({
            url: `/customers/${customerId}/edit`,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.customer) {
                    const customer = response.customer;
                    console.log("Customer data received:", customer);
                    
                    $('#edit_customerId').val(customer.customerId);
                    $('#edit_customerType').val(customer.customerType);
                    $('#edit_name').val(customer.name);
                    $('#edit_email').val(customer.email);
                    $('#edit_nric').val(customer.nric);
                    $('#edit_gender').val(customer.gender);
                    $('#edit_dateOfBirth').val(customer.dateOfBirth);
                    $('#edit_religion').val(customer.religion);
                    $('#edit_race').val(customer.race);
                    $('#edit_phoneNum').val(customer.phoneNum);
                    $('#edit_status').val(customer.status);
                    $('#edit_address').val(customer.address);

                    if (customer.profilePicture) {
                        $('#edit_imagePreview').attr('src', customer.profilePicture).show();
                    } else {
                        $('#edit_imagePreview').hide();
                    }

                    const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
                    editModal.show();
                } else {
                    console.error("Invalid response format:", response);
                    alert("Error loading customer details");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching customer:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
                alert("Error fetching customer details");
            }
        });
    });

    $("#editCustomerForm").on("submit", function (e) {
        e.preventDefault();

        // Use FormData to handle file uploads
        const formData = new FormData(this);
        const customerId = $("#edit_customerId").val();

        $.ajax({
            url: `/customers/update/${customerId}`,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    $("#success-message").text("Customer updated successfully!");
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                    $("#editCustomerModal").modal("hide");
                    $("#customers-table").DataTable().ajax.reload();
                }
            },
            error: function (xhr) {
                let errorMessage = "Error updating customer. Please try again.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $("#warning-message").text(errorMessage);
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
                console.error("Update error:", xhr.responseJSON);
            },
        });
    });

    // Delete button click handler
    $(document).on("click", ".delete-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#customers-table").DataTable().row(row).data();

        if (confirm("Are you sure you want to delete this customer?")) {
            $.ajax({
                url: `/customers/${data.customerId}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.success) {
                        $("#customers-table").DataTable().ajax.reload();
                        $("#success-message").text("Customer deleted successfully");
                        $("#success-alert").fadeIn().delay(3000).fadeOut();
                    }
                },
                error: function () {
                    $("#warning-message").text("Error deleting customer");
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                },
            });
        }
    });

    // Image preview functionality
    $(document).ready(function() {
        $("#profilePicture").change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    $("#imagePreview")
                        .attr("src", e.target.result)
                        .css("display", "block");
                };
                
                reader.readAsDataURL(file);
            } else {
                $("#imagePreview")
                    .attr("src", "")
                    .css("display", "none");
            }
        });

        // Reset image preview when form is cleared
        $("#resetButton").click(function() {
            $("#imagePreview")
                .attr("src", "")
                .css("display", "none");
        });
    });

    let customerIdValid = true;
    let nricValid = true;

    // Function to update submit button state
    function updateSubmitButton() {
        $("#btnCreateCustomer").prop('disabled', !(customerIdValid && nricValid));
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Check Customer ID
    $('#customerId').on('input', debounce(function() {
        const customerId = $(this).val();
        if (customerId) {
            $.ajax({
                url: "/check-customer-id",
                type: "POST",
                data: {
                    customerId: customerId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $("#customerIdMessage").html(response.message);
                    customerIdValid = !response.exists;
                    updateSubmitButton();
                },
                error: function(xhr, status, error) {
                    console.error("Error checking Customer ID:", error);
                    $("#customerIdMessage").html('<span class="text-danger">Error checking Customer ID</span>');
                    customerIdValid = false;
                    updateSubmitButton();
                }
            });
        } else {
            $("#customerIdMessage").html("");
            customerIdValid = true;
            updateSubmitButton();
        }
    }, 500));

    // Check NRIC
    $('#nric').on('input', debounce(function() {
        const nric = $(this).val();
        if (nric) {
            $.ajax({
                url: "/check-nric",
                type: "POST",
                data: {
                    nric: nric,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $("#nricMessage").html(response.message);
                    nricValid = !response.exists;
                    updateSubmitButton();
                },
                error: function(xhr, status, error) {
                    console.error("Error checking NRIC:", error);
                    $("#nricMessage").html('<span class="text-danger">Error checking NRIC</span>');
                    nricValid = false;
                    updateSubmitButton();
                }
            });
        } else {
            $("#nricMessage").html("");
            nricValid = true;
            updateSubmitButton();
        }
    }, 500));

    // Reset form handler
    $("#resetButton").click(function() {
        $("#addCustomerForm")[0].reset();
        $("#customerIdMessage, #nricMessage").html("");
        customerIdValid = true;
        nricValid = true;
        updateSubmitButton();
        $("#imagePreview").attr("src", "").hide();
    });

    $('#addCustomerForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#success-message').text(response.message);
                    $('#success-alert').fadeIn().delay(3000).fadeOut();
                    
                    // Close modal and reset form
                    $('#addCustomerModal').modal('hide');
                    $('#addCustomerForm')[0].reset();
                    $('#imagePreview').attr('src', '').hide();
                    
                    // Reload DataTable
                    $('#customers-table').DataTable().ajax.reload();
                }
            },
            error: function(xhr) {
                // Show error message
                let errorMessage = 'An error occurred while creating the customer.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#warning-message').text(errorMessage);
                $('#warning-alert').fadeIn().delay(3000).fadeOut();
            }
        });
    });

    // Add image preview functionality
    $('#profilePicture').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Add image preview functionality for edit form
    $('#edit_profilePicture').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#edit_imagePreview')
                    .attr('src', e.target.result)
                    .show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#edit_imagePreview')
                .attr('src', '')
                .hide();
        }
    });

    // Export dropdown functionality
    $(document).on('click', '.export-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const exportUrl = $(this).attr('href');
        
        // Show loading state
        const $exportBtn = $('#exportDropdown');
        const originalText = $exportBtn.html();
        $exportBtn.html('<i class="fas fa-spinner fa-spin"></i> Exporting...');
        
        // Download the file
        window.location.href = exportUrl;
        
        // Reset button text after a short delay
        setTimeout(function() {
            $exportBtn.html(originalText);
        }, 2000);
    });

    // Add form submission handler for import
    $("#importForm").on("submit", function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: "/customers/import",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#success-message').text(response.message);
                    $('#success-alert').fadeIn().delay(3000).fadeOut();
                    $('#importModal').modal('hide');
                    $('#importForm')[0].reset();
                    $('#customers-table').DataTable().ajax.reload();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error importing data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#warning-message').text(errorMessage);
                $('#warning-alert').fadeIn().delay(3000).fadeOut();
            }
        });
    });
});



