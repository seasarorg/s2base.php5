<font color="pink">
{foreach from=$errors item=val key=key}
{$key|escape} : {$val|escape}
{/foreach}
</font>
<br>

<h3>
Total {$helper->getCount()} Hits, 
[{$helper->getOffset()+1} - {$helper->getCurrentLastOffset()+1}] <br>
</h3>

<table width="100%"><tr>
<td>
{if $helper->isPrev()}
<a href="{$act_url}/s2pager_offset/{$helper->getPrevOffset()}">prev[{$helper->getLimit()}]</a>
{else}
prev[{$helper->getLimit()}]
{/if}
</td>

<td align="right">
{if $helper->isNext()}
<a href="{$act_url}/s2pager_offset/{$helper->getNextOffset()}">next[{$helper->getLimit()}]</a>
{else}
next[{$helper->getLimit()}]
{/if}
</td>
</tr></table>
<center>
<table class="list">
<caption align="top">
<a href="{$ctl_url}/@@ACTION_NAME@@-create">create</a>
</caption>
<tbody>
@@PROPERTY_ROWS_TITLE@@
{foreach from=$dtos item=row}
  @@PROPERTY_ROWS@@
{/foreach}
</tbody>
</table>
</center>

<h3>
Pages [{$helper->getLastPageIndex()+1}] : 
{foreach from=$pageIndex item=index}
  {if $index == $helper->getPageIndex()}
    {$index+1}
  {else}
    <a href="{$act_url}/s2pager_offset/{$index*$helper->getLimit()}">{$index+1}</a>
  {/if}
{/foreach}
</h3>
