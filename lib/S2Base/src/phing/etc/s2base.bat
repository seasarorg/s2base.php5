@ECHO OFF
REM +----------------------------------------------------------------------+
REM | Copyright 2005-2006 the Seasar Foundation and the Others.            |
REM +----------------------------------------------------------------------+
REM | Licensed under the Apache License, Version 2.0 (the "License");      |
REM | you may not use this file except in compliance with the License.     |
REM | You may obtain a copy of the License at                              |
REM |                                                                      |
REM |     http://www.apache.org/licenses/LICENSE-2.0                       |
REM |                                                                      |
REM | Unless required by applicable law or agreed to in writing, software  |
REM | distributed under the License is distributed on an "AS IS" BASIS,    |
REM | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
REM | either express or implied. See the License for the specific language |
REM | governing permissions and limitations under the License.             |
REM +----------------------------------------------------------------------+

SET php_bin=@PHP-BIN@

IF EXIST "%php_bin%" (
  "%php_bin%" -d html_errors=off -qC "@PEAR-DIR@\S2Base\bin\s2base.php" %*
) ELSE (
  ECHO [ERROR] Can not find the php.exe.
)
