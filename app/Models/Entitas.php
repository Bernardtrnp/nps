<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entitas extends Model
{
    use HasFactory;

    protected $table = 'entitas';
    protected $fillable = ['name','category','metadata'];
    protected $casts = ['metadata' => 'array'];

    public function units()
    {
        return $this->hasMany(Unit::class, 'entitas_id');
    }
}
