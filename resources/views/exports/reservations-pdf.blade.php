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
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h2>Reservations Report</h2>
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
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
