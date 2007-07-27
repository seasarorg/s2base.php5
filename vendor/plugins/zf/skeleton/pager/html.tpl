<img src="{$base_url}/images/seasar_logo_blue.gif" alt="The Seasar Project" height="180" width="390"/>
<h2>
Generated by S2Base.PHP5
</h2>

<font color="pink">
{foreach from=$errors.validate item=val key=key}
{$key|escape} : {$val.msg|escape}
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
<tbody>
@@PROPERTY_ROWS_TITLE@@
{foreach from=$dtos item=row}
  @@PROPERTY_ROWS@@
{/foreach}
</tbody>
</table>
<br>

<form action="{$act_url}" method="post">
<table class="list">
<tbody>
<tr>
<td>current keyword</td>
<td>{$keyword|escape}</td>
</tr>
<tr>
<td><input type="text" name="s2base_keyword" value=""/></td>
<td><input type="submit" name="" value="set keyword"/></td>
</tr>
</tbody>
</table>
</form>

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

<h2></h2>
