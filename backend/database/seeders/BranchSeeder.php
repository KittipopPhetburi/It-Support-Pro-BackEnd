<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'code' => 'BKK001',
                'name' => 'สำนักงานใหญ่',
                'province' => 'กรุงเทพมหานคร',
                'phone' => '02-123-4567',
                'status' => 'Active',
            ],
            [
                'code' => 'CNX001',
                'name' => 'สาขาเชียงใหม่',
                'province' => 'เชียงใหม่',
                'phone' => '053-123-456',
                'status' => 'Active',
            ],
            [
                'code' => 'PHA001',
                'name' => 'สาขาภูเก็ต',
                'province' => 'ภูเก็ต',
                'phone' => '076-123-456',
                'status' => 'Active',
            ],
            [
                'code' => 'KON001',
                'name' => 'สาขาขอนแก่น',
                'province' => 'ขอนแก่น',
                'phone' => '043-123-456',
                'status' => 'Active',
            ],
            [
                'code' => 'HDY001',
                'name' => 'สาขาหาดใหญ่',
                'province' => 'สงขลา',
                'phone' => '074-123-456',
                'status' => 'Active',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                $branch
            );
        }
    }
}
