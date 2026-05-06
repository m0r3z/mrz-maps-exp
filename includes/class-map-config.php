<?php
/**
 * Métaboxes de configuration d'une carte + sauvegarde sécurisée.
 */

namespace GmapsAA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MapConfig {

	const NONCE_ACTION = 'gmaps_aa_save_map';
	const NONCE_NAME   = '_gmaps_aa_nonce';

	/**
	 * Définition des valeurs autorisées pour les champs à choix fermé.
	 */
	public static function layouts_filters() {
		return array( 'above', 'side-left', 'side-right' );
	}
	public static function layouts_list() {
		return array( 'below', 'side-left', 'side-right', 'none' );
	}
	public static function list_formats() {
		return array( 'list', 'grid' );
	}
	public static function list_click_actions() {
		return array( 'tooltip', 'none', 'link' );
	}
	public static function search_layouts() {
		return array( 'inline', 'top' );
	}
	public static function taxo_modes() {
		return array( 'dropdown', 'radio', 'checkbox' );
	}
	public static function filter_logics() {
		return array( 'or', 'and' );
	}

	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'save_post_' . GMAPS_AA_CPT, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'admin_notices', array( $this, 'maybe_notice_missing_key' ) );
		add_action( 'wp_ajax_gmaps_aa_fetch_terms', array( $this, 'ajax_fetch_terms' ) );
	}

	public function add_metaboxes() {
		add_meta_box( 'gmaps_aa_source', __( 'Source des données', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'high', array( 'view' => 'source' ) );
		add_meta_box( 'gmaps_aa_templates', __( 'Templates HTML', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'high', array( 'view' => 'templates' ) );
		add_meta_box( 'gmaps_aa_filters', __( 'Filtres', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'high', array( 'view' => 'filters' ) );
		add_meta_box( 'gmaps_aa_display', __( 'Affichage', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'high', array( 'view' => 'display' ) );
		add_meta_box( 'gmaps_aa_cosmetic', __( 'Cosmétique', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'default', array( 'view' => 'cosmetic' ) );
		add_meta_box( 'gmaps_aa_style', __( 'Style de la carte', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'default', array( 'view' => 'style' ) );
		add_meta_box( 'gmaps_aa_shortcode', __( 'Shortcode', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'side', 'high', array( 'view' => 'shortcode' ) );
	}

	public function render( $post, $metabox ) {
		static $nonce_printed = false;
		if ( ! $nonce_printed ) {
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
			$nonce_printed = true;
		}

		$view = isset( $metabox['args']['view'] ) ? $metabox['args']['view'] : '';
		$file = GMAPS_AA_DIR . 'admin/views/metabox-' . $view . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		$values = self::get_values( $post->ID );
		include $file;
	}

	/**
	 * Charge toutes les valeurs de configuration d'une carte, avec defaults.
	 */
	public static function get_values( $post_id ) {
		$defaults = array(
			'source_pt'          => 'post',
			'acf_field'          => 'location',
			'taxonomies'         => array(),
			'taxo_modes'         => array(),
			'taxo_logic'         => array(),
			'taxo_labels'        => array(),
			'acf_filters'        => array(),
			'show_filter_counts' => 1,
			'limit'              => 0,
			'per_page'           => 10,
			'height'             => 500,
			'zoom'               => 10,
			'zoom_min'           => 1,
			'zoom_max'           => 22,
			'zoom_search'        => 12,
			'cooperative_zoom'   => 1,
			'fitbounds'          => 1,
			'center_on_current'  => 0,
			'close_popup_on_map_click' => 1,
			'show_clear_btn'     => 1,
			'clear_btn_text'     => '',
			'center_lat'         => 46.603354,
			'center_lng'         => 1.888334,
			'layout_filters'     => 'above',
			'layout_list'        => 'below',
			'list_format'        => 'list',
			'list_click_action'  => 'tooltip',
			'tpl_tooltip'        => "<div class=\"gmaps-aa-tooltip\">\n  <h6>{post_title}</h6>\n</div>",
			'tpl_list'           => "<div class=\"gmaps-aa-list-item\">\n  <h6>{post_title}</h6>\n</div>",
			'snazzy'               => '',
			'search_enabled'       => 0,
			'search_radius'        => 25,
			'search_label'         => '',
			'search_placeholder'   => '',
			'search_local_match'   => 1,
			'search_layout'        => 'inline',
			'marker_default_url'   => '',
			'marker_default_id'    => 0,
			'marker_default_width' => 32,
			'primary_taxonomy'     => '',
			'spiderfier'           => 1,
		);

		$out = array();
		foreach ( $defaults as $key => $default ) {
			$stored = get_post_meta( $post_id, '_gmaps_aa_' . $key, true );
			if ( '' === $stored || null === $stored ) {
				$out[ $key ] = $default;
			} elseif ( is_array( $default ) ) {
				$out[ $key ] = is_array( $stored ) ? $stored : $default;
			} elseif ( is_int( $default ) ) {
				$out[ $key ] = (int) $stored;
			} elseif ( is_float( $default ) ) {
				$out[ $key ] = (float) $stored;
			} else {
				$out[ $key ] = $stored;
			}
		}

		// Migration des anciennes valeurs « side » (avant gauche/droite).
		if ( 'side' === $out['layout_filters'] ) {
			$out['layout_filters'] = 'side-left';
		}
		if ( 'side' === $out['layout_list'] ) {
			$out['layout_list'] = 'side-left';
		}

		return $out;
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw = isset( $_POST['gmaps_aa'] ) && is_array( $_POST['gmaps_aa'] )
			? wp_unslash( $_POST['gmaps_aa'] )
			: array();

		$clean = array();

		// Source.
		$public_pts            = array_keys( get_post_types( array( 'public' => true ), 'names' ) );
		$clean['source_pt']    = ( isset( $raw['source_pt'] ) && in_array( $raw['source_pt'], $public_pts, true ) )
			? $raw['source_pt']
			: 'post';
		$clean['acf_field']    = isset( $raw['acf_field'] ) ? sanitize_key( $raw['acf_field'] ) : 'location';
		$clean['limit']        = isset( $raw['limit'] ) ? max( 0, absint( $raw['limit'] ) ) : 0;
		$clean['per_page']     = isset( $raw['per_page'] ) ? max( 0, min( 200, absint( $raw['per_page'] ) ) ) : 10;

		$all_tax = array_keys( get_taxonomies( array( 'public' => true ), 'names' ) );
		$taxo    = array();
		if ( isset( $raw['taxonomies'] ) && is_array( $raw['taxonomies'] ) ) {
			foreach ( $raw['taxonomies'] as $slug ) {
				$slug = sanitize_key( $slug );
				if ( in_array( $slug, $all_tax, true ) ) {
					$taxo[] = $slug;
				}
			}
		}
		$clean['taxonomies'] = $taxo;

		$modes = array();
		if ( isset( $raw['taxo_modes'] ) && is_array( $raw['taxo_modes'] ) ) {
			foreach ( $raw['taxo_modes'] as $slug => $mode ) {
				$slug = sanitize_key( $slug );
				if ( in_array( $slug, $taxo, true ) && in_array( $mode, self::taxo_modes(), true ) ) {
					$modes[ $slug ] = $mode;
				}
			}
		}
		$clean['taxo_modes'] = $modes;

		$logics = array();
		if ( isset( $raw['taxo_logic'] ) && is_array( $raw['taxo_logic'] ) ) {
			foreach ( $raw['taxo_logic'] as $slug => $logic ) {
				$slug = sanitize_key( $slug );
				if ( in_array( $slug, $taxo, true ) && in_array( $logic, self::filter_logics(), true ) ) {
					$logics[ $slug ] = $logic;
				}
			}
		}
		$clean['taxo_logic'] = $logics;

		$labels = array();
		if ( isset( $raw['taxo_labels'] ) && is_array( $raw['taxo_labels'] ) ) {
			foreach ( $raw['taxo_labels'] as $slug => $label ) {
				$slug = sanitize_key( $slug );
				if ( in_array( $slug, $taxo, true ) ) {
					$clean_label = sanitize_text_field( (string) $label );
					if ( '' !== $clean_label ) {
						$labels[ $slug ] = $clean_label;
					}
				}
			}
		}
		$clean['taxo_labels'] = $labels;

		// Filtres ACF.
		$acf_filters = array();
		if ( isset( $raw['acf_filters'] ) && is_array( $raw['acf_filters'] ) ) {
			foreach ( $raw['acf_filters'] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$field = isset( $row['field'] ) ? sanitize_key( $row['field'] ) : '';
				if ( '' === $field ) {
					continue;
				}
				$label_raw = isset( $row['label'] ) ? (string) $row['label'] : '';
				$mode      = ( isset( $row['mode'] ) && in_array( $row['mode'], self::taxo_modes(), true ) )
					? $row['mode']
					: 'dropdown';
				$logic     = ( isset( $row['logic'] ) && in_array( $row['logic'], self::filter_logics(), true ) )
					? $row['logic']
					: 'or';
				$acf_filters[] = array(
					'field' => $field,
					'label' => sanitize_text_field( $label_raw ),
					'mode'  => $mode,
					'logic' => $logic,
				);
			}
		}
		$clean['acf_filters'] = $acf_filters;

		$clean['show_filter_counts'] = ! empty( $raw['show_filter_counts'] ) ? 1 : 0;

		// Display.
		$clean['height']         = isset( $raw['height'] ) ? max( 100, min( 2000, absint( $raw['height'] ) ) ) : 500;
		$clean['zoom']           = isset( $raw['zoom'] ) ? max( 1, min( 22, absint( $raw['zoom'] ) ) ) : 10;
		$clean['zoom_min']       = isset( $raw['zoom_min'] ) ? max( 1, min( 22, absint( $raw['zoom_min'] ) ) ) : 1;
		$clean['zoom_max']       = isset( $raw['zoom_max'] ) ? max( 1, min( 22, absint( $raw['zoom_max'] ) ) ) : 22;
		if ( $clean['zoom_min'] > $clean['zoom_max'] ) {
			$tmp               = $clean['zoom_min'];
			$clean['zoom_min'] = $clean['zoom_max'];
			$clean['zoom_max'] = $tmp;
		}
		$clean['zoom_search']      = isset( $raw['zoom_search'] ) ? max( 1, min( 22, absint( $raw['zoom_search'] ) ) ) : 12;
		$clean['cooperative_zoom']  = ! empty( $raw['cooperative_zoom'] ) ? 1 : 0;
		$clean['fitbounds']         = ! empty( $raw['fitbounds'] ) ? 1 : 0;
		$clean['center_on_current']        = ! empty( $raw['center_on_current'] ) ? 1 : 0;
		$clean['close_popup_on_map_click'] = ! empty( $raw['close_popup_on_map_click'] ) ? 1 : 0;
		$clean['show_clear_btn'] = ! empty( $raw['show_clear_btn'] ) ? 1 : 0;
		$clean['clear_btn_text'] = isset( $raw['clear_btn_text'] ) ? sanitize_text_field( (string) $raw['clear_btn_text'] ) : '';
		$clean['center_lat']     = isset( $raw['center_lat'] ) ? self::clamp_float( (float) $raw['center_lat'], -90, 90 ) : 46.603354;
		$clean['center_lng']     = isset( $raw['center_lng'] ) ? self::clamp_float( (float) $raw['center_lng'], -180, 180 ) : 1.888334;
		$clean['layout_filters'] = ( isset( $raw['layout_filters'] ) && in_array( $raw['layout_filters'], self::layouts_filters(), true ) ) ? $raw['layout_filters'] : 'above';
		$clean['layout_list']    = ( isset( $raw['layout_list'] ) && in_array( $raw['layout_list'], self::layouts_list(), true ) ) ? $raw['layout_list'] : 'below';
		$clean['list_format']       = ( isset( $raw['list_format'] ) && in_array( $raw['list_format'], self::list_formats(), true ) ) ? $raw['list_format'] : 'list';
		$clean['list_click_action'] = ( isset( $raw['list_click_action'] ) && in_array( $raw['list_click_action'], self::list_click_actions(), true ) ) ? $raw['list_click_action'] : 'tooltip';

		// Templates.
		$allowed = self::allowed_html_for_templates();
		$clean['tpl_tooltip'] = isset( $raw['tpl_tooltip'] ) ? wp_kses( (string) $raw['tpl_tooltip'], $allowed ) : '';
		$clean['tpl_list']    = isset( $raw['tpl_list'] ) ? wp_kses( (string) $raw['tpl_list'], $allowed ) : '';

		// Style.
		$snazzy_raw = isset( $raw['snazzy'] ) ? trim( (string) $raw['snazzy'] ) : '';
		if ( '' !== $snazzy_raw ) {
			$decoded = json_decode( $snazzy_raw, true );
			if ( null === $decoded && json_last_error() !== JSON_ERROR_NONE ) {
				// JSON invalide : on stocke vide et on signale.
				$clean['snazzy'] = '';
				add_settings_error( 'gmaps_aa', 'snazzy_invalid', __( 'Snazzy Maps : JSON invalide, le style a été ignoré.', 'gmaps-aa' ), 'error' );
			} else {
				$clean['snazzy'] = wp_json_encode( $decoded );
			}
		} else {
			$clean['snazzy'] = '';
		}

		// Search.
		$clean['search_enabled']     = ! empty( $raw['search_enabled'] ) ? 1 : 0;
		$clean['search_radius']      = isset( $raw['search_radius'] ) ? max( 1, min( 500, absint( $raw['search_radius'] ) ) ) : 25;
		$clean['search_label']       = isset( $raw['search_label'] ) ? sanitize_text_field( (string) $raw['search_label'] ) : '';
		$clean['search_placeholder'] = isset( $raw['search_placeholder'] ) ? sanitize_text_field( (string) $raw['search_placeholder'] ) : '';
		$clean['search_local_match'] = ! empty( $raw['search_local_match'] ) ? 1 : 0;
		$clean['search_layout']      = ( isset( $raw['search_layout'] ) && in_array( $raw['search_layout'], self::search_layouts(), true ) )
			? $raw['search_layout']
			: 'inline';

		// Cosmétique.
		$clean['marker_default_url']   = isset( $raw['marker_default_url'] ) ? esc_url_raw( (string) $raw['marker_default_url'] ) : '';
		$clean['marker_default_id']    = isset( $raw['marker_default_id'] ) ? absint( $raw['marker_default_id'] ) : 0;
		if ( $clean['marker_default_id'] > 0 && ! wp_attachment_is_image( $clean['marker_default_id'] ) ) {
			$clean['marker_default_id'] = 0;
		}
		$clean['marker_default_width'] = isset( $raw['marker_default_width'] ) ? max( 8, min( 128, absint( $raw['marker_default_width'] ) ) ) : 32;
		$clean['primary_taxonomy']     = ( isset( $raw['primary_taxonomy'] ) && in_array( $raw['primary_taxonomy'], $all_tax, true ) )
			? $raw['primary_taxonomy']
			: '';
		$clean['spiderfier']           = ! empty( $raw['spiderfier'] ) ? 1 : 0;

		foreach ( $clean as $key => $value ) {
			update_post_meta( $post_id, '_gmaps_aa_' . $key, $value );
		}

		// Icônes par terme — stockées globalement en term_meta.
		if ( isset( $raw['term_icons'] ) && is_array( $raw['term_icons'] ) ) {
			foreach ( $raw['term_icons'] as $term_id => $icon_data ) {
				$term_id = absint( $term_id );
				if ( ! $term_id || ! term_exists( $term_id ) ) {
					continue;
				}
				if ( ! is_array( $icon_data ) ) {
					continue;
				}
				$url = isset( $icon_data['url'] ) ? esc_url_raw( (string) $icon_data['url'] ) : '';
				$id  = isset( $icon_data['id'] ) ? absint( $icon_data['id'] ) : 0;
				if ( $id > 0 && ! wp_attachment_is_image( $id ) ) {
					$id = 0;
				}
				if ( '' === $url && 0 === $id ) {
					delete_term_meta( $term_id, '_gmaps_aa_icon_url' );
					delete_term_meta( $term_id, '_gmaps_aa_icon_id' );
				} else {
					update_term_meta( $term_id, '_gmaps_aa_icon_url', $url );
					update_term_meta( $term_id, '_gmaps_aa_icon_id', $id );
				}
			}
		}

		// Invalide le cache transient.
		delete_transient( 'gmaps_aa_map_' . (int) $post_id );
	}

	/**
	 * Sanitize une couleur hex (#rgb ou #rrggbb). Retourne '' si invalide.
	 */
	public static function sanitize_hex_color( $color ) {
		$color = trim( (string) $color );
		if ( '' === $color ) {
			return '';
		}
		if ( preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color ) ) {
			return strtolower( $color );
		}
		return '';
	}

	/**
	 * Handler AJAX : récupère les termes d'une taxonomie (pour la métabox Cosmétique).
	 */
	public function ajax_fetch_terms() {
		check_ajax_referer( 'gmaps_aa_fetch_terms', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'gmaps-aa' ) ), 403 );
		}

		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			wp_send_json_error( array( 'message' => __( 'Taxonomie invalide.', 'gmaps-aa' ) ), 400 );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			wp_send_json_error( array( 'message' => $terms->get_error_message() ), 500 );
		}

		$out = array();
		foreach ( $terms as $term ) {
			$out[] = array(
				'id'       => (int) $term->term_id,
				'name'     => $term->name,
				'icon_url' => (string) get_term_meta( $term->term_id, '_gmaps_aa_icon_url', true ),
				'icon_id'  => (int) get_term_meta( $term->term_id, '_gmaps_aa_icon_id', true ),
			);
		}
		wp_send_json_success( $out );
	}

	private static function clamp_float( $value, $min, $max ) {
		if ( $value < $min ) {
			return $min;
		}
		if ( $value > $max ) {
			return $max;
		}
		return $value;
	}

	/**
	 * Allowlist HTML pour les templates utilisateur.
	 */
	public static function allowed_html_for_templates() {
		$allowed = wp_kses_allowed_html( 'post' );

		// Ajoute les attributs class/id/data-* sur les balises courantes.
		// 'style' est volontairement exclu (défense en profondeur : évite le
		// CSS tracking type background:url(...) par un éditeur hostile).
		$common_attrs = array(
			'class'    => true,
			'id'       => true,
			'data-*'   => true,
		);

		foreach ( array( 'div', 'span', 'p', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'strong', 'em', 'br', 'i', 'b' ) as $tag ) {
			if ( ! isset( $allowed[ $tag ] ) ) {
				$allowed[ $tag ] = array();
			}
			foreach ( $common_attrs as $attr => $val ) {
				$allowed[ $tag ][ $attr ] = $val;
			}
		}

		return apply_filters( 'gmaps_aa_template_kses_allowed', $allowed );
	}

	/**
	 * Enqueue admin.js / admin.css uniquement sur l'écran du CPT.
	 */
	public function enqueue_admin( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || GMAPS_AA_CPT !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'gmaps-aa-admin',
			GMAPS_AA_URL . 'admin/css/admin.css',
			array(),
			GMAPS_AA_VERSION
		);

		$api_key = gmaps_aa_get_api_key();

		if ( '' !== $api_key ) {
			wp_enqueue_script(
				'gmaps-aa-admin-gmaps',
				add_query_arg(
					array(
						'key'      => rawurlencode( $api_key ),
						'callback' => 'gmapsAAAdminBoot',
						'loading'  => 'async',
						'v'        => 'weekly',
					),
					'https://maps.googleapis.com/maps/api/js'
				),
				array(),
				null,
				array(
					'strategy'  => 'async',
					'in_footer' => true,
				)
			);
		}

		wp_enqueue_script(
			'gmaps-aa-admin',
			GMAPS_AA_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			GMAPS_AA_VERSION,
			true
		);

		wp_localize_script(
			'gmaps-aa-admin',
			'gmapsAAAdmin',
			array(
				'hasApiKey' => '' !== $api_key,
			)
		);
	}

	/**
	 * Notice admin si clé API absente (visible sur l'écran du CPT).
	 */
	public function maybe_notice_missing_key() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || GMAPS_AA_CPT !== $screen->post_type ) {
			return;
		}
		if ( '' !== gmaps_aa_get_api_key() ) {
			return;
		}
		?>
		<div class="notice notice-warning">
			<p><strong><?php esc_html_e( 'gmaps-aa : clé Google Maps API manquante.', 'gmaps-aa' ); ?></strong></p>
			<p>
				<?php esc_html_e( 'Ajoutez dans le functions.php de votre thème :', 'gmaps-aa' ); ?>
			</p>
			<pre style="background:#f6f7f7;padding:10px;overflow:auto;">add_filter( 'gmaps_aa_api_key', function () {
    return 'VOTRE_CLE_API';
} );</pre>
		</div>
		<?php
	}
}
