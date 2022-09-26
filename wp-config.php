<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/var/www/html/wp_hotel_booking/wp-content/plugins/wp-super-cache/' );
define( 'DB_NAME', 'wp_hotel_booking' );

/** Database username */
define( 'DB_USER', 'wpbooking' );

/** Database password */
define( 'DB_PASSWORD', '@2022iwebitech++' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'in1{@;q+j4&?+w^4Odp!G,Q<B]tE}IsV-S;AyM^NouD5v4>N-[Z|su~u5.*t0t?M');
define('SECURE_AUTH_KEY',  ':dr$}S17K*c-;|)XpS(#b;:*ct}v.&5+?BJbQ 4*SOyy)/Fckg:?H-4O?cR;qOUS');
define('LOGGED_IN_KEY',    'pM:08kPe;jrD+N$6F<b4P>9-kfc# %nE`Y]+mk%6e>&`K2!fL-ix0Q$m*xw|Wd(Y');
define('NONCE_KEY',        'W`^QSENEPw=~S*7|@y+8+JBJ<B]{fI6NR|oFO)1Fnk@-~tGP&l|y]gp9Ur/9R%fy');
define('AUTH_SALT',        '%fG.L7u+g>:8C)vrmw hGi/34HIxbaDZ%w-n=EXp~m5{Rbo$S-&P.{D4GjG<@Hd|');
define('SECURE_AUTH_SALT', '!P8+0K$F<Cp-hqIX,<5A#QId|Os!T2lp|+p8!d}@o8P#)4#C0D^*e+N=Db.2Ydq+');
define('LOGGED_IN_SALT',   'LVd T +U}qPZ,-:F9k/dnRU`^Z>50[|3kQ|p6s|j+oQ:UM=>_}J9wujtCN*wCq<#');
define('NONCE_SALT',       'or~M;YF+;t #q8!tuy.hd3cg`-SzjtA7}hfwFAm9Q?Onhk|.Q<g}rvhD#?,$wj=D');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
