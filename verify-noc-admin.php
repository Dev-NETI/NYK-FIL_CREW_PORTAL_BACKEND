<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\AdminRole;
use App\Models\AdminProfile;

echo "=== NOC Admin Verification ===\n\n";

$user = User::where('email', 'noc@neti.com.ph')->first();

if (!$user) {
    echo "❌ NOC admin user not found!\n";
    exit(1);
}

echo "✅ User Found:\n";
echo "   Email: {$user->email}\n";
echo "   ID: {$user->id}\n";
echo "   Is Crew: " . ($user->is_crew ? 'Yes' : 'No') . "\n";
echo "   Department ID: {$user->department_id}\n";
echo "   Created: {$user->created_at}\n\n";

$profile = AdminProfile::where('user_id', $user->id)->first();

if ($profile) {
    echo "✅ Admin Profile Found:\n";
    echo "   Name: {$profile->firstname} {$profile->middlename} {$profile->lastname}\n\n";
} else {
    echo "❌ Admin profile not found!\n\n";
}

$roles = AdminRole::where('user_id', $user->id)->with('role')->get();

echo "✅ Assigned Roles ({$roles->count()} total):\n";
foreach ($roles as $adminRole) {
    echo "   - {$adminRole->role->name}\n";
}

echo "\n=== Verification Complete ===\n";
