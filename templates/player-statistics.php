<?php
declare(strict_types=1);

/**
 * Player Statistics (Advanced) template
 * This template is modified copy from sportspress/templates/player-statistics.php
 *
 * @author      ThemeBoy & savvasha
 * @package     detailed-player-stats-for-sportspress/templates
 * @version   2.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( 'no' === get_option( 'sportspress_player_show_statistics', 'yes' ) && 'no' === get_option( 'sportspress_player_show_total', 'no' ) ) {
	return;
}

// Validate and sanitize the player ID.
if ( ! isset( $id ) ) {
	$id = get_the_ID();
}

// Ensure we have a valid player ID.
if ( ! $id || ! is_numeric( $id ) ) {
	return;
}

$id = (int) $id;

$player = new SP_Player( $id );

$scrollable         = get_option( 'sportspress_enable_scrollable_tables', 'yes' ) === 'yes';
$show_career_totals = 'yes' === get_option( 'sportspress_player_show_career_total', 'no' );
$sections           = get_option( 'sportspress_player_performance_sections', -1 );
$show_teams         = apply_filters( 'sportspress_player_team_statistics', true );
$leagues            = array_filter( (array) get_the_terms( $id, 'sp_league' ) );

// Sort Leagues by User Defined Order (PHP5.2 supported).
foreach ( $leagues as $key => $league ) {
	$leagues[ $key ]->sp_order = get_term_meta( $league->term_id, 'sp_order', true );
}

// Make the sorting of leagues based on "order" values set by user.
function dpsfs_sort_by_order( $a, $b ) {
	return (int) $a->sp_order - (int) $b->sp_order;
}
usort( $leagues, 'dpsfs_sort_by_order' );

$positions       = $player->positions();
$player_sections = array();
if ( $positions ) {
	foreach ( $positions as $position ) {
		$player_sections = array_merge( $player_sections, sp_get_term_sections( $position->term_id ) );
	}
}

// Determine order of sections.
if ( 1 === $sections ) {
	$section_order = array(
		1 => esc_attr__( 'Defense', 'sportspress' ),
		0 => esc_attr__( 'Offense', 'sportspress' ),
	);
} elseif ( 0 == $sections ) {
	$section_order = array( esc_attr__( 'Offense', 'sportspress' ), esc_attr__( 'Defense', 'sportspress' ) );
} else {
	$section_order = array( -1 => null );
}

// Loop through statistics for each league.
if ( is_array( $leagues ) ) :
	foreach ( $section_order as $section_id => $section_label ) {
		if ( -1 !== $section_id && ! empty( $player_sections ) && ! in_array( $section_id, $player_sections ) ) {
			continue;
		}

		if ( count( $leagues ) > 1 ) {
			printf( '<h3 class="sp-post-caption sp-player-statistics-section">%s</h3>', wp_kses_post( $section_label ?? '' ) );
		}

		foreach ( $leagues as $league ) :
			$caption = $league->name;

			if ( null !== $section_label ) {
				if ( count( $leagues ) === 1 ) {
					$caption = $section_label;
				}
			}

			$args = array(
				'data'               => $player->data( $league->term_id, false, $section_id ),
				'caption'            => $caption,
				'scrollable'         => $scrollable,
				'show_career_totals' => false,
				'league_id'          => $league->term_id,
				'player_id'          => $id,
			);
			if ( ! $show_teams ) {
				$args['hide_teams'] = true;
			}
			sp_get_template( 'dpsfs-player-statistics-league.php', $args, '', DPSFS_PLUGIN_DIR . 'templates/' );
		endforeach;

		if ( $show_career_totals ) {
			sp_get_template(
				'dpsfs-player-statistics-league.php',
				array(
					'data'               => $player->data( 0, false, $section_id ),
					'caption'            => __( 'Career Total', 'sportspress' ),
					'scrollable'         => $scrollable,
					'hide_teams'         => true,
					'show_career_totals' => true,
					'player_id'          => $id,
				),
				'',
				DPSFS_PLUGIN_DIR . 'templates/'
			);
		}
	}
endif; ?>
<div id="player_events" style="display:none;"></div>

