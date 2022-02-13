<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'note',
        'address',
        'phone',
        'status',
        'created_at',
        'updated_at'
    ];
}
