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

		$clear_btn_text = '' !== (string) $values['clear_btn_text']
			? (string) $values['clear_btn_text']
			: __( 'Effacer', 'gmaps-aa' );

		$marker_default_url = '' !== (string) $values['marker_default_url']
			? (string) $values['marker_default_url']
			: GMAPS_AA_URL . 'assets/default-marker.svg';

		return array(
			'height'         => (int) $values['height'],
			'zoom'           => (int) $values['zoom'],
			'zoomMin'        => (int) $values['zoom_min'],
			'zoomMax'        => (int) $values['zoom_max'],
			'zoomSearch'     => (int) $values['zoom_search'],
			'cooperativeZoom' => ! empty( $values['cooperative_zoom'] ),
			'fitbounds'      => ! empty( $values['fitbounds'] ),
			'showClearBtn'   => ! empty( $values['show_clear_btn'] ),
			'clearBtnText'   => $clear_btn_text,
			'center'         => array(
				'lat' => (float) $values['center_lat'],
				'lng' => (float) $values['center_lng'],
			),
			'layoutFilters'  => (string) $values['layout_filters'],
			'layoutList'     => (string) $values['layout_list'],
			'listFormat'     => (string) $values['list_format'],
			'style'          => $style,
			'search'         => array(
				'enabled' => ! empty( $values['search_enabled'] ),
				'radius'  => (int) $values['search_radius'],
			),
			'taxonomies'       => array_values( (array) $values['taxonomies'] ),
			'taxoModes'        => (array) $values['taxo_modes'],
			'showFilterCounts' => ! empty( $values['show_filter_counts'] ),
			'defaultIconUrl'   => $marker_default_url,
			'markerWidth'      => (int) $values['marker_default_width'],
			'primaryTaxonomy'  => (string) $values['primary_taxonomy'],
			'spiderfier'       => ! empty( $values['spiderfier'] ),
			'perPage'          => (int) $values['per_page'],
			'listClickAction'  => (string) $values['list_click_action'],
			'centerOnCurrent'  => ! empty( $values['center_on_current'] ),
			'closePopupOnMapClick' => ! empty( $values['close_popup_on_map_click'] ),
			'sourcePt'         => (string) $values['source_pt'],
		);
	}

	private static function build_points( $values, &$data ) {
		$source_pt    = $values['source_pt'];
		$acf_field    = $values['acf_field'];
		$taxonomies   = (array) $values['taxonomies'];
		$acf_filters  = (array) $values['acf_filters'];
		$primary_tax  = (string) $values['primary_taxonomy'];
		$limit        = (int) $values['limit'];

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
				'id'        => $post->ID,
				'lat'       => $lat,
				'lng'       => $lng,
				'url'       => get_permalink( $post->ID ),
				'address'   => isset( $raw['address'] ) ? (string) $raw['address'] : '',
				'tooltip'   => TemplateParser::render( (string) $values['tpl_tooltip'], $post->ID ),
				'listItem'  => TemplateParser::render( (string) $values['tpl_list'], $post->ID ),
				'terms'     => array(),
				'acfValues' => array(),
				'icon'      => '',
			);

			// Valeurs des champs ACF utilisés comme filtres (valeur brute, non formatée).
			foreach ( $acf_filters as $spec ) {
				$name = $spec['field'];
				if ( '' === $name ) {
					continue;
				}
				$val = get_field( $name, $post->ID, false );
				if ( null === $val || '' === $val ) {
					continue;
				}
				if ( is_array( $val ) ) {
					// Normalise en tableau de valeurs scalaires.
					$flat = array();
					foreach ( $val as $v ) {
						if ( is_scalar( $v ) ) {
							$flat[] = (string) $v;
						}
					}
					$point['acfValues'][ $name ] = $flat;
				} elseif ( is_scalar( $val ) ) {
					$point['acfValues'][ $name ] = (string) $val;
				}
			}

			// Icône : priorité à la taxonomie primaire (Cosmétique).
			if ( '' !== $primary_tax ) {
				$primary_terms = get_the_terms( $post->ID, $primary_tax );
				if ( ! empty( $primary_terms ) && ! is_wp_error( $primary_terms ) ) {
					foreach ( $primary_terms as $term ) {
						$icon = get_term_meta( $term->term_id, '_gmaps_aa_icon_url', true );
						if ( $icon ) {
							$point['icon'] = esc_url_raw( $icon );
							break;
						}
					}
				}
			}

			// Terms des taxonomies de filtre (+ fallback d'icône si pas encore trouvée).
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
		$filters = array();

		// Filtres par taxonomie.
		$taxonomies  = (array) $values['taxonomies'];
		$modes       = (array) $values['taxo_modes'];
		$tax_logics  = (array) $values['taxo_logic'];
		$tax_labels  = (array) $values['taxo_labels'];

		foreach ( $taxonomies as $slug ) {
			$tax_obj = get_taxonomy( $slug );
			if ( ! $tax_obj ) {
				continue;
			}

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

			$custom_label = isset( $tax_labels[ $slug ] ) ? (string) $tax_labels[ $slug ] : '';
			$filters[] = array(
				'type'     => 'tax',
				'taxonomy' => $slug,
				'label'    => '' !== $custom_label ? $custom_label : $tax_obj->labels->singular_name,
				'mode'     => isset( $modes[ $slug ] ) ? (string) $modes[ $slug ] : 'dropdown',
				'logic'    => isset( $tax_logics[ $slug ] ) ? (string) $tax_logics[ $slug ] : 'or',
				'options'  => $options,
			);
		}

		// Filtres par champ ACF.
		$acf_filters = (array) $values['acf_filters'];
		foreach ( $acf_filters as $spec ) {
			$field = $spec['field'];
			if ( '' === $field ) {
				continue;
			}

			$counts = array();
			foreach ( $points as $p ) {
				if ( ! isset( $p['acfValues'][ $field ] ) ) {
					continue;
				}
				$v = $p['acfValues'][ $field ];
				$vals = is_array( $v ) ? $v : array( (string) $v );
				foreach ( $vals as $single ) {
					$single = (string) $single;
					if ( '' === $single ) {
						continue;
					}
					$counts[ $single ] = isset( $counts[ $single ] ) ? $counts[ $single ] + 1 : 1;
				}
			}
			if ( empty( $counts ) ) {
				continue;
			}

			$choices = self::get_acf_choices( $field );

			$options = array();
			foreach ( $counts as $value => $count ) {
				$options[] = array(
					'id'    => (string) $value,
					'name'  => isset( $choices[ $value ] ) ? (string) $choices[ $value ] : (string) $value,
					'count' => (int) $count,
				);
			}

			usort(
				$options,
				static function ( $a, $b ) {
					return strcasecmp( $a['name'], $b['name'] );
				}
			);

			$filters[] = array(
				'type'    => 'acf',
				'field'   => $field,
				'label'   => '' !== $spec['label'] ? $spec['label'] : $field,
				'mode'    => $spec['mode'],
				'logic'   => isset( $spec['logic'] ) ? (string) $spec['logic'] : 'or',
				'options' => $options,
			);
		}

		return $filters;
	}

	/**
	 * Récupère les choix (value => label) d'un champ ACF de type select/radio/checkbox.
	 * Retourne un tableau vide si le champ n'a pas de choices définis.
	 */
	private static function get_acf_choices( $field_name ) {
		static $cache = array();
		if ( array_key_exists( $field_name, $cache ) ) {
			return $cache[ $field_name ];
		}
		$choices = array();
		if ( function_exists( 'acf_get_field' ) ) {
			$obj = acf_get_field( $field_name );
			if ( is_array( $obj ) && isset( $obj['choices'] ) && is_array( $obj['choices'] ) ) {
				foreach ( $obj['choices'] as $key => $label ) {
					$choices[ (string) $key ] = (string) $label;
				}
			}
			if ( is_array( $obj ) && isset( $obj['type'] ) && 'true_false' === $obj['type'] ) {
				$choices = array(
					'1' => isset( $obj['ui_on_text'] ) && '' !== $obj['ui_on_text'] ? (string) $obj['ui_on_text'] : __( 'Oui', 'gmaps-aa' ),
					'0' => isset( $obj['ui_off_text'] ) && '' !== $obj['ui_off_text'] ? (string) $obj['ui_off_text'] : __( 'Non', 'gmaps-aa' ),
				);
			}
		}
		$cache[ $field_name ] = $choices;
		return $choices;
	}
}
