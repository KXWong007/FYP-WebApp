<?php

namespace App\Console\Commands;

use App\Models\customers;
use App\Models\Staff;
use App\Models\Staffs;
use Illuminate\Console\Command;
use App\Models\Customer;

class UpdateProfilePictures extends Command
{
    protected $signature = 'update:profile-pictures';
    protected $description = 'Update profile picture links for all customers';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $customers = customers::all();
        foreach ($customers as $customer) {
            $customer->profilePicture = 'https://yourdomain.com/path/to/real/profile/image' . $customer->id . '.jpg'; // You can adjust the URL format as needed
            $customer->save();
        }
        $this->info('Profile pictures updated successfully!');

        // Update Staff Profile Pictures
        $staffMembers = Staffs::all();
        foreach ($staffMembers as $staff) {
            $staff->profilePicture = 'https://yourdomain.com/path/to/real/profile/image/staff' . $staff->id . '.jpg';
            $staff->save();
        }

        $this->info('Staff profile pictures updated successfully!');
    }
}


