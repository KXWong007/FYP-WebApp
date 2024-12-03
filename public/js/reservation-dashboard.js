document.addEventListener('DOMContentLoaded', function() {
    // Handle modal open
    const reservationModal = document.getElementById('reservationModal');
    reservationModal.addEventListener('show.bs.modal', function (event) {
        loadAllReservations();
    });

    function loadAllReservations() {
        fetch('/api/reservations/upcoming', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!Array.isArray(data)) {
                console.error('Expected array but got:', data);
                return;
            }

            const tbody = document.querySelector('#allReservationsTable tbody');
            tbody.innerHTML = '';
            
            data.forEach(reservation => {
                const row = document.createElement('tr');
                row.className = 'reservation-row';
                row.style.backgroundColor = row.rowIndex % 2 === 0 ? '#f2f2f2' : 'white';
                row.style.cursor = 'pointer';
                
                row.innerHTML = `
                    <td>${reservation.id}</td>
                    <td>
                        <i class="fas ${reservation.icon}"></i>
                        ${reservation.type ? 
                            `${reservation.type.toUpperCase()}` : 
                            'NONE'
                        }
                        ${isWithin24Hours(reservation.rdate) ? '<span class="badge bg-warning">Within 24h</span>' : ''}
                    </td>
                    <td>${reservation.rarea === 'W' ? 'Rajah Room' : 'Hornbill Room'}</td>
                    <td>${formatDate(reservation.rdate)}</td>
                    <td>${reservation.pax}</td>
                    <td><span class="badge" style="background-color: #198754; color: white;">Confirmed</span></td>
                `;

                row.addEventListener('click', () => showReservationDetails(reservation));
                
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load reservations. Please check the console for details.');
        });
    }

    function showReservationDetails(reservation) {
        const detailsContent = document.getElementById('reservationDetailsContent');
        detailsContent.innerHTML = `
            <div class="reservation-details-container">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Reservation ID:</span>
                            <span class="detail-value">${reservation.id}</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Area:</span>
                            <span class="detail-value">${reservation.rarea === 'W' ? 'Rajah Room' : 'Hornbill Room'}</span>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Customer ID:</span>
                            <span class="detail-value">${reservation.customerId}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value">${reservation.customerName}</span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">${reservation.orderId}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Payment ID:</span>
                            <span class="detail-value">${reservation.paymentId}</span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Date & Time:</span>
                            <span class="detail-value">${formatDate(reservation.rdate)}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Event Type:</span>
                            <span class="detail-value">
                                ${reservation.type ? 
                                    `<i class="fas ${reservation.icon}"></i> ${reservation.type.toUpperCase()}` : 
                                    'NONE'
                                }
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Number of Guests:</span>
                        <span class="detail-value">${reservation.pax}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Remark:</span>
                            <span class="detail-value">${reservation.remark}</span>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Phone Number:</span>
                            <span class="detail-value">${reservation.phoneNum}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                        <span class="badge" style="background-color: #198754; color: white;">
                            Confirmed
                        </span>
                    </span>
                </div>
                    </div>
                </div>
            </div>
        `;

        const reservationsModal = bootstrap.Modal.getInstance(document.getElementById('reservationModal'));
        reservationsModal.hide();

        const detailsModal = new bootstrap.Modal(document.getElementById('reservationDetailsModal'));
        detailsModal.show();
    }

    const detailsModal = document.getElementById('reservationDetailsModal');
    detailsModal.addEventListener('hidden.bs.modal', function (event) {
        const reservationsModal = new bootstrap.Modal(document.getElementById('reservationModal'));
        reservationsModal.show();
    });

    function isWithin24Hours(dateString) {
        const reservationDate = new Date(dateString);
        const now = new Date();
        const diffHours = (reservationDate - now) / (1000 * 60 * 60);
        return diffHours > 0 && diffHours <= 24;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const formattedDate = date.toLocaleDateString('en-GB'); // DD/MM/YYYY
        const formattedTime = date.toLocaleTimeString('en-GB', { 
            hour: '2-digit', 
            minute: '2-digit'
        }); // HH:MM
        return `${formattedDate} ${formattedTime}`;
    }
});
