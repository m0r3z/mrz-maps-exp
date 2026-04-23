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
		return array( 'above', 'side' );
	}
	public static function layouts_list() {
		return array( 'below', 'side', 'none' );
	}
	public static function list_formats() {
		return array( 'list', 'grid' );
	}
	public static function taxo_modes() {
		return array( 'dropdown', 'radio', 'checkbox' );
	}

	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'save_post_' . GMAPS_AA_CPT, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'admin_notices', array( $this, 'maybe_notice_missing_key' ) );
	}

	public function add_metaboxes() {
		add_meta_box( 'gmaps_aa_source', __( 'Source des données', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'high', array( 'view' => 'source' ) );
		add_meta_box( 'gmaps_aa_filters', __( 'Filtres', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'high', array( 'view' => 'filters' ) );
		add_meta_box( 'gmaps_aa_display', __( 'Affichage', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'high', array( 'view' => 'display' ) );
		add_meta_box( 'gmaps_aa_templates', __( 'Templates HTML', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'default', array( 'view' => 'templates' ) );
		add_meta_box( 'gmaps_aa_style', __( 'Style de la carte', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'default', array( 'view' => 'style' ) );
		add_meta_box( 'gmaps_aa_search', __( 'Recherche par adresse', 'gmaps-aa' ), array( $this, 'render' ), GMAPS_AA_CPT, 'normal', 'default', array( 'view' => 'search' ) );
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
			'acf_filters'        => array(),
			'limit'              => 0,
			'height'             => 500,
			'zoom'               => 10,
			'zoom_min'           => 1,
			'zoom_max'           => 22,
			'zoom_search'        => 12,
			'fitbounds'          => 1,
			'show_clear_btn'     => 1,
			'clear_btn_text'     => '',
			'center_lat'         => 46.603354,
			'center_lng'         => 1.888334,
			'layout_filters'     => 'above',
			'layout_list'        => 'below',
			'list_format'        => 'list',
			'tpl_tooltip'        => "<div class=\"gmaps-aa-tooltip\">\n  <h6>{post_title}</h6>\n</div>",
			'tpl_list'           => "<div class=\"gmaps-aa-list-item\">\n  <h6>{post_title}</h6>\n</div>",
			'snazzy'             => '',
			'clustering'         => 1,
			'search_enabled'     => 0,
			'search_radius'      => 25,
			'search_show_circle' => 0,
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
				$acf_filters[] = array(
					'field' => $field,
					'label' => sanitize_text_field( $label_raw ),
					'mode'  => $mode,
				);
			}
		}
		$clean['acf_filters'] = $acf_filters;

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
		$clean['zoom_search']    = isset( $raw['zoom_search'] ) ? max( 1, min( 22, absint( $raw['zoom_search'] ) ) ) : 12;
		$clean['fitbounds']      = ! empty( $raw['fitbounds'] ) ? 1 : 0;
		$clean['show_clear_btn'] = ! empty( $raw['show_clear_btn'] ) ? 1 : 0;
		$clean['clear_btn_text'] = isset( $raw['clear_btn_text'] ) ? sanitize_text_field( (string) $raw['clear_btn_text'] ) : '';
		$clean['center_lat']     = isset( $raw['center_lat'] ) ? self::clamp_float( (float) $raw['center_lat'], -90, 90 ) : 46.603354;
		$clean['center_lng']     = isset( $raw['center_lng'] ) ? self::clamp_float( (float) $raw['center_lng'], -180, 180 ) : 1.888334;
		$clean['layout_filters'] = ( isset( $raw['layout_filters'] ) && in_array( $raw['layout_filters'], self::layouts_filters(), true ) ) ? $raw['layout_filters'] : 'above';
		$clean['layout_list']    = ( isset( $raw['layout_list'] ) && in_array( $raw['layout_list'], self::layouts_list(), true ) ) ? $raw['layout_list'] : 'below';
		$clean['list_format']    = ( isset( $raw['list_format'] ) && in_array( $raw['list_format'], self::list_formats(), true ) ) ? $raw['list_format'] : 'list';

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
		$clean['clustering'] = ! empty( $raw['clustering'] ) ? 1 : 0;

		// Search.
		$clean['search_enabled']     = ! empty( $raw['search_enabled'] ) ? 1 : 0;
		$clean['search_radius']      = isset( $raw['search_radius'] ) ? max( 1, min( 500, absint( $raw['search_radius'] ) ) ) : 25;
		$clean['search_show_circle'] = ! empty( $raw['search_show_circle'] ) ? 1 : 0;

		foreach ( $clean as $key => $value ) {
			update_post_meta( $post_id, '_gmaps_aa_' . $key, $value );
		}

		// Invalide le cache transient.
		delete_transient( 'gmaps_aa_map_' . (int) $post_id );
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

		// Ajoute les attributs data-* et class sur les balises courantes.
		$common_attrs = array(
			'class'    => true,
			'id'       => true,
			'style'    => true,
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
			array( 'jquery' ),
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
