<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'shivyogsl');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'shivShiva');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';QhNak{+05I/Tv~z|JV)a*Juxwm|tZsAZn|CT_}b|j3RU|@|e3dzmB7:ls.}j-E0');
define('SECURE_AUTH_KEY',  ' A@WH fFJyws}] h[MG,(A7}mc^Vm%^p$uE@b#sB@Hkkj|N-u_.t?]D$|nB_eAMp');
define('LOGGED_IN_KEY',    '+1}-HUY-7^Z)V`y$d-3q:=]J]32Avt/dAhTZSRSo(d!x7:996KiDU3?t-Mgwt4jE');
define('NONCE_KEY',        'lEy4uP1Ewf@vr7D0(-9#_iV1hw@w|cMXe.fVK/2uPscSnsiFQb0+/sW~3P]x0xm2');
define('AUTH_SALT',        '!++L30P9nVzSNmcm,NPI{_AL7+f,KBuPgKX[B *^RMM$lD>92dY|K ~3{9pcuTT~');
define('SECURE_AUTH_SALT', 'YLK&|ZUs3>7dh+{[ 9Sx@a<_-e|w6Gh3]+f^W}MS07f.J;3@>idaZV8JL`{BC5sq');
define('LOGGED_IN_SALT',   ')i&N{?=rhb*6Mb&_PoJc7Fqoygy`yF|yd&z<<;0CQi5RPr@1%C8??Nibd<Y!(pyz');
define('NONCE_SALT',       'O}g8r55H?9?.++^VGIX-FiS$ E7.a$apRextRTB9iQZ^m`GOA$Zi8`/-`~cG@.fw');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
