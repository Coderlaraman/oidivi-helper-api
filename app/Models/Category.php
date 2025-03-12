<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    // Relación polimórfica inversa con Skill
    public function skills()
    {
        return $this->morphedByMany(Skill::class, 'categorizable');
    }

    // Relación polimórfica inversa con ServiceRequest
    public function serviceRequests()
    {
        return $this->morphedByMany(ServiceRequest::class, 'categorizable');
    }
}
