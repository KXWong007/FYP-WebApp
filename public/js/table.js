jQuery(document).ready(function ($) {
    const table = $("#tables-table").DataTable({
        processing: true,
        serverSide: false, // Change to false for simpler debugging
        ajax: {
            url: "/table",
            type: "GET",
        },
        columns: [
            { data: "tableNum" },
            { data: "capacity" },
            { data: "area" },
            { data: "status" },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <div class="action-buttons">
                            <button class="btn btn-dark edit-btn">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-dark delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-dark qr-btn" data-table-num="{{ $table->tableNum }}">
                                <i class="fa-solid fa-qrcode"></i>
                            </button>

                        </div>
                    `;
                },
            },
        ],
        responsive: true,
        drawCallback: function (settings) {
            // Initialize tooltips for buttons on each draw
            var tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            var tooltipList = tooltipTriggerList.map(function (
                tooltipTriggerEl
            ) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    placement: "bottom",
                    boundary: "viewport",
                });
            });

            // Adjust sidebar height after table updates
            adjustSidebarHeight();
        },
    });

    function reloadPage() {
        window.location.reload();
    }

    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll("[title]")
    );
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: "bottom",
            boundary: "viewport",
        });
    });
    function adjustSidebarHeight() {
        const contentHeight = $(".main-content").height();
        const windowHeight = $(window).height();
        const sidebarHeight = Math.max(contentHeight, windowHeight);
        $(".sidebar").css("height", sidebarHeight + "px");
    }

    // Also adjust on window resize
    $(window).on("resize", adjustSidebarHeight);

    $(".btn-close").on("click", function () {
        reloadPage();
    });

    // Open modal for adding a new table
    $("#addTablesBtn").on("click", function () {
        const myModal = new bootstrap.Modal(document.getElementById("addTablesModal"));
        myModal.show();
    });

    // Handle form submission for adding a new table
    $("#addtables").on("submit", function (e) {
        e.preventDefault();
        console.log("Submit form for adding table.");

        const formData = {
            tableNum: $('#tableNum').val(),
            capacity: $('#capacity').val(),
            area: $('#area').val(),
            status: $('#status').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: "/table/store",
            method: 'POST',
            data: formData,
            success: function (response) {
                console.log("Add table response:", response);
                if (response.success) {
                    $('#addTablesModal').modal('hide');
                    showSuccessMessage("Table added successfully!");
                    table.ajax.reload();
                    $('#addtables')[0].reset();  // Clear form
                } else {
                    showErrorMessage("Failed to add table.");
                }
            },
            error: function (xhr) {
                console.error("Error adding table:", xhr.responseText);
                showErrorMessage("Error adding table.");
            }
        });
    });

    // Reset form when reset button is clicked
    $('#resetButton').on('click', function () {
        console.log("Reset button clicked.");
        $('#addtables')[0].reset();
    });

    // Show success message
    function showSuccessMessage(message) {
        $("#success-message").text(message);
        $("#success-alert").fadeIn().delay(3000).fadeOut();
    }

    // Show error message
    function showErrorMessage(message) {
        $("#warning-message").text(message);
        $("#warning-alert").fadeIn().delay(3000).fadeOut();
    }

    // Handle the edit button click
    $(document).on("click", ".edit-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#tables-table").DataTable().row(row).data();

        $.ajax({
            url: `/table/edit/${data.tableNum}`, // Adjust URL to match the edit route for table
            method: "GET",
            success: function (response) {
                if (response.success) {
                    const tableData = response.data;

                    // Set initial values for the table fields
                    const initialTableNum = tableData.tableNum;
                    const initialCapacity = tableData.capacity;
                    const initialArea = tableData.area;
                    const initialStatus = tableData.status;

                    // Populate the fields in the Edit Table Modal
                    $("#edit_tableNum").val(initialTableNum);
                    $("#edit_capacity").val(initialCapacity);
                    $("#edit_area").val(initialArea);
                    $("#edit_status").val(initialStatus);

                    // Disable the Update button initially
                    $("#btnupdate").prop("disabled", true);

                    // Open the Edit Table Modal
                    $("#editTableModal").modal("show");
                } else {
                    showErrorMessage('Error fetching table details.');
                }
            },
            error: function () {
                showErrorMessage('Error fetching table details.');
            },
        });
    });

    // Update button click handler
    $("#editTableForm").on("submit", function (e) {
        e.preventDefault();
        const tableNum = $("#edit_tableNum").val();

        $.ajax({
            url: `/table/update/${tableNum}`,
            method: "PUT",
            data: {
                capacity: $("#edit_capacity").val(),
                area: $("#edit_area").val(),
                status: $("#edit_status").val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $("#editTableModal").modal("hide");
                    $("#tables-table").DataTable().ajax.reload();
                    $("#success-message").text("Table updated successfully");
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                } else {
                    $("#warning-message").text(
                        response.message || "Error updating table"
                    );
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                }
            },
            error: function (xhr) {
                console.log("Update error:", xhr);
                const errors =
                    xhr.responseJSON?.message || "Error updating table";
                $("#warning-message").text(errors);
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Delete button click handler
    $(document).on("click", ".delete-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#tables-table").DataTable().row(row).data();

        if (confirm("Are you sure you want to delete this table?")) {
            $.ajax({
                url: `/table/${data.tableNum}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.success) {
                        $("#tables-table").DataTable().ajax.reload();
                        showSuccessMessage("Table deleted successfully");
                    }
                },
                error: function () {
                    showErrorMessage("Error deleting table");
                },
            });
        }
    });

    // When the QR button is clicked
    $(document).on("click", ".qr-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#tables-table").DataTable().row(row).data();

        // Set the table number dynamically in the modal
        $('#modalTableNum').text(data.tableNum);

        // Reset the modal content
        $('#qrCodeImageContainer').html('<p class="text-muted">QR Code will appear here after generation.</p>');
        $('#viewQRCodeBtn').hide(); // Hide the "View QR Image" button initially

        // Open the modal
        const qrModal = new bootstrap.Modal(document.getElementById("qrCodeModal"));
        qrModal.show();
    });

    // When the Generate button is clicked
    $(document).on("click", "#generateQRCodeBtn", function () {
        const tableNum = $('#modalTableNum').text(); // Get the table number from the modal

        // Show confirmation dialog
        const isConfirmed = confirm(`Are you sure you want to generate a new QR code for Table ${tableNum}?`);

        if (isConfirmed) {
            // Send AJAX request to generate the QR code
            $.ajax({
                url: `/table/generate-qrcode/${tableNum}`, // Update this to match your route
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"), // CSRF token
                },
                success: function (response) {
                    if (response.success) {
                        // Display a success message in the container
                        $('#qrCodeImageContainer').html('<p class="text-success">QR Code generated successfully.</p>');

                        // Show the "View QR Image" button
                        $('#viewQRCodeBtn')
                            .attr('onclick', `window.open('/table/view-qrcode/${tableNum}', '_blank')`) // Link to QR image
                            .show();
                    } else {
                        console.error("Failed to generate QR code: " + response.message);
                        alert("Error: " + response.message);
                    }
                },
                error: function (xhr) {
                    console.error("Error fetching QR code:", xhr.responseText);
                    alert("An error occurred while generating the QR code.");
                }
            });
        }
    });

    // When the "View QR Image" button is clicked
    $(document).on("click", "#viewQRCodeBtn", function () {
        const tableNum = $('#modalTableNum').text();
        window.open(`/table/view-qrcode/${tableNum}`, '_blank'); // Open the QR code image in a new tab
    });

});
