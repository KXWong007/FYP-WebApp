<!DOCTYPE html>
<html>
<head>
    <title>Payments Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Payments Report</h2>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Reservation ID</th>
                <th>Customer</th>
                <th>Amount (RM)</th>
                <th>Type</th>
                <th>Date</th>
                <th>Method</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->paymentId }}</td>
                <td>{{ $payment->reservationId }}</td>
                <td>{{ $payment->customerId }} - {{ $payment->customerName }}</td>
                <td>{{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->paymentType }}</td>
                <td>{{ date('Y-m-d H:i:s', strtotime($payment->paymentDate)) }}</td>
                <td>{{ $payment->paymentMethod }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
