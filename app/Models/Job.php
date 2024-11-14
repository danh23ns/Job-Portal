<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function jobType() {
        return $this->belongsTo('App\Models\JobType');
    } 
    
    public function category() {
        return $this->belongsTo('App\Models\Category');
    }

    public function applications(){
        return $this->hasMany('App\Models\JobApplication');
    }

    public function users(){
        return $this->belongsToMany('App\Models\User');
    }

}
