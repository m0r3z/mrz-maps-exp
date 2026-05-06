<?php
/**
 * Wrapper HTML du shortcode [gmaps_aa].
 *
 * @var string $uid
 * @var array  $data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config      = $data['config'];
$filters     = $data['filters'];
$forced      = isset( $data['forced'] ) ? $data['forced'] : null;
$layout_f    = $config['layoutFilters'];
$layout_l    = $config['layoutList'];
$list_format = $config['listFormat'];

$search_enabled = ! empty( $config['search']['enabled'] );
$search_layout  = isset( $config['search']['layout'] ) ? (string) $config['search']['layout'] : 'inline';
$search_top     = $search_enabled && 'top' === $search_layout;
$search_inline  = $search_enabled && ! $search_top;

$wrapper_cls = sprintf(
	'gmaps-aa-wrapper gmaps-aa-filters-%s gmaps-aa-list-%s gmaps-aa-listfmt-%s%s',
	$layout_f,
	$layout_l,
	$list_format,
	$search_top ? ' gmaps-aa-has-search-top' : ''
);

/**
 * Retourne la clé unique d'un filtre (taxonomie ou champ ACF).
 */
$filter_key = static function ( $filter ) {
	return 'acf' === $filter['type']
		? 'acf:' . $filter['field']
		: 'tax:' . $filter['taxonomy'];
};

/**
 * Retourne les attributs data-* communs à un input de filtre.
 */
$filter_data_attrs = static function ( $filter ) {
	if ( 'acf' === $filter['type'] ) {
		return 'data-filter-type="acf" data-field="' . esc_attr( $filter['field'] ) . '"';
	}
	return 'data-filter-type="tax" data-taxonomy="' . esc_attr( $filter['taxonomy'] ) . '"';
};

$show_counts = ! empty( $config['showFilterCounts'] );

/**
 * Retourne le libellé affiché pour une option de filtre (avec ou sans compteur).
 */
$format_option = static function ( $opt ) use ( $show_counts ) {
	return $show_counts
		? $opt['name'] . ' (' . $opt['count'] . ')'
		: $opt['name'];
};

$dropdown_id = $uid . '-search-dropdown';

/**
 * Rend le champ de recherche (input + dropdown) — réutilisé en mode inline et top.
 */
