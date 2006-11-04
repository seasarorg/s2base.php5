<?php
class SqliteCommand
    implements S2Base_GenerateCommand {
 
    public function getName(){
        return "sqlite-cli";
    }
 
    public function execute(){
        $pdoCon = S2ContainerFactory::create(PDO_DICON);
        $ds = null;
        try {
            $ds = $pdoCon->getComponent('dataSource')->getConnection();
            $ds->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($ds->getAttribute(PDO::ATTR_DRIVER_NAME) != 'sqlite') {
                throw new Exception("not using sqlite database.");
            }
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            unset($ds);
            return;
        }
 
        print PHP_EOL
            . '[INFO ] DSN : '
            . $pdoCon->getComponentDef('dataSource')->getPropertyDef('dsn')->getValue()
            . PHP_EOL . PHP_EOL;
 
        $this->sqliteCli($ds);
 
        unset($ds);
    }
 
    private function sqliteCli(PDO $ds) {
        $sql = '';
        while(true) {
            print "sqlite> ";
            $val = trim(fgets(STDIN));
            if (trim($sql) == '') {
                if ($this->isExit($val)){
                    break;
                }
 
                if ($this->isDesc($val)) {
                    $val = 'select tbl_name, sql from sqlite_master;';
                }
            }
 
            $sql .= ' ' . $val;
            if (preg_match('/;$/', $sql)) {
                $this->executeSql($ds, $sql);
                $sql = '';
            }
        }
    }
 
    private function isDesc($val) {
        if (strtolower($val) == '.tables' or
            preg_match("/^show\s+tables;*$/",strtolower($val)) or
            $val == '\d') {
            return true;
        }
        return false;
    }
 
    private function isExit($val) {
        if (strtolower($val) == 'exit' or
            $val == '\q' or
            $val == '.q') {
            return true;
        }
        return false;
    }
 
    protected function printResult($rows, $result) {
        print "affected rows : $rows" . PHP_EOL;
        if (count($result) > 0) {
            for ($i=0; $i < count($result); $i++) {
                $row = $result[$i];
                if ($i == 0) {
                    print implode(' | ' ,array_keys($row)) . PHP_EOL;
                }
                print implode(' | ', array_values($row)) . PHP_EOL;
            }
        }
    }
 
    protected function executeSql(PDO $ds, $sql) {
        try {
            $stmt = $ds->query($sql);
            if ($stmt instanceof PDOStatement) {
                $result = $stmt->fetchAll(PDO::FETCH_NAMED);
                $rows   = $stmt->rowCount();
                $this->printResult($rows,$result);
            }
            else {
                throw new Exception("invalid result. [ $sql ] ");
            }
        }
        catch (Exception $e) {
            S2Base_CommandUtil::showException($e);
        }
    }
}
?>
