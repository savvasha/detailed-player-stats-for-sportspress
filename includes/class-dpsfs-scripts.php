<?php

/**
 * Scripts functionality
 * @package Player Stats for SportsPress
 * @author Savvas
 */
 
class PSFS_Scripts {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		//Load needed scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'dpsfs_adding_scripts' ) );

	}
	
	/**
	 * Load scripts and styles where needed
	 *
	 * @return void
	 */
	public function dpsfs_adding_scripts() {
		global $post;
		if ( is_singular('sp_player') || ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'player_statistics') ) ) {
			
			//Include thickbox libraries
			add_thickbox();
			
			//Needed for the ajaxify
			wp_enqueue_script( 'player_season_matches_ajax', DPSFS_PLUGIN_URL . 'assets/js/player-stats-for-sportspress.js', array( 'jquery' ) );
			wp_localize_script( 'player_season_matches_ajax', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php?lang='.get_bloginfo('language') ) ) );
			
			//Some css code
			wp_enqueue_style( 'player_season_matches_ajax',  DPSFS_PLUGIN_URL . '/assets/css/player-stats-for-sportspress.css' );
			
		}
		
	}
	
}

new PSFS_Scripts();