<?php 
style('metadatarepo', 'style');  // adds css/style.css
script('metadatarepo', 'search');  // adds js/search.js
?>
<div id="app">
<?php print_unescaped($this->inc('navi')); ?>
<div id="app-content">
	<div id="metadatarepo">
	<?php if($_['s']){?>
	<div class="pagination">
		<div class="pagi"><span><?php p($_['estimated_hits']);?> Hits</span></div>
		<?php if($_['estimated_hits']>count($_['hits'])){
		if($_['start_offset']){
		?>
		<div class="pagi"><a href="#" id="goback">previous page</a></div>		
		<?php }?>
		<div class="pagi"><a href="?s=<?php p($_['s']);?>&o=<?php p($_['end_offset']);?>">next page</a></div>
		<?php }?>
	</div>

	<table id="filestable">
	<?php if(! count($_['hits'])){	?>
	<tr><td><strong>No Results found</strong></td></tr>
	<?php 
	}
		for($i=0;$i<count($_['hits']);$i++){
		    $r=$_['hits'][$i];
	?>
	<tr>
	<?php if($r['readable']){?>
	<td><a  title="go to directory" class="icon-toggle svg" href="../files/?fileid=<?php p($r['id']);?>" style="display: block;">&nbsp;</a></td>
	<td>
	<a  href="../files/?fileid=<?php p($r['id']);?>" title="go to directory"><?php p($r['path'])?></a>
	</td>
	<?php } else {?>
	<td class="icon-password svg"></td>
	<td><?php p($r['path'])?></td>
	<?php } ?>
	<td><a href="page/<?php p($r['id']);?>?s=<?php p($_['s']);?>" title="show details">
	<?php if($r['thumb']){?>
	<img src="data:image/png;base64,<?php p($r['thumb'])?>">
	<?php } else {?>
	Details
	<?php }?>
	</a></td>
<td>
<?php 
for($j=0;$j<count($r['previews']);$j++){
    print_unescaped('<p>'.nl2br($r['previews'][$j]).'</p>');
}
?>
</td>		
		
		</tr>
		<?php }?>
		</table>
		<?php }?>
	</div>
</div>
</div>
