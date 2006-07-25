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
     * �᥽�åɸƤӽФ�����Javlin������Ƭ�˽��Ϥ��뼱�̻ҡ�
     */
    const KEY_CALL = "Call  ";

    /**
     * �᥽�å�������Javlin������Ƭ�˽��Ϥ��뼱�̻ҡ�
     */
    const KEY_RETURN = "Return";

    /**
     * �㳰throw����Javlin������Ƭ�˽��Ϥ��뼱�̻ҡ�
     */
    const KEY_THROW = "Throw ";

    /**
     * �㳰catch����Javlin������Ƭ�˽��Ϥ��뼱�̻ҡ�
     */
    const KEY_CATCH = "Catch ";

    
    public static $CALLER_STACKTRACE_INDEX = 1;

    /**
     * ���ζ��ڤ�ʸ����
     */
    const DELIM = ",";

    /**
     * ����åɾ���ζ��ڤ�ʸ��
     */
    const THREAD_DELIM = "@";

    /**
     * �����Ϥ������Υե����ޥåȡ�
     * const TIME_FORMAT_STR = "yyyy/MM/dd HH:mm:ss.SSS";
     */

    /**
     * �᥽�åɤθƤӽФ������֥������Ȥ�ɽ��ʸ����
     */
    private static $callerLog_ = "<unknown>,<unknown>,0";

    /**
     * ��������Ϥ��뤫�ɤ�����
     * args�ץ�ѥƥ����ͤ����åȤ���롣
     */
    private $isLogArgs_ = true;

    /**
     * ����ͤ���Ϥ��뤫�ɤ�����
     * return�ץ�ѥƥ����ͤ����åȤ���롣
     */
    private $isLogReturn_ = true;

    /**
     * �����å��ȥ졼������Ϥ��뤫�ɤ�����
     * stackTrace�ץ�ѥƥ����ͤ����åȤ���롣
     */
    private $isLogStackTrace_ = false;

    /**
     * �����å��ȥ졼������Ϥ��뤫�ɤ�����
     * stackTrace�ץ�ѥƥ����ͤ����åȤ���롣
     */
    private $logFile_ = "";

    /**
     * �㳰ȯ����Ͽ����ơ��֥롣
     * �����˥���åɤμ��̻ҡ��ͤ��㳰���֥������Ȥ�����롣
     * �㳰��ȯ�������餳�Υޥåפˤ����㳰����Ͽ����
     * ����å����줿�����Ǻ�����롣
     */
    static private $exceptionMap_ = array();
    
    /**
     * Javelin�������Ѥ�invoke�᥽�åɡ�
     * 
     * �ºݤΥ᥽�åɸƤӽФ���¹Ԥ�������ǡ�
     * �ƤӽФ����ֵѤξܺ٥���Javelin�����ǽ��Ϥ��롣
     * 
     * �¹Ի����㳰��ȯ���������ϡ����ξܺ٤�����Ϥ��롣
     * 
     * @param invocation
     *            ���󥿡����ץ��ˤ�äƼ������줿���ƤӽФ��᥽�åɤξ���
     * @return invocation��¹Ԥ����Ȥ��������
     * @throws Throwable invocation��¹Ԥ����Ȥ���ȯ�������㳰
     */
    public function invoke(S2Container_MethodInvocation $invocation)
    {
        $methodCallBuff = "";

        // �ƤӽФ�����������
        $calleeMethodName = $invocation->getMethod()->getName();
        $calleeClassName = $this->getTargetClass($invocation)->getName();
        $objectID = $invocation->getThis();
        $modifiers = implode(' ', Reflection::getModifierNames(
                                  $invocation->getMethod()->getModifiers()));
        // �ƤӽФ������������
        $currentCallerLog = self::$callerLog_;

        $threadName = "php";
        $threadClassName = "proccess";
        $threadID = $this->getId();
        $threadInfo = $threadName      . self::THREAD_DELIM .
                      $threadClassName . self::THREAD_DELIM .
                      $threadID;

        // �᥽�åɸƤӽФ�������ʬ������
        $methodCallBuff =  $calleeMethodName . self::DELIM .
                           $calleeClassName  . self::DELIM .
                           $objectID;

        // �ƤӽФ���Υ��򡢼�������ϻ��θƤӽФ����Ȥ��ƻ��Ѥ��뤿�����¸���롣
        self::$callerLog_ = $methodCallBuff;

        $methodCallBuff .= self::DELIM .
                           $currentCallerLog . self::DELIM .
                           $modifiers        . self::DELIM .
                           $threadInfo       . PHP_EOL;

        // Call �ܺ٥�������
        $callDetailBuff = $this->createCallDetail($methodCallBuff,$invocation);

        // Call �����ϡ�
        $this->logOut($callDetailBuff);

        //�ºݤΥ᥽�åɸƤӽФ���¹Ԥ��롣
        //�¹�����㳰��ȯ�������顢���ξܺ٥�����Ϥ��롣
        $ret = null;
        try
        {
            // �᥽�åɸƤӽФ���
            $ret = $invocation->proceed();
        }
        catch (Exception $cause)
        {
            // Throw�ܺ٥�������
            $throwBuff = $this->createThrowCatchDetail(
                                self::KEY_THROW, 
                                $calleeMethodName,
                                $calleeClassName,
                                $objectID,
                                $threadInfo, 
                                $cause);

            // Throw �����ϡ�
            $this->logOut($throwBuff);

            //�㳰ȯ����Ͽ���롣
            self::$exceptionMap_[$threadInfo] = $cause;
            
            //�㳰�򥹥�������λ���롣
            throw $cause;
        }
        
        //���Υ���åɤǡ�ľ�����㳰��ȯ�����Ƥ��������ǧ���롣
        //ȯ�����Ƥ��Ƥ����ˤ��ɤ��夤���ΤǤ���С����λ������㳰��
        //catch���줿�Ȥ������Ȥˤʤ�Τǡ�Catch������Ϥ��롣
        $isExceptionThrowd = array_key_exists($threadInfo,self::$exceptionMap_);
        if($isExceptionThrowd == true) 
        {
            //ȯ�����Ƥ����㳰���֥������Ȥ�ޥåפ�����Ф����ʤ��ĺ�������
            $exp = self::$exceptionMap_[$threadInfo];
            unset(self::$exceptionMap_[$threadInfo]);
            
            // Catch�ܺ٥�������
            $throwBuff = $this->createThrowCatchDetail(
                                self::KEY_CATCH,
                                $calleeMethodName,
                                $calleeClassName,
                                $objectID,
                                $threadInfo,
                                $exp);

            // Catch �����ϡ�
            $this->logOut($throwBuff);
        }

        // Return�ܺ٥�������
        $returnDetailBuff = $this->createReturnDetail($methodCallBuff,$ret);

        // Return�����ϡ�
        $this->logOut($returnDetailBuff);

        self::$callerLog_ = $currentCallerLog;

        //invocation��¹Ԥ����ݤ�����ͤ��֤���
        return $ret;
    }

    /**
     * �᥽�åɸƤӽФ��ʼ��̻�Call�ˤξܺ٤ʥ����������롣
     * 
     * @param methodCallBuff �᥽�åɸƤӽФ���ʸ����
     * @param invocation �᥽�åɸƤӽФ��ξ���
     * @return �᥽�åɸƤӽФ��ξܺ٤ʥ�
     */
    private function createCallDetail($methodCallBuff,
                                      $invocation)
    {
        $timeStr = $this->getTime();
        $callDetailBuff = self::KEY_CALL . self::DELIM .
                          $timeStr       .  self::DELIM .
                          $methodCallBuff;

        // �����Υ����������롣
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

        // �����å��ȥ졼���Υ����������롣
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
     * �᥽�åɤ����ʼ��̻�Return�ˤξܺ٤ʥ����������롣
     * 
     * @param methodCallBuff �᥽�åɸƤӽФ���ʸ����
     * @param ret �᥽�åɤ������
     * @return �᥽�åɤ����ξܺ٤ʥ�
     */
    private function createReturnDetail($methodCallBuff,$ret)
    {
        $returnTimeStr = $this->getTime();
        $returnDetailBuff = self::KEY_RETURN . self::DELIM .
                            $returnTimeStr . self::DELIM .
                            $methodCallBuff;

        // ����ͤΥ����������롣
        if ($this->isLogReturn_)
        {
            $returnDetailBuff .= "<<javelin.Return_START>>"        . PHP_EOL
                               . "    " . $this->mixToString($ret) . PHP_EOL
                               . "<<javelin.Return_END>>"          . PHP_EOL;
        }
        return $returnDetailBuff;
    }

    /**
     * �㳰ȯ���ʼ��̻�Throw�ˡ��ޤ����㳰����å���Catch�ˤξܺ٥����������롣
     * 
     * @param id ���̻ҡ�Throw�ޤ���Catch
     * @param calleeMethodName �ƤӽФ���᥽�å�̾
     * @param calleeClassName �ƤӽФ��襯�饹̾
     * @param objectID �ƤӽФ��襯�饹�Υ��֥�������ID
     * @param threadInfo ����åɾ���
     * @param cause ȯ�������㳰
     * @return �㳰ȯ���ξܺ٤ʥ�
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
     * ���������Ƥ�����Ϥ��뤫�ɤ������ꤹ�롣
     * @param isLogArgs ����������Ϥ���ʤ�true
     */
    public function setLogArgs($isLogArgs)
    {
        $this->isLogArgs_ = $isLogArgs;
    }

    /**
     * ����ͤ����Ƥ�����Ϥ��뤫�ɤ������ꤹ�롣
     * @param isLogReturn ����ͤ�����Ϥ���ʤ�true
     */
    public function setLogReturn($isLogReturn)
    {
        $this->isLogReturn_ = $isLogReturn;
    }

    /**
     * �᥽�åɸƤӽФ��ޤǤΥ����å��ȥ졼��������Ϥ��뤫���ꤹ�롣
     * @param isLogStackTrace �����å��ȥ졼��������Ϥ���ʤ�true
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
