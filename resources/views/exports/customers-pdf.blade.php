<!DOCTYPE html>
<html>
<head>
    <title>Customers List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
            color: #333;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Customers List</h2>
    <table>
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Customer Type</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->{'Customer ID'} }}</td>
                    <td>{{ $customer->{'Customer Type'} }}</td>
                    <td>{{ $customer->{'Customer Name'} }}</td>
                    <td>{{ $customer->{'Email'} }}</td>
                    <td>{{ $customer->{'Phone Number'} }}</td>
                    <td>{{ $customer->{'Status'} }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 