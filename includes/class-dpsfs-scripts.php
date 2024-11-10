<?php
/**
 * Scripts functionality
 *
 * @package Detailed Player Stats for SportsPress
 * @author Savvas
 */
class DPSFS_Scripts {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Load needed scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'dpsfs_adding_scripts' ) );

	}

	/**
	 * Enqueue scripts and styles needed for player statistics.
	 *
	 * This function checks if the page is displaying a single player
	 * or contains the player_statistics shortcode. If true, it adds
	 * the necessary scripts and styles.
	 *
	 * @return void
	 */
	public function dpsfs_adding_scripts() {
	    if ( is_singular( 'sp_player' ) || has_shortcode( get_post_field( 'post_content', get_the_ID() ), 'player_statistics' ) ) {

	        // Load ThickBox library if required for popups.
	        add_thickbox();

	        // Enqueue script with dynamic versioning based on file modification time.
	        wp_enqueue_script(
	            'player_season_matches_ajax',
	            DPSFS_PLUGIN_URL . 'assets/js/front.js',
	            array( 'jquery' ),
	            filemtime( DPSFS_PLUGIN_DIR . 'assets/js/front.js' ),
	            true
	        );

	        // Localize script with ajax URL.
	        wp_localize_script(
	            'player_season_matches_ajax',
	            'the_ajax_script',
	            array(
	                'ajaxurl' => admin_url( 'admin-ajax.php' ),
	            )
	        );

	        // Enqueue style with dynamic versioning.
	        wp_enqueue_style(
	            'player_season_matches_style',
	            DPSFS_PLUGIN_URL . 'assets/css/front.css',
	            array(),
	            filemtime( DPSFS_PLUGIN_DIR . 'assets/css/front.css' )
	        );
	    }
	}

}

new DPSFS_Scripts();
