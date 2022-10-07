<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class  Shop extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'shops';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'features',
        'note',
        'created_at',
        'updated_at'
    ];
    protected $appends = ['rate'];

    public function getRateAttribute()
    {
        $setting = new Setting();
        $rowRate = $setting->where('key', '=', 'rate')->first();
        return (int)$rowRate->value;
    }

    public function Carts()
    {
        return $this->hasMany(Cart::class, 'shop_id', 'id');
    }

    public function Orders()
    {
        return $this->hasMany(Order::class, 'shop_id', 'id');
    }
}
