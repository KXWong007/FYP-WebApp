<!DOCTYPE html>
<html>
<head>
    <title>Final Confirmation Required</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #5A2555;">Final Confirmation Required - Important</h2>
        
        <p>Dear {{ $reservation->customer_name }},</p>
        
        <p><strong>This is your final confirmation request.</strong> Your reservation will need to be confirmed to maintain your booking:</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Reservation ID:</strong> {{ $reservation->reservationId }}</p>
            <p><strong>Date & Time:</strong> {{ date('Y-m-d H:i', strtotime($reservation->reservationDate)) }}</p>
            <p><strong>Number of Guests:</strong> {{ $reservation->pax }}</p>
            <p><strong>Area:</strong> {{ $reservation->rarea === 'W' ? 'Western' : 'Chinese' }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('reservation.confirm', ['reservationId' => $reservation->reservationId]) }}"
               style="background-color: #5A2555; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Confirm Reservation Now
            </a>
        </div>

        <p style="color: #dc3545;"><strong>Please note:</strong> This is the final confirmation request. If no confirmation is received, your reservation may be cancelled.</p>
        
        <hr style="border: 1px solid #eee; margin: 20px 0;">
        
        <p style="color: #666; font-size: 14px;">
            Best regards,<br>
            {{ config('app.name') }}
        </p>

        <p style="color: #999; font-size: 12px; margin-top: 30px;">
            If you did not make this reservation, please ignore this email.
        </p>
    </div>
</body>
</html> 