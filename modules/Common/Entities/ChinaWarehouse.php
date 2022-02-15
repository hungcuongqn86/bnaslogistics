<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChinaWarehouse extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'china_warehouses';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'note',
        'address',
        'receiver',
        'phone',
        'zipcode',
        'status',
        'created_at',
        'updated_at'
    ];
}
