<!DOCTYPE html>
<html>
<head>
    <title>Second Confirmation Required</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #20498A;">Second Confirmation Required</h2>
        
        <p>Dear {{ $reservation->customer_name }},</p>
        
        <p>This is your second confirmation request for your upcoming reservation:</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reservation ID:</strong> {{ $reservation->reservationId }}</p>
            <p><strong>Date & Time:</strong> {{ date('Y-m-d H:i', strtotime($reservation->reservationDate)) }}</p>
            <p><strong>Number of Guests:</strong> {{ $reservation->pax }}</p>
            <p><strong>Area:</strong> {{ $reservation->rarea === 'W' ? 'Western' : 'Chinese' }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('reservation.confirm', ['reservationId' => $reservation->reservationId]) }}"
               style="background-color: #20498A; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Confirm Reservation
            </a>
        </div>

        <p>If you do not confirm your reservation, a final confirmation request will be sent in 24 hours.</p>
        
        <hr style="border: 1px solid #eee; margin: 20px 0;">
        
        <p style="color: #666; font-size: 14px;">
            Best regards,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html> 