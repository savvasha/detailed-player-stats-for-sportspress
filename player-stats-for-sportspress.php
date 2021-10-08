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
			exit("Επιλέξτε μια σαιζόν για να εμφανιστούν οι αγωνιστικές υποχρεώσεις του ποδοσφαιριστή...");
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
				'title' => $this->competition_name,
				'title_format' => 'homeaway',
				'time_format' => 'combined',
				'columns' => array( 'event', 'time', 'results' ),
				'order' => 'ASC',
			);
			//add_action( 'sportspress_event_list_head_row', array( $this, 'player_stats_head_row' ), 20 );
			//add_action( 'sportspress_event_list_row', array( $this, 'player_stats_body_row' ), 20, 2 );
			sp_get_template( 'event-list.php', $args );

			wp_die();
		}
	}

}

endif;

new Player_Stats_For_SportsPress();
