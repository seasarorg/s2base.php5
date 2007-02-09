@echo off

call pear install S2Base_Command_DaoMethod-0.7.0.tgz
call pear install S2Base_Command_Dto-0.7.0.tgz
call pear install S2Base_Command_Logic-0.7.0.tgz
call pear install S2Base_Command_SqliteCli-0.7.0.tgz
call pear install S2Base_Smarty_ActionDao-0.7.0.tgz
call pear install S2Base_Smarty_AjaxTpl-0.7.0.tgz
call pear install S2Base_Smarty_Pager-0.7.0.tgz
call pear install S2Base_Smarty_Scaffold-0.7.0.tgz

call pear list -c __uri
