document.addEventListener("DOMContentLoaded", function () {
    const calendarViewBtn = document.getElementById("calendarViewBtn");
    const calendarModal = document.getElementById("calendarModal");
    let calendar = null;

    calendarViewBtn.addEventListener("click", function () {
        const modal = new bootstrap.Modal(calendarModal);
        modal.show();

        calendarModal.addEventListener("shown.bs.modal", function () {
            if (!calendar) {
                initializeCalendar();
            } else {
                calendar.refetchEvents();
            }
        });
    });

    function initializeCalendar() {
        const calendarEl = document.getElementById("reservationCalendar");

        // Create legend div
        const legendDiv = document.createElement("div");
        legendDiv.className = "calendar-legend";
        legendDiv.innerHTML = `
            <div class="legend-container">
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #8AC7AD"></span>
                    <span class="legend-label">Confirm</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #c9c720"></span>
                    <span class="legend-label">Pending</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #94140a"></span>
                    <span class="legend-label">First Confirmation</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #20498A"></span>
                    <span class="legend-label">Second Confirmation</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #5A2555"></span>
                    <span class="legend-label">Third Confirmation</span>
                </div>
            </div>
        `;

        // Insert legend before calendar
        calendarEl.parentNode.insertBefore(legendDiv, calendarEl);

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            displayEventTime: true,
            eventDisplay: "block",
            events: function (fetchInfo, successCallback, failureCallback) {
                fetch("/api/reservations/calendar", {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                    },
                })
                    .then((response) => response.json())
                    .then((response) => {
                        if (response.status === "error") {
                            throw new Error(response.message);
                        }

                        const today = new Date();
                        today.setHours(0, 0, 0, 0); // Set to start of day

                        const events = response.data
                            .filter(
                                (reservation) =>
                                    !["completed", "cancel"].includes(
                                        reservation.rstatus.toLowerCase()
                                    )
                            )
                            .map((reservation) => {
                                const eventDate = new Date(reservation.rdate);
                                eventDate.setHours(0, 0, 0, 0); // Set to start of day
                                const isPast = eventDate < today;
                                const baseColor = getStatusColor(
                                    reservation.rstatus
                                );

                                return {
                                    id: reservation.reservationId,
                                    title: `${reservation.customer_name} (${reservation.pax} pax)`,
                                    start: reservation.rdate,
                                    allDay: false,
                                    backgroundColor: isPast
                                        ? adjustColorOpacity(baseColor, 0.5)
                                        : baseColor,
                                    borderColor: isPast
                                        ? adjustColorOpacity(baseColor, 0.5)
                                        : baseColor,
                                    extendedProps: {
                                        reservationId:
                                            reservation.reservationId,
                                        customerId: reservation.customerId,
                                        customer_name:
                                            reservation.customer_name,
                                        area: reservation.area,
                                        event: reservation.eventType,
                                        status: reservation.rstatus,
                                        remark: reservation.remark,
                                    },
                                };
                            });

                        successCallback(events);
                    })
                    .catch((error) => {
                        console.error("Error fetching calendar data:", error);
                        failureCallback(error);
                    });
            },
            eventDidMount: function (info) {
                // Function to get area display text
                function getAreaDisplay(area) {
                    switch (area) {
                        case "W":
                            return "Rajah Room";
                        case "C":
                            return "Hornbill Restaurant";
                        default:
                            return area;
                    }
                }

                function getStatusDisplay(status) {
                    const statusDisplay = {
                        firstc: "First Confirmation",
                        secondc: "Second Confirmation",
                        thirdc: "Third Confirmation",
                        confirm: "Confirmed",
                        pending: "Pending",
                    };

                    return statusDisplay[status] || status;
                }

                // Create tooltip content with all requested information
                const tooltipContent = `
                    <div class="reservation-tooltip">
                        <p><strong>Reservation ID:</strong> ${
                            info.event.extendedProps.reservationId
                        }</p>
                        <p><strong>Customer ID:</strong> ${
                            info.event.extendedProps.customerId
                        }</p>
                        <p><strong>Customer Name:</strong> ${
                            info.event.extendedProps.customer_name
                        }</p>
                        <p><strong>Area:</strong> ${getAreaDisplay(
                            info.event.extendedProps.area
                        )}</p>
                        <p><strong>Event:</strong> ${
                            info.event.extendedProps.event
                                ? info.event.extendedProps.event
                                : "No Event"
                        }</p>
                        <p><strong>Status:</strong> ${getStatusDisplay(
                            info.event.extendedProps.status
                        )}</p>
                        <p><strong>Remark:</strong> ${
                            info.event.extendedProps.remark
                                ? info.event.extendedProps.remark
                                : "-"
                        }</p>
                    </div>
                `;

                // Add Bootstrap tooltip
                new bootstrap.Tooltip(info.el, {
                    title: tooltipContent,
                    html: true,
                    placement: "top",
                    trigger: "hover",
                    container: "body",
                    template:
                        '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="max-width: 300px;"></div></div>',
                });
            },
            eventClick: function (info) {
                console.log("Event clicked:", info.event); // Debug log
            },
        });

        calendar.render();
    }

    function getStatusColor(status) {
        const colors = {
            confirm: "#8AC7AD", // green
            pending: "#c9c720", // yellow
            thirdc: "#5A2555", // red
            secondc: "#20498A", // blue
            firstc: "#94140a", // purple
        };
        return colors[status.toLowerCase()] || "#6c757d";
    }

    // Clean up tooltips when modal is hidden
    calendarModal.addEventListener("hidden.bs.modal", function () {
        const tooltips = document.querySelectorAll(".tooltip");
        tooltips.forEach((tooltip) => tooltip.remove());
    });

    function adjustColorOpacity(hexColor, opacity) {
        // Convert hex to RGB
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);

        // Calculate lighter values
        const lighterR = Math.round(r + (255 - r) * (1 - opacity));
        const lighterG = Math.round(g + (255 - g) * (1 - opacity));
        const lighterB = Math.round(b + (255 - b) * (1 - opacity));

        // Convert back to hex
        return (
            "#" +
            lighterR.toString(16).padStart(2, "0") +
            lighterG.toString(16).padStart(2, "0") +
            lighterB.toString(16).padStart(2, "0")
        );
    }
});
