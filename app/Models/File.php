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

    public function group():BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task():BelongsTo
    {
        return $this->belongsTo(Task::class);
    }


}
