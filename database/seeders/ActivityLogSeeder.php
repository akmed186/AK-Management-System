<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        if (Activity::exists()) {
            return;
        }

        $admin = User::where('email', 'admin@example.com')->first();
        $superAdmin = User::where('email', 'superadmin@example.com')->first();

        if (! $admin && ! $superAdmin) {
            return;
        }

        $causers = array_values(array_filter([$admin, $superAdmin]));

        $owners = Owner::take(3)->get();
        $properties = Property::with('owner')->take(3)->get();
        $tenants = Tenant::take(3)->get();

        $entries = [];

        foreach ($owners as $owner) {
            $entries[] = ['action' => 'created', 'subject' => $owner, 'description' => "Created owner \"{$owner->name}\""];
            $entries[] = ['action' => 'updated', 'subject' => $owner, 'description' => "Updated owner \"{$owner->name}\""];
        }

        foreach ($properties as $property) {
            $entries[] = ['action' => 'created', 'subject' => $property, 'description' => "Created property \"{$property->property_name}\""];
        }

        foreach ($tenants as $tenant) {
            $entries[] = ['action' => 'created', 'subject' => $tenant, 'description' => "Created tenant \"{$tenant->full_name}\""];
        }

        $entries[] = ['action' => 'updated', 'subject' => null, 'description' => 'Updated application settings'];
        $entries[] = ['action' => 'granted', 'subject' => null, 'description' => 'Granted "View Reports" permission to role "Property Manager"'];

        $timestamp = Carbon::now()->subDays(count($entries));

        foreach ($entries as $entry) {
            Activity::create([
                'causer_id' => $causers[array_rand($causers)]->id,
                'action' => $entry['action'],
                'subject_type' => $entry['subject']?->getMorphClass(),
                'subject_id' => $entry['subject']?->getKey(),
                'description' => $entry['description'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $timestamp = $timestamp->copy()->addHours(random_int(4, 30));
        }
    }
}
