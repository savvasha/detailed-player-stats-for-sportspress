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
		//add_filter( 'sportspress_player_templates', array( $this, 'templates' ) );
		
	}
	
	/**
	 * Define constants
	*/
	private function define_constants() {
		if ( !defined( 'PSFS_PLUGIN_BASE' ) )
			define( 'PSFS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
	}

}

endif;

new Player_Stats_For_SportsPress();
