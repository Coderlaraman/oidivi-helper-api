<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;


class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    // Relación polimórfica inversa con Skill
    public function skills(): MorphToMany
    {
        return $this->morphedByMany(Skill::class, 'categorizable');
    }

    // Relación polimórfica inversa con ServiceRequest
    public function serviceRequests(): MorphToMany
    {
        return $this->morphedByMany(ServiceRequest::class, 'categorizable');
    }
}
