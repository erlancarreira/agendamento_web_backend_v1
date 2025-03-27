<?php
namespace App\Interfaces;

interface DateConverterInterface {
    public function convertDateFormat(string $date): string;
}

