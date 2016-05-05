$(document).ready(function(){
	$('.delete').click(function(e){
		var check = confirm('Are you sure you want to delete this?');
		if(check == null || !check){
			e.preventDefault();
			return false;
		}
		
	});
		
	$('.address-table .active-toggle').find('.toggle').click(function(e){
		var address = $(this).find('input[type="checkbox"]').data('address');
		var checked = !$(this).find('input[type="checkbox"]').is(':checked'); //reverse check
		
		var url = '/inventory/address/' + address + '/toggle';
		$.post(url, {toggle: checked}, function(data){
			if(typeof data.error != 'undefined'){
				alert(data.error);
				return false;
			}
			return true;
		});
	});
	
	$('.asset-balance-toggle').click(function(e){
		e.preventDefault();
		var asset = $(this).data('asset');
		if($(this).hasClass('open')){
			$(this).removeClass('open');
			$('#' + asset + '_addresses').hide();
			$(this).find('.down-toggle').removeClass('fa-chevron-down').addClass('fa-chevron-right');
		}
		else{
			$(this).addClass('open');
			$('#' + asset + '_addresses').show();
			$(this).find('.down-toggle').removeClass('fa-chevron-right').addClass('fa-chevron-down');
			
		}
	});
	
	$('.balance-table .active-toggle').find('.toggle').click(function(e){
		var asset = $(this).find('input[type="checkbox"]').data('asset');
		var checked = !$(this).find('input[type="checkbox"]').is(':checked'); //reverse check
		var url = '/inventory/asset/' + asset + '/toggle';
		$.post(url, {toggle: checked}, function(data){
			if(typeof data.error != 'undefined'){
				alert(data.error);
				return false;
			}
			return true;
		});
	});
    
    window.inventory_refresh_check = false;
    $('.btn.instant-address').click(function(e){
        if(!window.inventory_refresh_check){
            window.inventory_refresh_check = setInterval(function(){
                var url = '/inventory/check-refresh';
                $.get(url, function(data){
                    if(data.result){
                        location.reload();
                    }
                });
            }, 5000);
        }
    });
});
