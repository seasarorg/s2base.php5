<?php
class PearPkg2SupportTask extends Task {

    private $pkgFile = null;

    public function init(){}

    public function main(){
        $URIs['S2Javelin']   = 'http://labs.s2php5.jp/s2base/peaer/S2Javelin-1.0.0';

        $this->log("pkgFile : {$this->pkgFile}");

        $key = 'role="data"';
        $rep = 'role="php"';
        $contents = file_get_contents($this->pkgFile);
        $contents = preg_replace("/$key/",$rep,$contents);

        file_put_contents($this->pkgFile,$contents,LOCK_EX);

        $cmd = "pear convert {$this->pkgFile}";
        $this->log($cmd);
        system($cmd);
        $pkg2File = dirname($this->pkgFile) . DIRECTORY_SEPARATOR . 'package2.xml';
        $contents = file_get_contents($pkg2File);

        foreach ($URIs as $name => $uri) {
            $key = "<name>$name<\/name>.*?<channel>pear.php.net<\/channel>";
            $rep = "<name>$name</name><uri>$uri</uri>";
            $contents = preg_replace("/$key/s",$rep,$contents);
        }

        $key = "http:\/\/www\.example\.com";
        $rep = "http://www.apache.org/licenses/LICENSE-2.0";
        $contents = preg_replace("/$key/s",$rep,$contents);

        file_put_contents($pkg2File,$contents,LOCK_EX);
    }

    public function setPkgFile($pkgFile){
        $this->pkgFile = $pkgFile;
    }
}
?>
