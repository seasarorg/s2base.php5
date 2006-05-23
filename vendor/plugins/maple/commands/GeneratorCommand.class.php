<?php
class GeneratorCommand implements S2Base_GenerateCommand {

    public function getName(){
        return "generate.php";
    }

    public function execute(){
        $cmd = BASE_DIR . "/script/generate.php";
        $args = S2Base_StdinManager::getValue('args ? : ');

        print "[INFO ] % php $cmd $args \n";
        system("php $cmd $args");
    }
}
?>