<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class OrderItem extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'order_items';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'order_id',
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
        'price',
        'price_arr',
        'pro_link',
        'pro_properties',
        'rate',
        'site',
        'size',
        'sizetxt',
        'created_at',
        'updated_at'
    ];
}
