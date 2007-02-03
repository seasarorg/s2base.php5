@echo off

call pear install S2Base_Zf-0.7.0.tgz
call pear install S2Base_Zf_Pager-0.7.0.tgz
call pear install S2Base_Zf_Scaffold-0.7.0.tgz

call pear list -c __uri
