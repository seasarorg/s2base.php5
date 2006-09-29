<?php
class PearPkg2SupportTask extends Task {

    private $pkgFile = null;

    public function init(){}

    public function main(){
        $URIs['S2Container'] = 'http://s2container.php5.seasar.org/download/S2Container-1.1.1';
        $URIs['S2Dao']       = 'http://s2dao.php5.sandbox.seasar.org/files/S2Dao-1.1.0';
        $URIs['S2Javelin']   = 'http://s2base.php5.sandbox.seasar.org/download/S2Javelin-1.0.0-rc4';
        $URIs['S2Base']      = 'http://s2base.php5.sandbox.seasar.org/download/S2Base-1.0.0-rc4';

        $this->log("pkgFile : {$this->pkgFile}");

        $key = 'role="data"';
        $rep = 'role="php"';
        $contents = file_get_contents($this->pkgFile);
        $contents = preg_replace("/$key/",$rep,$contents);

        $key = '<filelist>';
        $rep = '<filelist>
                    <file role="script" baseinstalldir="/" platform="windows" install-as="s2base.bat" name="bin/s2base.bat">
                        <replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
                        <replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
                    </file>
                    <file role="script" baseinstalldir="/" platform="unix" install-as="s2base" name="bin/s2base">
                        <replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
                        <replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
                    </file>';
        $contents = preg_replace("/$key/s",$rep,$contents);

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

        $key = "<name>phing<\/name>.*?<channel>pear.php.net<\/channel>";
        $rep = "<name>phing</name><channel>pear.phing.info</channel>";
        $contents = preg_replace("/$key/s",$rep,$contents);

        $key = "<pearinstaller>.*?<min>.+?<\/min>";
        $rep = "<pearinstaller><min>1.4.11</min>";
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
