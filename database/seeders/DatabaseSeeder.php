<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customers;
use App\Models\Staffs;
use App\Models\Menu;
use App\Models\Orders;
use App\Models\OrderItems;
use App\Models\Tables;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $faker = Faker::create();

        // Generate dummy customers
        $this->seedCustomers($faker);

        // Generate dummy staffs
        $this->seedStaffs($faker);

        // Generate dummy menu items
        $this->seedMenu();

        // Generate dummy tables
        $this->seedTables($faker);

        // Generate dummy orders with associated order items
        $this->seedOrders($faker);
    }

    private function seedCustomers($faker)
    {
        $customers = [
            [
                'customerId' => 'O001', // Static ID for Ordinary
                'customerType' => 'Ordinary',
                'name' => 'Ordinary Customer',
                'password' => bcrypt('12345678'),
                'email' => 'ordinary@gmail.com',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phoneNum' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Active']),
            ],
            [
                'customerId' => 'A001', // Static ID for Associate
                'customerType' => 'Associate',
                'name' => 'Associate Customer',
                'password' => bcrypt('12345678'),
                'email' => 'associate@gmail.com',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phoneNum' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Active']),
            ],
            [
                'customerId' => 'J001', // Static ID for Junior
                'customerType' => 'Junior',
                'name' => 'Junior Customer',
                'password' => bcrypt('12345678'),
                'email' => 'junior@gmail.com',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phoneNum' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Active']),
            ],
        ];

        foreach ($customers as $customer) {
            Customers::create($customer);
        }

        $ordinaryCounter = ['O' => 2, 'A' => 2, 'J' => 2]; // Start counters from 2 for initials O, A, J

        for ($i = 0; $i < 5; $i++) {
            $customerType = $faker->randomElement(['Ordinary', 'Associate', 'Junior']);
            $name = $faker->firstName;
            $initial = strtoupper(substr($name, 0, 1));

            if ($customerType === 'Ordinary') {
                $ordinaryCounter[$initial] = ($ordinaryCounter[$initial] ?? 1) + 1;
                $customerId = $initial . str_pad($ordinaryCounter[$initial], 3, '0', STR_PAD_LEFT);
            } elseif ($customerType === 'Associate') {
                $customerId = $initial . str_pad($ordinaryCounter[$initial] ?? 1, 3, '0', STR_PAD_LEFT) . '-1';
            } else { // Junior
                $customerId = $initial . str_pad($ordinaryCounter[$initial] ?? 1, 3, '0', STR_PAD_LEFT) . '-2';
            }

            Customers::create([
                'customerId' => $customerId,
                'customerType' => $customerType,
                'name' => $name,
                'password' => bcrypt('12345678'),
                'email' => $faker->unique()->safeEmail,
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phoneNum' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Pending Verification', 'Active', 'Frozen', 'Disabled', 'Banned']),
            ]);
        }
    }

    private function seedStaffs($faker)
    {
        $staffs = [
            [
                'staffId' => 'D001', // Static ID for Dining Area Staff
                'staffType' => 'Dining Area Staff',
                'name' => 'dining',
                'password' => bcrypt('12345678'),
                'email' => 'dining@gmail.com',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Current']),
            ],
            [
                'staffId' => 'K001', // Static ID for Kitchen Staff
                'staffType' => 'Kitchen Area Staff',
                'name' => 'kitchen',
                'password' => bcrypt('12345678'),
                'email' => 'kitchen@gmail.com',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Current']),
            ],
            [
                'staffId' => 'I001', // Static ID for Inventory Manager
                'staffType' => 'Inventory Manager',
                'name' => 'inventory',
                'password' => bcrypt('12345678'),
                'email' => 'inventory@gmail.com',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Current']),
            ],
            [
                'staffId' => 'M001', // Static ID for F&B Manager
                'staffType' => 'F&B Manager',
                'name' => 'fnbmanager',
                'password' => bcrypt('12345678'),
                'email' => 'fnbmanager@gmail.com',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                'profilePicture' => $faker->imageUrl(),
                'dateOfBirth' => $faker->date,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => $faker->randomElement(['Current']),
            ],
        ];

        foreach ($staffs as $staff) {
            Staffs::create($staff);
        }

        // Generate additional staff for each type with incrementing IDs
        $count = 4;
        foreach (['D' => 'Dining Area Staff', 'K' => 'Kitchen Area Staff'] as $prefix => $type) {
            for ($i = 1; $i <= 10; $i++) {
                Staffs::create([
                    'staffId' => $prefix . str_pad($count++, 3, '0', STR_PAD_LEFT),
                    'staffType' => $type,
                    'name' => $faker->name,
                    'password' => bcrypt('12345678'),
                    'email' => $faker->unique()->safeEmail,
                    'gender' => $faker->randomElement(['Male', 'Female']),
                    'religion' => $faker->randomElement(['Buddhist', 'Christian', 'Muslim', 'Others']),
                    'race' => $faker->randomElement(['Chinese', 'Malay', 'Indian', 'Others']),
                    'nric' => $faker->unique()->numberBetween(100000000000, 999999999999),
                    'profilePicture' => $faker->imageUrl(),
                    'dateOfBirth' => $faker->date,
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'status' => $faker->randomElement(['Current', 'Resigned']),
                ]);
            }
        }
    }    
    
    private function seedMenu()
    {
        $menuItems = [
            [
                'dishId' => 'DISH0001',
                'dishName' => 'Bubur',
                'category' => 'MainCourse',
                'subcategory' => 'Non-Vegetarian',
                'cuisine' => 'Chinese',
                'image' => 'menu-images/btVKILP3QLnX7uot7B4sakniCdkQxLx35AEIw0qf.jpg',
                'price' => 20.00,
                'availableTime' => '07:00',
                'availableArea' => 'Hornbill Restaurant,Badger Bar,Mainhall',
                'availability' => 1,
                'description' => 'hyyh',
                'created_at' => '2024-11-12 00:25:00',
                'updated_at' => '2024-11-12 00:25:35',
            ],
            [
                'dishId' => 'DISH0002',
                'dishName' => 'Fried Noodle',
                'category' => 'MainCourse',
                'subcategory' => 'Vegetarian',
                'cuisine' => 'Malaysian',
                'image' => 'menu-images/tIqNMocwRZacIuEUXOulq7hmmxyeGwSGQ1Rz6hV5.jpg',
                'price' => 20.00,
                'availableTime' => '07:00',
                'availableArea' => 'Badger Bar',
                'availability' => 1,
                'description' => 'dw3q32',
                'created_at' => '2024-11-12 00:33:57',
                'updated_at' => '2024-11-12 00:33:57',
            ],
            [
                'dishId' => 'DISH0003',
                'dishName' => 'Fried Rice',
                'category' => 'MainCourse',
                'subcategory' => 'Vegetarian',
                'cuisine' => 'Malaysian',
                'image' => 'menu-images/rv6zyNQXZCCcO7iBhKgQ8UynIlc5V0Vz4XMeVvBe.jpg',
                'price' => 20.00,
                'availableTime' => '07:00',
                'availableArea' => 'Hornbill Restaurant,Mainhall',
                'availability' => 1,
                'description' => 'wqdwdqw',
                'created_at' => '2024-11-12 00:34:58',
                'updated_at' => '2024-11-12 00:34:58',
            ],
            [
                'dishId' => 'DISH0004',
                'dishName' => 'Beef Burger',
                'category' => 'Specials',
                'subcategory' => 'Small Bites',
                'cuisine' => 'Western',
                'image' => 'menu-images/VbuFvoM5mmlQxYyajhRUHybUn2qeTDnc0bPvLirS.jpg',
                'price' => 16.00,
                'availableTime' => '12:00',
                'availableArea' => 'Hornbill Restaurant,Badger Bar,Mainhall',
                'availability' => 1,
                'description' => 'Beef Burger Western Style snack',
                'created_at' => '2024-11-13 18:39:06',
                'updated_at' => '2024-11-13 18:39:06',
            ],
            [
                'dishId' => 'DISH0005',
                'dishName' => 'Sandwiches',
                'category' => 'Snacks',
                'subcategory' => 'Burgers & Sandwiches',
                'cuisine' => 'Western',
                'image' => 'menu-images/889reTZZgw5aZ6A93RHE2yiLAgFZcZlb7Wv48vAW.jpg',
                'price' => 18.00,
                'availableTime' => '10:00',
                'availableArea' => 'Badger Bar,Mainhall',
                'availability' => 1,
                'description' => 'Sandwiches with fresh vege and beef',
                'created_at' => '2024-11-13 18:40:33',
                'updated_at' => '2024-11-13 18:40:33',
            ],
            [
                'dishId' => 'DISH0006',
                'dishName' => 'Carrot Juice',
                'category' => 'Beverage',
                'subcategory' => 'Cold',
                'cuisine' => 'Western',
                'image' => 'menu-images/4CwD17EDtfKURjHBd4uD1z1wC6rQAdatIsHG9xdL.jpg',
                'price' => 9.00,
                'availableTime' => '10:00',
                'availableArea' => 'Hornbill Restaurant,Badger Bar,Mainhall',
                'availability' => 1,
                'description' => 'Fresh Carrot Juice',
                'created_at' => '2024-11-13 18:41:47',
                'updated_at' => '2024-11-13 18:41:47',
            ],
            [
                'dishId' => 'DISH0007',
                'dishName' => 'Apple Juice',
                'category' => 'Beverage',
                'subcategory' => 'Cold',
                'cuisine' => 'Western',
                'image' => 'menu-images/xwbAMcKcg7xLEERZnTZze3sNVIhnR7hot1qFmRks.jpg',
                'price' => 9.00,
                'availableTime' => '10:00',
                'availableArea' => 'Hornbill Restaurant,Badger Bar,Mainhall',
                'availability' => 1,
                'description' => 'Fresh Apple Juice directly from farm',
                'created_at' => '2024-11-13 18:42:50',
                'updated_at' => '2024-11-13 18:42:50',
            ],
            [
                'dishId' => 'DISH0008',
                'dishName' => 'Orange Juice',
                'category' => 'Beverage',
                'subcategory' => 'Cold',
                'cuisine' => 'Western',
                'image' => 'menu-images/NoDNExSKhXNtqhUnau5BBy5cRF8v65UqO7GXovwA.jpg',
                'price' => 8.00,
                'availableTime' => '10:00',
                'availableArea' => 'Hornbill Restaurant,Badger Bar,Mainhall',
                'availability' => 1,
                'description' => 'Fresh orange juice from farm',
                'created_at' => '2024-11-13 18:43:57',
                'updated_at' => '2024-11-13 18:44:06',
            ],
        ];

        foreach ($menuItems as $menuItem) {
            Menu::create($menuItem);
        }
    }

    private function seedTables($faker)
    {
        $areas = [
            'M' => 'Main Hall',
            'R' => 'Rajah Room',
            'H' => 'Hornbill Restaurant',
            'B' => 'Badger Bar'
        ];
        
        foreach ($areas as $prefix => $area) {
            for ($i = 1; $i <= 5; $i++) {
                $tableNumber = $prefix . $i; // Creates table numbers like B1, H1, etc.
                    Tables::create([
                    'tableNum' => $tableNumber,
                    'capacity' => $faker->numberBetween(2, 10),
                    'area' => $area,
                    'status' => $faker->randomElement(['Available', 'Reserved', 'Occupied']),
                ]);
            }
        }
    }

        private function seedOrders($faker)
    {
        $customers = Customers::all();
        $tables = Tables::all();

        // Filter staff to only include those with IDs starting with 'D'
        $diningStaffs = Staffs::where('staffId', 'LIKE', 'D%')->get();

        foreach ($customers as $customer) {
            for ($i = 0; $i < 1; $i++) {
                $table = $tables->random();
                $orderDate = $faker->dateTimeBetween('-1 week', 'now');
                $orderDateFormatted = $orderDate->format('Y-m-d H:i');

                $microTime = microtime(true);  // Get the current Unix timestamp with microseconds
                $microseconds = sprintf("%02d", ($microTime - floor($microTime)) * 100);  // Get microseconds as a 2-digit string
                
                // Format the orderId: DateTime + "v" + microseconds
                $orderId = $orderDate->format('YmdHis') . $microseconds;
                
                // Reset totalPrice for each new order
                $totalPrice = 0;

                $order = Orders::create([
                    'orderId' => $orderId,
                    'customerId' => $customer->customerId,
                    'tableNum' => $table->tableNum,
                    'orderDate' => $orderDateFormatted,
                    'totalAmount' => $totalPrice, // Start with 0 to be updated later
                    'status' => 'Pending',
                ]);

                $numItems = rand(1, 5);

                for ($j = 0; $j < $numItems; $j++) {
                    $dish = Menu::inRandomOrder()->first();
                    $quantity = rand(1, 5);

                    // Ensure dish and dining staff are available
                    if (!$dish || $diningStaffs->isEmpty()) {
                        echo "No menu items or dining staff available!\n";
                        break;
                    }

                    // Select a random dining area staff
                    $staff = $diningStaffs->random();

                    // Create order item
                    OrderItems::create([
                        'orderId' => $orderId,
                        'dishId' => $dish->dishId,
                        'servedBy' => null,
                        'quantity' => $quantity,
                        'status' => 'Pending',
                    ]);

                    // Accumulate the total price for the order
                    $totalPrice += $quantity * $dish->price;
                }
    
                // Update the totalAmount
                Orders::where('orderId', '=', $orderId)->update(['totalAmount' => $totalPrice]);
            }
        }
    }

}