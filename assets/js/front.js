/* global jQuery, the_ajax_script, tb_show */
/* eslint-disable camelcase -- Identifiers mirror the PHP $_REQUEST keys and the HTML data-* attributes they are read from. */
jQuery( document ).ready( function( $ ) {
	$( 'button.player-season-stats-popup' ).click( function() {
		//Confirm that div#player_events is empty
		$( '#player_events' ).empty();

		const competition_name = $( this ).data( 'competition_name' );
		const player_name = $( this ).data( 'player_name' );
		const league_id = $( this ).data( 'league_id' );
		const season_id = $( this ).data( 'season_id' );
		const team_id = $( this ).data( 'team_id' );
		const player_id = $( this ).data( 'player_id' );
		const nonce = $( this ).data( 'nonce' );

		//Show loading info till ajax response is ready
		const original_button_text = $( this ).text();
		const $this = $( this );
		$this.html( '<img src="' + the_ajax_script.adminUrl + 'images/loading.gif" alt="loading.gif"/>' );

		//Call player_season_matches() function and return the response to div#player_events and from there to thickbox
		$.ajax( {
			url: the_ajax_script.ajaxurl,
			type: 'post',
			data: {
				action: 'player_season_matches',
				competition_name,
				league_id,
				season_id,
				team_id,
				player_id,
				nonce,
			},
			success( response ) {
				$this.text( original_button_text );
				$( '#player_events' ).html( response );
				tb_show( player_name + ' @ ' + competition_name, '#TB_inline?&width=640&height=300&inlineId=player_events', false );
			},
			error() {
				$this.text( 'ERROR' );
			},
		} );
	} );

	$( 'button.player-season-stats-inline' ).click( function() {
		const competition_name = $( this ).data( 'competition_name' );
		const league_id = $( this ).data( 'league_id' );
		const season_id = $( this ).data( 'season_id' );
		const team_id = $( this ).data( 'team_id' );
		const player_id = $( this ).data( 'player_id' );
		const nonce = $( this ).data( 'nonce' );

		//Confirm that div#player_events is empty
		$( '#player_events_inline_' + league_id ).empty();

		//Show the loading circle
		$( '#loading_' + league_id ).show();

		//Call player_season_matches() function and return the response to div#player_events and from there to thickbox
		$.ajax( {
			url: the_ajax_script.ajaxurl,
			type: 'post',
			data: {
				action: 'player_season_matches',
				competition_name,
				league_id,
				season_id,
				team_id,
				player_id,
				nonce,
			},
			success( response ) {
				$( '#loading_' + league_id ).hide();
				$( '#player_events_inline_' + league_id ).show();
				$( '#player_events_inline_' + league_id ).html( '&nbsp;' + '<a href="#" title="Close Table" id="#player_events_inline_close" class="dashicons dashicons-dismiss player-details-close-button"></a>' + response );
				$( '.player-details-close-button' ).click( function() {
					$( '#player_events_inline_' + league_id ).empty();
				} );
			},
			error() {
			},
		} );
	} );
} );
