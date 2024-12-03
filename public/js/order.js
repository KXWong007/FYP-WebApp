$(document).ready(function () {
    $("#orders-table").DataTable({
        ajax: {
            url: "/orders",
            type: "GET",
            dataType: "json",
            error: function (xhr, error, thrown) {
                console.error("Error fetching orders:", error, xhr);
            },
        },
        columns: [
            { data: "orderId" },
            { data: "customerId" },
            { data: "tableNum" },
            { data: "orderQuantity" },
            {
                data: "totalAmount",
                render: function (data, type, row) {
                    return `RM ${parseFloat(data).toFixed(2)}`;
                },
            },
            { data: "orderDate" },
            { data: "status" },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                            <div class="action-buttons">
                                <button class="btn btn-dark view-btn">
                                    <i class="fas fa-eye"></i>
                                </button>
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

    $("#addOrderBtn").on("click", function () {
        const myModal = new bootstrap.Modal(
            document.getElementById("addOrderModal")
        );

        // Check status of the Create button initially
        checkCreateButtonStatus();

        myModal.show();

        getTables();
        getMenuItems();
    });

    $(".btn-close").on("click", function () {
        reloadPage();
    });

    // Attach event listeners for Add and Edit Modals
    $("#customerId").on("input", function () {
        checkUsername("add");
    });
    $("#customerId").on("keyup", function () {
        getCustomerDetail(this.value, "add");
    });

    $("#edit_customerId").on("input", function () {
        checkUsername("edit"); // Check if customer exists
    });

    $("#edit_customerId").on("keyup", function () {
        getCustomerDetail(this.value, "edit"); // Get customer details
    });

    // Function to check if all conditions are met to enable the Create button
    function checkCreateButtonStatus() {
        const customerId = $("#customerId").val();
        const customerName = $("#name").val();
        const tableNum = $("#tableNum").val();

        // Check if all order items are selected and not disabled
        const allOrderItemsValid = $(".item-name")
            .toArray()
            .every(function (selectElement) {
                const selectedValue = $(selectElement).val();
                return (
                    selectedValue !== "" && // Ensure the item is selected
                    !$(selectElement).find("option:selected").prop("disabled") // Ensure the item is not disabled
                );
            });

        // Enable the Create button only if all conditions are met
        if (customerId && customerName && tableNum && allOrderItemsValid) {
            $("#btncreate").prop("disabled", false); // Enable button
        } else {
            $("#btncreate").prop("disabled", true); // Disable button
        }
    }

    // Store initial values of the fields when the Edit button is clicked
    let initialCustomerId,
        initialCustomerName,
        initialTableNum,
        initialOrderDate,
        initialStatus;

    // Function to check if any conditions are met to enable the Update button
    function checkUpdateButtonStatus() {
        const edit_customerId = $("#edit_customerId").val();
        const edit_customerName = $("#edit_name").val(); // Ensure we check the name field in Edit form
        const edit_tableNum = $("#edit_tableNum").val();
        const edit_orderDate = $("#edit_orderDate").val();
        const edit_status = $("#edit_status").val();

        // Enable the Update button if any field has been changed
        if (
            edit_customerId !== initialCustomerId ||
            edit_customerName !== initialCustomerName || // Check if customer name has changed
            edit_tableNum !== initialTableNum ||
            edit_orderDate !== initialOrderDate ||
            edit_status !== initialStatus
        ) {
            $("#btnupdate").prop("disabled", false); // Enable button
        } else {
            $("#btnupdate").prop("disabled", true); // Disable button
        }
    }

    // Call checkCreateButtonStatus on relevant input events
    $("#customerId, #name, #tableNum, .item-name").on(
        "input change",
        function () {
            checkCreateButtonStatus();
        }
    );

    $(
        "#edit_customerId, #edit_name, #edit_tableNum, #edit_orderDate, #edit_status"
    ).on("input change", function () {
        checkUpdateButtonStatus(); // Validate the Update button status when these fields are updated
    });

    function generateOrderId(callback) {
        $.ajax({
            url: "/generate-order-id",
            success: function (response) {
                if (response.orderId) {
                    callback(response.orderId);
                }
            },
        });
    }

    function generateOrderDate(callback) {
        $.ajax({
            url: "/generate-order-date",
            success: function (response) {
                if (response.orderDate) {
                    callback(response.orderDate);
                }
            },
        });
    }

    // Function to check customer ID existence and display status
    function checkUsername(modalType) {
        const customerIdField =
            modalType === "edit" ? "#edit_customerId" : "#customerId";
        const usernameField =
            modalType === "edit" ? "#check-edit-username" : "#check-username";

        $.ajax({
            url: "/check-customer",
            data: { customerId: $(customerIdField).val() },
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                console.log("Check Customer Response:", data);
                $(usernameField).html(data.message);
            },
            error: function () {
                console.error("Error checking customer ID");
                $(usernameField).html(
                    '<span style="color:red">Error checking customer ID</span>'
                );
            },
        });
    }

    // Function to get customer details and set name
    function getCustomerDetail(customerId, modalType) {
        const nameField = modalType === "edit" ? "#edit_name" : "#name";

        if (customerId.length === 0) {
            $(nameField).val("");
            return;
        }

        $.ajax({
            url: "/get-customer-detail",
            data: { customerId: customerId },
            type: "GET",
            success: function (response) {
                console.log("Get Customer Detail Response:", response); // Debugging output
                if (response && response.name) {
                    $(nameField).val(response.name);
                } else {
                    $(nameField).val("");
                }
            },
            error: function () {
                console.error("Error fetching customer details");
                $(nameField).val("");
            },
        });
    }

    function getTables(tableNum = null) {
        const tableSelect = tableNum ? $("#edit_tableNum") : $("#tableNum"); // Choose the correct select element based on context

        // Clear existing options
        tableSelect.empty();

        // Always add "Select a table" option
        tableSelect.append(
            '<option value="" disabled selected>Select a table</option>'
        );

        $.ajax({
            url: "/get-tables", // Assuming this returns a list of tables from the server
            type: "GET",
            dataType: "json",
            success: function (data) {
                const groupedTables = {};

                // Group tables by area for better organization
                data.forEach(function (table) {
                    if (!groupedTables[table.area]) {
                        groupedTables[table.area] = [];
                    }
                    groupedTables[table.area].push(table);
                });

                // Loop through each area and append tables
                for (const area in groupedTables) {
                    tableSelect.append(
                        `<option value="" disabled>${area}</option>`
                    ); // Add area heading
                    groupedTables[area].forEach(function (table) {
                        const paxInfo = `Pax: ${table.capacity}`;

                        // If tableNum exists, set it as selected
                        const isSelected =
                            table.tableNum === tableNum ? "selected" : "";

                        // Append the table options
                        tableSelect.append(
                            `<option value="${table.tableNum}" ${isSelected}>${table.tableNum} (${paxInfo})</option>`
                        );
                    });
                }
            },
            error: function (xhr, error, thrown) {
                console.error("Error fetching tables:", error, xhr);
            },
        });
    }

    function getMenuItems() {
        // Collect all selected dish IDs across all dropdowns
        const selectedDishIds = $(".item-name")
            .map(function () {
                return $(this).val();
            })
            .get(); // Convert to a plain array

        $(".item-name").each(function () {
            const $select = $(this);
            const selectedDishId = $select.val(); // Store selected value per dropdown

            $.ajax({
                url: "/get-menu-items",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    $select
                        .empty()
                        .append(
                            '<option value="" disabled selected>Select an item</option>'
                        );

                    data.forEach(function (item) {
                        // Disable the option if the dish is already selected in another dropdown
                        const isDisabled =
                            selectedDishIds.includes(item.dishId) &&
                            item.dishId !== selectedDishId;

                        $select.append(
                            `<option value="${item.dishId}" data-price="${
                                item.price
                            }" ${isDisabled ? "disabled" : ""}>${
                                item.dishName
                            }</option>`
                        );
                    });

                    // Restore the selected value after population
                    if (selectedDishId) {
                        $select.val(selectedDishId);
                    }
                },
                error: function (xhr, error, thrown) {
                    console.error("Error fetching menu items:", error, xhr);
                },
            });
        });
    }

    $(document).on("change", ".item-name", function () {
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

    $("#addMoreBtn").on("click", function () {
        const newRow = `
            <tr class="order-item">
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
                    <input type="text" class="form-control remarks" />
                </td>
                <td>
                    <button class="btn btn-danger remove-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $("#order-items tbody").append(newRow);
        getMenuItems(); // Populate the new row's item dropdown
        checkCreateButtonStatus();
        checkRemoveButtonVisibility(); // Check if any row needs to be removed
    });

    $(document).on("click", ".remove-btn", function () {
        $(this).closest("tr").remove(); // Remove the closest table row
        calculateGrandTotal(); // Update the grand total after removal
        checkCreateButtonStatus();
        checkRemoveButtonVisibility(); // Check if any row needs to be removed
    });

    // Function to show/hide remove buttons based on row count
    function checkRemoveButtonVisibility() {
        const rows = $("#order-items tbody tr");
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

    // Add this reset button handler
    $("#resetButton").on("click", function () {
        // Clear all form inputs except orderId
        $("#addorder")[0].reset();

        // Clear other specific fields
        $("#customerId").val("");
        $("#tableNum").val("");
        $("#orderQuantity").val("");
        $("#status").val("Pending");

        // Reset any validation messages
        $("#check-username").html("");

        checkCreateButtonStatus();
    });

    $("#addorder").on("submit", function (e) {
        e.preventDefault(); // Prevent default form submission

        // First, generate order ID and date, then handle form submission in a callback
        generateOrderId(function (orderId) {
            generateOrderDate(function (orderDate) {
                // Collect form data
                const formData = {
                    orderId: orderId,
                    customerId: $("#customerId").val(),
                    tableNum: $("#tableNum").val(),
                    orderQuantity: $("#orderQuantity").val(),
                    orderDate: orderDate, // Use the generated orderDate
                    totalAmount: parseFloat($("#grandTotal").text()), // Ensure it's a float
                    status: "Pending",
                    orderItems: [], // Array to store order items
                    _token: $('meta[name="csrf-token"]').attr("content"), // CSRF token
                };

                // Collect order items from the form
                $(".order-item").each(function () {
                    const item = {
                        orderId: orderId, // Pass the generated orderId
                        dishId: $(this).find(".item-name").val(),
                        quantity: parseInt($(this).find(".quantity").val()), // Ensure it's an integer
                        status: "Pending",
                        servedBy: null,
                        remark: $(this).find(".remarks").val(),
                    };
                    formData.orderItems.push(item);
                });

                // Make AJAX request to save order
                $.ajax({
                    url: "/orders/store",
                    method: "POST",
                    data: JSON.stringify(formData), // Send data as JSON
                    contentType: "application/json", // Set content type to JSON
                    processData: false, // Prevent jQuery from auto-processing data
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        if (response.success) {
                            $("#success-message").text(
                                "Order created successfully!"
                            );
                            $("#success-alert").fadeIn().delay(3000).fadeOut();
                            $("#addOrderModal").modal("hide");
                            $("#resetButton").click();
                            $("#orders-table").DataTable().ajax.reload(); // Reload data table
                        }
                    },
                    error: function (xhr) {
                        const errors =
                            xhr.responseJSON?.message ||
                            "Error creating order. Please try again.";
                        $("#warning-message").text(errors);
                        $("#warning-alert").fadeIn().delay(3000).fadeOut();
                    },
                });
            });
        });
    });

    // Handle view button click
    $(document).on("click", ".view-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#orders-table").DataTable().row(row).data();

        // Redirect to the details page
        window.location.href = `/orders/details/${data.orderId}`;
    });

    // Handle the edit button click
    $(document).on("click", ".edit-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#orders-table").DataTable().row(row).data();

        $.ajax({
            url: `/orders/edit/${data.orderId}`,
            method: "GET",
            success: function (response) {
                if (response.success) {
                    // Set initial values
                    const order = response.data;
                    initialCustomerId = order.customerId;
                    initialCustomerName = order.customerName;
                    initialTableNum = order.tableNum;
                    initialOrderDate = order.orderDate.slice(0, 16);
                    initialStatus = order.orderStatus;

                    // Populate the fields in the Edit Order Modal
                    $("#edit_orderId").val(order.orderId);
                    $("#edit_customerId").val(order.customerId);
                    $("#edit_orderDate").val(initialOrderDate);
                    $("#edit_status").val(order.status);
                    $("#edit_name").val(order.customerName);

                    // Fetch customer name
                    $.ajax({
                        url: `/customers/${order.customerId}`,
                        method: "GET",
                        success: function (customerResponse) {
                            if (customerResponse.success) {
                                $("#edit_name").val(
                                    customerResponse.customer.name
                                );
                            }
                        },
                        error: function () {
                            console.log("Error fetching customer details.");
                        },
                    });

                    // Pass tableNum to pre-select the table in the dropdown
                    getTables(order.tableNum);

                    // Disable the Update button initially
                    $("#btnupdate").prop("disabled", true);

                    // Open the Edit Order Modal
                    $("#editOrderModal").modal("show");
                } else {
                    console.log("Error:", response.message);
                }
            },
            error: function () {
                console.log("Error fetching order details.");
            },
        });
    });

    // Update button click handler
    $("#editOrderForm").on("submit", function (e) {
        e.preventDefault();
        const orderId = $("#edit_orderId").val();

        $.ajax({
            url: `/orders/update/${orderId}`,
            method: "PUT",
            data: {
                customerId: $("#edit_customerId").val(),
                tableNum: $("#edit_tableNum").val(),
                orderDate: $("#edit_orderDate").val(),
                status: $("#edit_status").val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $("#editOrderModal").modal("hide");
                    $("#orders-table").DataTable().ajax.reload();
                    $("#success-message").text("Order updated successfully");
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                } else {
                    $("#warning-message").text(
                        response.message || "Error updating order"
                    );
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                }
            },
            error: function (xhr) {
                console.log("Update error:", xhr);
                const errors =
                    xhr.responseJSON?.message || "Error updating order";
                $("#warning-message").text(errors);
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Delete button click handler
    $(document).on("click", ".delete-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#orders-table").DataTable().row(row).data();

        // Show confirmation dialog
        if (confirm("Are you sure you want to delete this order?")) {
            $.ajax({
                url: `/orders/${data.orderId}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    if (response.success) {
                        // Refresh the DataTable
                        $("#orders-table").DataTable().ajax.reload();

                        // Show success message
                        $("#success-message").text(
                            "Order deleted successfully"
                        );
                        $("#success-alert").fadeIn().delay(3000).fadeOut();
                    }
                    reloadPage();
                },
                error: function () {
                    // Show error message
                    $("#warning-message").text("Error deleting order");
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                },
            });
        }
    });
});
