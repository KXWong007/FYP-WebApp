jQuery(document).ready(function ($) {
    // Initialize DataTable for inventory
    const inventoryTable = $("#inventory-table").DataTable({
        processing: true,
        serverSide: false, // Disable server-side processing for now
        ajax: {
            url: "/inventory",
            type: "GET",
        },
        columns: [
            { data: "inventoryId" },
            { data: "itemName" },
            { data: "quantity" },
            { data: "minimum" }, // Fixed typo here
            { data: "maximum" },
            { data: "unitPrice" },
            { data: "measurementUnit" },
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

    // Open modal for adding a new inventory item
    $("#addInventoryBtn").on("click", function () {
        const myModal = new bootstrap.Modal(document.getElementById("addInventoryModal"));
        myModal.show();
    });

    // Handle form submission for adding a new table
    $("#addinventory").on("submit", function (e) {
        e.preventDefault();
        console.log("Submit form for adding inventory item.");

        const formData = {
            inventoryId: $('#inventoryId').val(),
            itemName: $('#itemName').val(),
            quantity: $('#quantity').val(),
            minimum: $('#minimum').val(),
            maximum: $('#maximum').val(),
            unitPrice: $('#unitPrice').val(),
            measurementUnit: $('#measurementUnit').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: "inventory/store",
            method: 'POST',
            data: formData,
            success: function (response) {
                console.log("Add inventory response:", response);
                if (response.success) {
                    $('#addInventoryModal').modal('hide');
                    showSuccessMessage("Inventory item added successfully!");
                    inventoryTable.ajax.reload(); // Reload inventory table (assumes DataTable)
                    $('#addinventory')[0].reset(); // Clear form
                } else {
                    showErrorMessage("Failed to add inventory item.");
                }
            },
            error: function (xhr) {
                console.error("Error adding inventory item:", xhr.responseText);
                showErrorMessage("Error adding inventory item.");
            }
        });
    });

    // Reset form when reset button is clicked
    $('#resetButton').on('click', function () {
        console.log("Reset button clicked.");
        $('#addinventory')[0].reset();
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
        const data = $("#inventory-table").DataTable().row(row).data();

        $.ajax({
            url: `/inventory/edit/${data.inventoryId}`, // Adjust URL to match the edit route for inventory
            method: "GET",
            success: function (response) {
                if (response.success) {
                    const inventoryData = response.data;

                    // Set initial values for the inventory fields
                    const initialInventoryId = inventoryData.inventoryId;
                    const initialItemName = inventoryData.itemName;
                    const initialQuantity = inventoryData.quantity;
                    const initialMinimum = inventoryData.minimum;
                    const initialMaximum = inventoryData.maximum;
                    const initialUnitPrice = inventoryData.unitPrice;
                    const initialMeasurementUnit = inventoryData.measurementUnit;

                    // Populate the fields in the Edit Inventory Modal
                    $("#edit_inventoryId").val(initialInventoryId);
                    $("#edit_itemName").val(initialItemName);
                    $("#edit_quantity").val(initialQuantity);
                    $("#edit_minimum").val(initialMinimum);
                    $("#edit_maximum").val(initialMaximum);
                    $("#edit_unitPrice").val(initialUnitPrice);
                    $("#edit_measurementUnit").val(initialMeasurementUnit);

                    // Disable the Update button initially
                    $("#btnupdate").prop("disabled", true);

                    // Open the Edit Inventory Modal
                    $("#editInventoryModal").modal("show");
                } else {
                    showErrorMessage('Error fetching inventory details.');
                }
            },
            error: function () {
                showErrorMessage('Error fetching inventory details.');
            },
        });
    });

    // Update button click handler
    $("#editInventoryForm").on("submit", function (e) {
        e.preventDefault();
        const inventoryId = $("#edit_inventoryId").val();

        $.ajax({
            url: `/inventory/update/${inventoryId}`,
            method: "PUT",
            data: {
                itemName: $("#edit_itemName").val(),
                quantity: $("#edit_quantity").val(),
                minimum: $("#edit_minimum").val(),
                maximum: $("#edit_maximum").val(),
                unitPrice: $("#edit_unitPrice").val(),
                measurementUnit: $("#edit_measurementUnit").val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $("#editInventoryModal").modal("hide");
                    $("#inventory-table").DataTable().ajax.reload();
                    $("#success-message").text("Inventory item updated successfully");
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
                    xhr.responseJSON?.message || "Error updating inventory item";
                $("#warning-message").text(errors);
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Delete button click handler
    $(document).on("click", ".delete-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#inventory-table").DataTable().row(row).data();
        const inventoryId = data.inventoryId; // Make sure to get inventoryId from the table data

        if (confirm("Are you sure you want to delete this item?")) {
            $.ajax({
                url: `/inventory/${inventoryId}`, // Correctly reference the inventoryId in the URL
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.success) {
                        $("#inventory-table").DataTable().ajax.reload();
                        showSuccessMessage("Inventory item deleted successfully");
                    }
                },
                error: function () {
                    showErrorMessage("Error deleting item");
                },
            });
        }
    });

    // Export button click handler
    $('#export-btn').on('click', function () {
        window.location.href = '/inventory/export';
    });

    // Import button click handler
    $('#import-btn').on('click', function () {
        $('#importModal').modal('show');
    });

    // Handle import form submission
    $('#importForm').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData();
        let fileInput = $('#csvFile')[0];

        if (fileInput.files.length > 0) {
            formData.append('csvFile', fileInput.files[0]);

            $.ajax({
                url: '/inventory/import',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        $('#importModal').modal('hide');
                        $('#importForm')[0].reset();
                        $('#reservations-table').DataTable().ajax.reload();

                        // Show success message
                        $("#success-message").text(response.message);
                        $("#success-alert").fadeIn().delay(3000).fadeOut();
                    } else {
                        // Show error message
                        $("#warning-message").text(response.message);
                        $("#warning-alert").fadeIn().delay(3000).fadeOut();
                    }
                },
                error: function (xhr) {
                    // Show error message
                    $("#warning-message").text("Error importing data: " + xhr.responseJSON.message);
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                }
            });
        } else {
            $("#warning-message").text("Please select a file to import");
            $("#warning-alert").fadeIn().delay(3000).fadeOut();
        }
    });
});
