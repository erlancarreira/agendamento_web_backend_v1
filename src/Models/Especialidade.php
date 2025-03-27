<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidade extends Model
{
    protected $table      = 'especialidades';
    protected $primaryKey = 'especialidade_id';
    protected $fillable   = ['active', 'order'];
}