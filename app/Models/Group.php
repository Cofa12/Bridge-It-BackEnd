<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Schema;

class Group extends Model
{
    use HasFactory;
    protected $fillable =[
        'doc_id',
        'title',
        'description',
        'image',
        'deadline',
        'stage',
        'category_id'
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

    public function files():MorphMany
    {
        return $this->morphMany(File::class,'filable');
    }
    public function templates():HasOne
    {
        return $this->hasOne(Template::class);
    }


}
