<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'user_id',
        'dob',
        'gender',
        'contact_number',
        'past_conditions',
        'surgical_history',
        'allergies',
        'family_history',
        'current_medications',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
