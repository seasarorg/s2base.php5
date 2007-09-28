<?php
class SqliteCommand
    implements S2Base_GenerateCommand {

    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return "sqlite-cli";
    }

    /**
     * @see S2Base_GenerateCommand::isAvailable()
     */
    public function isAvailable(){
        return true;
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
            print 'sqlite> ';
            $val = trim(fgets(STDIN));
            if (trim($sql) === '') {
                if ($this->isExit($val)){
                    break;
                } else if ($this->isDesc($val)) {
                    $val = 'select * from sqlite_master;';
                } else if ($this->isTables($val)) {
                    $val = 'select tbl_name from sqlite_master;';
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
        if ($val === '\d') {
            return true;
        }
        return false;
    }

    private function isTables($val) {
        if (strtolower($val) === '.tables' or
            preg_match("/^show\s+tables;*$/",strtolower($val)) ) {
            return true;
        }
        return false;
    }

    private function isExit($val) {
        if (strtolower($val) === 'exit' or
            strtolower($val) === 'quit' or
            $val === '\q' or
            $val === '.q') {
            return true;
        }
        return false;
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

    protected function printResult($rows, $result) {
        print "affected rows : $rows" . PHP_EOL;
        if (count($result) > 0) {
            $colSize = $this->getColSize($result);
            $headerFromat = $this->getHeaderFormatLine($result, $colSize);
            $dataFromat = $this->getDataFormatLine($result, $colSize);
            $justLine = $this->getSepLine($colSize);
            print $justLine;
            for ($i=0; $i < count($result); $i++) {
                $row = $result[$i];
                if ($i == 0) {
                    vprintf($headerFromat, array_keys($row));
                    print $justLine;
                }
                vprintf($dataFromat, array_values($row));
            }
            print $justLine;
        }
    }

    protected function getColSize($result) {
        $colSize = array();
        $values = array_keys($result[0]);
        for($i=0; $i<count($values); $i++) {
            $colSize[$i] = strlen((string)$values[$i]);
        }
        for($i=0; $i<count($result); $i++) {
            $values = array_values($result[$i]);
            for($j=0; $j<count($values); $j++) {
                $size = strlen((string)$values[$j]);
                if ($colSize[$j] < $size) {
                    $colSize[$j] = $size;
                }
            }
        }
        return $colSize;
    }

    protected function getSepLine($colSize) {
        $justLine = array();
        foreach($colSize as $size){
            $justLine[] = str_repeat('-', $size);
        }
        return '+-' . implode('-+-', $justLine) . '-+' . PHP_EOL;
    }

    protected function getHeaderFormatLine($result, $colSize) {
        $format = array();
        foreach ($colSize as $size) {
            $format[] = '%-' . $size . 's';
        }
        return '| ' . implode(' | ', $format) . ' |' . PHP_EOL;
    }

    protected function getDataFormatLine($result, $colSize) {
        $isNumeric = array_fill(0, count($colSize), 1);
        for($i=0; $i<count($result); $i++) {
            $values = array_values($result[$i]);
            for($j=0; $j<count($values); $j++) {
                if (trim((string)$values[$j]) !== '' and 
                    !is_numeric((string)$values[$j])) {
                    $isNumeric[$j] *= 0;
                }
            }
        }

        $format = array();
        for($i=0; $i<count($colSize); $i++) {
            if ($isNumeric[$i] === 1) {
                $format[] = '%' . $colSize[$i] . 's';
            } else {
                $format[] = '%-' . $colSize[$i] . 's';
            }
        }
        return '| ' . implode(' | ', $format) . ' |' . PHP_EOL;
    }
}
