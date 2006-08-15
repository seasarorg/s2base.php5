<?php
class PearPkg2SupportTask extends Task {

    private $pkgFile = null;

    public function init(){}

    public function main(){
        $URIs['s2container'] = 'http://s2container.php5.seasar.org/download/S2Container-1.1.1';
        $URIs['s2dao']       = 'http://s2dao.php5.sandbox.seasar.org/files/S2Dao-1.1.0';
        $URIs['s2javelin']   = 'http://';
        $URIs['phing']       = 'pear.phing.info';

        $this->log("pkgFile : {$this->pkgFile}");

        $key = 'role="data"';
        $rep = 'role="php"';
        $contents = file_get_contents($this->pkgFile);
        $contents = preg_replace("/$key/",$rep,$contents);

        $key = "<filelist>";
        $rep = '<filelist>
                    <file role="script" baseinstalldir="/" platform="(*ix|*ux|darwin*|*BSD|SunOS*)" install-as="s2base" name="bin/s2base">
                        <replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
                        <replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
                    </file>
                    <file role="script" baseinstalldir="/" platform="windows" install-as="s2base.bat" name="bin/s2base.bat">
                        <replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
                        <replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
                    </file>';
        $contents = preg_replace("/$key/",$rep,$contents);

        file_put_contents($this->pkgFile,$contents,LOCK_EX);

        $cmd = "pear convert {$this->pkgFile}";
        $this->log($cmd);
        system($cmd);
        $pkg2File = dirname($this->pkgFile) . DIRECTORY_SEPARATOR . 'package2.xml';
        $contents = file_get_contents($pkg2File);

        $uri = $URIs['s2container'];
        $key = "<name>S2Container<\/name>.*?<channel>pear.php.net<\/channel>";
        $rep = "<name>S2Container</name><uri>$uri</uri>";
        $contents = preg_replace("/$key/s",$rep,$contents);

        $uri = $URIs['s2dao'];
        $key = "<name>S2Dao<\/name>.*?<channel>pear.php.net<\/channel>";
        $rep = "<name>S2Dao</name><uri>$uri</uri>";
        $contents = preg_replace("/$key/s",$rep,$contents);

        $uri = $URIs['s2javelin'];
        $key = "<name>S2Javelin<\/name>.*?<channel>pear.php.net<\/channel>";
        $rep = "<name>S2Javelin</name><uri>$uri</uri>";
        $contents = preg_replace("/$key/s",$rep,$contents);

        $channel = $URIs['phing'];
        $key = "<name>Phing<\/name>.*?<channel>pear.php.net<\/channel>";
        $rep = "<name>Phing</name><channel>$channel</channel>";
        $contents = preg_replace("/$key/s",$rep,$contents);

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
