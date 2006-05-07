<?php
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
?>
