<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Nested Sets Example Page</title>
		<link type="text/css" href="<?php echo base_url(); ?>assets/css/smoothness/jquery-ui-1.8.17.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui-1.8.17.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.ui.nestedSortable.js"></script>

		<style type="text/css">

		body { font-size: 62.5%; }
		#dialog-form label,#dialog-form  input { display:block; }
		 input.text { margin-bottom:12px; width:95%;	}
			fieldset { padding:0; border:0;	 }
		 h1 { font-size: 1.1px; margin: .6em 0; }
		.ui-dialog .ui-state-error { padding: .3em; }
		.validateTips { border: 1px solid transparent; padding: 0.3em; }
		
		.placeholder {
			background-color: #cfcfcf;
		}
		.ui-nestedSortable-error {
			background:#fbe3e4;
			color:#8a1f11;
		}
		ol {
			margin: 0;
			padding: 0;
			padding-left: 30px;
			font-size: 14px;
		}
		ol.sortable, ol.sortable ol {
			margin: 0 0 0 25px;
			padding: 0;
			list-style-type: none;
		}
		ol.sortable {
			margin: 1em 0;
		}
		.sortable li {
			margin: 7px 0 0 0;
			padding: 0;
		}

		.sortable li div	{
			border: 1px solid black;
			padding: 3px;
			margin: 0;
			/*cursor: move;*/
		}
		.sortable li div span	 {
			cursor: pointer;
		}	
		.deletebutton {
			float:right;position:relative;right:0px;top:0px;display:block;width:4px;height:4px;margin:0;padding:0;background-image:url('http://localhost:8888/ci/assets/img/Delete.gif');border: 0px solid transparent !important;cursor:pointer
		}
		</style>	
	</head>
	<body>
		<script>


		$(document).ready(function() {
		
		var name = $( "#name" )
		var bNested = false;
		refresh_menu();
		
		// Add button
		$( "#addnew" )
			.button()
			.click(function() {$( "#dialog-form" ).dialog( "open" );
		});
		
		// Edit button
		$( "#edit" )
			.button()
			.click(function() {
				if (bNested == false){
					bNested = true;	
					$("span", this).text("edit: off");		
				} else {
					bNested = false;
					$("span", this).text("edit: on");
				}
				refresh_menu();
		});
		
		// dialog form
		$( "#dialog-form" ).dialog({
			autoOpen: false,
			height: 175,
			width: 350,
			modal: true,
			buttons: {
				"Add new section": function() {
			        $.ajax({
			        	url: "<?php echo base_url(); ?>index.php/nested/ajax_addnew",
			        	type: "POST",
			        	data: ({ title : name.val()}),
			        	success: function(data){refresh_menu();}
			        });	
					refresh_menu();
					$(this).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {}
		});
				
		// Making nested 
		function makeNested() { 
			$('ol.sortable').nestedSortable('destroy');
			$('ol.sortable').nestedSortable({
				disableNesting: 'no-nest',
				forcePlaceholderSize: true,
				handle: 'span.handle',
				helper:	'clone',
				items: 'li',
				maxLevels: 4,
				opacity: .6,
				placeholder: 'placeholder',
				revert: 250,
				tabSize: 25,
				tolerance: 'pointer',
				toleranceElement: '> div',
				
				update : function (event, ui)  {
				   var result = $('ol.sortable').nestedSortable('toArray');
				   var item = ui.item;
				   var moveto = "";
				   var type = "";
				   
						if ($(item).index() != 0){
						moveto = $(item).prev().attr("id");
						type = 'addnext';
						} else {
						moveto =($($(item).parents().get(0)).attr("id") == "root") ? "1" : $($(item).parents().get(1)).attr("id") ;
						type = 'appendfirst';
						}
					   					  
					$.ajax({
					  url: "<?php echo base_url(); ?>index.php/nested/ajax_sort",
					  type: "POST",
					  data: ({ item : $(item).attr("id").match(/[0-9]+/g)[0], moveto : moveto.match(/[0-9]+/g)[0], type : type}),
					  success: function(data){refresh_menu();}
					 });
				   }
				   
			});
			}
			
	
			// getting Json and parsing menu..
			function refresh_menu() { 
			 $.ajax({
				 url: "<?php echo base_url(); ?>index.php/nested/get_json",
			  type: "POST",
			  data: ({ title : $("#t_addnew").val()}),
			  success: function(data){ 
			   var items = $.parseJSON(data);
			   var k = 1, c = items.length, j, nestedhtml = '';
			  	$.each(items, function(i,item){ // 
			  	 if(item.depth == j)	 {nestedhtml += '</li>';}
			  	 else if(parseInt(item.depth) > j){nestedhtml += '<ol>';}
			  	 else if(parseInt(item.depth) < j){nestedhtml += Array(Math.abs(parseInt(item.depth)-parseInt(j+1))).join('</li></ol>') + "</li>";}	
				 nestedhtml += '<li id="item_' + item.id + '" class="ui-state-default"><div><span class="handle" ><a class="ui-icon ui-icon-grip-dotted-vertical" style="float:left;"></a></span>' + item.title + '<small> (' + item.num_pages + ' item)</small><span class="lft" id="'+item.lft+'"></span></div>';
				 if(k == c) { nestedhtml += Array(Math.abs(parseInt(item.depth)-1)).join('</li></ol>') + "</li>" };
				 k++;
				 j = parseInt(item.depth);
				 });
				 $('#root').html(nestedhtml);
				 $("<div class='deletebutton'></div>").appendTo("#root.sortable li div");
				 $(".deletebutton").hide();
				 
				 if (bNested == false){
					$(".handle").attr("style","display:none");
				 }else{
					$('#root.sortable li div').bind({
					 mouseenter: function() {$(this).find(".deletebutton").show();},
					 mouseleave: function() {$(this).find(".deletebutton").hide();},});	
					 makeNested();
				 }
				 
				 $('.deletebutton').bind('click', function() {
					if (confirm("Are you sure to delete this item ?")==true)
					{	
  				 	  $.ajax({
					  	 url: "<?php echo base_url(); ?>index.php/nested/ajax_delete",
					  	 type: "POST",
					  	 data: ({ lft : $(this).parent().find("span.lft").attr("id")}),
					  	 success: function(data){}
					  });	
					  refresh_menu();
					}
				  });
			  }
			 });	
			}
						
		});
		</script>
			<button id="edit">edit: on</button>
			<button id="addnew">add new item</button>

		<div style="width:480px;">
		<ol class="sortable" id="root">
		
		</ol>
		</div>		

<div id="dialog-form" title="Create new section">
	<p class="validateTips">All form fields are required.</p>

	<form>
	<fieldset>
		<label for="name">Title</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	</form>
</div>


		
	</body>
</html>


