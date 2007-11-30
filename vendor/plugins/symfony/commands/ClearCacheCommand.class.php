<?php
class ClearCacheCommand implements S2Base_GenerateCommand
{
    
    public function getName ()
    {
        return "clear-cache";
    }

    public function isAvailable(){
        return true;
    }

    public function execute ()
    {
        S2Base_SymfonyCommandUtil::execSfCmd('clear-cache', null, S2BASE_PHP5_ROOT);
    }
}
