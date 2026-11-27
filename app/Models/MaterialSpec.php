<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialSpec extends Model
{
    use SoftDeletes;

    protected $fillable = ['work_type_id', 'name'];

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function tariffs(): HasMany
    {
        return $this->hasMany(Tariff::class);
    }
}
