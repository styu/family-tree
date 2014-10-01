$(document).ready(function(){
	$('li').each(function(){
		
		var id = $(this).attr('id').substring(0,8);
		var member = $('div[name='+id+']');
		var info = $('#'+id);

		var distance = 10;
		var position = $(this).position();
		var top = position.top - 10;
		var left = position.left+40+ member.width();

	    var showtime = 1000;
		var hidetime = 250;
	    var hideDelay = 200;
	
	    var hideDelayTimer = null;
	
	    // tracker
	    var beingShown = false;
	    var shown = false;

		$(member, info).mouseover(function(){
			member.parent().css({color:'#3F82B5'});
			highlight(member.parent(), '#3F82B5');
			if (hideDelayTimer) clearTimeout(hideDelayTimer);
	
		      if (beingShown || shown) {
		        return;
		      } else {
				
		        beingShown = true;
			        info.css({
					  top: top,
					  left: left,
					  display: 'block'
			        })
			        .animate({
			          opacity: 1
			        }, showtime, 'swing', function() {
			          beingShown = false;
			          shown = true;
			        });
				
			}
		}).mouseout(function () {
			member.parent().css({color:'#ffffff'});
			highlight(member.parent(), '#ffffff');
		      if (hideDelayTimer) clearTimeout(hideDelayTimer);
		      
		      hideDelayTimer = setTimeout(function () {
		        hideDelayTimer = null;
		        info.animate({
		          left: '+=' + distance + 'px',
		          opacity: 0
		        }, hidetime, 'swing', function () {
		          shown = false;
		          info.css('display', 'none');
		        });
		      }, hideDelay);
		 });
	});
	function highlight(member, color){
		while (member.parent().attr('id') != "00000000p"){
			parent = member.parent();
			member = $('#'+parent.attr('id').substring(0, 8)+'m');
			member.css({color:color});
		}
	}
});