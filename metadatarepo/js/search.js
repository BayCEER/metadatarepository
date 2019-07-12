$(document).ready(function() {
	$("#search_textarea").keydown(function (e) {
		if (e.keyCode == 13 && e.ctrlKey) {
			$("#search_form").trigger('submit');
			return false;
		}
	});
	$("#search_textarea").focus();
	$("#goback").click(function(){
		  window.history.back();
	});
});

