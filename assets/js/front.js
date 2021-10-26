jQuery(document).ready(function($) {
	$("button.player-season-stats-popup").click(function() {

		//Confirm that div#player_events is empty
		$( '#player_events' ).empty();
		
		var competition_name = $(this).data('competition_name');
		var player_name = $(this).data('player_name');
		var league_id = $(this).data('league_id');
		var season_id = $(this).data('season_id');
		var team_id = $(this).data('team_id');
		var player_id = $(this).data('player_id');
		var nonce = $(this).data('nonce');
		
		//Call player_season_matches() function and return the response to div#player_events and from there to thickbox
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
				$('#player_events').html( response );
				tb_show(player_name + ' @ ' + competition_name, '#TB_inline?&width=640&height=300&inlineId=player_events', false);
			},
			error : function (response){
			}
		})
	})
	
	$("button.player-season-stats-inline").click(function() {

		var competition_name = $(this).data('competition_name');
		var player_name = $(this).data('player_name');
		var league_id = $(this).data('league_id');
		var season_id = $(this).data('season_id');
		var team_id = $(this).data('team_id');
		var player_id = $(this).data('player_id');
		var nonce = $(this).data('nonce');
		
		//Confirm that div#player_events is empty
		$( '#player_events_'+league_id ).empty();
		
		//Call player_season_matches() function and return the response to div#player_events and from there to thickbox
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
				$( '#player_events_'+league_id ).html( response );
			},
			error : function (response){
			}
		})
	})
});