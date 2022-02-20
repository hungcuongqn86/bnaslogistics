<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;

class  Shop extends BaseEntity
{
    use Notifiable;

    protected $table = 'shops';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function Carts()
    {
        return $this->hasMany(Cart::class, 'shop_id', 'id');
    }
}
