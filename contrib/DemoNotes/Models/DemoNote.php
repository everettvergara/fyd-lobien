<?php

namespace App\Modules\DemoNotes\Models;

use Illuminate\Database\Eloquent\Model;

class DemoNote extends Model
{
    protected $fillable = [
        'title',
        'body',
    ];
}
