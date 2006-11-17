<?php
class Pkg2ChannelBasedSupportTask extends Task {

    private $pkgFile = null;
    private $channel = null;
    private $pkgName = null;

    public function init(){}

    public function main(){
        //$this->log("pkgFile : {$this->pkgFile}");
        $contents = file_get_contents($this->pkgFile);
        $key = "<name>{$this->pkgName}<\/name>.*?<channel>.+?<\/channel>";
        $rep = "<name>{$this->pkgName}</name><channel>{$this->channel}</channel>";
        $contents = preg_replace("/$key/s",$rep,$contents);
        file_put_contents($this->pkgFile, $contents,LOCK_EX);
    }

    public function setPkgFile($pkgFile){
        $this->pkgFile = $pkgFile;
    }

    public function setChannel($channel){
        $this->channel = $channel;
    }

    public function setPkgName($pkgName){
        $this->pkgName = $pkgName;
    }
}
?>
