$(document).ready(function(){
	 
	// Select all the checkboxes
	$('.selectall').click(
		function(){
			$(this).parent().parent().parent().parent().find("input[name='item[]'], type='checkbox'").attr('checked', $(this).is(':checked'));   
		}
	);
	
	// Select all the checkboxes
	$('.selectalltlds').click(
			function(){
				$(this).parent().parent().parent().parent().find("input[name='tlds[]'], type='checkbox'").attr('checked', $(this).is(':checked'));   
			}
	);
	
	// Select box redirector
	 $(".redirect").change( function() {
		 window.location.replace($(this).val());
	 });
	 
	// Clear the domain text box
	$(".www-input").click(function () {
		$(this).val('');
	});
	
	// Highlight the row of a table
	$('.highlight').each(function() {
		var options = {};
		$(this).effect('highlight', options, 2000);
	});
	
    // Cart management
	$('.checkdomain').each(function() {
		var domain = $(this).attr("rel");
	      var item = $(this);
	      $.post('/products/checkdomain/', {fulldomain: domain}, function(data){
	    	  if(data.isavailable){
	    		  item.addClass('available');
	    		  item.append('<input type="hidden" name="domstatus[]" value="1">');
	    	  }else{
	    		  item.addClass('notavailable');
	    		  item.append('<input type="hidden" name="domstatus[]" value="0">');
	    	  }
	      }, 'json');
	});
	
	$("#products").change(function () {
		$.post('/admin/orders/getproductinfo/id/' + $("#products").val(), {}, function(data){
			$('#price').empty();
			$('#description').empty();
			
			if(data.name) {
				$('#description').val(data.name);
			}
			
			if(data.price_1) {
				$('#price').val(data.price_1);
			}
			
		}, 'json');
	}); 
	
	$("#legalform").change(function () {
		var id = $("#legalform").val();
		if(id > 0){
			$.post('/default/cart/getcompanytypes/id/' + $("#legalform").val(), {}, function(data){
				$('#company_type_id').empty();
				$.each(data, function(name,value){
					$('#company_type_id').append('<option value="' + name + '">' + value + '</option>');
				});
			}, 'json');
		}else{
			$('#company_type_id').empty();
		}
	}); 
});