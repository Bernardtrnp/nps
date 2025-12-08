<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskValue extends Model
{
    use HasFactory;

    protected $table = 'risk_values';

    protected $fillable = [
        'risk_variable_id',
        'year',
        'quarter',
        'month',
        'value'
    ];

    /**
     * Relasi balik ke variable
     */
    public function variable()
    {
        return $this->belongsTo(RiskVariable::class, 'risk_variable_id');
    }
}
