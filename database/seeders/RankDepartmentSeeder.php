<?php

namespace Database\Seeders;

use App\Models\RankDepartment;
use Illuminate\Database\Seeder;

class RankDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['id' => 1, 'name' => 'Deck'],
            ['id' => 2, 'name' => 'Engine'],
            ['id' => 3, 'name' => 'Catering'],
        ];

        foreach ($departments as $department) {
            RankDepartment::create($department);
        }
    }
}
