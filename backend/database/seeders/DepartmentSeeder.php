<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Branch;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        
        if (!$branch) {
            $this->command->warn('No branches found. Run BranchSeeder first.');
            return;
        }

        $departments = [
            ['code' => 'IT001', 'name' => 'แผนกไอที', 'branch_id' => $branch->id],
            ['code' => 'HR001', 'name' => 'แผนกทรัพยากรบุคคล', 'branch_id' => $branch->id],
            ['code' => 'FIN001', 'name' => 'แผนกการเงิน', 'branch_id' => $branch->id],
            ['code' => 'ACC001', 'name' => 'แผนกบัญชี', 'branch_id' => $branch->id],
            ['code' => 'MKT001', 'name' => 'แผนกการตลาด', 'branch_id' => $branch->id],
            ['code' => 'SAL001', 'name' => 'แผนกขาย', 'branch_id' => $branch->id],
            ['code' => 'OPS001', 'name' => 'แผนกปฏิบัติการ', 'branch_id' => $branch->id],
            ['code' => 'LOG001', 'name' => 'แผนกโลจิสติกส์', 'branch_id' => $branch->id],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}
