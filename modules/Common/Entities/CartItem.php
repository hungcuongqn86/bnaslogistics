<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class CartItem extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'cart_items';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'cart_id',
        'amount',
        'begin_amount',
        'color',
        'colortxt',
        'count',
        'domain',
        'image',
        'method',
        'name',
        'note',
        'nv_note',
        'kho_note',
        'price',
        'price_arr',
        'pro_link',
        'pro_properties',
        'rate',
        'site',
        'size',
        'sizetxt',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
}
