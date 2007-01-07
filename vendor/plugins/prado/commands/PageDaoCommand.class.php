<?php
class PageDaoCommand extends AbstractPageLogicDaoCommand {
    public function getName(){
        return "page dao";
    }

    protected function isUseCommonsDao() {
        return DaoCommand::isCommonsDaoAvailable();
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function isUseDao() {
        return true;
    }

    protected function finalConfirm(){
        print  PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  page name             : {$this->pageName}" . PHP_EOL;
        print "  page class name       : {$this->pageClassName}" . PHP_EOL;
        print "  page dicon file name  : {$this->pageClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  page template file    : {$this->pageName}" . S2BASE_PHP5_PRADO_PAGE_SUFFIX . PHP_EOL;
        if ($this->useDao) {
            print "  dao interface name      : {$this->daoInterfaceName}" . PHP_EOL;
            print "  dao test class name     : {$this->daoInterfaceName}Test" . PHP_EOL;
            print "  entity class name       : {$this->entityClassName}" . PHP_EOL;
            if (!$this->useCommonsDao) {
                if (!$this->useDB) {
                    print "  entity class extends    : {$this->extendsEntityClassName}" . PHP_EOL;
                }
                print "  table name              : {$this->tableName}" . PHP_EOL;
                $cols = implode(', ',$this->cols);
                print "  columns                 : $cols" . PHP_EOL;
            }
        }
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->preparePageFile();
        $this->prepareTemplateFile();
        $this->preparePageDiconFile();
        $this->prepareDaoTestFile();
        $this->prepareDaoDiconFile();
        if (!$this->useCommonsDao) {
            $this->prepareDaoFile();
            $this->prepareEntityFile();
        }
    }

    protected function preparePageFile(){
        $srcFile = DOCUMENT_ROOT_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_PRADO_PAGES_DIR
                 . $this->pageClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-dao/page.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@DAO_INTERFACE@@/",
                          "/@@DAO_PROPERTY@@/");
        $replacements = array($this->pageClassName,
                              $this->daoInterfaceName,
                              $daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function preparePageDiconFile(){
        $srcFile = DOCUMENT_ROOT_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_PRADO_DICON_DIR
                 . $this->pageClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-dao/page_dicon.php');
        $patterns = array("/@@COMPONENT_NAME@@/",
                          "/@@CLASS_NAME@@/",
                          "/@@DAO_CLASS@@/");
        $replacements = array($this->pageName,
                              $this->pageClassName,
                              $this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName 
                 . S2BASE_PHP5_DAO_DIR
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/test.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@DAO_CLASS@@/");
        $replacements = array($testName,$this->moduleName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/dicon.php');
        $tempContent = preg_replace("/@@DAO_CLASS@@/",
                                    $this->daoInterfaceName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
