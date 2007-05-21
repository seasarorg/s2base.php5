<?php
class Pkg2UriBasedSupportTask extends Task {

    private $pkgFile = null;
    private $uri = null;
    private $pkgName = null;

    public function init(){}

    public function main(){
        //$this->log("pkgFile : {$this->pkgFile}");
        //$this->log("pkgName : {$this->pkgName}");
        //$this->log("uri     : {$this->uri}");
        $contents = file_get_contents($this->pkgFile);
        $key = "<name>{$this->pkgName}<\/name>.*?<channel>.+?<\/channel>";
        $rep = "<name>{$this->pkgName}</name><uri>{$this->uri}</uri>";
        //$this->log("key     : $key");
        //$this->log("rep     : $rep");
        $contents = preg_replace("/$key/s",$rep,$contents);
        file_put_contents($this->pkgFile, $contents,LOCK_EX);
    }

    public function setPkgFile($pkgFile){
        $this->pkgFile = $pkgFile;
    }

    public function setUri($uri){
        $this->uri = $uri;
    }

    public function setPkgName($pkgName){
        $this->pkgName = $pkgName;
    }
}
?>
