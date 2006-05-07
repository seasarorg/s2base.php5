<?php
class CmdCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $cmdName;

    public function getName(){
        return "command";
    }

    public function execute(){
        $this->cmdName = S2Base_StdinManager::getValue('command name ? : ');
        $this->validate($this->cmdName);
        $this->prepareFiles();
    }        

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid command name. [ $name ]");
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
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $cmdClassName,
                             $tempContent);   
        $tempContent = preg_replace("/@@COMMAND_NAME@@/",
                             $this->cmdName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }
}
?>