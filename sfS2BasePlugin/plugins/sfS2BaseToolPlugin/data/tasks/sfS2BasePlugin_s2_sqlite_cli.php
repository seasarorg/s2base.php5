<?php
pake_desc('SQLite CLI with S2Base');
pake_task('s2_sqlite_cli', 'project_exists');
pake_alias('s2sqlite', 's2_sqlite_cli');

function run_s2_sqlite_cli($task, $args) {
    $pluginName = basename(realpath(dirname(__FILE__) . '/../..'));
    $env = isset($args[0]) ? $args[0] : 'prod';
    $pdoDicon = sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'pdo_' . $env . '.dicon';

    pake_echo_comment('');
    pake_echo_comment('sfS2BasePlugin s2_sqlite_cli task');
    pake_echo_comment('');
    pake_echo_comment("SF_ENVIRONMENT : $env");
    pake_echo_comment("Pdo Dicon      : $pdoDicon");
    $pdo = sfS2BasePlugin_util_getPdoInstance($pdoDicon);
    pake_echo_comment('');

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) != 'sqlite') {
        throw new Exception("not using sqlite database.");
    }

    $engine = new S2Base_SqliteCliEngine();
    $engine->run($pdo);
}

class S2Base_SqliteCliEngine {

    private $uiCharcode = null;
    private $dbCharcode = null;

    public function run(PDO $ds) {
        $sql = '';
        while(true) {
            if (trim($sql) === '') {
                print 'sqlite=# ';
            } else {
                print 'sqlite-# ';
            }
            $val = trim(fgets(STDIN));
            if (trim($sql) === '') {
                $cmdVal = trim($val);
                if ($this->isExit($cmdVal)){
                    break;
                } else if ($this->isDesc($cmdVal)) {
                    $val = 'select * from sqlite_master;';
                } else if ($this->isTables($cmdVal)) {
                    $val = "select tbl_name from sqlite_master where type = 'table';";
                } else if ($this->isSetCharcode($cmdVal)) {
                    $this->setCharcode($cmdVal);
                    continue;
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

    private function isSetCharcode($val) {
        if (preg_match('/^set_charcode_(ui|db)\s*=/', strtolower($val))) {
            return true;
        }
        return false;
    }

    private function setCharcode($val) {
        $matches = array();
        if (preg_match('/^set_charcode_(ui|db)\s*=\s*(.+)$/', strtolower($val), $matches)) {
            if ($matches[1] === 'ui') {
                $this->uiCharcode = $matches[2];
                print "[info ] ui charcode set [{$this->uiCharcode}]." . PHP_EOL . PHP_EOL;
            } else {
                $this->dbCharcode = $matches[2];
                print "[info ] db charcode set [{$this->dbCharcode}]." . PHP_EOL . PHP_EOL;
            }
        }
    }

    private function isCharcodeConvertEnable() {
        return $this->uiCharcode !== null and $this->dbCharcode !== null;
    }

    private function convertCharcode(&$data) {
        for($i=0; $i<count($data); $i++) {
            $data[$i] = mb_convert_encoding($data[$i], $this->uiCharcode, $this->dbCharcode);
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
            pake_echo_action('sql error', $e->getMessage());
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
                    $headerData = array_keys($row);
                    if ($this->isCharcodeConvertEnable()) {
                        $this->convertCharcode($headerData);
                    }
                    vprintf($headerFromat, $headerData);
                    print $justLine;
                }
                $data = array_values($row);
                if ($this->isCharcodeConvertEnable()) {
                    $this->convertCharcode($data);
                }
                vprintf($dataFromat, $data);
            }
            print $justLine;
            print PHP_EOL;
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
