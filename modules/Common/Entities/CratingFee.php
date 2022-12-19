<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class CratingFee extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'crating_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'note',
        'min_count',
        'max_count',
        'first_count',
        'first_val',
        'val',
        'created_at',
        'updated_at'
    ];
}
