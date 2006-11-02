<?php
class CmdCommand implements S2Base_GenerateCommand {
    const COMMAND_CLASS_SUFFIX = 'Command';
    protected $cmdName;

    public static function writeFile($srcFile,$tempContent) {
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public static function showException(Exception $e){
        S2Base_CommandUtil::showException($e);
    }

    public function getName(){
        return "command";
    }

    public function execute(){
        try{
            $this->cmdName = S2Base_StdinManager::getValue('command name ? : ');
            $this->validate($this->cmdName);

            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid command name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  command name : {$this->cmdName} " . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareCmdFile();
    }
    
    protected function prepareCmdFile(){

        $cmdClassName = ucfirst($this->cmdName) . self::COMMAND_CLASS_SUFFIX;
        $srcFile = S2BASE_PHP5_COMMANDS_DIR 
                 . $cmdClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR 
                     . 'command/command.php');
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@COMMAND_NAME@@/");
        $replacements = array($cmdClassName,
                              $this->cmdName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        self::writeFile($srcFile,$tempContent);
    }
}
?>