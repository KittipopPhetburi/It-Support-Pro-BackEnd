<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'name',
        'organization',
        'department',
        'start_date',
        'end_date',
        'budget',
        'manager_id',
        'description',
        'status',
        'contract_file',
        'tor_file',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Generate next project code
    public static function generateProjectCode(): string
    {
        $lastProject = self::orderBy('id', 'desc')->first();
        if ($lastProject) {
            $lastNumber = (int) str_replace('PRJ-', '', $lastProject->project_code);
            return 'PRJ-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }
        return 'PRJ-0001';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'start_date',
        'end_date',
        'project_value',
        'project_manager_id',
        'organization',
        'department',
        'description',
        'contract_file_name',
        'contract_file_path',
        'tor_file_name',
        'tor_file_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'project_value' => 'decimal:2',
    ];

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }
}
