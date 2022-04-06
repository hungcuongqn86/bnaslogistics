<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class CarrierPackage extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'carrier_package';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'carrier_id',
        'package_code',
        'product_name',
        'product_count',
        'carrier_brand',
        'description',
        'note',
        'is_main',
        'created_at',
        'updated_at'
    ];
}
