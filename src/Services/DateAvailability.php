<?php 

namespace App\Services;

use App\Interfaces\DateAvailabilityInterface;

class DateAvailability implements DateAvailabilityInterface {
    public function getDateAvailable(array $date, array $appointments, int $quantity): ?array {
        $appointments = array_reverse($appointments);
        $datesAfter = array_reduce($appointments, function ($acc, $d) use ($date, $quantity) {
            $dataAgenda = \DateTime::createFromFormat('d/m/Y', $date['dataAgenda']);
            $dDate = new \DateTime($d);
            
            if ($dDate > $dataAgenda && $quantity) {
                $acc[] = $d;
            }

            return $acc;
            
        }, []);
        
        if (!$quantity) {
            return $date;
        }

        return $quantity >= count($datesAfter) ? $date : null;
    }
}