#!/bin/sh

pear uninstall channel://__uri/S2Base_Command_DaoMethod
pear uninstall channel://__uri/S2Base_Command_Dto
pear uninstall channel://__uri/S2Base_Command_Logic
pear uninstall channel://__uri/S2Base_Command_SqliteCli
pear uninstall channel://__uri/S2Base_Smarty_ActionDao
pear uninstall channel://__uri/S2Base_Smarty_AjaxTpl
pear uninstall channel://__uri/S2Base_Smarty_Pager
pear uninstall channel://__uri/S2Base_Smarty_Scaffold

pear list -c __uri

