<?php
interface S2DaoSkelConst {
    const REP_AUTHOR = '@@AUTHOR@@';
    const REP_DATE = '@@DATE@@';
    const REP_DAO = '@@DAO@@';
    const REP_BEAN = '@@BEAN@@';
    const REP_DAOIMPL = '@@DAOIMPL@@';
    const REP_TABLE = '@@TABLE@@';
    const REP_COLUMN = '@@COLUMN@@';
    const REP_CONSTANTS = '@@CONSTANTS@@';
    const REP_PROP = '@@PROP@@';
    const REP_PROPS = '@@PROPS@@';
    const REP_METHODS = '@@METHODS@@';
    const REP_QUERY = '@@QUERY@@';
    const REP_PROPERTY = 'private $@@PROP@@;';
    const REP_ANNO_COLUMN = 'const @@PROP@@_COLUMN = "@@COLUMN@@";';
    const ext = ".class.php";
    const DaoName = "Dao";
    const BeanName = "Entity";
    const DaoImplName = "DaoImpl";
    const SEP_CHAR = '_';
    const SkelDir = "/skel";
    const DaoFile = "/Dao.php.skel";
    const EntityFile = "/Bean.php.skel";
    const DaoImplFile = "/DaoImpl.php.skel";
    const DateFormat = 'Y/m/d';
}
class S2DaoSkeletonTask extends Task {
    protected $toDir = "";
    protected $skeldir = "";
    protected $dsn = "";
    protected $user = "";
    protected $pass = "";
    public function init(){
    }
    public function main(){
        $this->setupTask();
        $dbms = new S2DaoSkeletonDbms($this->dsn, $this->user, $this->pass);
        $skel = new S2DaoSkeletonGen($this->skeldir);
        foreach($dbms->getAllColumns() as $table => $columns){
            $skel->setTableName($table);
            $skel->setColumns($columns);
            $this->generateDao($skel);
            $this->generateBean($skel);
            $this->generateDaoImpl($skel);
        }
        $this->log("[info] see the files");
        $files = glob($this->toDir . DIRECTORY_SEPARATOR . '*' . S2DaoSkelConst::ext);
        foreach($files as $file){
            $this->log("[file]: " . $file);
        }
    }
    public function setToDir($toDir){
        $this->toDir = $toDir;
    }
    protected function setupTask(){
        include_once "S2Container/S2Container.php";
        include_once "S2Dao/S2Dao.php";
        S2ContainerClassLoader::import(S2CONTAINER_PHP5);
        S2ContainerClassLoader::import(S2DAO_PHP5);
        function __autoload($class = null){
            S2ContainerClassLoader::load($class);
        }
        $srcdir = $this->getProject()->getProperty("project.src.dir");
        $pjname = $this->getProject()->getProperty("project.name");
        $this->dsn = $this->getProject()->getProperty("dsn");
        $this->user = $this->getProject()->getProperty("user");
        $this->pass = $this->getProject()->getProperty("password");
        $this->skeldir = dirname(__FILE__) . S2DaoSkelConst::SkelDir;
    }
    protected function generateDao(S2DaoSkeletonGen $skel){
        $this->log("[create] [Dao]: " . $skel->getDaoName());
        $path = $this->toDir . DIRECTORY_SEPARATOR . $skel->getDaoFileName();
        $this->write($path, $skel->createDaoContent());
    }
    protected function generateBean(S2DaoSkeletonGen $skel){
        $this->log("[create] [Bean]: " . $skel->getBeanName());
        $path = $this->toDir . DIRECTORY_SEPARATOR . $skel->getEntityFileName();
        $this->write($path, $skel->createEntityContent());
    }
    protected function generateDaoImpl(S2DaoSkeletonGen $skel){
        $this->log("[create] [DaoImpl]: " . $skel->getDaoImplName());
        $path = $this->toDir . DIRECTORY_SEPARATOR . $skel->getDaoImplFileName();
        $this->write($path, $skel->createDaoImplContent());
    }
    protected function write($path, $content){
        @file_put_contents($path, $content);
    }
}
class S2DaoSkeletonDbms {
    protected $pdo = null;
    protected $tables = array();
    protected $columns = array();
    public function __construct($dsn, $user, $pass){
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->setupTables();
        $this->setupColumns();
    }
    public function __destruct(){
        unset($this->pdo);
    }
    protected function setupTables(){
        $dbms = S2Dao_DbmsManager::getDbms($this->pdo);
        $stmt = $this->pdo->query($dbms->getTableSql());
        $this->tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    protected function setupColumns(){
        foreach($this->tables as $table){
            $cols = S2Dao_DatabaseMetaDataUtil::getColumns($this->pdo, $table);
            $this->columns[$table] = $cols;
        }
    }
    public function getTables(){
        return $this->tables;
    }
    public function getColumns($table){
        return $this->columns[$table];
    }
    public function getAllColumns(){
        return $this->columns;
    }
}
class S2DaoSkeletonGen {
    protected $skelDir = "";
    protected $table = "";
    protected $columns = array();
    protected $className = "";
    protected $dao = "";
    protected $entity = "";
    protected $daoImpl = "";
    protected $author = "";
    protected $date = 0;
    public function __construct($skelDir){
        $this->skelDir = $skelDir;
        if(is_readable($skelDir . S2DaoSkelConst::DaoFile)){
            $this->dao = file_get_contents($skelDir . S2DaoSkelConst::DaoFile);
        }
        if(is_readable($skelDir . S2DaoSkelConst::EntityFile)){
            $this->entity = file_get_contents($skelDir . S2DaoSkelConst::EntityFile);
        }
        if(is_readable($skelDir . S2DaoSkelConst::DaoImplFile)){
            $this->daoImpl= file_get_contents($skelDir . S2DaoSkelConst::DaoImplFile);
        }
        $this->author = getenv('USER');
        $this->date = date(S2DaoSkelConst::DateFormat, time());
    }
    public function setTableName($table){
        $this->table = $table;
    }
    public function setColumns(array $columns){
        $this->columns = $columns;
    }
    public function getDaoFileName(){
        return $this->getDaoName() . S2DaoSkelConst::ext;
    }
    public function getEntityFileName(){
        return  $this->getBeanName() . S2DaoSkelConst::ext;
    }
    public function getDaoImplFileName(){
        return $this->getDaoImplName() . S2DaoSkelConst::ext;
    }
    public function getDaoName(){
        return ucfirst(strtolower($this->table)) . S2DaoSkelConst::DaoName;
    }
    public function getBeanName(){
        return ucfirst(strtolower($this->table)) . S2DaoSkelConst::BeanName;
    }
    public function getDaoImplName(){
        return ucfirst(strtolower($this->table)) . S2DaoSkelConst::DaoImplName;
    }
    public function replaceCommon($copy){
        $copy = str_replace(S2DaoSkelConst::REP_DATE, $this->date, $copy);
        $copy = str_replace(S2DaoSkelConst::REP_AUTHOR, $this->author, $copy);
        return $copy;
    }
    public function createDaoContent(){
        $copy = $this->dao;
        $copy = str_replace(S2DaoSkelConst::REP_DAO, $this->getDaoName(), $copy);
        $copy = str_replace(S2DaoSkelConst::REP_BEAN, $this->getBeanName(), $copy);
        $copy = $this->replaceCommon($copy);
        return $copy;
    }
    public function createEntityContent(){
        $copy = $this->entity;
        $properties = array();
        $annotations = array();
        $methods = array();
        foreach($this->columns as $column){
            $prop = $this->getProperty($column);
            $anno = str_replace(S2DaoSkelConst::REP_PROP, $prop, S2DaoSkelConst::REP_ANNO_COLUMN);
            $annotations[] = str_replace(S2DaoSkelConst::REP_COLUMN, $column, $anno);
            $properties[] = str_replace(S2DaoSkelConst::REP_PROP, $prop, S2DaoSkelConst::REP_PROPERTY);
            $methods[] = implode($this->createGetter($prop));
            $methods[] = implode($this->createSetter($prop));
        }
        $copy = str_replace(S2DaoSkelConst::REP_TABLE, $this->table, $copy);
        $copy = str_replace(S2DaoSkelConst::REP_BEAN, $this->getBeanName(), $copy);
        $copy = str_replace(S2DaoSkelConst::REP_CONSTANTS, implode(PHP_EOL, $annotations), $copy);
        $copy = str_replace(S2DaoSkelConst::REP_PROPS, implode(PHP_EOL, $properties), $copy);
        $copy = str_replace(S2DaoSkelConst::REP_METHODS, implode(PHP_EOL, $methods), $copy);
        $copy = $this->replaceCommon($copy);
        return $copy;
    }
    public function createDaoImplContent(){
        $copy = $this->daoImpl;
        $orderby = 'ORDER BY ' . $this->columns[0] . ' ASC';
        $copy = str_replace(S2DaoSkelConst::REP_DAOIMPL, $this->getDaoImplName(), $copy);
        $copy = str_replace(S2DaoSkelConst::REP_DAO, $this->getDaoName(), $copy);
        $copy = str_replace(S2DaoSkelConst::REP_BEAN, $this->getBeanName(), $copy);
        $copy = str_replace(S2DaoSkelConst::REP_TABLE, $this->table, $copy);
        $copy = str_replace(S2DaoSkelConst::REP_QUERY, $orderby, $copy);
        $copy = $this->replaceCommon($copy);
        return $copy;
    }
    protected function getProperty($column){
        $nameArr = $this->getMethodName($column);
        $nameArr[0] = strtolower($nameArr[0]);
        return $nameArr;
    }
    protected function getMethodName($propName){
        $name = '';
        $token = strtok($propName, S2DaoSkelConst::SEP_CHAR);
        while ($token){
            $name .= ucfirst(strtolower($token));
            $token = strtok(S2DaoSkelConst::SEP_CHAR);
        }
        return $name;
    }
    protected function createGetter($propName){
        $methodName = $this->getMethodName($propName);
        $getter = array();
        $getter[] = 'public function ';
        $getter[] = 'get' . $methodName . '(){';
        $getter[] = 'return $this->' . $propName . ';';
        $getter[] = '}';
        return $getter;
    }
    protected function createSetter($propName){
        $methodName = $this->getMethodName($propName);
        $setter = array();
        $setter[] = 'public function ';
        $setter[] = 'set' . $methodName . '($' . $propName . '){';
        $setter[] = '$this->' . $propName . ' = $' . $propName . ';';
        $setter[] = '}';
        return $setter;
    }
}

class S2Base_CommandTask extends Task {
    private $incFileSets = array();
    private $incDirSets = array();
    public function init(){}
    public function main(){
        $includefiles = array();
        foreach($this->incFileSets as $fileset){
            $includefiles = array_merge($includefiles,$this->getFileList($fileset));
        }
        $launcher = S2Base_CommandLauncherFactory::create($includefiles);
        try{
            $launcher->main();
        } catch(Exception $e){
            $this->log($e->getMessage());
        }
    }
    private function getFileList(FileSet $fileset){
        $ds = $fileset->getDirectoryScanner($this->project);
        $files = $ds->getIncludedFiles();
        foreach($files as &$file){
            $relativePath = $ds->getBaseDir() . DIRECTORY_SEPARATOR . $file;
            $file = realpath($relativePath);
        }
        return $files;
    }
    public function createFileSet(){
        $fs = new FileSet();
        $this->incFileSets[] = $fs;
        return $fs;
    }
    public function createDirSet(){
        $ds = new DirSet();
        $this->incDirSets[] = $ds;
        return $ds;
    }
}

class S2Base_S2DaoSkeletonTask extends S2DaoSkeletonTask {
    protected function setupTask(){
        $container = S2ContainerFactory::create(PDO_DICON);
        $cd = $container->getComponentDef('dataSource');
        $this->dsn = $cd->getPropertyDef('dsn')->getValue();
        if ($cd->hasPropertyDef('user')) {
            $this->user = $cd->getPropertyDef('user')->getValue();       	
        }
        if ($cd->hasPropertyDef('password')) {
            $this->pass = $cd->getPropertyDef('password')->getValue();
        }
        $this->skeldir = S2BASE_PHP5_ROOT . '/app/skeleton/gendao';
    }
}

?>
