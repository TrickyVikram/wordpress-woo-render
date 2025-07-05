<?php
define('WP_CACHE', true );

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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u674474931_wordpress_woo' );

/** Database username */
define( 'DB_USER', 'u674474931_wordpress_woo2' );

/** Database password */
define( 'DB_PASSWORD', 'Wordpress-woo_@_2025' );

/** Database hostname */
define( 'DB_HOST', ' https://auth-db1578.hstgr.io/index.php?db=u674474931_wordpress_woo' );

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
define( 'AUTH_KEY',          '6.:z5$w8e@O8s2m|q!d[QAeoNM=OsZe|diL`ZMM Gb$a|X7gcb+ y0U4:91WlfVb' );
define( 'SECURE_AUTH_KEY',   '#R$sdkO3K-trZzjvepXAJ`7Q})v_A1s6UNoJqqMUoR59<K/{6BE`zauY8ZMhJ%HT' );
define( 'LOGGED_IN_KEY',     '2)7MmN.&{q0:AzjUO^1p9|wy!2*~3?gg|zi.y6%,,;qT6EXLa4`ih }3V4.eq%Xg' );
define( 'NONCE_KEY',         ',jfifZ7vBKks3v.qNSS6VqaR_1H$.Mu@JV m?vAqHA{~C!O3tYTe-t;pg7RY[98I' );
define( 'AUTH_SALT',         'NFr3H(x.K;y=4EWcu]K+QfP@z=3ZbazaIui@+bRKye|l;X1Cl4  4LN_8JPr`Fc2' );
define( 'SECURE_AUTH_SALT',  'sU8x>jlIYI=>6*ZM/;~6uU5-]|Ky=k%Uu*fyIklm?#L-IM=Bnq6_>y6L-zGw[(b&' );
define( 'LOGGED_IN_SALT',    ',<.S6t0_@=r?}O<I7|Y5f5QmO9|<=OA3cwBFF2==*Tl;F*:D=[e5.iN7UHum,Uw/' );
define( 'NONCE_SALT',        'nT(nO~[KOojuu3gd2eBEb7aFojt-?>iPlA]#,@|!]B~((a&C,%}IAyHeZzAQlmLg' );
define( 'WP_CACHE_KEY_SALT', ')gljl>FZ>5ZQRn&9-7USsbSu#jxUP`$s72{!`(u!^2zUuDz!=Y$@W1X&dCUMqrhY' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '4edc14fae350db966f6518b733a150cf' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
