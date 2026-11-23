<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ship extends Model
{
    use SoftDeletes;

    protected $fillable = ['owner_id', 'name', 'hull_number'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function opnams(): HasMany
    {
        return $this->hasMany(Opnam::class);
    }
}
