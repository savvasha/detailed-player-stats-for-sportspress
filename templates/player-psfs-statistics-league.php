<?php
/**
 * Player Statistics (Advanced) template for Single League
 * This template is modified copy from sportspress/templates/player-statistics-league.php
 */

//Protection from certain types of misuse, malicious or otherwise of ajax callings
$nonce = wp_create_nonce('player_psfs_statistics_league_ajax');

//Load needed script file
//add_thickbox();
//wp_enqueue_script( 'player_season_matches_ajax', PSFS_PLUGIN_URL . 'assets/js/player-stats-for-sportspress.js', array( 'jquery' ) );

//var_dump($GLOBALS['wp_scripts']->registered);

// The first row should be column labels
$labels = $data[0];

// Remove the first row to leave us with the actual data
unset( $data[0] );

// Skip if there are no rows in the table
if ( empty( $data ) )
	return;

$output = '<h4 class="sp-table-caption">' . $caption . '</h4>' .
	'<div class="sp-table-wrapper">' .
	'<table class="sp-player-statistics sp-data-table' . ( $scrollable ? ' sp-scrollable-table' : '' ) . '">' . '<thead>' . '<tr>';

foreach( $labels as $key => $label ):
	if ( isset( $hide_teams ) && 'team' == $key )
		continue;
	$output .= '<th class="data-' . $key . '">' . $label . '</th>';
endforeach;

$output .= '</tr>' . '</thead>' . '<tbody>';

$i = 0;

foreach( $data as $season_id => $row ):

	$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';
	
	$team_object = get_page_by_title ( strip_tags( $row['team'] ), OBJECT, 'sp_team' );
	
	$league_object = get_term_by( 'id', $league_id, 'sp_league' ); 
	$season_object = get_term_by( 'id', $season_id, 'sp_season' ); 
	$competition_name = $league_object->name . ' ' . $season_object->name;
	
	$ajax_url = add_query_arg( 
		array(
			'TB_iframe' => 'true',
			'nonce' => $nonce,
			'action' => 'player_season_matches', 
			'season_id' => $season_id, 
			'league_id' => $league_id, 
			'player_id' => $player_id, 
			'team_id' => $team_object->ID, 
			'competition_name' => $competition_name, 
		), 
		admin_url('admin-ajax.php')
	);

	foreach( $labels as $key => $value ):
		if ( isset( $hide_teams ) && 'team' == $key )
			continue;
		if ( 'name' == $key ) {
			$output .= '<td class="data-' . $key . ( -1 === $season_id ? ' sp-highlight' : '' ) . '"><a href="#TB_inline?&width=600&height=550&inlineId=player_events" class="thickbox"><button data-season_id="' . $season_id . '" data-league_id="' . $league_id . '" data-player_id="' . $player_id . '" data-team_id="' . $team_object->ID . '" data-nonce="' . $nonce . '" data-competition_name="' . $competition_name . '" class="player-season-stats">' . sp_array_value( $row, $key, '' ) . '</a></button></td>';
			//$output .= '<td class="data-' . $key . ( -1 === $season_id ? ' sp-highlight' : '' ) . '"><a class="thickbox" href="' . $ajax_url . '"><button class="player-season-stats">' . sp_array_value( $row, $key, '' ) . '</button></a></td>';
		}else{
			$output .= '<td class="data-' . $key . ( -1 === $season_id ? ' sp-highlight' : '' ) . '">' . sp_array_value( $row, $key, '' ) . '</td>';
		}
	endforeach;

	$output .= '</tr>';

	$i++;

endforeach;

$output .= '</tbody>' . '</table>' . '</div>';
?>
<div class="sp-template sp-template-player-statistics">
	<?php echo $output; ?>
</div>
<div id="player_events"><p>savvas</p></div>

