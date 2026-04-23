<?php
/**
 * Moteur de templates pour infobulles et listes.
 *
 * Placeholders supportés :
 *   - {post_title}, {post_url}, {post_excerpt}
 *   - {post_thumbnail} (balise <img>), {post_thumbnail_url}
 *   - {%nom_champ_acf%}
 *   - {taxonomy:slug} (liste des termes, virgule)
 *   - {taxonomy:slug:first}
 *
 * Conditionnels :
 *   - {#if %champ_acf%}...{/if}
 *   - {#if post_title}...{/if}
 *
 * Aucune exécution de code : pas d'eval/extract. Échappement par type à la substitution.
 */

namespace GmapsAA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TemplateParser {

	const MAX_RECURSION = 5;

	/**
	 * Cache ACF par post_id (in-memory, scope requête).
	 *
	 * @var array
	 */
	private static $acf_cache = array();

	public static function render( $template, $post_id ) {
		$template = (string) $template;
		if ( '' === $template || ! $post_id ) {
			return '';
		}

		$post_id = (int) $post_id;

		// Passe 1 : conditionnels (résolus récursivement sur max 5 niveaux).
		$out = self::resolve_conditionals( $template, $post_id, 0 );

		// Passe 2 : placeholders simples.
		$out = self::resolve_placeholders( $out, $post_id );

		return $out;
	}

	private static function resolve_conditionals( $template, $post_id, $depth ) {
		if ( $depth >= self::MAX_RECURSION ) {
			// Retire tous les marqueurs restants pour ne pas les laisser fuiter.
			return preg_replace( '/\{#if\s+[^}]+\}|\{\/if\}/', '', $template );
		}

		$pattern = '/\{#if\s+(%?)([\w:-]+)\1\}((?:(?!\{#if\b|\{\/if\}).)*)\{\/if\}/s';

		return preg_replace_callback(
			$pattern,
			function ( $m ) use ( $post_id, $depth ) {
				$is_acf = ( '%' === $m[1] );
				$key    = $m[2];
				$body   = $m[3];

				$value = $is_acf
					? self::get_acf_raw( $key, $post_id )
					: self::get_native_raw( $key, $post_id );

				if ( ! self::is_truthy( $value ) ) {
					return '';
				}

				// Résolution récursive pour conditionnels imbriqués.
				return self::resolve_conditionals( $body, $post_id, $depth + 1 );
			},
			$template
		);
	}

	private static function resolve_placeholders( $template, $post_id ) {
		// ACF : {%field_name%}
		$template = preg_replace_callback(
			'/\{%([\w-]+)%\}/',
			function ( $m ) use ( $post_id ) {
				return self::escape_acf( $m[1], $post_id );
			},
			$template
		);

		// Taxonomies : {taxonomy:slug} ou {taxonomy:slug:first}
		$template = preg_replace_callback(
			'/\{taxonomy:([\w-]+)(?::(first))?\}/',
			function ( $m ) use ( $post_id ) {
				$terms = get_the_terms( $post_id, $m[1] );
				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					return '';
				}
				if ( isset( $m[2] ) && 'first' === $m[2] ) {
					return esc_html( $terms[0]->name );
				}
				$names = wp_list_pluck( $terms, 'name' );
				return esc_html( implode( ', ', $names ) );
			},
			$template
		);

		// Placeholders natifs WP.
		$native = array(
			'{post_title}'         => esc_html( get_the_title( $post_id ) ),
			'{post_url}'           => esc_url( get_permalink( $post_id ) ),
			'{post_excerpt}'       => esc_html( wp_strip_all_tags( get_the_excerpt( $post_id ) ) ),
			'{post_thumbnail}'     => wp_kses_post( get_the_post_thumbnail( $post_id, 'medium' ) ),
			'{post_thumbnail_url}' => esc_url( (string) get_the_post_thumbnail_url( $post_id, 'medium' ) ),
			'{post_id}'            => (string) $post_id,
		);

		return strtr( $template, $native );
	}

	private static function get_native_raw( $key, $post_id ) {
		switch ( $key ) {
			case 'post_title':
				return get_the_title( $post_id );
			case 'post_url':
				return get_permalink( $post_id );
			case 'post_excerpt':
				return wp_strip_all_tags( get_the_excerpt( $post_id ) );
			case 'post_thumbnail':
			case 'post_thumbnail_url':
				return (string) get_the_post_thumbnail_url( $post_id, 'medium' );
			default:
				// Taxonomy checks via taxonomy:slug
				if ( 0 === strpos( $key, 'taxonomy:' ) ) {
					$parts = explode( ':', $key );
					$terms = get_the_terms( $post_id, $parts[1] );
					return ( ! empty( $terms ) && ! is_wp_error( $terms ) );
				}
				return '';
		}
	}

	private static function get_acf_raw( $name, $post_id ) {
		if ( isset( self::$acf_cache[ $post_id ][ $name ] ) ) {
			return self::$acf_cache[ $post_id ][ $name ]['raw'];
		}
		$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : null;
		self::$acf_cache[ $post_id ][ $name ] = array( 'raw' => $value );
		return $value;
	}

	private static function escape_acf( $name, $post_id ) {
		$raw = self::get_acf_raw( $name, $post_id );

		if ( null === $raw || '' === $raw ) {
			$raw = '';
		}

		// Détection du type ACF pour choisir l'échappement adapté.
		$type = 'text';
		if ( function_exists( 'get_field_object' ) ) {
			$obj = get_field_object( $name, $post_id, false, false );
			if ( is_array( $obj ) && isset( $obj['type'] ) ) {
				$type = (string) $obj['type'];
			}
		}

		if ( is_array( $raw ) ) {
			// Cas google_map / relations : on ne rend qu'une représentation safe.
			if ( isset( $raw['address'] ) ) {
				$raw = $raw['address'];
			} elseif ( isset( $raw['url'] ) ) {
				$raw = $raw['url'];
			} else {
				$raw = '';
			}
		}

		$value = (string) $raw;
		$value = apply_filters( 'gmaps_aa_template_value', $value, $name, $post_id, $type );

		switch ( $type ) {
			case 'url':
			case 'link':
				return esc_url( $value );
			case 'wysiwyg':
				return wp_kses_post( $value );
			case 'image':
			case 'file':
				return esc_url( $value );
			default:
				return esc_html( $value );
		}
	}

	private static function is_truthy( $value ) {
		if ( null === $value ) {
			return false;
		}
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_array( $value ) ) {
			return ! empty( $value );
		}
		$str = (string) $value;
		return ( '' !== $str && '0' !== $str );
	}
}
