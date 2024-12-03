<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class UpdateReservationStatus extends Command
{
    protected $signature = 'reservations:update-status';
    protected $description = 'Update reservation statuses and send confirmation emails';

    public function handle()
    {
        // Get reservations that need status updates
        $reservations = DB::table('reservations')
            ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
            ->where('reservations.rstatus', 'NOT LIKE', 'confirm')
            ->where('reservations.created_at', '<=', now()->subHours(24))
            ->select(
                'reservations.*',
                'customers.name as customer_name',
                'customers.email'
            )
            ->get();

        foreach ($reservations as $reservation) {
            $newStatus = $this->getNextStatus($reservation->rstatus);
            
            if ($newStatus) {
                // Update status
                DB::table('reservations')
                    ->where('reservationId', $reservation->reservationId)
                    ->update([
                        'rstatus' => $newStatus,
                        'updated_at' => now()
                    ]);

                // Send email
                $this->sendConfirmationEmail($reservation, $newStatus);
                
                $this->info("Updated reservation {$reservation->reservationId} to {$newStatus}");
            }
        }
    }

    private function getNextStatus($currentStatus)
    {
        return match($currentStatus) {
            'firstc' => 'secondc',
            'secondc' => 'thirdc',
            'thirdc' => null, // No next status after third
            default => null
        };
    }

    private function sendConfirmationEmail($reservation, $status)
    {
        try {
            Mail::send("emails.confirmation_{$status}", [
                'reservation' => $reservation
            ], function($message) use ($reservation) {
                $message->to($reservation->email)
                       ->subject("Reservation Confirmation Required - {$reservation->reservationId}");
            });
            
            $this->info("Sent {$status} confirmation email to {$reservation->email}");
        } catch (\Exception $e) {
            $this->error("Failed to send email: {$e->getMessage()}");
        }
    }
} 