$render_search_field = static function () use ( $config, $dropdown_id ) {
	?>
	<div class="gmaps-aa-search-wrapper">
		<input type="text"
			class="gmaps-aa-search"
			role="combobox"
			aria-autocomplete="list"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $dropdown_id ); ?>"
			autocomplete="off"
			placeholder="<?php echo esc_attr( $config['search']['placeholder'] ); ?>" />
		<ul id="<?php echo esc_attr( $dropdown_id ); ?>"
			class="gmaps-aa-search-dropdown"
			role="listbox"
			hidden></ul>
	</div>
	<?php
};
?>
<div id="<?php echo esc_attr( $uid ); ?>" class="<?php echo esc_attr( $wrapper_cls ); ?>" data-gmaps-aa="1">

	<?php if ( $search_top ) : ?>
		<div class="gmaps-aa-search-top">
			<?php if ( '' !== (string) $config['search']['label'] ) : ?>
				<div class="gmaps-aa-filter-label"><?php echo esc_html( $config['search']['label'] ); ?></div>
			<?php endif; ?>
			<?php $render_search_field(); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $filters ) || $search_inline ) : ?>
		<div class="gmaps-aa-filters">

			<?php if ( $search_inline ) : ?>
				<div class="gmaps-aa-filter gmaps-aa-filter-search">
					<div class="gmaps-aa-filter-label"><?php echo esc_html( $config['search']['label'] ); ?></div>
					<?php $render_search_field(); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $filters ) || ! empty( $config['showClearBtn'] ) ) : ?>
				<button type="button" class="gmaps-aa-filters-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $uid ); ?>-filters-body">
					<?php esc_html_e( 'Filtres', 'gmaps-aa' ); ?>
				</button>
			<?php endif; ?>

			<div class="gmaps-aa-filters-body" id="<?php echo esc_attr( $uid ); ?>-filters-body">

			<?php foreach ( $filters as $filter ) : ?>
				<?php
				// Masquer le filtre forcé via shortcode si demandé.
				$hide_this = false;
				if ( $forced && 'tax' === $filter['type'] && $forced['taxonomy'] === $filter['taxonomy'] && $forced['hide'] ) {
					$hide_this = true;
				}
				if ( $hide_this ) {
					continue;
				}
				$fkey  = $filter_key( $filter );
				$dattr = $filter_data_attrs( $filter );
				?>
				<div class="gmaps-aa-filter gmaps-aa-filter-<?php echo esc_attr( $filter['mode'] ); ?>" data-filter-key="<?php echo esc_attr( $fkey ); ?>">
					<div class="gmaps-aa-filter-label"><?php echo esc_html( $filter['label'] ); ?></div>

					<?php if ( 'dropdown' === $filter['mode'] ) : ?>
						<select class="gmaps-aa-filter-input" <?php echo $dattr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — attributs déjà échappés ?>>
							<option value=""><?php esc_html_e( 'Tous', 'gmaps-aa' ); ?></option>
							<?php foreach ( $filter['options'] as $opt ) : ?>
								<?php
								$is_forced = ( $forced && 'tax' === $filter['type']
									&& $forced['taxonomy'] === $filter['taxonomy']
									&& (int) $forced['term'] === (int) $opt['id'] );
								?>
								<option value="<?php echo esc_attr( $opt['id'] ); ?>" <?php selected( $is_forced ); ?>>
									<?php echo esc_html( $format_option( $opt ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'radio' === $filter['mode'] ) : ?>
						<div class="gmaps-aa-filter-group">
							<label>
								<input type="radio" name="<?php echo esc_attr( $uid . '-' . $fkey ); ?>" class="gmaps-aa-filter-input" <?php echo $dattr; // phpcs:ignore ?> value="" <?php checked( ! $forced || 'tax' !== $filter['type'] || $forced['taxonomy'] !== $filter['taxonomy'] ); ?> />
								<?php esc_html_e( 'Tous', 'gmaps-aa' ); ?>
							</label>
							<?php foreach ( $filter['options'] as $opt ) : ?>
								<?php
								$is_forced = ( $forced && 'tax' === $filter['type']
									&& $forced['taxonomy'] === $filter['taxonomy']
									&& (int) $forced['term'] === (int) $opt['id'] );
								?>
								<label>
									<input type="radio" name="<?php echo esc_attr( $uid . '-' . $fkey ); ?>" class="gmaps-aa-filter-input" <?php echo $dattr; // phpcs:ignore ?> value="<?php echo esc_attr( $opt['id'] ); ?>" <?php checked( $is_forced ); ?> />
									<?php echo esc_html( $format_option( $opt ) ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					<?php else : // checkbox ?>
						<div class="gmaps-aa-filter-group">
							<?php foreach ( $filter['options'] as $opt ) : ?>
								<?php
								$is_forced = ( $forced && 'tax' === $filter['type']
									&& $forced['taxonomy'] === $filter['taxonomy']
									&& (int) $forced['term'] === (int) $opt['id'] );
								?>
								<label>
									<input type="checkbox" class="gmaps-aa-filter-input" <?php echo $dattr; // phpcs:ignore ?> value="<?php echo esc_attr( $opt['id'] ); ?>" <?php checked( $is_forced ); ?> />
									<?php echo esc_html( $format_option( $opt ) ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $config['showClearBtn'] ) ) : ?>
				<div class="gmaps-aa-filter gmaps-aa-filter-reset">
					<button type="button" class="gmaps-aa-search-clear"><?php echo esc_html( $config['clearBtnText'] ); ?></button>
				</div>
			<?php endif; ?>

			</div><!-- /.gmaps-aa-filters-body -->

		</div>
	<?php endif; ?>

	<div class="gmaps-aa-main">
		<div class="gmaps-aa-map" style="height:<?php echo (int) $config['height']; ?>px;"></div>
		<?php if ( 'none' !== $layout_l ) : ?>
			<div class="gmaps-aa-list-wrap">
				<div class="gmaps-aa-list"></div>
				<?php if ( (int) $config['perPage'] > 0 ) : ?>
					<nav class="gmaps-aa-pagination" hidden>
						<button type="button" class="gmaps-aa-page-prev" aria-label="<?php esc_attr_e( 'Page précédente', 'gmaps-aa' ); ?>">&lsaquo;</button>
						<span class="gmaps-aa-page-info">
							<span class="gmaps-aa-page-current">1</span>
							<?php echo ' / '; ?>
							<span class="gmaps-aa-page-total">1</span>
						</span>
						<button type="button" class="gmaps-aa-page-next" aria-label="<?php esc_attr_e( 'Page suivante', 'gmaps-aa' ); ?>">&rsaquo;</button>
					</nav>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<script type="application/json" class="gmaps-aa-data"><?php echo wp_json_encode( $data ); ?></script>
</div>
