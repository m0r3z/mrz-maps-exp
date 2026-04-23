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
$wrapper_cls = sprintf(
	'gmaps-aa-wrapper gmaps-aa-filters-%s gmaps-aa-list-%s gmaps-aa-listfmt-%s',
	$layout_f,
	$layout_l,
	$list_format
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
?>
<div id="<?php echo esc_attr( $uid ); ?>" class="<?php echo esc_attr( $wrapper_cls ); ?>" data-gmaps-aa="1">

	<?php if ( ! empty( $filters ) || ! empty( $config['search']['enabled'] ) ) : ?>
		<div class="gmaps-aa-filters">

			<?php if ( ! empty( $config['search']['enabled'] ) ) : ?>
				<div class="gmaps-aa-filter gmaps-aa-filter-search">
					<div class="gmaps-aa-filter-label"><?php esc_html_e( 'Rechercher', 'gmaps-aa' ); ?></div>
					<input type="text" class="gmaps-aa-search" placeholder="<?php echo esc_attr( $data['i18n']['search_placeholder'] ); ?>" />
					<button type="button" class="gmaps-aa-search-clear"><?php echo esc_html( $data['i18n']['clear'] ); ?></button>
				</div>
			<?php endif; ?>

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
									<?php echo esc_html( $opt['name'] . ' (' . $opt['count'] . ')' ); ?>
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
									<?php echo esc_html( $opt['name'] . ' (' . $opt['count'] . ')' ); ?>
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
									<?php echo esc_html( $opt['name'] . ' (' . $opt['count'] . ')' ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

		</div>
	<?php endif; ?>

	<div class="gmaps-aa-main">
		<div class="gmaps-aa-map" style="height:<?php echo (int) $config['height']; ?>px;"></div>
		<?php if ( 'none' !== $layout_l ) : ?>
			<div class="gmaps-aa-list"></div>
		<?php endif; ?>
	</div>

	<script type="application/json" class="gmaps-aa-data"><?php echo wp_json_encode( $data ); ?></script>
</div>
