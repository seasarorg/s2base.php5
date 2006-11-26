<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2006 the Seasar Foundation and the Others.            |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// |                                                                      |
// |     http://www.apache.org/licenses/LICENSE-2.0                       |
// |                                                                      |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
// | either express or implied. See the License for the specific language |
// | governing permissions and limitations under the License.             |
// +----------------------------------------------------------------------+
// | Authors: klove, KIYO-SHIT                                            |
// +----------------------------------------------------------------------+
//
// $Id$
/**
 * @author klove, KIYO-SHIT
 */
require_once(LOG4PHP_DIR . "/layouts/LoggerPatternLayout.php");
class S2JavelinPatternLayout extends LoggerPatternLayout
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
