<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeSolution extends Model
{

    protected $table = 'challenge_solutions';
    //
    protected $fillable = [
        'contents',
    ];
}
