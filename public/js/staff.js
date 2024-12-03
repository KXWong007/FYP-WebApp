jQuery(document).ready(function ($) {
    $("#staff-table").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        ajax: {
            url: "/staff",
            type: "GET",
            dataSrc: 'data'
        },
        columns: [
            { data: "staffId", name: "staffId" },
            { data: "staffType", name: "staffType" },
            { data: "name", name: "name" },
            { data: "email", name: "email" },
            { data: "phone", name: "phone" },
            { data: "status", name: "status" },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="action-buttons">
                            <button class="btn btn-dark btn-sm edit-btn" data-id="${data.staffId}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-dark btn-sm delete-btn" data-id="${data.staffId}">
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

    $("#addStaffBtn").on("click", function () {
        const myModal = new bootstrap.Modal(document.getElementById("addStaffModal"));
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
    
    $("#staff-table").on("click", ".delete-btn", function () {
        var id = $(this).data("id");
        // Implement delete staff functionality
    });

    $("#staffId").on("input", function () {
        checkUsername();
    });

    $("#staffId").on("keyup", function () {
        getStaffrDetail(this.value);
    });

    function checkUsername() {
        $.ajax({
            url: "/check-staff",
            data: { staffId: $("#staffId").val() },
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                $("#check-username").html(data.message);
                $("#btncreate").prop("disabled", !data.exists);
            },
            error: function () {
                $("#check-username").html('<span style="color:red">Error checking staff ID</span>');
            },
        });
    }

    function getStaffDetail(staffId) {
        if (staffId.length === 0) {
            $("#sName").val("");
            return;
        }

        $.ajax({
            url: "/get-staff-detail",
            data: { staffId: staffId },
            type: "GET",
            success: function (response) {
                console.log("Response:", response);
                if (response && response.name) {
                    $("#sName").val(response.name);
                } else {
                    $("#sName").val("");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                $("#sName").val("");
            },
        });
    }

    $("#resetButton").on("click", function () {
        $("#addStaffForm")[0].reset();
        $("#staffId").val("");
        $("#name").val("");
        $("#email").val("");
        $("#gender").val("Select");
        $("#phone").val("");
        $("#staffIdMessage, #nricMessage").html("");
        $("#imagePreview").attr("src", "").hide();
        staffIdValid = true;
        nricValid = true;
        updateSubmitButton();
    });

    $(document).on("click", ".edit-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#staff-table").DataTable().row(row).data();

        $.ajax({
            url: `/staff/${data.staffId}`,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.staff) {
                    const staff = response.staff;
                    $("#edit_staffId").val(staff.staffId);
                    $("#edit_staffType").val(staff.staffType);
                    $("#edit_name").val(staff.name);
                    $("#edit_email").val(staff.email);
                    $("#edit_gender").val(staff.gender);
                    $("#edit_religion").val(staff.religion);
                    $("#edit_race").val(staff.race);
                    $("#edit_nric").val(staff.nric);
                    $("#edit_dateOfBirth").val(staff.dateOfBirth);
                    $("#edit_phone").val(staff.phone);
                    $("#edit_address").val(staff.address);
                    $("#edit_status").val(staff.status);
                    
                    if (staff.profilePicture) {
                        $("#edit_imagePreview").attr('src', '/' + staff.profilePicture).show();
                    } else {
                        $("#edit_imagePreview").hide();
                    }
                    
                    $("#editStaffModal").modal("show");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error details:", error);
                $("#warning-message").text("Error fetching staff details");
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            }
        });
    });

    $("#editStaffForm").on("submit", function (e) {
        e.preventDefault();

        // Use FormData to handle file uploads
        const formData = new FormData(this);
        const staffId = $("#edit_staffId").val();

        $.ajax({
            url: `/staff/update/${staffId}`,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    $("#success-message").text("Staff updated successfully!");
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                    $("#editStaffModal").modal("hide");
                    $("#staff-table").DataTable().ajax.reload();
                }
            },
            error: function (xhr) {
                let errorMessage = "Error updating staff. Please try again.";
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
        const data = $("#staff-table").DataTable().row(row).data();

        if (confirm("Are you sure you want to delete this staff?")) {
            $.ajax({
                url: `/staff/${data.staffId}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.success) {
                        $("#staff-table").DataTable().ajax.reload();
                        $("#success-message").text("Staff deleted successfully");
                        $("#success-alert").fadeIn().delay(3000).fadeOut();
                    }
                },
                error: function () {
                    $("#warning-message").text("Error deleting staff");
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

    let staffIdValid = true;
    let nricValid = true;

    // Function to update submit button state
    function updateSubmitButton() {
        $("#btnCreateStaff").prop('disabled', !(staffIdValid && nricValid));
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Check Staff ID
    $('#staffId').on('input', debounce(function() {
        const staffId = $(this).val();
        if (staffId) {
            $.ajax({
                url: "/check-staff-id",
                type: "POST",
                data: {
                    staffId: staffId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $("#staffIdMessage").html(response.message);
                    staffIdValid = !response.exists;
                    updateSubmitButton();
                },
                error: function(xhr, status, error) {
                    console.error("Error checking Staff ID:", error);
                    $("#staffIdMessage").html('<span class="text-danger">Error checking Staff ID</span>');
                    customerIdValid = false;
                    updateSubmitButton();
                }
            });
        } else {
            $("#staffIdMessage").html("");
            staffIdValid = true;
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
        $("#addStaffForm")[0].reset();
        $("#staffIdMessage, #nricMessage").html("");
        staffIdValid = true;
        nricValid = true;
        updateSubmitButton();
        $("#imagePreview").attr("src", "").hide();
    });

    $('#addStaffForm').on('submit', function(e) {
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
                    $('#addStaffModal').modal('hide');
                    $('#addStaffForm')[0].reset();
                    $('#imagePreview').attr('src', '').hide();
                    
                    // Reload DataTable
                    $('#staff-table').DataTable().ajax.reload();
                }
            },
            error: function(xhr) {
                // Show error message
                let errorMessage = 'An error occurred while creating the staff.';
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
            url: "/staff/import",
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
                    $('#staff-table').DataTable().ajax.reload();
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

