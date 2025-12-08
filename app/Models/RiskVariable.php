<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskVariable extends Model
{
    use HasFactory;

    protected $table = 'risk_variables';

    protected $fillable = [
        'risk_id',
        'variable_name',
        'unit_name',
        'entitas_name',
        'project_name',
        'unit_value',
        'value_type',
        'time_dimension',
        'method',
        'source',
        'notes'
    ];

    /** 
     * Relasi ke Risk 
     */
    public function risk()
    {
        return $this->belongsTo(Risk::class, 'risk_id');
    }

    /** 
     * RELASI YANG WAJIB ADA
     * RiskVariable → many RiskValue 
     */
    public function values()
    {
        return $this->hasMany(RiskValue::class, 'risk_variable_id');
    }
}
