$(document).ready(function() {
    const CHECK_INTERVAL = 30000; // Check every 30 seconds

    function getStatusText(status) {
        switch(status.toLowerCase()) {
            case 'firstc':
                return 'First Confirmation';
            case 'secondc':
                return 'Second Confirmation';
            case 'thirdc':
                return 'Third Confirmation';
            case 'confirm':
                return 'Confirmed';
            default:
                return status;
        }
    }

    function getAreaText(area) {
        switch(area) {
            case 'W':
                return 'Rajah Room';
            case 'C':
                return 'Hornbill Restaurant';
            default:
                return area;
        }
    }

    function getStatusColor(status) {
        switch(status.toLowerCase()) {
            case 'firstc':
                return '#94140a';  // Dark red
            case 'secondc':
                return '#20498A';  // Blue
            case 'thirdc':
                return '#5A2555';  // Purple
            default:
                return '#6c757d';  // Default gray
        }
    }

    function checkNewReservations() {
        $.ajax({
            url: '/api/new-reservations',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    updateNotificationBadge(response.data.length);
                    updateNotificationDropdown(response.data);
                } else {
                    hideNotificationBadge();
                }
            }
        });
    }

    function updateNotificationBadge(count) {
        const badge = $('.notification-badge');
        badge.text(count);
        badge.css({
            'font-size': '12px',
            'font-weight': 'bold',
            'padding': '4px 8px',
            'border-radius': '50%',
            'background-color': '#dc3545',
            'border': '2px solid #fff',
            'box-shadow': '0 2px 5px rgba(0,0,0,0.2)'
        });
        badge.show();
    }

    function hideNotificationBadge() {
        $('.notification-badge').hide();
    }

    function updateNotificationDropdown(notifications) {
        const notificationList = $('.notification-list');
        notificationList.empty();

        notifications.forEach(notification => {
            // Format the date and time
            const reservationDate = new Date(notification.reservationDate);
            const now = new Date();
            const hoursDifference = (reservationDate - now) / (1000 * 60 * 60); // Convert to hours
            
            // Determine if reservation needs attention (within 24 hours)
            const needsAttention = hoursDifference <= 24 && hoursDifference > 0;
            
            const formattedDate = reservationDate.toLocaleDateString();
            const formattedTime = reservationDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            // Format the area display and color
            const areaText = notification.area === 'W' ? 'Rajah Room' : 'Hornbill Restaurant';
            const areaColor = notification.area === 'W' ? '#3A3B3C' : '#3A3B3C';
            
            // Get status color
            const statusColor = getStatusColor(notification.rstatus);
            
            // Add highlight styling for reservations within 24 hours
            const highlightStyle = needsAttention 
                ? 'background-color: rgba(255, 243, 205, 0.9); border-left: 4px solid #ffc107;' 
                : '';
            
            const notificationHtml = `
                <div class="notification-item p-3 border-bottom hover-effect" style="${highlightStyle}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold d-flex align-items-center mb-2">
                                ${notification.reservationId}
                                <i class="fas fa-copy ms-2 copy-icon" style="cursor: pointer; font-size: 14px; color: #6c757d;" 
                                   data-id="${notification.reservationId}" 
                                   title="Copy Reservation ID"></i>
                                ${needsAttention ? '<span class="ms-2 badge bg-warning text-dark">Within 24h</span>' : ''}
                            </div>
                            <div class="mb-1">
                                ${notification.customer_name}
                                <span class="text-muted">- ${notification.phone_number}</span>
                            </div>
                            <div class="text-muted small mb-2">
                                ${notification.pax} pax - ${formattedDate} ${formattedTime}
                            </div>
                            <div class="mt-1">
                                <span class="badge rounded-pill" style="background-color: ${statusColor}; padding: 6px 12px;">${getStatusText(notification.rstatus)}</span>
                                <span class="badge rounded-pill" style="background-color: ${areaColor}; padding: 6px 12px;">${areaText}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            notificationList.append(notificationHtml);
        });

        // Add click handler for copy icons
        $('.copy-icon').on('click', function(e) {
            e.stopPropagation();
            const idToCopy = $(this).data('id');
            
            // Create temporary input element
            const tempInput = document.createElement('input');
            tempInput.value = idToCopy;
            document.body.appendChild(tempInput);
            
            // Copy the text
            tempInput.select();
            document.execCommand('copy');
            
            // Remove temporary element
            document.body.removeChild(tempInput);
            
            // Show feedback
            const originalColor = $(this).css('color');
            $(this).css('color', '#28a745').attr('title', 'Copied!');
            
            // Reset after 1.5 seconds
            setTimeout(() => {
                $(this).css('color', originalColor).attr('title', 'Copy Reservation ID');
            }, 1500);
        });
    }

    // Start periodic checking
    checkNewReservations();
    setInterval(checkNewReservations, CHECK_INTERVAL);
});
