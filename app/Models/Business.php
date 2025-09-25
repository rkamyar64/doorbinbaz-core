<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
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
