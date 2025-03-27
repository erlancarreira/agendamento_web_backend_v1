<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $table = 'consultas'; // Nome da tabela no banco de dados

    protected $fillable = [
        'quantidade',
        'convenio_id',
        'limite'
    ];

    // Se sua tabela não tiver timestamps
    public $timestamps = false;

    // Método para upsert
    public static function upsert(array $values, array $uniqueBy)
    {
        return static::updateOrCreate(
            [$uniqueBy => $values[$uniqueBy]],
            $values
        );
    }
}
