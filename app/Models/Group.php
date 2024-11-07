<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Group extends Model
{
    use HasFactory;
    protected $fillable =[
        'doc_id',
    ];
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('position');
    }

    public static function hasAttributes(string $table, string $att): bool
    {
        return Schema::hasColumn($table,$att);
    }


}
