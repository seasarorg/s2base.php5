<?php
require_once('sfCommandUtil.class.php');
require_once(S2BASE_PHP5_ROOT . '/app/commands/CmdCommand.class.php');
require_once('init/sfCommandFactory.class.php');
class sfInitCommand implements S2Base_GenerateCommand
{
    public function getName ()
    {
        return "init-*";
    }

    public function execute ()
    {
        $cmds = array('init-project', 'init-app', 'init-module');
        $cmd = S2Base_StdinManager::getValueFromArray($cmds, 'symfony commands list');
        if($cmd == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        $cmdCls = sfCommandFactory::create($cmd);
        $cmdCls->execute();
    }
}
?>
