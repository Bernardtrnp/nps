<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Risk extends Model
{
    use HasFactory;

    protected $table = 'risks';

    protected $fillable = [
        'risk_type_id',
        'unit_id',
        'entitas_id',
        'name',
        'subcategory',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /* ---------------------------------------------------
     | RELASI UTAMA
     | --------------------------------------------------- */

    /**
     * Relasi standar Laravel (nama lama)
     * Banyak kode lama Anda menggunakan type()
     */
    public function type()
    {
        return $this->belongsTo(RiskType::class, 'risk_type_id', 'id');
    }

    /**
     * Relasi yang dipakai Service baru
     * mencegah error: "Call to undefined relationship riskType"
     */
    public function riskType()
    {
        return $this->belongsTo(RiskType::class, 'risk_type_id', 'id');
    }

    /**
     * Relasi Risk → Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    /**
     * Relasi Risk → Entitas (PLN Grup, Anak Perusahaan, dll)
     * Sesuaikan nama model jika berbeda
     */
    public function entitas()
    {
        return $this->belongsTo(Entitas::class, 'entitas_id', 'id');
    }

    /**
     * Semua variabel dari satu risk
     */
    public function variables()
    {
        return $this->hasMany(RiskVariable::class, 'risk_id', 'id');
    }

    /* ---------------------------------------------------
     | RELASI BANTUAN (OPSIONAL TAPI BERGUNA)
     | --------------------------------------------------- */

    /**
     * Values dari seluruh variable dalam satu risk.
     * Mempermudah aggregasi tanpa looping manual.
     */
    public function allValues()
    {
        return $this->hasManyThrough(
            RiskValue::class,
            RiskVariable::class,
            'risk_id',     // RiskVariable.risk_id
            'variable_id', // RiskValue.variable_id
            'id',          // Risk.id
            'id'           // RiskVariable.id
        );
    }
}
