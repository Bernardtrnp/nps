<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';
    protected $fillable = ['name','entitas_id','metadata'];
    protected $casts = ['metadata' => 'array'];

    public function entitas()
    {
        return $this->belongsTo(Entitas::class, 'entitas_id');
    }

    public function risks()
    {
        return $this->hasMany(Risk::class, 'unit_id');
    }
}
