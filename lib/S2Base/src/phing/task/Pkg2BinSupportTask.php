<?php
class Pkg2BinSupportTask extends Task {

    private $pkgFile = null;

    public function init(){}

    public function main(){
        //$this->log("pkgFile : {$this->pkgFile}");
        $contents = file_get_contents($this->pkgFile);

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
        file_put_contents($this->pkgFile, $contents,LOCK_EX);
    }

    public function setPkgFile($pkgFile){
        $this->pkgFile = $pkgFile;
    }
}
?>
