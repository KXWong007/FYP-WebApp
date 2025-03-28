jQuery(document).ready(function ($) {
    let unreadCount = 0;
    
    // Function to fetch new reservations
    function fetchNewReservations() {
        $.ajax({
            url: '/api/new-reservations',
            method: 'GET',
            success: function(response) {
                console.log('Fetched reservations:', response); // Debug log
                if (response.success && response.data.length > 0) {
                    // Filter only customer reservations with 'confirm' status
                    const customerReservations = response.data.filter(res => 
                        res.reservedBy === 'customer' && 
                        res.rstatus === 'confirm'
                    );
                    
                    console.log('Filtered reservations:', customerReservations); // Debug log
                    
                    if (customerReservations.length > 0) {
                        updateNotificationBadge(customerReservations.length);
                        updateNotificationDropdown(customerReservations);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching reservations:', error); // Debug log
            }
        });
    }

    // Update the notification badge
    function updateNotificationBadge(count) {
        unreadCount = count;
        const badge = $('.notification-badge');
        if (count > 0) {
            badge.text(count).show();
        } else {
            badge.hide();
        }
    }

    // Update the notification dropdown content
    function updateNotificationDropdown(reservations) {
        const notificationList = $('.notification-list');
        notificationList.empty();

        reservations.forEach(reservation => {
            const date = new Date(reservation.created_at);
            const formattedDate = date.toLocaleString();
            
            const notificationItem = `
                <a href="/reservations/edit/${reservation.reservationId}" class="dropdown-item notification-item p-2 border-bottom" data-id="${reservation.reservationId}">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${reservation.customer_name}</strong>
                            <div class="small text-muted">
                                ${reservation.pax} pax - ${reservation.area === 'W' ? 'Rajah Room' : 'Hornbill Restaurant'}
                            </div>
                            <div class="small text-muted">
                                Reserved for: ${new Date(reservation.rdate).toLocaleString()}
                            </div>
                        </div>
                        <div class="small text-muted">
                            ${formattedDate}
                        </div>
                    </div>
                </a>
            `;
            notificationList.append(notificationItem);
        });
    }

    // Mark notification as read when clicked
    $(document).on('click', '.notification-item', function() {
        const reservationId = $(this).data('id');
        $.ajax({
            url: `/api/mark-notification-read/${reservationId}`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                unreadCount--;
                updateNotificationBadge(unreadCount);
            }
        });
    });

    // Check for new reservations every 30 seconds
    fetchNewReservations(); // Initial fetch
    setInterval(fetchNewReservations, 30000);
}); 