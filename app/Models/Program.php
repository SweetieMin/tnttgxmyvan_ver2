<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;
    use LogsModelActivity;

    protected $fillable = [
        'ordering',
        'course',
        'sector',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ordering' => 'integer',
        ];
    }
}
