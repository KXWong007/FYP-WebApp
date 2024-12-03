jQuery(document).ready(function ($) {
    $("#reservations-table").DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "/reservations",
            type: "GET",
        },
        columns: [
            { data: "reservationId" },
            { data: "customerId" },
            { data: "name" },
            { data: "pax" },
            { data: "reservationDate" },
            { data: "eventType" },
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
        order: [[6, "desc"]],
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

    $("#addReservationBtn").on("click", function () {
        const myModal = new bootstrap.Modal(
            document.getElementById("addReservationModal")
        );
        myModal.show();
    });

    $("#importDataBtn").on("click", function () {
        $("#importCsvModal").modal("show");
    });

    $("#exportDataBtn").on("click", function () {
        // Implement export data functionality
    });

    $("#reservations-table").on("click", ".view-btn", function () {
        var id = $(this).data("id");
        // Implement view reservation functionality
    });

    $("#reservations-table").on("click", ".delete-btn", function () {
        var id = $(this).data("id");
        // Implement delete reservation functionality
    });

    $("#customerId").on("input", function () {
        checkUsername();
        checkCustomerstatus();
    });

    $("#customerId").on("keyup", function () {
        getCustomerDetail(this.value);
    });

    function checkUsername() {
        $.ajax({
            url: "/check-customer", // We'll create this route
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
                $("#check-username").html(
                    '<span style="color:red">Error checking customer ID</span>'
                );
            },
        });
    }

    function checkCustomerstatus() {
        const customerId = $("#customerId").val();

        if (!customerId) {
            $("#check-customerstatus").html("");
            $("#btncreate").prop("disabled", true);
            return;
        }

        $.ajax({
            url: "/check-customerstatus",
            data: { customerId: customerId },
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                $("#check-customerstatus").html(data.message);
                $("#btncreate").prop("disabled", !data.exists);
            },
            error: function () {
                $("#check-customerstatus").html(
                    '<span style="color:red"> Error checking customer status.</span>'
                );
                $("#btncreate").prop("disabled", true);
            },
        });
    }

    function getCustomerDetail(customerId) {
        if (customerId.length === 0) {
            $("#name").val("");
            return;
        }

        $.ajax({
            url: "/get-customer-detail",
            data: { customerId: customerId },
            type: "GET",
            success: function (response) {
                // Add console.log to debug the response
                console.log("Response:", response);
                if (response && response.name) {
                    $("#name").val(response.name);
                } else {
                    $("#name").val("");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                $("#name").val("");
            },
        });
    }

    // Add this reset button handler
    $("#resetButton").on("click", function () {
        // Clear all form inputs
        $("#addreservation")[0].reset();

        // Clear specific fields
        $("#reservationId").val("");
        $("#orderId").val("");
        $("#customerId").val("");
        $("#name").val("");
        $("#paymentId").val("");
        $("#reservation_date").val("");
        $("#pax").val("");
        $("#event").val("");
        $("#rarea").val("Select"); // Reset dropdown to default
        $("#remark").val("");

        // Reset the validation message
        $("#check-username").html("");

        // Enable the create button
        $("#btncreate").prop("disabled", false);
    });

    $("#rarea").on("change", function () {
        const selectedArea = $(this).val();
        if (selectedArea) {
            generateReservationId(selectedArea);
        } else {
            $("#reservationId").val("");
            $("#reservationId").attr(
                "placeholder",
                "Please select an area first"
            );
        }
    });

    function generateReservationId(areaCode) {
        // Get current date in YYYYMMDD format for Malaysia timezone
        const now = new Date().toLocaleString("en-US", {
            timeZone: "Asia/Kuala_Lumpur",
        });
        const date = new Date(now);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        const dateStr = `${year}${month}${day}`;

        // Get milliseconds for counter
        const milliseconds = new Date().getTime();
        const counter = String(milliseconds).slice(-2);

        // Generate ID based on area code
        const reservationId = `${areaCode}${dateStr}${counter}`;

        $("#reservationId").val(reservationId);
    }

    // Add this to prevent form submission if no area is selected
    $("#addreservation").on("submit", function (e) {
        e.preventDefault();

        // Validate pax
        const pax = parseInt($("#pax").val());
        if (isNaN(pax) || pax < 1) {
            $("#warning-message").text("Number of guests must be at least 1");
            $("#warning-alert").fadeIn().delay(3000).fadeOut();
            return false;
        }

        // Validate reservation date
        const reservationDate = new Date($("#reservation_date").val());
        const now = new Date();
        if (reservationDate < now) {
            $("#warning-message").text("Please select a future date and time");
            $("#warning-alert").fadeIn().delay(3000).fadeOut();
            return false;
        }

        const formData = {
            reservationId: $("#reservationId").val(),
            customerId: $("#customerId").val(),
            orderId: $("#orderId").val(),
            paymentId: $("#paymentId").val(),
            pax: $("#pax").val(),
            reservation_date: $("#reservation_date").val(),
            event: $("#event").val() || null,
            remark: $("#remark").val() || null,
            rarea: $("#rarea").val(),
            reservedBy: "admin",
            rstatus: "confirm",
            _token: $('meta[name="csrf-token"]').attr("content"),
        };

        $.ajax({
            url: "/reservations/store",
            method: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    // Show success message
                    $("#success-message").text(
                        "Reservation created successfully!"
                    );
                    $("#success-alert").fadeIn().delay(3000).fadeOut();

                    // Close modal and reset
                    $("#addReservationModal").modal("hide");
                    $("#resetButton").click();
                    $("#reservations-table").DataTable().ajax.reload();
                }
            },
            error: function () {
                // Show error message
                $("#warning-message").text(
                    "Error creating reservation. Please try again."
                );
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Set minimum date for reservation
    function setMinDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, "0");
        const day = String(now.getDate()).padStart(2, "0");
        const hours = String(now.getHours()).padStart(2, "0");
        const minutes = String(now.getMinutes()).padStart(2, "0");

        const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        $("#reservation_date").attr("min", minDateTime);

        // If current value is before minimum, reset it
        if ($("#reservation_date").val() < minDateTime) {
            $("#reservation_date").val(minDateTime);
        }
    }

    // Set initial minimum date/time
    setMinDateTime();

    // Update minimum date/time every minute
    setInterval(setMinDateTime, 60000);

    // Validate pax input
    $("#pax").on("input", function () {
        let value = parseInt($(this).val());
        if (isNaN(value) || value < 1) {
            $(this).val(1);
        }
    });

    // Validate reservation date
    $("#reservation_date").on("change", function () {
        const selectedDate = new Date($(this).val());
        const now = new Date();

        if (selectedDate < now) {
            alert("Please select a future date and time.");
            $(this).val("");
        }
    });

    function adjustSidebarHeight() {
        const contentHeight = $(".main-content").height();
        const windowHeight = $(window).height();
        const sidebarHeight = Math.max(contentHeight, windowHeight);
        $(".sidebar").css("height", sidebarHeight + "px");
    }

    // Also adjust on window resize
    $(window).on("resize", adjustSidebarHeight);

    // Handle edit button click
    $(document).on("click", ".edit-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#reservations-table").DataTable().row(row).data();

        // Fetch reservation details
        $.ajax({
            url: `/reservations/edit/${data.reservationId}`,
            method: "GET",
            success: function (response) {
                if (response.success) {
                    const reservation = response.data;

                    // Convert area code to full name
                    let areaName = "";
                    switch (reservation.rarea) {
                        case "W":
                            areaName = "Rajah Room";
                            break;
                        case "C":
                            areaName = "Hornbill Restaurant";
                            break;
                        default:
                            areaName = reservation.rarea;
                    }

                    // Populate all fields
                    $("#edit_reservationId").val(reservation.reservationId);
                    $("#edit_customerId").val(reservation.customerId);
                    $("#edit_name").val(data.name);
                    $("#edit_rarea").val(areaName);
                    $("#edit_orderId").val(reservation.orderId);
                    $("#edit_paymentId").val(reservation.paymentId);
                    $("#edit_pax").val(reservation.pax);
                    $("#edit_event").val(reservation.eventType);
                    $("#edit_remark").val(reservation.remark);
                    $("#edit_status").val(reservation.rstatus);
                    $("#edit_reservedBy").val(reservation.reservedBy);
                    $("#edit_reservation_date").val(
                        formatDateForInput(reservation.reservationDate)
                    );

                    // Show the modal
                    $("#editReservationModal").modal("show");
                }
            },
            error: function () {
                $("#warning-message").text(
                    "Error fetching reservation details"
                );
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Handle form submission
    $("#editReservationForm").on("submit", function (e) {
        e.preventDefault();

        const formData = {
            reservationId: $("#edit_reservationId").val(),
            customerId: $("#edit_customerId").val(),
            orderId: $("#edit_orderId").val(),
            paymentId: $("#edit_paymentId").val(),
            pax: $("#edit_pax").val(),
            reservation_date: $("#edit_reservation_date").val(),
            event: $("#edit_event").val(),
            remark: $("#edit_remark").val(),
            rarea: $("#edit_rarea").val(),
            rstatus: $("#edit_status").val(),
            _token: $('meta[name="csrf-token"]').attr("content"),
        };

        $.ajax({
            url: `/reservations/update/${formData.reservationId}`,
            method: "POST",
            data: formData,
            success: function (response) {
                if (response.success) {
                    $("#success-message").text(
                        "Reservation updated successfully!"
                    );
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                    $("#editReservationModal").modal("hide");
                    $("#reservations-table").DataTable().ajax.reload();
                }
            },
            error: function () {
                $("#warning-message").text(
                    "Error updating reservation. Please try again."
                );
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });

    // Helper function to format date for datetime-local input
    function formatDateForInput(dateString) {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        const hours = String(date.getHours()).padStart(2, "0");
        const minutes = String(date.getMinutes()).padStart(2, "0");

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    $("#edit_reservation_date").on("change", function () {
        const selectedDate = new Date($(this).val());
        const now = new Date();

        if (selectedDate < now) {
            alert("Please select a future date and time.");
            $(this).val("");
        }
    });

    // Export button click handler
    $("#export-btn").on("click", function () {
        window.location.href = "/reservations/export";
    });

    // Delete button click handler
    $(document).on("click", ".delete-btn", function () {
        const row = $(this).closest("tr");
        const data = $("#reservations-table").DataTable().row(row).data();

        // Show confirmation dialog
        if (confirm("Are you sure you want to delete this reservation?")) {
            $.ajax({
                url: `/reservations/${data.reservationId}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    if (response.success) {
                        // Refresh the DataTable
                        $("#reservations-table").DataTable().ajax.reload();

                        // Show success message
                        $("#success-message").text(
                            "Reservation deleted successfully"
                        );
                        $("#success-alert").fadeIn().delay(3000).fadeOut();
                    }
                },
                error: function () {
                    // Show error message
                    $("#warning-message").text("Error deleting reservation");
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                },
            });
        }
    });

    // Import button click handler
    $("#import-btn").on("click", function () {
        $("#importModal").modal("show");
    });

    // Handle import form submission
    $("#importForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData();
        let fileInput = $("#csvFile")[0];

        if (fileInput.files.length > 0) {
            formData.append("csvFile", fileInput.files[0]);

            $.ajax({
                url: "/reservations/import",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    if (response.success) {
                        $("#importModal").modal("hide");
                        $("#importForm")[0].reset();
                        $("#reservations-table").DataTable().ajax.reload();

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
                    $("#warning-message").text(
                        "Error importing data: " + xhr.responseJSON.message
                    );
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                },
            });
        } else {
            $("#warning-message").text("Please select a file to import");
            $("#warning-alert").fadeIn().delay(3000).fadeOut();
        }
    });

    // Reset form when modal is closed
    $("#importModal").on("hidden.bs.modal", function () {
        $("#importForm")[0].reset();
    });

    // Update button click handler
    $("#updateReservationForm").on("submit", function (e) {
        e.preventDefault();
        const reservationId = $("#edit_reservationId").val();

        // Add console.log to debug
        console.log("Updating reservation:", {
            reservationId: reservationId,
            pax: $("#edit_pax").val(),
            reservationDate: $("#edit_reservation_date").val(),
            eventType: $("#edit_event").val(),
            remark: $("#edit_remark").val(),
            rstatus: $("#edit_status").val(),
        });

        $.ajax({
            url: `/reservations/update/${reservationId}`,
            method: "POST",
            data: {
                pax: $("#edit_pax").val(),
                reservationDate: $("#edit_reservation_date").val(),
                eventType: $("#edit_event").val(),
                remark: $("#edit_remark").val(),
                rstatus: $("#edit_status").val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $("#editReservationModal").modal("hide");
                    $("#reservations-table").DataTable().ajax.reload();
                    $("#success-message").text(
                        "Reservation updated successfully"
                    );
                    $("#success-alert").fadeIn().delay(3000).fadeOut();
                } else {
                    $("#warning-message").text(
                        response.message || "Error updating reservation"
                    );
                    $("#warning-alert").fadeIn().delay(3000).fadeOut();
                }
            },
            error: function (xhr) {
                console.log("Update error:", xhr); // Add error logging
                $("#warning-message").text("Error updating reservation");
                $("#warning-alert").fadeIn().delay(3000).fadeOut();
            },
        });
    });
});
