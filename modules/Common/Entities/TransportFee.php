<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportFee extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'transport_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'type',
        'warehouse_id',
        'title',
        'note',
        'min_r',
        'max_r',
        'val',
        'created_at',
        'updated_at'
    ];
}
