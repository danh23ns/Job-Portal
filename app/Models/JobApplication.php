<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplication extends Model
{
    use HasFactory;
    public function job(){
        return $this->belongsTo('App\Models\Job');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function employer(){
        return $this->belongsTo('App\Models\user', 'employer_id');
    }
}
