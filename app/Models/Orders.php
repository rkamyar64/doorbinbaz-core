<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orders extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'services',
        'description',
        'status',
        'full_price',
        'fee_price',
        'profit_price',
        'discount',
        'service_user_id',
        'store_user_id',
    ];
    public function storeUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'store_user_id');
    }
       public function serviceUsers(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'service_user_id');
    }
    public function businessId(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

}
