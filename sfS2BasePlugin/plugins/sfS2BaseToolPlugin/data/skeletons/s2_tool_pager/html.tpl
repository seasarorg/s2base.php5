<?php
if (count($sf_request->getErrors()) > 0) {
    print '<pre>' . PHP_EOL;
    foreach($sf_request->getErrors() as $error) {
        print "<font color=\"red\">$error</font><br>";
    }
    print '</pre>' . PHP_EOL;
}
?>

<h3>
Results <?php echo $helper->getOffset()+1;?> - <?php echo $helper->getCurrentLastOffset()+1?> of <?php echo $helper->getCount();?> 
</h3>

<center>
<table class="list">
<tbody>
@@PROPERTY_ROWS_TITLE@@
<?php foreach ($dtos as $row): ?>
  @@PROPERTY_ROWS@@
<?php endforeach; ?>
</tbody>
</table>

<?php if ($helper->isPrev()):?>
  <?php echo link_to('Previous', '@@MODULE_NAME@@/@@ACTION_NAME@@?offset=' . $helper->getPrevOffset()); ?>
<?php endif;?>
<?php if ($helper->getLastPageIndex() > 0): ?>
  <?php foreach ($pageIndex as $index): ?>
    <?php if($index == $helper->getPageIndex()):?>
      <?php echo $index+1;?>
    <?php else:?>
      <?php echo link_to($index+1, '@@MODULE_NAME@@/@@ACTION_NAME@@?offset=' . $index*$helper->getLimit());?>
    <?php endif;?>
  <?php endforeach; ?>
<?php endif;?>
<?php if ($helper->isNext()):?>
  <?php echo link_to('Next', '@@MODULE_NAME@@/@@ACTION_NAME@@?offset=' . $helper->getNextOffset()); ?>
<?php endif;?>

<br>
<br>

<?php echo form_tag('@@MODULE_NAME@@/@@ACTION_NAME@@');?>
<table class="list">
<tbody>
<tr>
<td>current keyword</td>
<td><?php echo $keyword; ?></td>
</tr>
<tr>
<td><input type="text" name="keyword" value=""/></td>
<td><input type="submit" name="" value="set keyword"/></td>
</tr>
</tbody>
</table>
</form>

</center>

Total <?php echo $helper->getLastPageIndex()+1; ?> pages. List <?php echo $helper->getLimit();?> items per page.

<h2></h2>
