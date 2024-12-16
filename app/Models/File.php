<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'path',
        'task_id',
        'group_id',
        'publisher_id'
    ];

    public function filable():BelongsTo
    {
        return $this->morphTo();
    }






}
