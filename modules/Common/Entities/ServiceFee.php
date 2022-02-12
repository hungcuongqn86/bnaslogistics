<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceFee extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'service_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'note',
        'min_tot_tran',
        'max_tot_tran',
        'val',
        'created_at',
        'updated_at'
    ];
}
