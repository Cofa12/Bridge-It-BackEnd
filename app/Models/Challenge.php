<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    //
    protected $fillable = [
        'content',
        'solution_id',
        'task_id'
    ];

    public function solution()
    {
        return $this->belongsTo(ChallengeSolution::class,'solution_id','id');
    }
}
