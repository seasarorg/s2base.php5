<img src="{$base_url}/images/seasar_logo_blue.gif" alt="The Seasar Project" height="180" width="390"/>
<h2>
Generated by S2Base.PHP5
</h2>

<center>
<table class="list">
<tbody>
<tr>
<th></th>
<th>Tabel Name</th>
</tr>
{foreach from=$ctls item=ctl key=k}
<tr>
<th>{$k+1}</th>
<td>
{if $module==null}
    <a href="{$base_url}/{$ctl}/{$ctl}">{$ctl}</a>
{else}
    <a href="{$base_url}/{$module}/{$ctl}/{$ctl}">{$ctl}</a>
{/if}
</td>
</tr>
{/foreach}
</tbody>
</table>
</center>

<h2></h2>