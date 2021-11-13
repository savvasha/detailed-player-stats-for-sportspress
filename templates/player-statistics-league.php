<?php
/**
 * Player Statistics (Advanced) template for Single League
 * This template is modified copy from sportspress/templates/player-statistics-league.php
 */

//Protection from certain types of misuse, malicious or otherwise of ajax callings
$nonce = wp_create_nonce('dpsfs_player_statistics_league_ajax');

//Get the detailed stats mode from settings
$mode = get_option( 'dpsfs_player_statistics_mode', 'popup' );

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

$player_assignments = get_post_meta ( $player_id, 'sp_assignments', false);

foreach( $data as $season_id => $row ):

	$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';
	
	//Get some more info
	$competition_name = __( 'Career', 'sportspress' );
	if ( -1 != $season_id )
		$season_object = get_term_by( 'id', $season_id, 'sp_season' );
	
	if ( isset( $league_id ) ) {
		$league_object = get_term_by( 'id', $league_id, 'sp_league' );
	}else{
		$league_id = 0;
	}		
	
	if ( isset( $season_object ) && isset( $league_object ) && !$show_career_totals )
		$competition_name = $league_object->name . ' ' . $season_object->name;
	
	$team_id = null;
	if ( -1 != $season_id && !$show_career_totals ) {
		if ( $player_assignments ) {
			$search_text = $league_id . '_' . $season_id  . '_';
			$matches = array_filter( $player_assignments, function( $el ) use ( $search_text ) {
				return ( strpos( $el, $search_text ) !== false );
			});
			if ( !empty( $matches ) ){
				$team_id = (int) explode( $search_text, reset( $matches ) )[1];
			}
		}else{
			$team_object = get_page_by_title ( strip_tags( $row['team'] ), OBJECT, 'sp_team' );
			$team_id = $team_object->ID;
		}
	}
	
	foreach( $labels as $key => $value ):
		if ( 'name' == $key && -1 != $season_id && !$show_career_totals ) {
			$output .= '<td class="data-' . $key . ( -1 === $season_id ? ' sp-highlight' : '' ) . '"><button data-season_id="' . $season_id . '" data-league_id="' . $league_id . '" data-player_id="' . $player_id . '" data-team_id="' . $team_id . '" data-nonce="' . $nonce . '" data-competition_name="' . $competition_name . '" data-player_name="' . esc_html( get_the_title( $player_id ) ) . '" class="player-season-stats-' . $mode . '">' . sp_array_value( $row, $key, '' ) . '</button></td>';
		}elseif ( isset( $hide_teams ) && 'team' == $key ){
			continue;
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
	<?php echo wp_kses_post( $output ); ?>
	<center><span id="loading_<?php echo esc_attr( $league_id ); ?>" style="display:none;" class="color:blue; dashicons spin dashicons-update-alt"></span></center>
	<div class="player_events_inline" id="player_events_inline_<?php echo esc_attr( $league_id ); ?>" style="display:none;"></div>
</div>

