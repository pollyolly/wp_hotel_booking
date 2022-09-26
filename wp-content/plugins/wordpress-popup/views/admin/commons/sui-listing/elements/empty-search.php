<?php
/**
 * Displays the listing page view when there are not found modules.
 *
 * @package Hustle
 */

$image_1x = self::$plugin_url . 'assets/images/hustle-empty-message.png';
$image_2x = self::$plugin_url . 'assets/images/hustle-empty-message@2x.png';
?>

<div class="sui-box sui-message sui-message-lg">

	<?php
	if ( ! $this->is_branding_hidden ) :
		echo $this->render_image_markup( $image_1x, $image_2x, 'sui-image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	else :
		echo $this->render_image_markup( $this->branding_image, '', 'sui-image', 172, 192 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	endif;
	?>

	<div class="sui-message-content">

		<h2>
			<?php /* translators: search keyword */ ?>
			<?php printf( esc_html__( 'No results for "%s"', 'hustle' ), esc_html( $search_keyword ) ); ?>
		</h2>

		<?php /* translators: module type */ ?>
		<p><?php echo esc_html( sprintf( __( "We couldn't find any %s matching your search keyword. Perhaps try again?", 'hustle' ), $capitalize_plural ) ); ?></p>

	</div>

</div>
