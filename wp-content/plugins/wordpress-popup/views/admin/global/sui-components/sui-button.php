<?php
/**
 * SUI Button.
 *
 * @package Hustle
 * @since 4.3.0
 */

$class  = empty( $class ) ? '' : $class;
$class .= ' sui-button';
$class .= ! empty( $color ) ? ' sui-button-' . $color : '';
$class .= ! empty( $outlined ) ? ' sui-button-ghost' : '';

if ( 'link' === $type ) :
	?>
	<a
		class="<?php echo esc_attr( $class ); ?>"
		<?php echo empty( $id ) ? '' : 'id="' . esc_attr( $id ) . '"'; ?>
	>
		<?php echo esc_html( $label ); ?>
	</a>

<?php elseif ( 'button' === $type ) : ?>
	<button
		class="<?php echo esc_attr( $class ); ?>"
		<?php echo empty( $id ) ? '' : 'id="' . esc_attr( $id ) . '"'; ?>
	>
		<span class="sui-loading-text">
			<?php echo esc_html( $label ); ?>
		</span>
		<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
	</button>
	<?php
endif;
