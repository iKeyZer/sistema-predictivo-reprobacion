<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'semester', 'credits', 'historical_difficulty',
    ];

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function academicHistory()
    {
        return $this->hasMany(AcademicHistory::class);
    }

    public function getRiskLabel(): string
    {
        if ($this->historical_difficulty >= 40) return 'alto';
        if ($this->historical_difficulty >= 25) return 'medio';
        return 'bajo';
    }
}
