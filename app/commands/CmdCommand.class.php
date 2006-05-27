<?php
class CmdCommand implements S2Base_GenerateCommand {

    private $cmdName;

    public function getName(){
        return "command";
    }

    public function execute(){
        $this->cmdName = S2Base_StdinManager::getValue('command name ? : ');
        try{
            $this->validate($this->cmdName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }

        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid command name. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  command name : {$this->cmdName} \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareCmdFile();
    }
    
    private function prepareCmdFile(){

        $cmdClassName = ucfirst($this->cmdName) . "Command";
        $srcFile = S2BASE_PHP5_COMMANDS_DIR . 
                   "$cmdClassName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                        'command.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@COMMAND_NAME@@/");
        $replacements = array( $cmdClassName,$this->cmdName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        self::writeFile($srcFile,$tempContent);
    }

    public static function writeFile($srcFile,$tempContent) {
        try{
            S2Base_CommandUtil::writeFile($srcFile,$tempContent);
            print "[INFO ] create : $srcFile\n";
        }catch(Exception $e){
            if ($e instanceof S2Base_FileExistsException){
                print "[INFO ] exists : $srcFile\n";
            } else {
                throw $e;
            }
        }
    }

    public static function showException(Exception $e){
        print "\n!!! Exception\n!!! {$e->getMessage()}\n\n";
    }
    
}
?>