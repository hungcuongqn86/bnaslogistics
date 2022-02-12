<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vip extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'vips';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'note',
        'min_tot_tran',
        'max_tot_tran',
        'ck_dv',
        'ck_vc',
        'deposit',
        'created_at',
        'updated_at'
    ];
}
