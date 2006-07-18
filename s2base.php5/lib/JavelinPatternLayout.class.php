<?php
require_once(LOG4PHP_DIR . "/layouts/LoggerPatternLayout.php");
class JavelinPatternLayout extends LoggerPatternLayout
{
     function format($event)
     {
         $timeStamp = $event->getTimeStamp();
         $usecs = round(($timeStamp - (int)$timeStamp) * 1000);
         $usec  = sprintf('%03d', $usecs);
         return preg_replace("/(\d\d:\d\d),\d{3}/","$1.$usec",parent::format($event),1);
     }
}
?>