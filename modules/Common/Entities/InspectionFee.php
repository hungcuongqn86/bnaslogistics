<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionFee extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'inspection_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'note',
        'min_count',
        'max_count',
        'val',
        'created_at',
        'updated_at'
    ];
}