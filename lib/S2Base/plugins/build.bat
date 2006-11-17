@echo off

call phing -f Command_DaoMethod.xml
call phing -f Command_SqliteCli.xml
call phing -f Command_Dto.xml
call phing -f Command_Logic.xml
call phing -f Smarty_Pager.xml
call phing -f Smarty_Scaffold.xml
call phing -f Smarty_ActionDao.xml
call phing -f Smarty_AjaxTpl.xml
