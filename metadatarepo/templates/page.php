<?php 
style('metadatarepo', 'style');  // adds css/style.css
script('metadatarepo', 'search');  // adds js/search.js
?>
<div id="app">
<?php print_unescaped($this->inc('navi')); ?>
<div id="app-content">
	<?php if($_['has_image']){?>
	<img src="../image/<?php p($_['id'])?>">
	<?php }?>
	<h3>ReadmeDC.txt:</h3>
	<pre><?php p($_['readmedc']);?></pre>
	<div class="pagination">
<div class="pagi"><a href="#" id="goback">back to search results</a></div>	
</div>	
</div>
</div>