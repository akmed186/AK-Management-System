<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitiesSeeder extends Seeder
{
    private const AMENITIES = [
        'Air Conditioning',
        'Ceiling Fan',
        'Bed & Mattress',
        'Wardrobe',
        'POP Ceiling',
        'Fiber Broadband',
        'Water Heater',
        'Kitchen Cabinet',
        'Balcony',
        'Dedicated Parking',
        '24/7 Security',
        'Backup Generator',
        'Water Storage / Borehole',
        'Prepaid Meter',
        'Fully Furnished',
    ];

    public function run(): void
    {
        foreach (self::AMENITIES as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity]);
        }
    }
}
