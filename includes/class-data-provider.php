<?php
/**
 * Agrège les données d'une carte pour le rendu front.
 */

namespace GmapsAA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DataProvider {

	const CACHE_PREFIX = 'gmaps_aa_map_';

	public static function invalidate( $map_id ) {
		delete_transient( self::CACHE_PREFIX . (int) $map_id );
	}

	public static function get_map_data( $map_id ) {
		$map_id = (int) $map_id;
		if ( $map_id <= 0 || get_post_type( $map_id ) !== GMAPS_AA_CPT ) {
			return null;
		}

		$cache_key = self::CACHE_PREFIX . $map_id;
		$cached    = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$values = MapConfig::get_values( $map_id );

		$data = array(
			'id'      => $map_id,
			'config'  => self::build_config( $values ),
			'filters' => array(),
			'points'  => array(),
			'i18n'    => array(
				'search_placeholder' => __( 'Rechercher une adresse…', 'gmaps-aa' ),
				'radius_label'       => __( 'Rayon (km)', 'gmaps-aa' ),
				'clear'              => __( 'Effacer', 'gmaps-aa' ),
			),
		);

		$points = self::build_points( $values, $data );

		$data['points']  = $points;
		$data['filters'] = self::build_filters( $values, $points );

		$ttl = (int) apply_filters( 'gmaps_aa_cache_ttl', 5 * MINUTE_IN_SECONDS );
		if ( $ttl > 0 ) {
			set_transient( $cache_key, $data, $ttl );
		}

		return $data;
	}

	private static function build_config( $values ) {
		$style = array();
		if ( ! empty( $values['snazzy'] ) ) {
			$decoded = json_decode( (string) $values['snazzy'], true );
			if ( is_array( $decoded ) ) {
				$style = $decoded;
			}
		}

		return array(
			'height'         => (int) $values['height'],
			'zoom'           => (int) $values['zoom'],
			'center'         => array(
				'lat' => (float) $values['center_lat'],
				'lng' => (float) $values['center_lng'],
			),
			'layoutFilters'  => (string) $values['layout_filters'],
			'layoutList'     => (string) $values['layout_list'],
			'listFormat'     => (string) $values['list_format'],
			'clustering'     => ! empty( $values['clustering'] ),
			'style'          => $style,
			'search'         => array(
				'enabled'    => ! empty( $values['search_enabled'] ),
				'radius'     => (int) $values['search_radius'],
				'showCircle' => ! empty( $values['search_show_circle'] ),
			),
			'taxonomies'     => array_values( (array) $values['taxonomies'] ),
			'taxoModes'      => (array) $values['taxo_modes'],
			'defaultIconUrl' => GMAPS_AA_URL . 'assets/default-marker.svg',
		);
	}

	private static function build_points( $values, &$data ) {
		$source_pt  = $values['source_pt'];
		$acf_field  = $values['acf_field'];
		$taxonomies = (array) $values['taxonomies'];
		$limit      = (int) $values['limit'];

		$args = array(
			'post_type'              => $source_pt,
			'post_status'            => 'publish',
			'posts_per_page'         => $limit > 0 ? $limit : -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);

		$query = new \WP_Query( $args );
		if ( ! $query->have_posts() ) {
			return array();
		}

		$points = array();
		foreach ( $query->posts as $post ) {
			if ( ! function_exists( 'get_field' ) ) {
				break;
			}

			$raw = get_field( $acf_field, $post->ID );
			if ( ! is_array( $raw ) || ! isset( $raw['lat'], $raw['lng'] ) ) {
				continue;
			}

			$lat = (float) $raw['lat'];
			$lng = (float) $raw['lng'];
			if ( 0.0 === $lat && 0.0 === $lng ) {
				continue;
			}

			$point = array(
				'id'       => $post->ID,
				'lat'      => $lat,
				'lng'      => $lng,
				'address'  => isset( $raw['address'] ) ? (string) $raw['address'] : '',
				'tooltip'  => TemplateParser::render( (string) $values['tpl_tooltip'], $post->ID ),
				'listItem' => TemplateParser::render( (string) $values['tpl_list'], $post->ID ),
				'terms'    => array(),
				'icon'     => '',
			);

			// Terms + icône (priorité au premier terme ayant une icône).
			foreach ( $taxonomies as $tax ) {
				$terms = get_the_terms( $post->ID, $tax );
				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					continue;
				}
				$ids = array();
				foreach ( $terms as $term ) {
					$ids[] = (int) $term->term_id;
					if ( '' === $point['icon'] ) {
						$icon = get_term_meta( $term->term_id, '_gmaps_aa_icon_url', true );
						if ( $icon ) {
							$point['icon'] = esc_url_raw( $icon );
						}
					}
				}
				$point['terms'][ $tax ] = $ids;
			}

			$points[] = $point;
		}

		return $points;
	}

	private static function build_filters( $values, $points ) {
		$taxonomies = (array) $values['taxonomies'];
		$modes      = (array) $values['taxo_modes'];

		$filters = array();
		foreach ( $taxonomies as $slug ) {
			$tax_obj = get_taxonomy( $slug );
			if ( ! $tax_obj ) {
				continue;
			}

			// Collecte les term IDs effectivement présents dans les points.
			$used_ids = array();
			foreach ( $points as $p ) {
				if ( ! empty( $p['terms'][ $slug ] ) ) {
					foreach ( $p['terms'][ $slug ] as $tid ) {
						$used_ids[ $tid ] = isset( $used_ids[ $tid ] ) ? $used_ids[ $tid ] + 1 : 1;
					}
				}
			}
			if ( empty( $used_ids ) ) {
				continue;
			}

			$terms = get_terms(
				array(
					'taxonomy'   => $slug,
					'include'    => array_keys( $used_ids ),
					'hide_empty' => false,
				)
			);
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			$options = array();
			foreach ( $terms as $term ) {
				$options[] = array(
					'id'    => (int) $term->term_id,
					'name'  => $term->name,
					'count' => isset( $used_ids[ $term->term_id ] ) ? (int) $used_ids[ $term->term_id ] : 0,
				);
			}

			$filters[] = array(
				'taxonomy' => $slug,
				'label'    => $tax_obj->labels->singular_name,
				'mode'     => isset( $modes[ $slug ] ) ? (string) $modes[ $slug ] : 'dropdown',
				'options'  => $options,
			);
		}

		return $filters;
	}
}
