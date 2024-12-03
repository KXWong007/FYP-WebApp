@extends('layout')

@section('title', 'Reservation')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="
        script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.datatables.net https://cdn.jsdelivr.net; 
        style-src 'self' 'unsafe-inline' https://cdn.datatables.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
        font-src 'self' https://cdnjs.cloudflare.com data:;">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('styles')
    <!-- Existing styles -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    
    <!-- Add these calendar-specific styles -->
    <style>
        #reservationCalendar {
            height: 700px;
            padding: 20px;
        }
        .fc-event {
            cursor: pointer;
        }
        .fc-event-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .reservation-tooltip {
            text-align: left;
            font-size: 16px;
            padding: 10px;
        }

        .reservation-tooltip p {
            margin: 8px 0;
            white-space: nowrap;
            line-height: 1.5;
        }

        .tooltip-inner {
            max-width: 400px !important;
            background-color: #2a2b2b !important;
            color: white !important;
            border-radius: 8px;
            padding: 15px !important;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .tooltip {
            opacity: 1 !important;
            font-size: 16px !important;
        }

        .tooltip strong {
            color: #ffffff;
            margin-right: 8px;
        }

        .calendar-legend {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .legend-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 0 5px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 5px;
        }

        .legend-label {
            font-size: 14px;
            color: #666;
        }

        .fc a {
            color: #000000 !important;  /* Changes all calendar links to black */
            text-decoration: none;      /* Removes underline from links */
        }

        .fc-daygrid-day-number {
            color: #000000 !important;  /* Changes day numbers to black */
        }

        .fc-col-header-cell-cushion {
            color: #000000 !important;  /* Changes header text (Sun, Mon, etc) to black */
        }

        .fc-daygrid-more-link {
            color: #000000 !important;  /* Changes "more" link to black */
        }

    </style>
    
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
@endsection

@section('content')
    <h1 class="dashboard-title">RESERVATION</h1>

    <!-- Reservation Actions -->
    <div class="reservation-actions mb-3">
        <button type="button" class="btn btn-custom btn-success" id="addReservationBtn" target="#addReservationModal">
            <i class="fas fa-plus"></i> Add Reservation
        </button>
        <button type="button" class="btn btn-custom btn-primary" id="import-btn">
            <i class="fas fa-file-import"></i> Import Data
        </button>

        
            <div class="dropdown d-inline-block">
                <button class="btn btn-custom btn-danger dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export"></i> EXPORT DATA
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="{{ route('reservations.export', ['type' => 'xlsx']) }}">
                        <i class="fas fa-file-excel me-2"></i>Excel (.xlsx)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('reservations.export', ['type' => 'csv']) }}">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('reservations.export', ['type' => 'pdf']) }}">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a></li>
                </ul>
            </div>
        
        <button type="button" class="btn btn-custom btn-secondary" id="calendarViewBtn">
            <i class="fas fa-calendar-alt"></i> Calendar View
        </button>
    </div>

    <!-- Add this right after your header or where you want to show messages -->
    <div id="alert-container">
        <div class="alert alert-success alert-fade" id="success-alert" style="display: none;">
            <span id="success-message"></span>
        </div>
        <div class="alert alert-warning alert-fade" id="warning-alert" style="display: none;">
            <span id="warning-message"></span>
        </div>
    </div>

    @if (session('success'))
                <div class="alert alert-success alert-fade">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('warning'))
              <div class="alert alert-warning alert-fade">
                  {{ session('warning') }}
              </div>
          @endif

    <!-- Reservations Table -->
    <div class="table-responsive">
        <table id="reservations-table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Pax</th>
                    <th>Reservation Date</th>
                    <th>Event</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this tbody with JSON data -->
            </tbody>
        </table>
    </div>

    <!-- Add Reservation Modal -->
    <div class="modal fade" id="addReservationModal" tabindex="-1" aria-labelledby="addReservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReservationModalLabel">Add Reservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addreservation" method="POST">
                    <div class="modal-body">
                        <div class="single-row">
                        <div class="mb-3 single-column">
                                <label for="rarea">Area</label>
                                <select name="rarea" id="rarea" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="W">Rajah Room</option>
                                    <option value="C">Hornbill Restaurant</option>
                                </select>
                            </div>
                            
                            <div class="mb-3 single-column">
                                <label for="reservationId">Reservation ID</label>
                                <input type="text" name="reservationId" id="reservationId" class="form-control" readonly 
                                       placeholder="Please select an area first">
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="customerId">Customer ID</label><span id="check-customerstatus"></span>
                                <input type="text" id="customerId" name="customerId" class="form-control" required>
                            </div>
                            <div class="mb-3 single-column">
                                <label for="customer_name">Customer name </label><span id="check-username"></span>
                                <input type="text" id="name" name="name" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="orderId">Order ID</label>
                                <input type="number" id="orderId" name="orderId" class="form-control">
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="pax">Number of Guests</label>
                                <input type="number" 
                                       id="pax" 
                                       name="pax" 
                                       class="form-control" 
                                       min="1" 
                                       required 
                                       oninput="this.value = this.value <= 0 ? 1 : this.value">
                            </div>

                            <div class="mb-3 single-column">
                                <label for="reservation_date">Reservation Date</label>
                                <input type="datetime-local" 
                                       id="reservation_date" 
                                       name="reservation_date" 
                                       class="form-control" 
                                       required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="event">Event (optional)</label>
                                <input type="text" id="event" name="event" class="form-control">
                            </div>
                            
                            <div class="mb-3 single-column">
                                <label for="remark">Remark</label>
                                <input type="text" id="remark" name="remark" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" id="resetButton">Clear</button>
                        <button type="submit" class="btn btn-dark" id="btncreate">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add this right after your opening content section -->
    
