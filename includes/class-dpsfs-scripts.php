<?php
declare(strict_types=1);

/**
 * Scripts functionality
 *
 * @package Detailed Player Stats for SportsPress
 * @author Savvas
 */
class DPSFS_Scripts {

	/**
	 * Script handle for AJAX functionality.
	 *
	 * @var string
	 */
	private const SCRIPT_HANDLE = 'player_season_matches_ajax';

	/**
	 * Style handle for frontend styles.
	 *
	 * @var string
	 */
	private const STYLE_HANDLE = 'player_season_matches_style';

	/**
	 * Constructor.
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
	public function dpsfs_adding_scripts(): void {
		if ( $this->should_load_scripts() ) {
			// Load ThickBox library if required for popups.
			add_thickbox();

			$this->enqueue_script();
			$this->enqueue_style();
		}
	}

	/**
	 * Check if scripts should be loaded on current page.
	 *
	 * @return bool
	 */
	private function should_load_scripts(): bool {
		// Check if it's a single player page.
		if ( is_singular( 'sp_player' ) ) {
			return true;
		}

		// Check if current post contains relevant shortcodes.
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return false;
		}

		$post_content = get_post_field( 'post_content', $post_id );
		if ( ! $post_content ) {
			return false;
		}

		return has_shortcode( $post_content, 'player_statistics' ) || has_shortcode( $post_content, 'player' );
	}

	/**
	 * Enqueue the main JavaScript file.
	 *
	 * @return void
	 */
	private function enqueue_script(): void {
		$script_path = DPSFS_PLUGIN_DIR . 'assets/js/front.js';
		$script_url  = DPSFS_PLUGIN_URL . 'assets/js/front.js';

		// Check if script file exists.
		if ( ! file_exists( $script_path ) ) {
			error_log( 'DPSFS: Script file not found: ' . $script_path );
			return;
		}

		// Get file modification time for cache busting.
		$version = filemtime( $script_path );
		if ( false === $version ) {
			$version = '1.8.0';
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			$script_url,
			array( 'jquery' ),
			$version,
			true
		);

		// Localize script with AJAX URL, nonce, and admin URL for loading image.
		wp_localize_script(
			self::SCRIPT_HANDLE,
			'the_ajax_script',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dpsfs_player_statistics_league_ajax' ),
				'adminUrl' => admin_url(),
			)
		);
	}

	/**
	 * Enqueue the main CSS file.
	 *
	 * @return void
	 */
	private function enqueue_style(): void {
		$style_path = DPSFS_PLUGIN_DIR . 'assets/css/front.css';
		$style_url  = DPSFS_PLUGIN_URL . 'assets/css/front.css';

		// Check if style file exists.
		if ( ! file_exists( $style_path ) ) {
			error_log( 'DPSFS: Style file not found: ' . $style_path );
			return;
		}

		// Get file modification time for cache busting.
		$version = filemtime( $style_path );
		if ( false === $version ) {
			$version = '1.8.0';
		}

		wp_enqueue_style(
			self::STYLE_HANDLE,
			$style_url,
			array(),
			$version
		);
	}
}

new DPSFS_Scripts();
