<?php
class DtoCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $dtoClassName;
    protected $properties;
    protected $dtoOrBean = 'dto';

    public function getName(){
        return $this->dtoOrBean;
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            if ($this->getDtoInfoInteractive() and
                $this->finalConfirm()){
                $this->prepareFiles();
            }
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function getDtoInfoInteractive() {
        $this->dtoClassName = S2Base_StdinManager::getValue('class name ? : ');
        $this->validate($this->dtoClassName);
        $properties = S2Base_StdinManager::getValue("properties ? (id,name,--,) : ");
        $this->properties = EntityCommand::validateCols($properties);
        return true;
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name          : {$this->moduleName}" . PHP_EOL;
        print "  class name           : {$this->dtoClassName}" . PHP_EOL;
        $properties = implode(', ', $this->properties);
        print "  properties           : $properties" . PHP_EOL;

        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareDtoFile();
    }
    
    protected function prepareDtoFile(){
        $dtoDir = S2BASE_PHP5_MODULES_DIR 
                . $this->moduleName
                . S2BASE_PHP5_DS
                . $this->dtoOrBean;
        S2Base_CommandUtil::createDirectory($dtoDir);

        $srcFile = $dtoDir
                 . S2BASE_PHP5_DS
                 . $this->dtoClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $accessorSrc = self::getAccessorSrc($this->properties);
        $toStringSrc = self::getToStringSrc($this->properties);
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dto/dto.php');
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@ACCESSOR@@/",
                          "/@@TO_STRING@@/");
        $replacements = array($this->dtoClassName,
                              $accessorSrc,
                              $toStringSrc);

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public static function getAccessorSrc($props){
        $tempContent  = '    protected $@@PROP_NAME@@;' . PHP_EOL .
                        '    public function set@@UC_PROP_NAME@@($val){$this->@@PROP_NAME@@ = $val;}' . PHP_EOL . 
                        '    public function get@@UC_PROP_NAME@@(){return $this->@@PROP_NAME@@;}' . PHP_EOL . PHP_EOL;
        $retSrc = "";
        foreach($props as $prop){
            $patterns = array("/@@UC_PROP_NAME@@/",
                              "/@@PROP_NAME@@/");
            $replacements = array(ucfirst($prop),
                                  $prop);
            $retSrc .= preg_replace($patterns,$replacements,$tempContent);
        }
        return $retSrc;
    }

    public static function getToStringSrc($props){
        
        if (count($props) == 0){
            return "";
        }
        
        $src      = '    public function __toString() {' . PHP_EOL;
        $src     .= '        $buf = array();' . PHP_EOL;
        foreach($props as $prop){
            $getter = '\' . $this->get' . ucfirst($prop) . '();';            
            $src .= '        $buf[] = \'' . "$prop => " . $getter . PHP_EOL;
        }
        $src     .= '        return \'{\' . implode(\', \',$buf) . \'}\';' . PHP_EOL;
        $src     .= '    }' . PHP_EOL;
        return $src;
    }
}
?>
