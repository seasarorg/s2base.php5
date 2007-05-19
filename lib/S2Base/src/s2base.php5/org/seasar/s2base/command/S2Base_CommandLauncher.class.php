<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
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
// | Authors: klove                                                       |
// +----------------------------------------------------------------------+
//
// $Id$
/**
 * S2Base_GenerateCommandを実行するランチャークラスです。
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.command
 * @author     klove
 */
class S2Base_CommandLauncher {

    private $commands = array();

    /**
     * S2Base_GenerateCommandをリストに追加します。
     * 
     * @param S2Base_GenerateCommand $command 追加するコマンド
     */
    public function addCommand(S2Base_GenerateCommand $command){
        $this->commands[$command->getName()] = $command;
    }

    /**
     * コマンドランチャーを起動します。
     */
    public function main(){
        $cmds = array_keys($this->commands);
        sort($cmds);
        while(true){
            $cmd = S2Base_StdinManager::getValueFromArray($cmds,"Command list");
            if($cmd == S2Base_StdinManager::EXIT_LABEL){
                break;
            }else{
                $this->commands[$cmd]->execute();
            }
        }
    }
}
?>
