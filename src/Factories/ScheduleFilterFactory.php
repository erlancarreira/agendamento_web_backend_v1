<?php

namespace App\Factories;
use App\Filters\ScheduleFilter;
use App\Interfaces\ScheduleFilterInterface;
use App\Providers\UnixTimeProvider;


class ScheduleFilterFactory
{
    
    public static function create(): ScheduleFilterInterface
    {
        
        $timeProvider   = new UnixTimeProvider();
        $scheduleFilter = new ScheduleFilter($timeProvider);
        
        return $scheduleFilter;
    }
}