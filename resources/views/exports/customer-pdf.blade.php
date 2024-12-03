<!DOCTYPE html>
<html>
<head>
    <title>Customer Report</title>
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
    <h2>Customer Report</h2>
    <table>
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Customer Type</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Religion</th>
                <th>Race</th>
                <th>NRIC</th>
                <th>Profile Picture</th>
                <th>Date of Birth</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->customerId }}</td>
                    <td>{{ $customer->customerType }}</td>
                    <td>{{ $customer->cName }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->gender }}</td>
                    <td>{{ $customer->religion }}</td>
                    <td>{{ $customer->race }}</td>
                    <td>{{ $customer->nric }}</td>
                    <td><img src="{{ $customer->profilePicture }}" alt="Profile Picture" width="50"></td>
                    <td>{{ $customer->dateOfBirth }}</td>
                    <td>{{ $customer->phoneNum }}</td>
                    <td>{{ $customer->address }}</td>
                    <td>{{ $customer->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
