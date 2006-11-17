<?php
class DaoMethodCommand implements S2Base_GenerateCommand {

    const SQL_DEF_TYPE_NONE       = 'none';
    const SQL_DEF_TYPE_QUERY_ANNO = 'QUERY annotation';
    const SQL_DEF_TYPE_SQL_ANNO   = 'SQL annotation';
    const SQL_DEF_TYPE_SQL_FILE   = 'sql file';
    const SQL_FILE_SUFFIX = '.sql';
    const ARGS_ANNO_NONE  = 'none';

    protected $moduleName;
    protected $daoInterfaces;
    protected $daoInterfaceName;
    protected $methodDef;
    protected $sqlDefType;
    protected $sqlDef;
        
    public function getName(){
        return "dao method";
    }

    private function getdaoInterfaces($dir) {
        $items = scandir($dir);
        $daos = array();
        foreach ($items as $item) {
            $matches = array();
            if (preg_match("/^(\w+Dao)\./", $item, $matches)) {
                $daos[] = $matches[1];
            }
        }
        return $daos;
    }
    
    public function getSqlDef(){
        $sql = array();
        print PHP_EOL;
        while(true){
            print 'sql> ';
            $sqlTmp = trim(fgets(STDIN));
            if (preg_match('/;$/',$sqlTmp)) {
                if ($this->sqlDefType == self::SQL_DEF_TYPE_QUERY_ANNO) {
                    $sqlTmp = preg_replace('/;$/', '', $sqlTmp);
                }
                $sql[] = $sqlTmp;
                break;
            } else {
                $sql[] = $sqlTmp;
            }
        }
        return $sql;
    }

    public function getmethodName($methodDef){
        $matches = array();
        if (preg_match("/function\s+(.+)\s*\(/", $methodDef, $matches)) {
            return $matches[1];
        } else {
            throw new Exception('could not get method name.');
        }
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->daoDir = S2BASE_PHP5_ROOT 
                          . "/app/modules/"
                          . $this->moduleName
                          . S2BASE_PHP5_DAO_DIR;
            $this->daoInterfaces = $this->getdaoInterfaces($this->daoDir);
            if (count($this->daoInterfaces) == 0) {
                throw new Exception('dao not found at all.');
            }
            $this->daoInterfaceName = S2Base_StdinManager::getValueFromArray($this->daoInterfaces, 'dao list');
            if (S2Base_CommandUtil::isListExitLabel($this->daoInterfaceName)){
                return;
            }
            $this->methodDef = S2Base_StdinManager::getValue('method definition ? : ');
            $this->methodName = $this->getmethodName($this->methodDef);
            $sqlDefType = array(self::SQL_DEF_TYPE_NONE,
                                self::SQL_DEF_TYPE_QUERY_ANNO,
                                self::SQL_DEF_TYPE_SQL_ANNO,
                                self::SQL_DEF_TYPE_SQL_FILE);
            $this->sqlDefType = S2Base_StdinManager::getValueFromArray($sqlDefType, 'sql definition list');
            if (S2Base_CommandUtil::isListExitLabel($this->sqlDefType)){
                return;
            }

            if ($this->sqlDefType != self::SQL_DEF_TYPE_NONE) {
                $this->sqlDef = $this->getSqlDef();
                $this->argsAnno = S2Base_StdinManager::getValue('ARGS annotation [' . self::ARGS_ANNO_NONE . '] ? : ');
                if ($this->argsAnno == '') {
                    $this->argsAnno = self::ARGS_ANNO_NONE;
                }
            }
            
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name         : {$this->moduleName}" . PHP_EOL;
        print "  dao class name      : {$this->daoInterfaceName}" . PHP_EOL;
        print "  method definition   : {$this->methodDef}" . PHP_EOL;
        print "  method name         : {$this->methodName}" . PHP_EOL;
        print "  sql definition type : {$this->sqlDefType}" . PHP_EOL;
        if ($this->sqlDefType != self::SQL_DEF_TYPE_NONE) {
            print "  args annotation     : {$this->argsAnno}"  . PHP_EOL;
            print "  sql definition      : "  . PHP_EOL;
            foreach ($this->sqlDef as $line){
                print '    ' . $line . PHP_EOL;
            }
        }
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareDaoFile();
        if ($this->sqlDefType == self::SQL_DEF_TYPE_SQL_FILE) {
            $this->prepareSqlFile();
        }
    }

    protected function prepareDaoFile(){
        $src = '';
        if ($this->sqlDefType == self::SQL_DEF_TYPE_QUERY_ANNO) {
            $src .= '    const '
                  . $this->methodName
                  . "_QUERY = '"
                  . implode(' ' . PHP_EOL, $this->sqlDef)
                  . "';" . PHP_EOL;
        } else if ($this->sqlDefType == self::SQL_DEF_TYPE_SQL_ANNO) {
            $src .= '    const '
                  . $this->methodName
                  . "_SQL = '"
                  . implode(' ' . PHP_EOL, $this->sqlDef)
                  . "';" . PHP_EOL;
        }

        if ($this->argsAnno != self::ARGS_ANNO_NONE) {
            $src .= '    const '
                  . $this->methodName
                  . "_ARGS = '"
                  . $this->argsAnno
                  . "';" . PHP_EOL;
        }

        $src .= '    ' . $this->methodDef . PHP_EOL;
        $src .= PHP_EOL . '    /** S2BASE_DAO_METHOD **/';

        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile($srcFile);
        $pattern = "/\s\s\s\s\/\*\*\sS2BASE_DAO_METHOD\s\*\*\//";
        if (!preg_match($pattern, $tempContent)) {
            print PHP_EOL;
            print '    could not find entry point. please copy & paste.' . PHP_EOL;
            print '    ------------------------------------------------' . PHP_EOL;
            print $src;
            print PHP_EOL;
            print '    ------------------------------------------------' . PHP_EOL;
            return;
        }

        $tempContent = preg_replace($pattern, $src, $tempContent,1);
        if(!file_put_contents($srcFile,$tempContent,LOCK_EX)){
            throw new Exception("Cannot write to file [ $srcFile ]");
        } else {
            print "[INFO ] modify : $srcFile" . PHP_EOL;
        }
    }

    protected function prepareSqlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . '_'
                 . $this->methodName
                 . self::SQL_FILE_SUFFIX;
        S2Base_CommandUtil::writeFile($srcFile,implode(' ' . PHP_EOL, $this->sqlDef));
    }
}
?>
