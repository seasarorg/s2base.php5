<?php
class sfClearCacheCommand implements S2Base_GenerateCommand
{
    private $pathName   = S2BASE_PHP5_SF_DEFAULT_PATH;
    
    public function getName ()
    {
        return "clear-cache";
    }

    public function execute ()
    {
        $pathName = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_PATH);
        if (strlen($pathName) > 0) {
            $this->pathName = $pathName;
        }
        sfCommandUtil::execSfCmd('clear-cache', null, $this->pathName);
    }
}
?>
