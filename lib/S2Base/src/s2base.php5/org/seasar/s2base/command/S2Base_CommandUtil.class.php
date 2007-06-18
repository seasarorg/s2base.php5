<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// |                                                                      |
// |     http://www.apache.org/licenses/LICENSE-2.0                       |
// |                                                                      |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
// | either express or implied. See the License for the specific language |
// | governing permissions and limitations under the License.             |
// +----------------------------------------------------------------------+
// | Authors: klove                                                       |
// +----------------------------------------------------------------------+
//
// $Id$
/**
 * 各コマンドで使用する共通メソッドをまとめたUtilityクラスです。
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.command
 * @author     klove
 */
class S2Base_CommandUtil {

    /**
     * ファイルを読み込みます。
     * 
     * @param string $filePath 読み込むファイルのパス
     * @return string ファイル内容
     * @throws Exception 読み込みに失敗した場合にスローされます。
     */
    public static function readFile($filePath){
        if(!is_readable($filePath)){
            throw new Exception("Cannot read file [ $filePath ]");
        }
        return file_get_contents($filePath);
    }

    /**
     * app/modulesのモジュールを選択します。
     * 
     * @return string module名
     */
    public static function getModuleName(){
        $modules = self::getAllModules();
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"Module list");
    }

    /**
     * app/modulesのすべてのモジュールを取得します。
     * 
     * @return array app/modulesのmodule名の配列
     */
    public static function getAllModules(){
        $modulesDir = S2BASE_PHP5_APP_DIR . "modules";
        $entries = scandir($modulesDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $modulesDir ]");
        }

        $modules = array();
        foreach($entries as $entry) {
            $path = S2BASE_PHP5_MODULES_DIR . $entry;
            if(!preg_match("/^\./",$entry) and is_dir($path)){
                array_push($modules,$entry);
            }
        }
        return $modules;
    }

    /**
     * 共通の文字列検証を行います。
     * 
     * @param string $target 検証を行う文字列
     * @param string $exceptionMsg 検証に失敗した場合のメッセージ
     * @throws Exception 検証に失敗した場合にスローされます。
     */
    public static function validate($target, $exceptionMsg) {
        if(!preg_match("/^\w+$/",$target)){
           throw new Exception($exceptionMsg);
        }   
    }

    /**
     * list選択でexitが選択されたかどうかを確認します。
     * 
     * @param string $label list選択の結果文字列
     * @return boolean
     */
    public static function isListExitLabel($label) {
        return $label == S2Base_StdinManager::EXIT_LABEL;
    }

    /**
     * pdo.diconを用いてS2DaoSkeletonDbmsを生成します。
     * 
     * @return S2DaoSkeletonDbms
     */
    public static function getS2DaoSkeletonDbms() {
        $container = S2ContainerFactory::create(PDO_DICON);
        $cd = $container->getComponentDef('dataSource');
        $dsn = $cd->getPropertyDef('dsn')->getValue();
        $user = '';
        $pass = '';
        if ($cd->hasPropertyDef('user')) {
            $user = $cd->getPropertyDef('user')->getValue();
        }
        if ($cd->hasPropertyDef('password')) {
            $pass = $cd->getPropertyDef('password')->getValue();
        }
        return new S2DaoSkeletonDbms($dsn, $user, $pass);
    }

    /**
     * ファイルに書き出します。
     * 
     * @param string $srcFile 書き込みを行うファイルへのパス
     * @param string $tempContent 書き込み内容
     * @throws Exception 書き込みに失敗した場合にスローされます。
     */
    public static function writeFile($srcFile,$tempContent) {
        try{
            self::writeFileInternal($srcFile,$tempContent);
            print "[INFO ] create : $srcFile" . PHP_EOL;
        }catch(Exception $e){
            if ($e instanceof S2Base_FileExistsException){
                print "[INFO ] exists : $srcFile" . PHP_EOL;
            } else {
                throw $e;
            }
        }
    }

    /**
     * ファイルに書き出します。
     * 
     * @param string $filePath 書き込みを行うファイルへのパス
     * @param string $contents 書き込み内容
     * @throws S2Base_FileExistsException ファイルが既に存在していた場合にスローされます。
     * @throws Exception 書き込みに失敗した場合にスローされます。
     */
    public static function writeFileInternal($filePath, $contents) {
        if (file_exists($filePath)) {
            throw new S2Base_FileExistsException("Already exists. [ $filePath ]");
        }

        if(!file_put_contents($filePath,$contents,LOCK_EX)){
            throw new Exception("Cannot write to file [ $filePath ]");
        }
    }

    /**
     * ディレクトリを作成します。
     * 
     * @param string $dirPath 作成するディレクトリパス
     * @throws Exception ディレクトリ作成に失敗した場合にスローされます。
     */
    public static function createDirectory($dirPath){
        try{
            self::createDirectoryInternal($dirPath);
            print "[INFO ] create : $dirPath" . PHP_EOL;
        }catch(Exception $e){
            if ($e instanceof S2Base_FileExistsException){
                print "[INFO ] exists : $dirPath" . PHP_EOL;
            } else {
                throw $e;
            }
        }
    }

    /**
     * ディレクトリを作成します。
     * 
     * @param string $directoryPath 作成するディレクトリパス
     * @throws S2Base_FileExistsException ディレクトリが既に存在していた場合にスローされます。
     * @throws Exception ディレクトリ作成に失敗した場合にスローされます。
     */
    public static function createDirectoryInternal($directoryPath){
        if(!file_exists($directoryPath)){
            if(!mkdir($directoryPath)){
               throw new Exception("Cannot make dir [ $directoryPath ]");
            }
            return true;
        }else{
            throw new S2Base_FileExistsException("Already exists. [ $directoryPath ]");
        }
    }

    /**
     * 例外メッセージを出力します。
     * 
     * @param Exception $e 例外
     */
    public static function showException(Exception $e){
        print PHP_EOL . '!!! Exception' . PHP_EOL;
        print "!!! {$e->getMessage()}" . PHP_EOL . PHP_EOL;
    }
}
?>
