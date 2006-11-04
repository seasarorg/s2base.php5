<br>
<font color="pink">
{foreach from=$errors item=val key=key}
{$key|escape} : {$val|escape}
{/foreach}
</font>
<br>

Total {$helper->getCount()} Hits, 
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
<a href="?mod={$module}&act=@@ACTION_NAME@@Create">create</a><br>
<table>
@@PROPERTY_ROWS_TITLE@@
{foreach from=$dtos item=row}
  @@PROPERTY_ROWS@@
{/foreach}
</table>
<hr>

Pages [{$helper->getLastPageIndex()+1}] : 
{foreach from=$pageIndex item=index}
  {if $index == $helper->getPageIndex()}
    {$index+1}
  {else}
    <a href="?mod={$module}&act={$action}&s2pager_offset={$index*$helper->getLimit()}">{$index+1}</a>
  {/if}
{/foreach}

<br>
