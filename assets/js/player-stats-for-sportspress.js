jQuery(document).ready(function($) {
	$("button.player-season-stats").click(function() {
		//alert('This alert is displayed');
		//alert($(this).data('competition_name'));
		$( '#player_events' ).empty();
		/*$('html, body').animate({
			scrollTop: $( '#player_events' ).offset().top
		}, 800, function(){
			// Add hash (#) to URL when done scrolling (default click behavior)
			//window.location.hash = hash;
		});*/
		
		//$( '#loadingp' ).show();
		
		var competition_name = $(this).data('competition_name');
		var league_id = $(this).data('league_id');
		var season_id = $(this).data('season_id');
		var team_id = $(this).data('team_id');
		var player_id = $(this).data('player_id');
		var nonce = $(this).data('nonce');
		
		var ajax_call = $.ajax({
			url  : the_ajax_script.ajaxurl,
			type : 'post',
			data : {
				action: 'player_season_matches',
				competition_name: competition_name , 
				league_id: league_id,
				season_id: season_id,
				team_id: team_id,
				player_id: player_id,
				nonce: nonce
			},
			success : function( response ) {
				//$('#loadingp').hide();
				//$('#print_button_player_events').show();
				$('#player_events').html( response );
				tb_show(competition_name, '#TB_inline?&width=500&height=270&inlineId=player_events', false);
			},
			error : function (response){
				//$('#loadingp').hide();
				//$('#print_button_player_events').hide();
			}
		})
		//ajax_call.fail(function(response) { console.log('Failed ' + response); }); 
		//ajax_call.done(function(response) { console.log('Success ' + response); }); 
		//ajax_call.always(function(response) { console.log('Ajax Request complete: ' + response); });
	})
});