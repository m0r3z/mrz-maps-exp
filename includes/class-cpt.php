<?php
/**
 * Enregistre le Custom Post Type des cartes.
 */

namespace MrzMapsExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT {

	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'manage_' . MRZ_MAPS_EXP_CPT . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . MRZ_MAPS_EXP_CPT . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_menu_icon_style' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Cartes', 'post type general name', 'mrz-maps-exp' ),
			'singular_name'      => _x( 'Carte', 'post type singular name', 'mrz-maps-exp' ),
			'menu_name'          => _x( 'GMaps', 'admin menu', 'mrz-maps-exp' ),
			'name_admin_bar'     => _x( 'Carte', 'add new on admin bar', 'mrz-maps-exp' ),
			'add_new'            => _x( 'Ajouter', 'map', 'mrz-maps-exp' ),
			'add_new_item'       => __( 'Ajouter une carte', 'mrz-maps-exp' ),
			'new_item'           => __( 'Nouvelle carte', 'mrz-maps-exp' ),
			'edit_item'          => __( 'Modifier la carte', 'mrz-maps-exp' ),
			'view_item'          => __( 'Voir la carte', 'mrz-maps-exp' ),
			'all_items'          => __( 'Toutes les cartes', 'mrz-maps-exp' ),
			'search_items'       => __( 'Rechercher une carte', 'mrz-maps-exp' ),
			'not_found'          => __( 'Aucune carte trouvée.', 'mrz-maps-exp' ),
			'not_found_in_trash' => __( 'Aucune carte dans la corbeille.', 'mrz-maps-exp' ),
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

		register_post_type( MRZ_MAPS_EXP_CPT, $args );
	}

	/**
	 * Injecte l'icône du menu via CSS mask-image : pas de flash de la couleur
	 * native du SVG au chargement, couleur directement conforme au thème admin
	 * (gris 60% au repos, blanc au hover / submenu ouvert / page active).
	 *
	 * Implémenté via wp_register_style + wp_add_inline_style plutôt qu'un
	 * <style> imprimé en dur dans admin_head, pour respecter le pattern
	 * d'enqueue attendu par les guidelines wordpress.org.
	 */
	public function enqueue_menu_icon_style() {
		$handle = 'mrz-maps-exp-menu-icon';
		wp_register_style( $handle, false, array(), MRZ_MAPS_EXP_VERSION );
		wp_enqueue_style( $handle );

		$url = esc_url( MRZ_MAPS_EXP_URL . 'assets/menu-icon.svg?ver=' . MRZ_MAPS_EXP_VERSION );
		// L'ID exact du <li> varie selon la façon dont WP sanitise le menu_file
		// (edit.php?post_type=...). Sélecteur tolérant : tout menu_top dont l'ID
		// contient le slug du CPT.
		$sel = '#adminmenu li.menu-top[id*="' . MRZ_MAPS_EXP_CPT . '"]';

		$css  = $sel . ' .wp-menu-image{background:none!important;background-color:rgba(240,246,252,.6)!important;';
		$css .= '-webkit-mask:url(\'' . $url . '\') no-repeat 9px 7px/20px;mask:url(\'' . $url . '\') no-repeat 9px 7px/20px;}';
		$css .= $sel . ' .wp-menu-image::before{display:none;}';
		$css .= $sel . ':hover .wp-menu-image,';
		$css .= $sel . '.wp-has-current-submenu .wp-menu-image,';
		$css .= $sel . '.current .wp-menu-image,';
		$css .= $sel . '.wp-menu-open .wp-menu-image{background-color:#fff!important;}';

		wp_add_inline_style( $handle, $css );
	}

	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['mrz_maps_exp_shortcode'] = __( 'Shortcode', 'mrz-maps-exp' );
			}
		}
		return $new;
	}

	public function column_content( $column, $post_id ) {
		if ( 'mrz_maps_exp_shortcode' !== $column ) {
			return;
		}
		printf(
			'<code>[mrz_maps_exp id="%d"]</code>',
			(int) $post_id
		);
	}
}
