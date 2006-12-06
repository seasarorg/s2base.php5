@echo off

call pear uninstall channel://__uri/S2Base_Zf_Scaffold
call pear uninstall channel://__uri/S2Base_Zf_Pager
call pear uninstall channel://__uri/S2Base_Zf

call pear list -c __uri

