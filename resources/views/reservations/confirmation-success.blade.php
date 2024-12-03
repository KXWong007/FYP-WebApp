<!DOCTYPE html>
<html>
<head>
    <title>Reservation Confirmed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .success-icon {
            color: #4CAF50;
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .reservation-id {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Reservation Confirmed!</h1>
        <p>Your reservation has been successfully confirmed.</p>
        <div class="reservation-id">
            Reservation ID: {{ $reservationId }}
        </div>
        <p>Thank you for choosing our restaurant.</p>
    </div>
</body>
</html> 