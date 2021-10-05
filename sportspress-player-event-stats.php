<?php
/**
Plugin Name: SportsPress Player Event Stats per Season
Description: An advanced player stats template.
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

if ( ! class_exists( 'SportsPress_Advanced_Player_Stats' ) ) :

/**
 * Main SportsPress Advanced Player Stats
 *
 * @class SportsPress_Advanced_Player_Stats
 * @version	1.0.0
 */
class SportsPress_Advanced_Player_Stats {

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

}

endif;

new SportsPress_Advanced_Player_Stats();
