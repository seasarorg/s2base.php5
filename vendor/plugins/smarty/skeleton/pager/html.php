<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<title>@@MODULE_NAME@@</title>
</head>
<body>

This is @@ACTION_NAME@@ template. <br>
<br>

total {$helper->getCount()} hits, 
[{$helper->getOffset()+1} - {$helper->getCurrentLastOffset()+1}] <br>

<table width="100%"><tr>
<td>
{if $helper->isPrev()}
<a href="?mod={$module}&act={$action}&s2pager_offset={$helper->getPrevOffset()}">&lt; prev {$helper->getLimit()}</a>
{/if}
</td>

<td align="right">
{if $helper->isNext()}
<a href="?mod={$module}&act={$action}&s2pager_offset={$helper->getNextOffset()}">next {$helper->getLimit()} &gt;</a>
{/if}
</td>
</tr><table>

<hr>
<table>
@@PROPERTY_ROWS_TITLE@@
{foreach from=$dtos item=row}
  @@PROPERTY_ROWS@@
{/foreach}
</table>
<hr>

pages [{$helper->getLastPageIndex()+1}] : 
{foreach from=$pageIndex item=index}
  {if $index == $helper->getPageIndex()}
    {$index+1}
  {else}
    <a href="?mod={$module}&act={$action}&s2pager_offset={$index*$helper->getLimit()}">{$index+1}</a>
  {/if}
{/foreach}

<br>
</body>
</html>
