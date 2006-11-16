<?php
class Pkg2PearVersionSupportTask extends Task {

    private $pkgFile = null;
    private $pearVersion = null;

    public function init(){}

    public function main(){
        //$this->log("pkgFile : {$this->pkgFile}");
        $contents = file_get_contents($this->pkgFile);
        $key = "<pearinstaller>.*?<min>.+?<\/min>";
        $rep = "<pearinstaller><min>{$this->pearVersion}</min>";
        $contents = preg_replace("/$key/s",$rep,$contents);
        file_put_contents($this->pkgFile, $contents,LOCK_EX);
    }

    public function setPkgFile($pkgFile){
        $this->pkgFile = $pkgFile;
    }

    public function setPearVersion($pearVersion){
        $this->pearVersion = $pearVersion;
    }

}
?>
