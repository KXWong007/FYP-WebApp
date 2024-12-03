<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Inventory Report</h2>
    <table>
        <thead>
            <tr>
                <th>Inventory ID</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Minimum Quantity</th>
                <th>Maximum Quantity</th>
                <th>Unit Price</th>
                <th>Measurement Unit</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $item)
                <tr>
                    <td>{{ $item->{'Inventory ID'} }}</td>
                    <td>{{ $item->{'Item Name'} }}</td>
                    <td>{{ $item->Quantity }}</td>
                    <td>{{ $item->{'Minimum Quantity'} }}</td>
                    <td>{{ $item->{'Maximum Quantity'} }}</td>
                    <td>{{ $item->{'Unit Price'} }}</td>
                    <td>{{ $item->{'Measurement Unit'} }}</td>
                    <td>{{ $item->{'Created At'} }}</td>
                    <td>{{ $item->{'Updated At'} }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
