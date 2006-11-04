<br>
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

<pre>
{foreach from=$dtos item=row}
  {$row|escape}
{/foreach}
</pre>
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
