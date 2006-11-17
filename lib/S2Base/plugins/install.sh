#!/bin/sh

pear install S2Base_Command_DaoMethod-1.0.0.tgz
pear install S2Base_Command_Dto-1.0.0.tgz
pear install S2Base_Command_Logic-1.0.0.tgz
pear install S2Base_Command_SqliteCli-1.0.0.tgz
pear install S2Base_Smarty_ActionDao-1.0.0.tgz
pear install S2Base_Smarty_AjaxTpl-1.0.0.tgz
pear install S2Base_Smarty_Pager-1.0.0.tgz
pear install S2Base_Smarty_Scaffold-1.0.0.tgz

pear list -c __uri
