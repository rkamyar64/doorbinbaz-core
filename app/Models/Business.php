<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'family',
        'business_name',
        'address',
        'mobile',
        'tell',
        'zipcode',
        'national_id',
        'store_user_id',
    ];

    public function storeUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'store_user_id');
    }
}
