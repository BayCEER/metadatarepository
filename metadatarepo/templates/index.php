<?php 
style('metadatarepo', 'style');  // adds css/style.css
style('metadatarepo', 'simplePagination');  // adds css/simplePagination.css
script('metadatarepo', 'jquery.simplePagination');  // adds js/simplePagination.js
script('metadatarepo', 'search');  // adds js/search.js
?>
<div id="app">
<div id="app-navigation">
<div style="border-top:1px dotted #ddd;">
<h3>Filter</h3>
<div id="agg-filter"></div>
</div>
<div style="border-top:1px dotted #ddd;">
<h3>Fields</h3>
<ul class="with-icon" id="agg-fields-ul">
</ul>
</div>
</div>
<div id="app-content">
<div id="details">
<div id="details-text">Details</div></div>
<div id="controls">
<div class="breadcrumb">
<div class=" crumb svg last"><span>Search</span></div>
		<form id="search_form" style="position: relative; top:6px;">
		<input id="search_field" style="width:60%;max-width:300px;" name="s">
		</form>
</div>
</div>

<div id="search-results" style="padding-top:50px">
<div class="pagination"></div>
<table id="resultstable">
<thead>
<tr><th>Data access</th><th>Path</th><th>Preview</th><th>Thumbnail</th><th>Score</th></tr>
</thead>
<tbody id="result_table"></tbody>
</table>
<div class="pagination"></div>
</div>
</div>
</div>

