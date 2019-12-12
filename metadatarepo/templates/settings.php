<?php 
script('metadatarepo', 'settings_admin');
?>
<div id="metadatarepo_settings" class="section">
<h2 class="app-name">Metadatarepository Settings</h2>
<p>
<textarea id="search_help_text" name="search_help_text" rows=10 cols=60><?php p($_['search_help_text'])?></textarea>
<label for="search_help_text">Additional help text for search (e.g. keys to use...)</label>
</p>
<p>
<p>
<textarea id="default_readmedc.txt" name="default_readmedc.txt" rows=10 cols=60><?php p($_['default_readmedc.txt'])?></textarea>
<label for="default_readmedc.txt">Default ReadmeDC.txt</label>
</p>
<h3>Filter Fields</h3>
<p id="search_fields">
</p>


<h3>Config.php Settings</h3>
<p>
Metadatarepository uses elastic search as backend. Default url is http://localhost:5541/.
You can overwrite the default by setting a key <strong>metadatarepo.elastic_search.url</strong> in your config.php
</p>
<p>
For multiple OC-Instances using the same backend you must set <strong>metadatarepo.elastic_search.collection</strong>. 
Default is &quot;ownlcloud&quot;.
</p>
</div>