@endsection

@section('scripts')
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Just before closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Then Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Then DataTables -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <!-- Your custom JS last -->
    <script>
        // Add this to verify jQuery is loaded
        console.log('jQuery version:', jQuery.fn.jquery);
        // Verify DataTables is loaded
        console.log('DataTables version:', $.fn.dataTable.version);
    </script>
    <script src="{{ asset('js/reservation.js') }}"></script>
    <script src="{{ asset('js/reservation-calendar.js') }}"></script>
@endsection

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
@endsection

<!-- Edit Reservation Modal -->
<div class="modal fade" id="editReservationModal" tabindex="-1" aria-labelledby="editReservationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReservationModalLabel">Edit Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editReservationForm" method="POST">
                    @csrf
                    <!-- Read-only fields -->
                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="edit_rarea">Area</label>
                            <input type="text" id="edit_rarea" name="rarea" class="form-control" readonly>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_reservationId">Reservation ID</label>
                            <input type="text" id="edit_reservationId" name="reservationId" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="edit_customerId">Customer ID</label>
                            <input type="text" id="edit_customerId" name="customerId" class="form-control" readonly>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_name">Customer Name</label>
                            <input type="text" id="edit_name" name="name" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Editable fields -->
                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="edit_orderId">Order ID</label>
                            <input type="text" id="edit_orderId" name="orderId" class="form-control">
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_paymentId">Payment ID</label>
                            <input type="text" id="edit_paymentId" name="paymentId" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="edit_pax">Number of Guests</label>
                        <input type="number" id="edit_pax" name="pax" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_reservation_date">Reservation Date</label>
                            <input type="datetime-local" id="edit_reservation_date" name="reservation_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="edit_event">Event</label>
                        <input type="text" id="edit_event" name="event" class="form-control">
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_remark">Remark</label>
                            <textarea id="edit_remark" name="remark" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="single-row">
                        <div class="mb-3 single-column">
                            <label for="edit_status">Status</label>
                            <select class="form-control" id="edit_status" name="rstatus" required>
                                <option value="confirm">Confirm</option>
                                <option value="pending">Pending</option>
                                <option value="thirdc">Third Confirmation</option>
                                <option value="secondc">Second Confirmation</option>
                                <option value="firstc">First Confirmation</option>
                                <option value="cancel">Cancel</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="mb-3 single-column">
                            <label for="edit_reservedBy">Reserved By</label>
                            <input type="text" id="edit_reservedBy" name="reservedBy" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark" id="updateButton">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Reservations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Download template button -->
                <div class="mb-3">
                    <a href="{{ route('reservations.template') }}" class="btn btn-dark">
                        <i class="fas fa-download"></i> Download CSV Template
                    </a>
                </div>
                
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Choose CSV File</label>
                        <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-custom btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarModalLabel">Reservation Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reservationCalendar"></div>
            </div>
        </div>
    </div>
</div>

@if(isset($editReservation))
<script>
    $(document).ready(function() {
        // Populate and show edit modal when page loads
        const reservation = @json($editReservation);
        populateEditModal(reservation);
        $('#editReservationModal').modal('show');
    });
</script>
@endif

