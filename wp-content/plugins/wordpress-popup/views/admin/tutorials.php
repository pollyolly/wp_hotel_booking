<?php
/**
 * Main wrapper for Hustle's tutorials.
 *
 * @uses ./../global/sui-components/sui-header
 *
 * @package Hustle
 * @since 4.4.6
 */
$is_free = Opt_In_Utils::_is_free();

$header = array(
	'title' => 'Tutorials'
);

$footer = array(
	'is_large' => true,
	'is_free'  => $is_free,
);
?>

<?php
$this->render( 'admin/global/sui-components/sui-header', $header );

echo '<div id="hustle-tutorials-page"></div>';

$this->render( 'admin/global/sui-components/sui-footer', $footer );
