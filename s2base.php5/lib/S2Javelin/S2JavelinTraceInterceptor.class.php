<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2006 the Seasar Foundation and the Others.            |
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
// | Authors: klove, KIYO-SHIT                                            |
// +----------------------------------------------------------------------+
//
// $Id:$
/**
 * @author klove, KIYO-SHIT
 */
class S2JavelinTraceInterceptor extends S2Container_AbstractInterceptor
{
    /**
     * メソッド呼び出し時にJavlinログの先頭に出力する識別子。
     */
    const KEY_CALL = "Call  ";

    /**
     * メソッド戻り時にJavlinログの先頭に出力する識別子。
     */
    const KEY_RETURN = "Return";

    /**
     * 例外throw時にJavlinログの先頭に出力する識別子。
     */
    const KEY_THROW = "Throw ";

    /**
     * 例外catch時にJavlinログの先頭に出力する識別子。
     */
    const KEY_CATCH = "Catch ";

    
    public static $CALLER_STACKTRACE_INDEX = 1;

    /**
     * ログの区切り文字。
     */
    const DELIM = ",";

    /**
     * スレッド情報の区切り文字
     */
    const THREAD_DELIM = "@";

    /**
     * ログ出力する時刻のフォーマット。
     * const TIME_FORMAT_STR = "yyyy/MM/dd HH:mm:ss.SSS";
     */

    /**
     * メソッドの呼び出し元オブジェクトを表す文字列。
     */
    private static $callerLog_ = "<unknown>,<unknown>,0";

    /**
     * 引数を出力するかどうか。
     * argsプロパティの値がセットされる。
     */
    private $isLogArgs_ = true;

    /**
     * 戻り値を出力するかどうか。
     * returnプロパティの値がセットされる。
     */
    private $isLogReturn_ = true;

    /**
     * スタックトレースを出力するかどうか。
     * stackTraceプロパティの値がセットされる。
     */
    private $isLogStackTrace_ = false;

    /**
     * スタックトレースを出力するかどうか。
     * stackTraceプロパティの値がセットされる。
     */
    private $logFile_ = "";

    /**
     * 例外発生を記録するテーブル。
     * キーにスレッドの識別子、値に例外オブジェクトを入れる。
     * 例外が発生したらこのマップにその例外を登録し、
     * キャッチされた時点で削除する。
     */
    static private $exceptionMap_ = array();
    
