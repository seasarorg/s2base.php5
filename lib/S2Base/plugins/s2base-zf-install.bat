@echo off

call pear install http://pear.s2php5.jp/s2base/S2Base_Zf-0.8.0.tgz
call pear install http://pear.s2php5.jp/s2base/S2Base_Zf_Pager-0.8.0.tgz
call pear install http://pear.s2php5.jp/s2base/S2Base_Zf_Scaffold-0.8.0.tgz

call pear list -c __uri
