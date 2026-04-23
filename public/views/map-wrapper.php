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

$config       = $data['config'];
$filters      = $data['filters'];
$forced       = isset( $data['forced'] ) ? $data['forced'] : null;
$layout_f     = $config['layoutFilters'];
$layout_l     = $config['layoutList'];
$list_format  = $config['listFormat'];
$wrapper_cls  = sprintf(
	'gmaps-aa-wrapper gmaps-aa-filters-%s gmaps-aa-list-%s gmaps-aa-listfmt-%s',
	$layout_f,
	$layout_l,
	$list_format
);
?>
<div id="<?php echo esc_attr( $uid ); ?>" class="<?php echo esc_attr( $wrapper_cls ); ?>" data-gmaps-aa="1">

	<?php if ( ! empty( $filters ) || ! empty( $config['search']['enabled'] ) ) : ?>
		<div class="gmaps-aa-filters">

			<?php foreach ( $filters as $filter ) : ?>
				<?php
				$hide_this = false;
				if ( $forced && $forced['taxonomy'] === $filter['taxonomy'] && $forced['hide'] ) {
					$hide_this = true;
				}
				if ( $hide_this ) {
					continue;
				}
				?>
				<div class="gmaps-aa-filter gmaps-aa-filter-<?php echo esc_attr( $filter['mode'] ); ?>" data-taxonomy="<?php echo esc_attr( $filter['taxonomy'] ); ?>">
					<div class="gmaps-aa-filter-label"><?php echo esc_html( $filter['label'] ); ?></div>

					<?php if ( 'dropdown' === $filter['mode'] ) : ?>
						<select class="gmaps-aa-filter-input" data-taxonomy="<?php echo esc_attr( $filter['taxonomy'] ); ?>">
							<option value=""><?php esc_html_e( 'Tous', 'gmaps-aa' ); ?></option>
							<?php foreach ( $filter['options'] as $opt ) : ?>
								<?php
								$is_forced = ( $forced && $forced['taxonomy'] === $filter['taxonomy'] && (int) $forced['term'] === $opt['id'] );
								?>
								<option value="<?php echo esc_attr( $opt['id'] ); ?>" <?php selected( $is_forced ); ?>>
									<?php echo esc_html( $opt['name'] . ' (' . $opt['count'] . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'radio' === $filter['mode'] ) : ?>
						<div class="gmaps-aa-filter-group">
							<label>
								<input type="radio" name="<?php echo esc_attr( $uid . '-' . $filter['taxonomy'] ); ?>" class="gmaps-aa-filter-input" data-taxonomy="<?php echo esc_attr( $filter['taxonomy'] ); ?>" value="" <?php checked( ! $forced || $forced['taxonomy'] !== $filter['taxonomy'] ); ?> />
								<?php esc_html_e( 'Tous', 'gmaps-aa' ); ?>
							</label>
							<?php foreach ( $filter['options'] as $opt ) : ?>
								<?php $is_forced = ( $forced && $forced['taxonomy'] === $filter['taxonomy'] && (int) $forced['term'] === $opt['id'] ); ?>
								<label>
									<input type="radio" name="<?php echo esc_attr( $uid . '-' . $filter['taxonomy'] ); ?>" class="gmaps-aa-filter-input" data-taxonomy="<?php echo esc_attr( $filter['taxonomy'] ); ?>" value="<?php echo esc_attr( $opt['id'] ); ?>" <?php checked( $is_forced ); ?> />
									<?php echo esc_html( $opt['name'] . ' (' . $opt['count'] . ')' ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					<?php else : // checkbox ?>
						<div class="gmaps-aa-filter-group">
							<?php foreach ( $filter['options'] as $opt ) : ?>
								<?php $is_forced = ( $forced && $forced['taxonomy'] === $filter['taxonomy'] && (int) $forced['term'] === $opt['id'] ); ?>
								<label>
									<input type="checkbox" class="gmaps-aa-filter-input" data-taxonomy="<?php echo esc_attr( $filter['taxonomy'] ); ?>" value="<?php echo esc_attr( $opt['id'] ); ?>" <?php checked( $is_forced ); ?> />
									<?php echo esc_html( $opt['name'] . ' (' . $opt['count'] . ')' ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $config['search']['enabled'] ) ) : ?>
				<div class="gmaps-aa-filter gmaps-aa-filter-search">
					<div class="gmaps-aa-filter-label"><?php esc_html_e( 'Rechercher', 'gmaps-aa' ); ?></div>
					<input type="text" class="gmaps-aa-search" placeholder="<?php echo esc_attr( $data['i18n']['search_placeholder'] ); ?>" />
					<label>
						<?php echo esc_html( $data['i18n']['radius_label'] ); ?>
						<input type="number" class="gmaps-aa-radius" value="<?php echo esc_attr( (int) $config['search']['radius'] ); ?>" min="1" max="500" step="1" />
					</label>
					<button type="button" class="gmaps-aa-search-clear"><?php echo esc_html( $data['i18n']['clear'] ); ?></button>
				</div>
			<?php endif; ?>

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
