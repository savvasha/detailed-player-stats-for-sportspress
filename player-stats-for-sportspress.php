<?php
/**
Plugin Name: Player Stats for SportsPress
Description: An advanced player per season stats template.
Author: Savvas
Author URI: https://profiles.wordpress.org/savvasha/
Version: 1.0.0
Requires at least: 5.3
Requires PHP: 7.2
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Player_Stats_For_SportsPress' ) ) :

/**
 * Main Player Stats For SportsPress Class
 *
 * @class Player_Stats_For_SportsPress
 * @version	1.0.0
 */
class Player_Stats_For_SportsPress {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();
		
		// Include required files
		$this->includes();
		
		// Hooks
		add_action( 'wp_ajax_player_season_matches', array( $this, 'player_season_matches' ) );
		add_action( 'wp_ajax_nopriv_player_season_matches', array( $this, 'player_season_matches' ) );//for users that are not logged in.
		
		add_filter( 'sportspress_player_templates', array( $this, 'templates' ) );
		
	}
	
	/**
	 * Define constants
	*/
	private function define_constants() {
		if ( !defined( 'PSFS_PLUGIN_BASE' ) )
			define( 'PSFS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
		
		if ( !defined( 'PSFS_PLUGIN_DIR' ) )
			define( 'PSFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		
		if ( !defined( 'PSFS_PLUGIN_URL' ) )
			define( 'PSFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}
	
	/**
	 * Include required files
	*/
	private function includes() {
			//load the needed scripts and styles
			include( PSFS_PLUGIN_DIR . '/includes/class-psfs-scripts.php' );
	}
	
	/**
	 * Add templates to player layout.
	 *
	 * @return array
	 */
	public function templates( $templates = array() ) {
		$templates['psfs_statistics'] = array(
			'title' => __( 'Statistics (Advanced)', 'sportspress' ),
			'option' => 'sportspress_player_show_psfs_statistics',
			'action' => array( $this, 'output' ),
			'default' => 'yes',
		);
		
		return $templates;
	}
	
	/**
	 * Output Statistics (Advanced) template.
	 *
	 * @access public
	 * @return void
	 */
	public function output() {
		sp_get_template( 'player-psfs-statistics.php', array(), '', PSFS_PLUGIN_DIR . 'templates/' );
	}
	
	/**
	 * Output and a player specific event list.
	 *
	 * @access public
	 * @return void
	 */
	public function player_season_matches() {
		if ( !wp_verify_nonce( $_REQUEST['nonce'], 'player_psfs_statistics_league_ajax')) {
			exit("Something went wrong...");
		}
		if( isset( $_REQUEST['player_id'] ) ) {
			$this->competition_name = esc_attr( $_REQUEST['competition_name'] );
			$this->league_id = intval( $_REQUEST['league_id'] );
			$this->season_id = intval( $_REQUEST['season_id'] );
			$this->team_id = intval( $_REQUEST['team_id'] );
			$this->player_id = intval( $_REQUEST['player_id'] );
			
			$args = array(
				'player' => $this->player_id,
				'league' => $this->league_id,
				'season' => $this->season_id,
				'team' => $this->team_id,
				//'title' => $this->competition_name,
				'title_format' => 'homeaway',
				'time_format' => 'combined',
				'columns' => array( 'event', 'time', 'results' ),
				'order' => 'ASC',
			);
			add_action( 'sportspress_event_list_head_row', array( $this, 'player_stats_head_row' ), 20 );
			add_action( 'sportspress_event_list_row', array( $this, 'player_stats_body_row' ), 20, 2 );
			sp_get_template( 'event-list.php', $args );

			wp_die();
		}
	}
	
	public function player_stats_head_row( $usecolumns ) {
		echo '<th class="data-stats">' . __( 'Performances', 'sportspress' ) . '</th>';
		echo '<th class="data-minutes">' . __( 'Minutes', 'sportspress' ) . '</th>';
	}
	
	public function player_stats_body_row( $event, $usecolumns ) {
		echo '<td class="data-stats">';
			$stats = $this->get_player_match_performance( (int)$this->player_id, $event->ID, $this->team_id );
			echo wp_kses_post( $stats );
		echo '</td>';
		
		echo '<td class="data-stats">';
			$minutes = $this->get_player_match_minutes( (int)$this->player_id, $event->ID );
			echo esc_attr( $minutes ) . '\'';
		echo '</td>';
	}
	
	private function get_player_match_performance( $player_id, $match_id = null, $team_id = null ) {
		$player_match_performance = null;
		$team_performance = (array)get_post_meta( $match_id, 'sp_players', true );
		
		if ( !isset ( $team_performance[ $team_id ] ) )
			return $player_match_performance;
		
		foreach ( $team_performance[ $team_id ] as $tplayer_id => $performances ) {
			if ( $tplayer_id == $player_id ) {
				foreach ( $performances as $key => $times ) {
					if ( in_array( $key, array( 'sub', 'status', 'number', 'position' ) ) ) continue;
					
					if ( $post = get_page_by_path( $key, OBJECT, 'sp_performance' ) ) {
						$performance_id = $post->ID;
					}
					$icon = '';
					$icon = apply_filters( 'sportspress_event_performance_icons', $icon, $performance_id, 1 );

					$player_match_performance .= str_repeat( $icon, (int)$times );
				}
			}
		}
		
		return $player_match_performance;
	}
	
	private function get_player_match_minutes( $player_id, $match_id = null, $team_id = null ) {
		$team_performance = (array)get_post_meta( $match_id, 'sp_players', true );
		$timeline = (array)get_post_meta( $match_id, 'sp_timeline', true );
		$sendoffs = array();
		$minutes = get_post_meta( $match_id, 'sp_minutes', true );
		if ( '' === $minutes ) $minutes = get_option( 'sportspress_event_minutes', 90 );
		$played_minutes = 0;
		
		// Get performance labels
		$args = array(
			'post_type' => array( 'sp_performance' ),
			'numberposts' => 100,
			'posts_per_page' => 100,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'sp_format',
					'value' => 'number',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => 'sp_format',
					'value' => array( 'equation', 'text' ),
					'compare' => 'NOT IN',
				),
			),
		);

		$performances = get_posts( $args );
		
		foreach ( $performances as $performance ) {
			$sendoff = get_post_meta( $performance->ID, 'sp_sendoff', true );
			if ( $sendoff ) {
				$sendoffs[] = $performance->post_name;
			}
		}

		foreach ( $team_performance as $team_id => $players ) {
			if ( is_array( $players ) && array_key_exists( $player_id, $players ) ) {
				$player_performance = sp_array_value( $players, $player_id, array() );
				
				// Continue if active in event
				if ( sp_array_value( $player_performance, 'status' ) != 'sub' || sp_array_value( $player_performance, 'sub', 0 ) ) {
					$played_minutes = (int) $minutes;
					// Adjust for substitution time
					if ( sp_array_value( $player_performance, 'status' ) === 'sub' ) {
						// Substituted for another player
						$timeline_performance = sp_array_value( sp_array_value( $timeline, $team_id, array() ), $player_id, array() );
						
						if ( empty( $timeline_performance ) ) continue;
						foreach ( $sendoffs as $sendoff_key ){
							if ( ! array_key_exists( $sendoff_key, $timeline_performance ) ) continue;
							$sendoff_times = sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $player_id ), $sendoff_key );
							$sendoff_times = array_filter( $sendoff_times );
							$sendoff_time = end( $sendoff_times );
							if ( ! $sendoff_time ) $sendoff_time = 0;

							// Count minutes until being sent off
							$played_minutes = (int) $sendoff_time;
						}
						
						// Subtract minutes prior to substitution
						$substitution_time = sp_array_value( sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $player_id ), 'sub' ), 0, 0 );
						$played_minutes -= (int) $substitution_time;
					}else{
						// Starting lineup with possible substitution
						$subbed_out = false;
						foreach ( $timeline as $timeline_team => $timeline_players ){
							if ( ! is_array( $timeline_players ) ) continue;
							foreach ( $timeline_players as $timeline_player => $timeline_performance ){
								if ( 'sub' === sp_array_value( sp_array_value( $players, $timeline_player, array() ), 'status' ) && $player_id === (int) sp_array_value( sp_array_value( $players, $timeline_player, array() ), 'sub', 0 ) ):
									$substitution_time = sp_array_value( sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $timeline_player ), 'sub' ), 0, 0 );
									if ( $substitution_time ):

										// Count minutes until substitution
										$played_minutes = (int) $substitution_time;
										$subbed_out = true;
									endif;
								endif;
							}
							
							// No need to check for sendoffs if subbed out
							if ( $subbed_out ) continue;
							
							// Check for sendoffs
							$timeline_performance = sp_array_value( $timeline_players, $player_id, array() );
							if ( empty( $timeline_performance ) ) continue;
							foreach ( $sendoffs as $sendoff_key ){
								if ( ! array_key_exists( $sendoff_key, $timeline_performance ) ) continue;
								$sendoff_times = (array) sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $player_id ), $sendoff_key, array() );
								$sendoff_times = array_filter( $sendoff_times );
								$sendoff_time = end( $sendoff_times );
								if ( false === $sendoff_time ) continue;

								// Count minutes until being sent off
								$played_minutes = (int) $sendoff_time;
							}
						}
					}
				}
			}
			
		}
		return $played_minutes;
	}

}

endif;

new Player_Stats_For_SportsPress();
