<!DOCTYPE html>
<html>
<head>
    <title>Staff List</title>
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
    <h2>Staff List</h2>
    <table>
        <thead>
            <tr>
                <th>Staff ID</th>
                <th>Staff Type</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $staffMember)
                <tr>
                    <td>{{ $staffMember->{'Staff ID'} }}</td>
                    <td>{{ $staffMember->{'Staff Type'} }}</td>
                    <td>{{ $staffMember->{'Staff Name'} }}</td>
                    <td>{{ $staffMember->{'Email'} }}</td>
                    <td>{{ $staffMember->{'Phone Number'} }}</td>
                    <td>{{ $staffMember->{'Status'} }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 