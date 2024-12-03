$(document).ready(function () {
    var orderId = window.orderId; // Retrieve orderId dynamically

    // Initialize the DataTable with custom filters and no global search
    var table = $("#orderitems-table").DataTable({
        processing: true,
        serverSide: true,
        searching: false, // Disable global search
        ajax: {
            url: "/orders/details/" + orderId,
            type: "GET",
            data: function (d) {
                // Send the custom filters with the AJAX request
                d.filterDishName = $("#filterDishName").val(); // Filter by dish ID or name
                d.filterStatus = $("#filterStatus").val(); // Filter by status
                d.filterStaff = $("#filterStaff").val(); // Filter by staff ID or name
            },
            dataSrc: "data",
        },
        pageLength: 10, // Show 10 items per page
        paging: true, // Enable pagination
        info: true, // Show "Showing 1 to 10 of X entries"
        language: {
            emptyTable: "No items found", // Custom message when the table is empty
            info: "Showing _START_ to _END_ of _TOTAL_ entries", // Info message format
            infoEmpty: "No entries available", // Message when there are no entries
        },
        columns: [
            { data: "dishId" },
            { data: "dishName" },
            { data: "quantity" },
            { data: "created_at" },
            { data: "servedBy" },
            { data: "orderItemStatus" },
            { data: "remark" },
            {
                data: "orderItemId",
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

            adjustSidebarHeight();
        },
    });

    // When a filter changes, reload the table data based on the new filter values
    $("#filterDishName, #filterStatus, #filterStaff").on("change", function () {
        table.ajax.reload(); // Reload the table with the new filters
    });

    // Function to reload the page
    function reloadPage() {
        window.location.reload();
    }

    // Initialize tooltips for elements with the title attribute
    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll("[title]")
    );
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: "bottom",
            boundary: "viewport",
        });
    });

    // Function to adjust the sidebar height
    function adjustSidebarHeight() {
        const contentHeight = $(".main-content").height();
        const windowHeight = $(window).height();
        const sidebarHeight = Math.max(contentHeight, windowHeight);
        $(".sidebar").css("height", sidebarHeight + "px");
    }

    // Adjust sidebar height on window resize
    $(window).on("resize", adjustSidebarHeight);

    $("#addOrderItemsBtn").on("click", function () {
        const myModal = new bootstrap.Modal(
            document.getElementById("addOrderItemsModal")
        );

        // Disable the Create button initially
        $("#btncreate").prop("disabled", true);

        myModal.show();

        getMenuItems();
    });

    $(".btn-close").on("click", function () {
        reloadPage();
    });

    // Attach event listeners for Add and Edit Modals
    $("#staffId").on("input", function () {
        checkStaffUsername("add");
    });

    $("#staffId").on("keyup", function () {
        getStaffDetail(this.value, "add");
    });

    $("#edit_staffId").on("input", function () {
        checkStaffUsername("edit"); // Check if staff exists
    });

    $("#edit_staffId").on("input keyup", function () {
        getStaffDetail(this.value, "edit"); // Get staff details
    });

    // Function to check if all conditions are met to enable the Create button
    function checkCreateButtonStatus() {
        let allOrderItemsValid = true;

        $(".item-name").each(function () {
            const selectedValue = $(this).val();
            const isItemDisabled = $(this)
                .find("option:selected")
                .prop("disabled");

            console.log("Checking item:", $(this).attr("id"));
            console.log("Selected value:", selectedValue);
            console.log("Is Disabled:", isItemDisabled);

            if (selectedValue === "" || isItemDisabled) {
                allOrderItemsValid = false;
            }
        });

        console.log("All items valid:", allOrderItemsValid);

        if (allOrderItemsValid) {
            console.log("Enabling create button");
            $("#btncreate").prop("disabled", false);
        } else {
            console.log("Disabling create button");
            $("#btncreate").prop("disabled", true);
        }
    }

    // Store initial values of the fields when the Edit button is clicked
    let initial_quantity,
        initial_status,
        initial_servedBy,
        initial_staffName,
        initial_remark;

    function checkUpdateButtonStatus() {
        const edit_quantity = $("#edit_quantity").val();
        const edit_staffId = $("#edit_staffId").val();
        const edit_staffName = $("#edit_staffName").val();
        const edit_remark = $("#edit_remark").val();
        const edit_status = $("#edit_status").val();

        // Enable the Update button if any field has been changed
        if (
            edit_quantity !== initial_quantity ||
            edit_status !== initial_status ||
            edit_staffId !== initial_servedBy ||
            edit_staffName !== initial_staffName ||
            edit_remark !== initial_remark
        ) {
            $("#btnupdate").prop("disabled", false);
        } else {
            $("#btnupdate").prop("disabled", true);
        }
    }

    // Call checkCreateButtonStatus on relevant input events
    $(".item-name").on("input change", function () {
        checkCreateButtonStatus();
    });

    // Call checkUpdateButtonStatus on relevant input events for order items
    $("#edit_quantity, #edit_status, #edit_staffId, #edit_remarks").on(
        "change",
        function () {
            checkUpdateButtonStatus();
        }
    );

    // Function to check staff ID existence and display status
    function checkStaffUsername(modalType) {
        const staffIdField =
            modalType === "edit" ? "#edit_staffId" : "#staffId";
        const usernameField =
            modalType === "edit" ? "#check-edit-username" : "#check-username";

        $.ajax({
            url: "/check-staff",
            data: { staffId: $(staffIdField).val() },
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                console.log("Check Staff Response:", data);
                $(usernameField).html(data.message);
            },
            error: function () {
                console.error("Error checking staff ID");
                $(usernameField).html(
                    '<span style="color:red">Error checking staff ID</span>'
                );
            },
        });
    }

    function getStaffDetail(staffId, modalType) {
        const nameField =
            modalType === "edit" ? "#edit_staffName" : "#staffName";

        if (staffId.length === 0) {
            $(nameField).val("");
            return;
        }

        $.ajax({
            url: "/get-staff-detail",
            data: { staffId: staffId },
            type: "GET",
            success: function (response) {
                if (response && response.name) {
                    console.log("Get Staff Detail Response:", response); // Debugging output
                    $(nameField).val(response.name);
                } else {
                    $(nameField).val("");
                }
            },
            error: function () {
                console.error("Error fetching staff details");
                $(nameField).val("");
            },
        });
    }

    function getMenuItems() {
        $(".item-name").each(function () {
            const $select = $(this); // The current dropdown

            // Store the currently selected value (if any)
            const selectedDishId = $select.val();

            $.ajax({
                url: "/get-menu-items",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    // Clear previous options and add the default placeholder option
                    $select
                        .empty()
                        .append(
                            '<option value="" disabled selected>Select an item</option>'
                        );

                    // Handle the menu items
                    data.forEach(function (item) {
                        const isDisabled = $(".item-name")
                            .toArray()
                            .some(function (otherSelect) {
                                return (
                                    $(otherSelect).val() == item.dishId &&
                                    $(otherSelect)[0] !== $select[0]
                                );
                            });

                        // Append menu items, disabling previously selected items in other rows
                        $select.append(
                            `<option value="${item.dishId}" data-price="${
                                item.price
                            }" ${isDisabled ? "disabled" : ""}>
                                ${item.dishName}
                            </option>`
                        );
                    });

                    // If the dropdown had a selected value, restore it
                    if (selectedDishId) {
                        $select.val(selectedDishId);
                    }

                    // Recheck the Create button status
                    checkCreateButtonStatus();
                },
                error: function (xhr, error, thrown) {
                    console.error("Error fetching menu items:", error, xhr);
                },
            });
        });
    }

    $("#orderitems-items").on("change", ".item-name", function () {
        const $row = $(this).closest("tr");
        const unitPrice = parseFloat($(this).find(":selected").data("price"));
        const quantity = parseInt($row.find(".quantity").val());

        $row.find(".unit-price").text(unitPrice);
        $row.find(".total-price").text((unitPrice * quantity).toFixed(2));
        calculateGrandTotal();
        checkCreateButtonStatus();
    });

    $(document).on("input", ".quantity", function () {
        const $row = $(this).closest("tr");
        const unitPrice = parseFloat($row.find(".unit-price").text());
        const quantity = parseInt($(this).val());

        $row.find(".total-price").text((unitPrice * quantity).toFixed(2));
        calculateGrandTotal();
    });

    let rowCounter = 1; // To keep track of the number of rows

    // Add new row
    $("#addMoreBtn").on("click", function () {
        const newRow = `
        <tr class="orderitems-item">
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
                <input type="text" class="form-control staff-id" id="staffId" />
            </td>
            <td>
                <input type="text" class="form-control staff-name" id="staffName" readonly style="background-color: #f0f0f0; border: 1px solid #ccc; color: #666; cursor: not-allowed;"/>
            </td>
            <td>
                <select id="status" class="form-select">
                    <option value="Pending" selected>Pending</option>
                    <option value="Cooking">Cooking</option>
                    <option value="Ready to serve">Ready to serve</option>
                    <option value="Served">Served</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control remarks" />
            </td>
            <td>
                <button class="btn btn-danger remove-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

        $("#orderitems-items tbody").append(newRow); // Append the new row to the table
        rowCounter++; // Increase the row counter

        getMenuItems();
        $("#btncreate").prop("disabled", true); // Disable create button due to new empty value
        checkRemoveButtonVisibility(); // Check if any row needs to be removed
    });

    // Remove row
    $("#orderitems-items").on("click", ".remove-btn", function () {
        if (rowCounter > 1) {
            $(this).closest("tr").remove(); // Remove the clicked row
            rowCounter--; // Decrease the row counter
            checkRemoveButtonVisibility(); // Check if any row needs to be removed
        }
    });

    // Function to show/hide remove buttons based on row count
    function checkRemoveButtonVisibility() {
        const rows = $("#orderitems-items tbody tr");
        rows.each(function (index) {
            const removeBtn = $(this).find(".remove-btn");
            if (rows.length <= 1) {
                removeBtn.hide(); // Hide the remove button if only one row remains
            } else {
                removeBtn.show(); // Show the remove button if there are multiple rows
            }
        });
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        $(".total-price").each(function () {
            grandTotal += parseFloat($(this).text()) || 0;
        });
        $("#grandTotal").text(grandTotal.toFixed(2));
    }

    $("#addorderitems").on("submit", function (e) {
        e.preventDefault(); // Prevent default form submission

        const formData = {
            orderId: $("#orderId").val(), // Ensure orderId is included
            orderItems: [], // Initialize orderItems array
        };

        // Loop through each item row and collect the data
        $("#orderitems-items tbody .orderitems-item").each(function () {
            const item = {
                dishId: $(this).find(".item-name").val(),
                quantity: parseInt($(this).find(".quantity").val()),
                status: "Pending", // Default status
                servedBy: $(this).find(".staff-id").val(),
                remark: $(this).find(".remarks").val(),
            };

            formData.orderItems.push(item); // Add item to orderItems array
        });

        // Make AJAX request to save order items
        $.ajax({
            url: `/orders/details/${formData.orderId}/store`,
            method: "POST",
            data: JSON.stringify(formData), // Send data as JSON
            contentType: "application/json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $("#success-message").text(
                        "Order Items created successfully!"
                    );
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                    $("#addOrderItemsModal").modal("hide");
                    $("#resetButton").click();
                    $("#orderitems-table").DataTable().ajax.reload();
                    reloadPage();
                }
            },
            error: function (xhr) {
                console.log(xhr);

                // Extract and display validation errors
                let errors = xhr.responseJSON?.message || {};
                let errorMessage =
                    "Error creating order items. Please try again.";

                if (typeof errors === "object") {
                    errorMessage = Object.values(errors).flat().join(", ");
                }

                $("#warning-message").text(errorMessage);
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Handle the click event for the Edit button
    $(document).on("click", ".edit-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#orderitems-table").DataTable().row(row).data(); // Retrieve data from DataTable

        // Make an AJAX request to get the order item details
        $.ajax({
            url: `/orders/details/${data.orderId}/edit/${data.orderItemId}`, // Use the correct orderId and orderItemId
            method: "GET",
            success: function (response) {
                if (response.success) {
                    const orderItem = response.data;

                    // Store initial values for reset or tracking purposes
                    initial_quantity = orderItem.quantity;
                    initial_status = orderItem.orderItemStatus;
                    initial_servedBy = orderItem.servedBy;
                    initial_staffName = orderItem.staffName;
                    initial_remark = orderItem.remark;

                    // Set item name as a label (non-editable)
                    $("#edit_itemId").val(orderItem.dishId);
                    $("#edit_itemName").val(orderItem.dishName);

                    // Populate other modal form fields
                    $("#edit_quantity").val(orderItem.quantity);
                    $("#edit_status").val(orderItem.status);
                    $("#edit_staffId").val(orderItem.servedBy);
                    $("#edit_staffName").val(orderItem.staffName);
                    $("#edit_remarks").val(orderItem.remark);

                    // Set hidden orderId and orderItemId fields
                    $("#orderId").val(data.orderId);
                    $("#orderItemId").val(data.orderItemId);

                    const unitPrice = parseFloat(data.unitPrice).toFixed(2);
                    const totalPrice = parseFloat(
                        unitPrice * orderItem.quantity
                    ).toFixed(2);
                    $("#edit_unitPrice").text(unitPrice);
                    $("#edit_totalPrice").text(totalPrice);

                    // Fetch staff name if servedBy ID exists
                    if (orderItem.servedBy) {
                        getStaffDetail(orderItem.servedBy, "edit");
                    }

                    // Disable the Update button initially
                    $("#btnupdate").prop("disabled", true);

                    // Show the edit modal
                    $("#editOrderItemsModal").modal("show");
                } else {
                    console.error("Error:", response.message);
                }
            },
            error: function () {
                alert("Error fetching order item details.");
            },
        });
    });

    // Update button click handler for order items
    $("#editOrderItemForm").on("submit", function (e) {
        e.preventDefault();
        // Retrieve orderItemId and orderId from hidden fields in the form
        const orderItemId = $("#orderItemId").val(); // Retrieve the correct orderItemId
        const orderId = $("#orderId").val(); // Retrieve the correct orderId
        // Make an AJAX request to update the order item
        $.ajax({
            url: `/orders/details/${orderId}/update/${orderItemId}`, // Use the correct orderId and orderItemId
            method: "PUT",
            data: {
                dishId: $("#edit_itemId").val(),
                quantity: $("#edit_quantity").val(),
                status: $("#edit_status").val(),
                servedBy: $("#edit_staffId").val(),
                remark: $("#edit_remarks").val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $("#editOrderItemsModal").modal("hide");
                    $("#orderitems-table").DataTable().ajax.reload();
                    $("#success-message").text(
                        "Order item updated successfully!"
                    );
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                } else {
                    $("#warning-message").text(
                        response.message || "Error updating order item"
                    );
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                }

                reloadPage();
            },
            error: function (xhr) {
                console.log("Update error:", xhr);
                const errors =
                    xhr.responseJSON?.message || "Error updating order item";
                $("#warning-message").text(errors);
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Delete button click handler
    $(document).on("click", ".delete-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#orderitems-table").DataTable().row(row).data();
        // Show confirmation dialog
        if (confirm("Are you sure you want to delete this order item?")) {
            $.ajax({
                url: `/orders/details/${data.orderId}/${data.orderItemId}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    if (response.success) {
                        // Refresh the DataTable
                        $("#orderitems-table").DataTable().ajax.reload();

                        // Show success message
                        $("#success-message").text(
                            "Order item deleted successfully"
                        );
                        $("#success-alert").fadeIn().delay(3000).fadeOut();

                        reloadPage();
                    }
                },
                error: function () {
                    // Show error message
                    $("#warning-message").text("Error deleting order item");
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                },
            });
        }
    });
});
