<?php
/**
 * SUI Tutorials.
 *
 * @uses ./sui-icon
 *
 * @package Hustle
 * @since 4.4.6
 */

$hide_docs = apply_filters( 'wpmudev_branding_hide_doc_link', false );
$hidden    = get_option( 'hustle-hide_tutorials' );

if ( ! $hide_docs && ! $hidden ) {
	echo '<div id="hustle-tutorials-slider" class="sui-box" style="background-color: transparent; box-shadow: none;"></div>';
}