    /**
     * Javelinログ出力用のinvokeメソッド。
     * 
     * 実際のメソッド呼び出しを実行する前後で、
     * 呼び出しと返却の詳細ログを、Javelin形式で出力する。
     * 
     * 実行時に例外が発生した場合は、その詳細もログ出力する。
     * 
     * @param invocation
     *            インターセプタによって取得された、呼び出すメソッドの情報
     * @return invocationを実行したときの戻り値
     * @throws Throwable invocationを実行したときに発生した例外
     */
    public function invoke(S2Container_MethodInvocation $invocation)
    {
        $methodCallBuff = "";

        // 呼び出し先情報取得。
        $calleeMethodName = $invocation->getMethod()->getName();
        $calleeClassName = $this->getTargetClass($invocation)->getName();
        $objectID = $invocation->getThis();
        $modifiers = implode(' ', Reflection::getModifierNames(
                                  $invocation->getMethod()->getModifiers()));
        // 呼び出し元情報取得。
        $currentCallerLog = self::$callerLog_;

        $threadName = "php";
        $threadClassName = "proccess";
        $threadID = $this->getId();
        $threadInfo = $threadName      . self::THREAD_DELIM .
                      $threadClassName . self::THREAD_DELIM .
                      $threadID;

        // メソッド呼び出し共通部分生成。
        $methodCallBuff =  $calleeMethodName . self::DELIM .
                           $calleeClassName  . self::DELIM .
                           $objectID;

        // 呼び出し先のログを、次回ログ出力時の呼び出し元として使用するために保存する。
        self::$callerLog_ = $methodCallBuff;

        $methodCallBuff .= self::DELIM .
                           $currentCallerLog . self::DELIM .
                           $modifiers        . self::DELIM .
                           $threadInfo       . PHP_EOL;

        // Call 詳細ログ生成。
        $callDetailBuff = $this->createCallDetail($methodCallBuff,$invocation);

        // Call ログ出力。
        $this->logOut($callDetailBuff);

        //実際のメソッド呼び出しを実行する。
        //実行中に例外が発生したら、その詳細ログを出力する。
        $ret = null;
        try
        {
            // メソッド呼び出し。
            $ret = $invocation->proceed();
        }
        catch (Exception $cause)
        {
            // Throw詳細ログ生成。
            $throwBuff = $this->createThrowCatchDetail(
                                self::KEY_THROW, 
                                $calleeMethodName,
                                $calleeClassName,
                                $objectID,
                                $threadInfo, 
                                $cause);

            // Throw ログ出力。
            $this->logOut($throwBuff);

            //例外発生を記録する。
            self::$exceptionMap_[$threadInfo] = $cause;
            
            //例外をスローし、終了する。
            throw $cause;
        }
        
        //このスレッドで、直前に例外が発生していたかを確認する。
        //発生していてここにたどり着いたのであれば、この時点で例外が
        //catchされたということになるので、Catchログを出力する。
        $isExceptionThrowd = array_key_exists($threadInfo,self::$exceptionMap_);
        if($isExceptionThrowd == true) 
        {
            //発生していた例外オブジェクトをマップから取り出す。（かつ削除する）
            $exp = self::$exceptionMap_[$threadInfo];
            unset(self::$exceptionMap_[$threadInfo]);
            
            // Catch詳細ログ生成。
            $throwBuff = $this->createThrowCatchDetail(
                                self::KEY_CATCH,
                                $calleeMethodName,
                                $calleeClassName,
                                $objectID,
                                $threadInfo,
                                $exp);

            // Catch ログ出力。
            $this->logOut($throwBuff);
        }

        // Return詳細ログ生成。
        $returnDetailBuff = $this->createReturnDetail($methodCallBuff,$ret);

        // Returnログ出力。
        $this->logOut($returnDetailBuff);

        self::$callerLog_ = $currentCallerLog;

        //invocationを実行した際の戻り値を返す。
        return $ret;
    }

    /**
     * メソッド呼び出し（識別子Call）の詳細なログを生成する。
     * 
     * @param methodCallBuff メソッド呼び出しの文字列
     * @param invocation メソッド呼び出しの情報
     * @return メソッド呼び出しの詳細なログ
     */
    private function createCallDetail($methodCallBuff,
                                      $invocation)
    {
        $timeStr = $this->getTime();
        $callDetailBuff = self::KEY_CALL . self::DELIM .
                          $timeStr       .  self::DELIM .
                          $methodCallBuff;

        // 引数のログを生成する。
        if ($this->isLogArgs_ == true)
        {
            $callDetailBuff .= "<<javelin.Args_START>>" . PHP_EOL;
            $args = $invocation->getArguments();
            $c = count($args);
            for($i = 0; $i < $c; $i++){
                $callDetailBuff .= "    args[$i] = " . $this->mixToString($args[$i]) . PHP_EOL;
            }
            $callDetailBuff .= "<<javelin.Args_END>>" . PHP_EOL;
        }

        // スタックトレースのログを生成する。
        if ($this->isLogStackTrace_ == true)
        {
            $callDetailBuff .= "<<javelin.StackTrace_START>>" . PHP_EOL;
            $stackTraceElements = null;
            try{
                throw new Exception();    
            }
            catch(Exception $e){
                $stackTraceElements = preg_split("/#\d+\s/",$e->getTraceAsString());
            }
            $c = count($stackTraceElements);
            for ($index = self::$CALLER_STACKTRACE_INDEX; $index < $c; $index++)
            {
                $callDetailBuff .= "    at " . $stackTraceElements[$index];
            }

            $callDetailBuff .= PHP_EOL . "<<javelin.StackTrace_END>>" . PHP_EOL;
        }

        if ($invocation->getThis() instanceof Exception)
        {
            $callDetailBuff .= "<<javelin.Exception>>" . PHP_EOL;
        }
        return $callDetailBuff;
    }

