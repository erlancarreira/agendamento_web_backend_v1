<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convenio extends Model
{
    protected $table = 'convenios';

    public function limite()
    {
        return $this->hasOne(Consulta::class, 'convenio_id', 'convenio_id');
    }
}