$(document).ready(function() {
	// global variables for application
	var fields=[];
	var filter={};
	var startPage=0;
	var totalHits;
	var hitsPerPage=10;
		
	//formating function
	function format_content (str, highlight='',is_xhtml=true) {
		if (typeof str === 'undefined' || str === null || str === '') {
			return '';
		}
		var breakTag = is_xhtml ? '<br />' : '<br>';
		res=''+str
		res=res.replace(/^([^ :\n]+):/g,'<b>$1</b>: ')
		res=res.replace(/(\r\n|\n\r|\r|\n)([^ ][^:]+):/g,'$1'+ breakTag +'<b>$2</b>: ')
		if(highlight.length && highlight!='*'){
			
			var regexp = new RegExp('('+highlight+')', "gi");
			res=res.replace(regexp,'<mark>$1</mark>')
		}
		return res;
	}
	
	//remove function for array
	Array.prototype.remove = function() {
	    var what, a = arguments, L = a.length, ax;
	    while (L && this.length) {
	        what = a[--L];
	        while ((ax = this.indexOf(what)) !== -1) {
	            this.splice(ax, 1);
	        }
	    }
	    return this;
	};
	
	//Function for creating the initial field tree. Childs are closed
	function create_fields(data){
		$.each(data, function(){
			$("#agg-fields-ul").append('<li class="nav-files">'+
					'<a class="agg_field icon-triangle-s svg" id="agg_field_'+this+'">'
    				+this+'</a><div class="agg_childs" id="agg_childs_'+this+'"></div></li>');
			fields.push(this);
		});
		
		//register handler for opening and closing the fields
    	$(".agg_field").click(function () {

    	    f = $(this);
    	    //find childs
    	    c = f.next();
    	    c.slideToggle(200, function () {
    	    	if(c.is(":visible")){
    	    		f.removeClass("icon-triangle-s");
    	    		f.addClass("icon-triangle-e");	
    	    	} else {
    	    		f.removeClass("icon-triangle-e");
    	    		f.addClass("icon-triangle-s");	
    	    		
    	    	}
    	    });
		});
    	//hide the childs at startup
		$(".agg_childs").hide();
	}
	
	//Function for filling the field-tree after successful search
	function fill_field(key,title,childs){
		key=key.replace(/[: <>;,]/g,'_')
		$("#agg_field_"+key).html(title);
		$("#agg_childs_"+key).html("");
		for(var i=0;i<childs.length;i++){
			$("#agg_childs_"+key).append('<a class="agg_filter" data-key="'+key+'" data-value="'+childs[i].key+'">'+
					childs[i].key+' ('+childs[i].count+')</a><br/>');
		}
		$(".agg_filter").unbind('click');
	
		$('.agg_filter').click(function(){
			key=$(this).attr('data-key')
			value=$(this).attr('data-value')
			if(! filter[key]){
				filter[key]=[]
			}
			if(! filter[key].includes(value))
				filter[key].push(value)
			else {
				filter[key].remove(value)
				if(! filter[key].length) delete(filter[key])
			}
			
			fill_filter()
			es_search()
			
		})
	}
	
	//Function for printing the pagination
	function print_pagination(){
		$('.pagination').html('')
		if(totalHits>hitsPerPage){
		$('.pagination').pagination({
	        items: totalHits,
	        itemsOnPage: hitsPerPage,
	        currentPage: startPage/hitsPerPage+1,
	        cssStyle: 'light-theme',
	        onPageClick: function(p,e){
	        	startPage=(p-1)*hitsPerPage;
	        	es_search(false)
	        }
	    });		
		}
	}
	
	//Function for printing the filter
	function fill_filter(){
		t=$("#agg-filter")
		t.html("")
		for(var key in filter){
			var s=key.replace(/^_/,'')
			t.append('<b>'+s[0].toUpperCase()+s.slice(1)+'</b>: ')
			for(var i=0; i<filter[key].length; i++){
				if(i>0) t.append('<i>or</i> ')
				t.append('<a class="agg_filter_rm" data-key="'+key+'" data-value="'+filter[key][i]+'">'+
						filter[key][i]+'</a> ')
				
			}
			t.append('<br/>')
		}
		$('.agg_filter_rm').click(function(){
			key=$(this).attr('data-key')
			value=$(this).attr('data-value')
			filter[key].remove(value)
			if(! filter[key].length) delete(filter[key])
			fill_filter()
			es_search()
			
		})
		
	}
	
	//Function for printing the search results as table
	function fill_table(data){
		var t=$("#result_table");
		t.html("");
		$.each(data,function(){
			t.append('<tr data-private="'+(this.private && ! this.readable)+'" data-id="'+this.key+'"></tr>');
			r=t.find("tr").last()
			if(this.readable){
				r.append('<td><a title="go to directory" class="icon-toggle svg" href="../files/?fileid='+this.key+
						'" style="display: block;">&nbsp;</a></td>'+
						'<td>'+this.path+'</td>');
			} else if(this.deleted){
				r.append('<td class="icon-delete svg"></td><td>'+this.path+'</td>');
				
			} else {
				r.append('<td class="icon-password svg"></td><td>'+this.path+'</td>');
			}
			r.append('<td></td>');
			td=r.find("td").last()
			for(var key in this.previews){
				if(this.previews[key].length) td.append("<b>"+key+":</b> ")
				for(var i=0;i<this.previews[key].length;i++){
					td.append(this.previews[key][i]+' ');
				}
				if(this.previews[key].length) td.append("<br/>")
			}
			r.append('<td></td>');
			td=r.find("td").last()
			if(! this.private){
				if(this.thumb) td.append('<img src="data:image/png;base64,'+this.thumb+'" style="max-width:100%;height:auto;">')
			}
			r.append('<td>'+this.score+'</td>');

		});
		$("tr[data-private='false']").click(function(){
			var id=$(this).attr('data-id')
			$.ajax({
			    	type: 'GET',
			    	url: OC.generateUrl('apps/metadatarepo/query/'+id),
			    	success: function (data) {
			    		t=$("#details-text");
			    		t.html('<h2>'+data.name+'</h2');
			    		if(data.file_path) t.append('<p>Path: <a title="go to directory" href="../files/?fileid='+data.key+
							'">'+data.file_path+'</a></p>')
			    		else t.append('<p>Path: '+data.path+'</p>')
			    		t.append('<p>User: '+data.user+'</p>')
			    		var lm=new Date(data.lastModified*1000)
			    		t.append('<p>Last modified: '+lm.toLocaleString()+'</p>')
			    		t.append('<hr><p>'+format_content(data.content,$("#search_field").val())+'</p>')
			    		if(data.has_image) t.append('<hr><img src="image/'+id+'" alt="data preview image" style="max-width:100%;height:auto;">')
			    		$("#details").show()
			    	}
			});

			
		})
		
	}
	
	//function for the search
	//calls fill_table and print_pagination
	function es_search(redrawPagination=true){
		if(! $("#search_field").val()) $("#search_field").val("*")
		if(redrawPagination) startPage=0;
        $.ajax({
            type: 'GET',
            url: OC.generateUrl('apps/metadatarepo/query'),
            data: {
                'query': $("#search_field").val(),
                'startPage': startPage,
                'filter' : filter,
                'fields' : fields,
                'hitsPerPage' : hitsPerPage
            },
            success: function (data) {
            	$.each(data.aggs,function(){
            		fill_field(this.key,this.title,this.results);
            	})
            	fill_table(data.hits);
            	totalHits=data.totalHits;
            	if(redrawPagination) print_pagination();
            }
        });
		return false;
	}

	//Register close function for hit details
	$("#details").click(function(){
		$(this).hide();
	})

	//register event for submit
	$("#search_form").submit(function(event){
		es_search();
		event.preventDefault();
	});
	
	//focus and autocomplete for search_field
	$("#search_field").focus();
	$("#search_field").autocomplete({
	      source: function( request, response ) {
	        $.ajax({
	          url: OC.generateUrl('apps/metadatarepo/query/words'),
	          //dataType: "json",
	          data: {
	            'q': request.term,
	            'filter': filter
	          },
	          success: function( data ) {
	            response( data );
	          }
	        });
	      },
	      minLength: 3,
	      select: function( event, ui ) {
	    	  $("#search_field").val(ui.item.value)
	        es_search();
	      }
		}).keyup(function (e) {
	        if(e.which === 13) {
	            $('#search_field').autocomplete('close');
	        }            
	    });

	//Start the application
	//load fieds und run search
    $.ajax({
        type: 'GET',
        url: OC.generateUrl('apps/metadatarepo/query/fields'),
        data: {
            'selected': 1,
        },
        success: function (data) {
        	create_fields(data);
        	es_search();
        }
    });

});