    /**
     * メソッドの戻り（識別子Return）の詳細なログを生成する。
     * 
     * @param methodCallBuff メソッド呼び出しの文字列
     * @param ret メソッドの戻り値
     * @return メソッドの戻りの詳細なログ
     */
    private function createReturnDetail($methodCallBuff,$ret)
    {
        $returnTimeStr = $this->getTime();
        $returnDetailBuff = self::KEY_RETURN . self::DELIM .
                            $returnTimeStr . self::DELIM .
                            $methodCallBuff;

        // 戻り値のログを生成する。
        if ($this->isLogReturn_)
        {
            $returnDetailBuff .= "<<javelin.Return_START>>"        . PHP_EOL
                               . "    " . $this->mixToString($ret) . PHP_EOL
                               . "<<javelin.Return_END>>"          . PHP_EOL;
        }
        return $returnDetailBuff;
    }

    /**
     * 例外発生（識別子Throw）、または例外キャッチ（Catch）の詳細ログを生成する。
     * 
     * @param id 識別子。ThrowまたはCatch
     * @param calleeMethodName 呼び出し先メソッド名
     * @param calleeClassName 呼び出し先クラス名
     * @param objectID 呼び出し先クラスのオブジェクトID
     * @param threadInfo スレッド情報
     * @param cause 発生した例外
     * @return 例外発生の詳細なログ
     */
    private function createThrowCatchDetail(
                     $id,
                     $calleeMethodName,
                     $calleeClassName,
                     $objectID,
                     $threadInfo,
                     $cause)
    {
        $throwTimeStr = $this->getTime();
        $throwableID = (string)$cause;
        $throwBuff = $id . self::DELIM .
                     $throwTimeStr     . self::DELIM .
                     get_class($cause) . self::DELIM .
                     $throwableID      . self::DELIM .
                     $calleeMethodName . self::DELIM .
                     $calleeClassName  . self::DELIM .
                     $objectID         . self::DELIM .
                     $threadInfo       . PHP_EOL;
        return $throwBuff;
    }

    /**
     * 引数の内容をログ出力するかどうか設定する。
     * @param isLogArgs 引数をログ出力するならtrue
     */
    public function setLogArgs($isLogArgs)
    {
        $this->isLogArgs_ = $isLogArgs;
    }

    /**
     * 戻り値の内容をログ出力するかどうか設定する。
     * @param isLogReturn 戻り値をログ出力するならtrue
     */
    public function setLogReturn($isLogReturn)
    {
        $this->isLogReturn_ = $isLogReturn;
    }

    /**
     * メソッド呼び出しまでのスタックトレースをログ出力するか設定する。
     * @param isLogStackTrace スタックトレースをログ出力するならtrue
     */
    public function setLogStackTrace($isLogStackTrace)
    {
        $this->isLogStackTrace_ = $isLogStackTrace;
    }

    public function setLogFile($logFile)
    {
        if(!is_writable(dirname($logFile))){
            throw new Exception("can not write log file. [ $logFile ]");
        }
        $this->logFile_ = $logFile;
    }
    
    private function logOut($msg)
    {
        if(S2Container_S2LogFactory::$LOGGER == S2Container_S2LogFactory::LOG4PHP){
            S2Container_S2Logger::getLogger(__CLASS__)->debug($msg); 	
        } else {
            if(!file_put_contents($this->logFile_, $msg, FILE_APPEND | LOCK_EX)){
                throw new Exception("can not write file. [{$this->logFile_}]");
            }
        }
    }

    private function getTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return date('Y/m/d H:i:s',$sec) . substr($usec,1,4);
    }

    private function getId()
    {
        return self::$ID;
    }
    
    private static $ID = null;
    public function __construct(){
        if (self::$ID == null){
            self::$ID = uniqid();
        }
    }

    private function mixToString($val) {
        if (is_array($val)) {
            $c = count($val);
            return "array($c)";
        } else if (is_object($val)){
            $name = get_class($val);
            return "object<$name>";
        } else if (is_bool($val)){
            if ($val) {
                return 'true';
            } else {
                return 'false';
            }
        } else if (is_null($val)){
            return 'null';
        } else {
            return $val;   
        }
    }

}
?>
