<!DOCTYPE html>
<html>
<head>
    <title>Reservation Confirmation Required</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2d3748;">Reservation Confirmation Required</h2>
        
        <p>Dear {{ $reservation->customer_name }},</p>
        
        <p>Please confirm your reservation details:</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reservation ID:</strong> {{ $reservation->reservationId }}</p>
            <p><strong>Date & Time:</strong> {{ date('Y-m-d H:i', strtotime($reservation->reservationDate)) }}</p>
            <p><strong>Number of Guests:</strong> {{ $reservation->pax }}</p>
            <p><strong>Area:</strong> {{ $reservation->rarea === 'W' ? 'Western' : 'Chinese' }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('reservation.confirm', ['reservationId' => $reservation->reservationId]) }}"
               style="background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Confirm Reservation
            </a>
        </div>

        <p>If you did not make this reservation, please ignore this email.</p>
        
        <hr style="border: 1px solid #eee; margin: 20px 0;">
        
        <p style="color: #666; font-size: 14px;">
            Best regards,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
