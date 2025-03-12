<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    // Relación polimórfica con Category
    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    /**
     * The users that possess this skill.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
