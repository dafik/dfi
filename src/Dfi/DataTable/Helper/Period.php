<?
namespace Dfi\DataTable\Helper;

use DateTime;

class Period
{

    public static function run($date)
    {
        $now = new DateTime();
        $now->setTime(0, 0, 0);
        $date = new DateTime($date);
        $date->setTime(0, 0, 0);

        $timeDiff = $now->diff($date);
        $period = $timeDiff->format('%d');

        return $period;
    }
}