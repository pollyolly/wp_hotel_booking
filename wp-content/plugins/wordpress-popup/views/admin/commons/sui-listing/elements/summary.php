<?php
/**
 * Displays the summary section at the top of the listing page.
 *
 * @package Hustle
 * @since 4.0.0
 */

$last_conversion_text = __( 'Last Conversion', 'hustle' );
$latest_amount_text   = __( 'Conversions in the last 30 days', 'hustle' );

if ( Hustle_Module_Model::SOCIAL_SHARING_MODULE === $module_type ) {
	$last_conversion_text = __( 'Last Share', 'hustle' );
	$latest_amount_text   = __( 'Shares in the last 30 days', 'hustle' );
}
$search_keyword = filter_input( INPUT_GET, 'q' );
?>
<div class="<?php echo esc_attr( implode( ' ', $sui['summary']['classes'] ) ); ?>">
	<div class="sui-summary-image-space" aria-hidden="true" style="<?php echo esc_attr( $sui['summary']['style'] ); ?>"></div>
	<div class="sui-summary-segment">
		<div class="sui-summary-details">
			<span class="sui-summary-large"><?php echo esc_attr( $active_modules_count ); ?></span>
			<?php if ( 1 === $active_modules_count ) { ?>
				<?php /* translators: module type capitalized and in singular */ ?>
				<span class="sui-summary-sub"><?php printf( esc_html__( 'Active %s', 'hustle' ), esc_html( $capitalize_singular ) ); ?></span>
			<?php } else { ?>
				<?php /* translators: module type capitalized and in plural */ ?>
				<span class="sui-summary-sub"><?php printf( esc_html__( 'Active %s', 'hustle' ), esc_html( $capitalize_plural ) ); ?></span>
			<?php } ?>

			<form class="hustle-search-modules">
				<input type="hidden" name="page" value="<?php echo esc_attr( (string) filter_input( INPUT_GET, 'page' ) ); ?>" />

				<div class="sui-row">

					<div class="sui-col-lg-10 sui-col-md-12">

						<div class="sui-form-field">

							<div class="sui-control-with-icon">
								<button class="hustle-search-submit"><i class="sui-icon-magnifying-glass-search"></i></button>
								<input type="text" name="q" value="<?php echo esc_attr( $search_keyword ); ?>" placeholder="<?php echo esc_attr( sprintf( __( 'Search %s...', 'hustle' ), $capitalize_singular ) ); ?>" id="hustle-module-search" class="sui-form-control">
							</div>
							<?php if ( $search_keyword ) { ?>
							<div class="search-reset sui-button-icon" title="<?php esc_attr_e( 'Reset search', 'hustle' ); ?>">
								<span class="sui-icon-cross-close" aria-hidden="true"></span>
								<span class="sui-screen-reader-text"><?php esc_html_e( 'Reset search', 'hustle' ); ?></span>
							</div>
							<?php } ?>

						</div>

					</div>

				</div>

			</form>

		</div>
	</div>
	<div class="sui-summary-segment">
		<ul class="sui-list">
			<li>
				<span class="sui-list-label"><?php echo esc_html( $last_conversion_text ); ?></span>
				<span class="sui-list-detail"><?php echo esc_html( $latest_entry_time ); ?></span>
			</li>
			<li>
				<span class="sui-list-label"><?php echo esc_html( $latest_amount_text ); ?></span>
				<span class="sui-list-detail"><?php echo esc_html( $latest_entries_count ); ?></span>
			</li>
		</ul>
	</div>
</div>
