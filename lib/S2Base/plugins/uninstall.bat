@echo off

call pear uninstall channel://__uri/S2Base_Command_DaoMethod
call pear uninstall channel://__uri/S2Base_Command_Dto
call pear uninstall channel://__uri/S2Base_Command_Logic
call pear uninstall channel://__uri/S2Base_Command_SqliteCli
call pear uninstall channel://__uri/S2Base_Smarty_ActionDao
call pear uninstall channel://__uri/S2Base_Smarty_AjaxTpl
call pear uninstall channel://__uri/S2Base_Smarty_Pager
call pear uninstall channel://__uri/S2Base_Smarty_Scaffold

call pear list -c __uri

