<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class  Cart extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'carts';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'shop_id',
        'user_id',
        'kiem_hang',
        'dong_go',
        'bao_hiem',
        'status',
        'created_at',
        'updated_at'
    ];

    public function CartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    public function Shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
