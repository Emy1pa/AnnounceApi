<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    protected $fillable = ['announcement_id', 'volunteer_id', 'required_skills', 'message', 'status'];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    // Define the relationship with Volunteer model
    public function volunteer()
    {
        return $this->belongsTo(User::class);
    }
}

