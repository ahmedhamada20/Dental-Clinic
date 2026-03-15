<?php

namespace App\Models\Clinic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Holiday
 *
 * Clinic holidays and special closure dates.
 *
 * @property \Carbon\Carbon $date
 * @property string $title
 * @property string|null $notes
 * @property bool $is_active
 */
class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'title',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}

