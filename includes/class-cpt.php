<?php
/**
 * Enregistre le Custom Post Type des cartes.
 */

namespace GmapsAA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT {

	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'manage_' . GMAPS_AA_CPT . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . GMAPS_AA_CPT . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'print_menu_icon_css' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Cartes', 'post type general name', 'gmaps-aa' ),
			'singular_name'      => _x( 'Carte', 'post type singular name', 'gmaps-aa' ),
			'menu_name'          => _x( 'GMaps', 'admin menu', 'gmaps-aa' ),
			'name_admin_bar'     => _x( 'Carte', 'add new on admin bar', 'gmaps-aa' ),
			'add_new'            => _x( 'Ajouter', 'map', 'gmaps-aa' ),
			'add_new_item'       => __( 'Ajouter une carte', 'gmaps-aa' ),
			'new_item'           => __( 'Nouvelle carte', 'gmaps-aa' ),
			'edit_item'          => __( 'Modifier la carte', 'gmaps-aa' ),
			'view_item'          => __( 'Voir la carte', 'gmaps-aa' ),
			'all_items'          => __( 'Toutes les cartes', 'gmaps-aa' ),
			'search_items'       => __( 'Rechercher une carte', 'gmaps-aa' ),
			'not_found'          => __( 'Aucune carte trouvée.', 'gmaps-aa' ),
			'not_found_in_trash' => __( 'Aucune carte dans la corbeille.', 'gmaps-aa' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'show_in_rest'       => false,
			'menu_icon'          => 'none',
			'menu_position'      => 90,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'query_var'          => false,
		);

		register_post_type( GMAPS_AA_CPT, $args );
	}

	/**
	 * Injecte l'icône du menu via CSS mask-image : pas de flash de la couleur
	 * native du SVG au chargement, couleur directement conforme au thème admin
	 * (gris 60% au repos, blanc au hover / submenu ouvert / page active).
	 */
	public function print_menu_icon_css() {
		$url = GMAPS_AA_URL . 'assets/menu-icon.svg?ver=' . GMAPS_AA_VERSION;
		// L'ID exact du <li> varie selon la façon dont WP sanitise le menu_file
		// (edit.php?post_type=...). Sélecteur tolérant : tout menu_top dont l'ID
		// contient le slug du CPT.
		$sel = '#adminmenu li.menu-top[id*="' . GMAPS_AA_CPT . '"]';
		?>
		<style>
			<?php echo $sel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — sélecteur construit à partir d'un slug contrôlé ?> .wp-menu-image {
				background: none !important;
				background-color: rgba(240, 246, 252, 0.6) !important;
				-webkit-mask: url('<?php echo esc_url( $url ); ?>') no-repeat 9px 7px / 20px;
				        mask: url('<?php echo esc_url( $url ); ?>') no-repeat 9px 7px / 20px;
			}
			<?php echo $sel; // phpcs:ignore ?> .wp-menu-image::before {
				display: none;
			}
			<?php echo $sel; // phpcs:ignore ?>:hover .wp-menu-image,
			<?php echo $sel; // phpcs:ignore ?>.wp-has-current-submenu .wp-menu-image,
			<?php echo $sel; // phpcs:ignore ?>.current .wp-menu-image,
			<?php echo $sel; // phpcs:ignore ?>.wp-menu-open .wp-menu-image {
				background-color: #fff !important;
			}
		</style>
		<?php
	}

	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['gmaps_aa_shortcode'] = __( 'Shortcode', 'gmaps-aa' );
			}
		}
		return $new;
	}

	public function column_content( $column, $post_id ) {
		if ( 'gmaps_aa_shortcode' !== $column ) {
			return;
		}
		printf(
			'<code>[gmaps_aa id="%d"]</code>',
			(int) $post_id
		);
	}
}
