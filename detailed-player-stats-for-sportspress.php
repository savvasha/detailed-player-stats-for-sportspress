<?php
/**
 * Plugin Name: Detailed Player Stats for SportsPress
 * Description: An advanced player per season stats template.
 * Version: 1.2.2
 * Author: Savvas
 * Author URI: https://profiles.wordpress.org/savvasha/
 * Requires at least: 5.3
 * Requires PHP: 7.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl.html
 *
 * @package detailed-player-stats-for-sportspress
 * @category Core
 * @author savvasha
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Player_Stats_For_SportsPress' ) ) :

	/**
	 * Main Detailed Player Stats For SportsPress Class
	 *
	 * @class Detailed_Player_Stats_For_SportsPress
	 * @version 1.2.2
	 */
	class Detailed_Player_Stats_For_SportsPress {

		/**
		 * The plugins mode.
		 *
		 * @var string
		 */
		public static $mode;

		/**
		 * Constructor.
		 */
		public function __construct() {

			self::$mode = get_option( 'dpsfs_player_statistics_mode', 'popup' );

			// Define constants.
			$this->define_constants();

			// Include required files.
			$this->includes();

			// Hooks.
			add_action( 'wp_ajax_player_season_matches', array( $this, 'player_season_matches' ) );
			add_action( 'wp_ajax_nopriv_player_season_matches', array( $this, 'player_season_matches' ) );// for users that are not logged in.
			add_action( 'sportspress_admin_field_multigroupselect', array( $this, 'multigroupselect_settings' ) );
			add_action( 'sportspress_update_option_multigroupselect', array( $this, 'multigroupselect_settings_save' ) );

			add_filter( 'sportspress_locate_template', array( $this, 'shortcode_override' ), 10, 3 );
			add_filter( 'sportspress_player_settings', array( $this, 'add_settings' ) );

		}


		/**
		 * Define constants
		 */
		private function define_constants() {
			if ( ! defined( 'DPSFS_PLUGIN_BASE' ) ) {
				define( 'DPSFS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
			}

			if ( ! defined( 'DPSFS_PLUGIN_DIR' ) ) {
				define( 'DPSFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'DPSFS_PLUGIN_URL' ) ) {
				define( 'DPSFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
		}

		/**
		 * Include required files
		 */
		private function includes() {
			// load the needed scripts and styles.
			include DPSFS_PLUGIN_DIR . '/includes/class-dpsfs-scripts.php';
		}

		/**
		 * Shortcode override
		 *
		 * @param mixed $template The template path plus the name.
		 * @param mixed $template_name The template name.
		 * @param mixed $template_path The template path.
		 * @return string
		 */
		public function shortcode_override( $template = null, $template_name = null, $template_path = null ) {

			if ( 'player-statistics.php' === $template_name ) {
				$template_path = DPSFS_PLUGIN_DIR . 'templates/';
				$template      = $template_path . $template_name;
			}

			return $template;
		}

		/**
		 * Output a player specific event list.
		 *
		 * @access public
		 * @return void
		 */
		public function player_season_matches() {
			if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dpsfs_player_statistics_league_ajax' ) ) {
				exit( 'Something went wrong...' );
			}
			if ( isset( $_REQUEST['player_id'] ) ) {
				$this->competition_name = sanitize_text_field( $_REQUEST['competition_name'] );
				$this->league_id        = intval( $_REQUEST['league_id'] );
				$this->season_id        = intval( $_REQUEST['season_id'] );
				$this->team_id          = intval( $_REQUEST['team_id'] );
				$this->player_id        = intval( $_REQUEST['player_id'] );

				if ( 'inline' === self::$mode ) {
					$title = get_the_title( $this->player_id ) . ' @ ' . $this->competition_name;
				} else {
					$title = $this->competition_name;
				}

				$args = array(
					'player'       => $this->player_id,
					'league'       => $this->league_id,
					'season'       => $this->season_id,
					'team'         => $this->team_id,
					'title'        => $title,
					'title_format' => 'homeaway',
					'time_format'  => 'combined',
					'columns'      => array( 'event', 'time', 'results' ),
					'order'        => 'ASC',
				);
				add_action( 'sportspress_event_list_head_row', array( $this, 'player_stats_head_row' ), 20 );
				add_action( 'sportspress_event_list_row', array( $this, 'player_stats_body_row' ), 20, 2 );
				sp_get_template( 'event-list.php', $args );

				wp_die();
			}
		}

		/**
		 * Output the extra needed head row columns.
		 *
		 * @access public
		 * @param mixed $usecolumns The columns that are used.
		 * @return void
		 */
		public function player_stats_head_row( $usecolumns ) {
			if ( 'yes' === get_option( 'dpsfs_show_performances', 'yes' ) ) {
				echo '<th class="data-stats">' . esc_html__( 'Performances', 'sportspress' ) . '</th>';
			}

			if ( 'yes' === get_option( 'dpsfs_show_minutes', 'yes' ) ) {
				echo '<th class="data-minutes">' . esc_html__( 'Minutes', 'sportspress' ) . '</th>';
			}
		}

		/**
		 * Output the extra needed body row columns.
		 *
		 * @access public
		 * @param object $event The event object.
		 * @param mixed  $usecolumns The columns that are used.
		 * @return void
		 */
		public function player_stats_body_row( $event, $usecolumns ) {
			if ( 'yes' === get_option( 'dpsfs_show_performances', 'yes' ) ) {
				echo '<td class="data-stats">';
				$stats = $this->get_player_match_performance( (int) $this->player_id, $event->ID, $this->team_id );
				echo wp_kses_post( $stats );
				echo '</td>';
			}
			if ( 'yes' === get_option( 'dpsfs_show_minutes', 'yes' ) ) {
				echo '<td class="data-stats">';
				$minutes = $this->get_player_match_minutes( (int) $this->player_id, $event->ID );
				echo esc_attr( $minutes ) . '\'';
				echo '</td>';
			}
		}

		/**
		 * Return the player performances.
		 *
		 * @access private
		 * @param integer $player_id The Player ID.
		 * @param integer $match_id The Match ID.
		 * @param integer $team_id The Team ID.
		 * @return string
		 */
		private function get_player_match_performance( $player_id, $match_id = null, $team_id = null ) {
			$player_match_performance = null;
			$team_performance         = (array) get_post_meta( $match_id, 'sp_players', true );

			if ( ! isset( $team_performance[ $team_id ] ) ) {
				return $player_match_performance;
			}

			foreach ( $team_performance[ $team_id ] as $tplayer_id => $performances ) {
				if ( $tplayer_id === $player_id ) {
					foreach ( $performances as $key => $times ) {
						if ( in_array( $key, array( 'sub', 'status', 'number', 'position' ), true ) ) {
							continue;
						}
						$performance_id = 0;
						$post           = get_page_by_path( $key, OBJECT, 'sp_performance' );
						if ( $post ) {
							$performance_id = $post->ID;
						}
						$icon = '';
						if ( $performance_id && has_post_thumbnail( $performance_id ) ) {
							$icon = get_the_post_thumbnail( $performance_id, 'sportspress-fit-mini', array( 'title' => sp_get_singular_name( $performance_id ) ) );
						} else {
							$icon = apply_filters( 'sportspress_event_performance_icons', $icon, $performance_id, 1 );
						}

						$player_match_performance .= str_repeat( $icon, (int) $times );
					}
				}
			}

			return $player_match_performance;
		}

		/**
		 * Return the player minutes.
		 *
		 * @access private
		 * @param integer $player_id The Player ID.
		 * @param integer $match_id The Match ID.
		 * @param integer $team_id The Team ID.
		 * @return integer
		 */
		private function get_player_match_minutes( $player_id, $match_id = null, $team_id = null ) {
			$team_performance = (array) get_post_meta( $match_id, 'sp_players', true );
			$timeline         = (array) get_post_meta( $match_id, 'sp_timeline', true );
			$sendoffs         = array();
			$minutes          = get_post_meta( $match_id, 'sp_minutes', true );
			if ( '' === $minutes ) {
				$minutes = get_option( 'sportspress_event_minutes', 90 );
			}
			$played_minutes = 0;

			// Get performance labels.
			$args = array(
				'post_type'      => array( 'sp_performance' ),
				'numberposts'    => 100,
				'posts_per_page' => 100,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'sp_format',
						'value'   => 'number',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'sp_format',
						'value'   => array( 'equation', 'text' ),
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

					// Continue if active in event.
					if ( sp_array_value( $player_performance, 'status' ) !== 'sub' || sp_array_value( $player_performance, 'sub', 0 ) ) {
						$played_minutes = (int) $minutes;
						// Adjust for substitution time.
						if ( sp_array_value( $player_performance, 'status' ) === 'sub' ) {
							// Substituted for another player.
							$timeline_performance = sp_array_value( sp_array_value( $timeline, $team_id, array() ), $player_id, array() );

							if ( empty( $timeline_performance ) ) {
								continue;
							}
							foreach ( $sendoffs as $sendoff_key ) {
								if ( ! array_key_exists( $sendoff_key, $timeline_performance ) ) {
									continue;
								}
								$sendoff_times = sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $player_id ), $sendoff_key );
								$sendoff_times = array_filter( $sendoff_times );
								$sendoff_time  = end( $sendoff_times );
								if ( ! $sendoff_time ) {
									$sendoff_time = 0;
								}

								// Count minutes until being sent off.
								$played_minutes = (int) $sendoff_time;
							}

							// Subtract minutes prior to substitution.
							$substitution_time = sp_array_value( sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $player_id ), 'sub' ), 0, 0 );
							$played_minutes   -= (int) $substitution_time;
						} else {
							// Starting lineup with possible substitution.
							$subbed_out = false;
							foreach ( $timeline as $timeline_team => $timeline_players ) {
								if ( ! is_array( $timeline_players ) ) {
									continue;
								}
								foreach ( $timeline_players as $timeline_player => $timeline_performance ) {
									if ( 'sub' === sp_array_value( sp_array_value( $players, $timeline_player, array() ), 'status' ) && $player_id === (int) sp_array_value( sp_array_value( $players, $timeline_player, array() ), 'sub', 0 ) ) :
										$substitution_time = sp_array_value( sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $timeline_player ), 'sub' ), 0, 0 );
										if ( $substitution_time ) :

											// Count minutes until substitution.
											$played_minutes = (int) $substitution_time;
											$subbed_out     = true;
										endif;
									endif;
								}

								// No need to check for sendoffs if subbed out.
								if ( $subbed_out ) {
									continue;
								}

								// Check for sendoffs.
								$timeline_performance = sp_array_value( $timeline_players, $player_id, array() );
								if ( empty( $timeline_performance ) ) {
									continue;
								}
								foreach ( $sendoffs as $sendoff_key ) {
									if ( ! array_key_exists( $sendoff_key, $timeline_performance ) ) {
										continue;
									}
									$sendoff_times = (array) sp_array_value( sp_array_value( sp_array_value( $timeline, $team_id ), $player_id ), $sendoff_key, array() );
									$sendoff_times = array_filter( $sendoff_times );
									$sendoff_time  = end( $sendoff_times );
									if ( false === $sendoff_time ) {
										continue;
									}

									// Count minutes until being sent off.
									$played_minutes = (int) $sendoff_time;
								}
							}
						}
					}
				}
			}
			return $played_minutes;
		}

		/**
		 * Add settings.
		 *
		 * @param mixed $settings The SportsPress settings array.
		 * @return array
		 */
		public function add_settings( $settings ) {

			$dpsfs_show_extra_details = array();
			$dpsfs_show_extra_details[ 'Performances' ] = array();
			$dpsfs_show_extra_details[ 'Metrics' ]  = array();
			$dpsfs_show_extra_details[ 'Statistics' ] = array();
			
			$sp_performances = get_posts(
					array(
						'post_type' 	=> 'sp_performance',
						'numberposts'   => -1,
					)
				);
			foreach ( $sp_performances as $sp_performance ) {
				$dpsfs_show_extra_details[ 'Performances' ][ $sp_performance->post_name ] = $sp_performance->post_title;
			}
			$sp_metrics = get_posts(
					array(
						'post_type' 	=> 'sp_metric',
						'numberposts'   => -1,
					)
				);
			foreach ( $sp_metrics as $sp_metric ) {
				$dpsfs_show_extra_details[ 'Metrics' ][ $sp_metric->post_name ] = $sp_metric->post_title;
			}
			$sp_statistics = get_posts(
					array(
						'post_type' 	=> 'sp_statistic',
						'numberposts'   => -1,
					)
				);
			foreach ( $sp_statistics as $sp_statistic ) {
				$dpsfs_show_extra_details[ 'Statistics' ][ $sp_statistic->post_name ] = $sp_statistic->post_title;
			}
				//var_dump($sp_performances);
				//var_dump($sp_metrics);
				//var_dump($dpsfs_show_extra_details);
			
			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => __( 'Detailed Season Statistics', 'detailed-player-statistics-for-sportspress' ),
						'type'  => 'title',
						'id'    => 'dpsfs_detailed_stats_options',
					),
				),
				apply_filters(
					'dpsfs_detailed_stats_options',
					array(
						array(
							'title'   => __( 'Mode', 'sportspress' ),
							'id'      => 'dpsfs_player_statistics_mode',
							'default' => 'popup',
							'type'    => 'radio',
							'options' => array(
								'popup'  => __( 'Popup (thickbox)', 'sportspress' ),
								'inline' => __( 'Inline', 'sportspress' ),
							),
						),
						array(
							'title'         => __( 'Display', 'sportspress' ),
							'desc'          => __( 'Performances', 'sportspress' ),
							'id'            => 'dpsfs_show_performances',
							'default'       => 'yes',
							'type'          => 'checkbox',
							'checkboxgroup' => 'start',
						),

						array(
							'desc'          => __( 'Minutes', 'sportspress' ),
							'id'            => 'dpsfs_show_minutes',
							'default'       => 'yes',
							'type'          => 'checkbox',
							'checkboxgroup' => 'end',
						),
						array(
							'title'   => esc_attr__( 'Extra Details', 'sportspress' ),
							'id'      => 'dpsfs_show_extra_details',
							'default' => 'title',
							'type'    => 'multigroupselect',
							'options' => $dpsfs_show_extra_details,
						),
					)
				),
				array(
					array(
						'type' => 'sectionend',
						'id'   => 'dpsfs_detailed_stats_options',
					),
				)
			);
			return $settings;
		}
		
		/**
		 * Add settings.
		 *
		 * @param mixed $value The settings value array.
		 * @return void
		 */
		public function multigroupselect_settings( $value ) {
			$option_value = SP_Admin_Settings::get_option( $value['id'], $value['default'] );
			var_dump($value);
			var_dump($option_value);
			var_dump($_POST);
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				</th>
				<td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
					<select
						name="<?php echo esc_attr( $value['id'] ); ?><?php echo '[]'; ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						class="chosen-select
						<?php
						if ( is_rtl() ) :
							?>
							 chosen-rtl<?php endif; ?> <?php echo esc_attr( $value['class'] ); ?>"
						<?php
							echo 'multiple="multiple"';
						?>
						>
						<?php
						foreach ( $value['options'] as $group => $options ) {
							?>
							<optgroup label="<?php esc_attr_e( $group, 'sportspress' ); ?>">
								<?php foreach ( $options as $key => $val ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" 
															  <?php

																if ( is_array( $option_value ) ) {
																	selected( in_array( $key, $option_value ), true );
																} else {
																	selected( $option_value, $key );
																}

																?>
									><?php echo esc_attr( $val ); ?>
									</option>
								<?php } ?>
							</optgroup>
								<?php
						}
						?>
				   </select>
				</td>
			</tr>
			<?php
		}
		
		/**
		 * Save settings.
		 *
		 * @param mixed $value The settings value array.
		 * @return void
		 */
		public function multigroupselect_settings_save( $value ) {
			if ( isset( $_POST[ $value['id'] ] ) ) {
				$dpsfs_show_extra_details = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) $_POST[ $value['id'] ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			} else {
				$dpsfs_show_extra_details = array();
			}

			$option_value = $dpsfs_show_extra_details;
			var_dump($option_value);
		}

	}

endif;

new Detailed_Player_Stats_For_SportsPress();
