<div id="app-navigation">
		<form id="search_form" style="padding: 12px;" action="<?php p($_['url']);?>">
		<textarea id="search_textarea" style="width:100%" name="s"><?php p($_['s']);?></textarea>
		<input type="submit" class="icon-search svg" value="" title="submit"> or press CTRL+ENTER</form>
	<p style="padding: 12px;">Example search query:<br/>
<em>
title:"Find me" AND creator:holzheu
</em> <br/>
		For more examples and options read <a href="http://lucene.apache.org/core/7_7_0/queryparser/org/apache/lucene/queryparser/classic/package-summary.html#package.description">here</a>.
</p>	
		<div style="padding:12px;"><?php print_unescaped($_['search_help_text']);?></div>
	<p style="padding: 12px;">Metadatarepository will automatically index all files ending 
	with <strong>ReadmeDC.txt</strong>. 
	These files will be 
	searchable and readable to all cloud users.<br/><br/>
	There is no automatic access right to the data. Data access is handled
	by the normal cloud sharing functions.<br/><br/>
	Files ending with <strong>ReadmeDC.private.txt</strong> will be indexed as well but only
	shown to user with read access to the file.<br/><br/>
	A image file with the same name will be treated as a preview (e.g. <strong>ReadmeDC.jpg</strong>)

</p>
	<p style="padding: 12px;">
	(c) Stefan Holzheu and Oliver Archner, BayCEER IT, Version 0.0.1</p>
</div>
