<?php
class Pkg2LicenseSupportTask extends Task {

    private $pkgFile = null;

    public function init(){}

    public function main(){
        //$this->log("pkgFile : {$this->pkgFile}");
        $contents = file_get_contents($this->pkgFile);
        $key = "http:\/\/www\.example\.com";
        $rep = "http://www.apache.org/licenses/LICENSE-2.0";
        $contents = preg_replace("/$key/s",$rep,$contents);
        file_put_contents($this->pkgFile, $contents,LOCK_EX);
    }

    public function setPkgFile($pkgFile){
        $this->pkgFile = $pkgFile;
    }

}
?>
