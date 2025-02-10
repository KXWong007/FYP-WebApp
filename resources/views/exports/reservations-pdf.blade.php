<!DOCTYPE html>
<html>
<head>
    <title>Reservations</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <h2>Reservations List</h2>
    <div class="date-range">
        Week of {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Reservation ID</th>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Guests</th>
                <th>Date</th>
                <th>Event</th>
                <th>Area</th>
                <th>Table</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->{'Reservation ID'} }}</td>
                    <td>{{ $reservation->{'Customer ID'} }}</td>
                    <td>{{ $reservation->{'Customer Name'} }}</td>
                    <td>{{ $reservation->{'Number of Guests'} }}</td>
                    <td>{{ $reservation->{'Reservation Date'} }}</td>
                    <td>{{ $reservation->Event }}</td>
                    <td>{{ $reservation->Area }}</td>
                    <td>{{ $reservation->{'Table Number'} }}</td>
                    <td>{{ $reservation->Status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
