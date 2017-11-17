<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'imarkcli_cityfam');

/** MySQL database username */
define('DB_USER', 'imarkcli_cityfam');

/** MySQL database password */
define('DB_PASSWORD', '0Xs[BQgJN6eV');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'Ev*?aa!$~[9BABzq8]GWO+.5L2b-O?kZ*UvJgbPM|/AHoXJ3LH ?,~X,Y2InOg&u');
define('SECURE_AUTH_KEY',  '`C5cxLSQG;:>~%i~L/@{*P`4#3Ihtjn(S05/=DY9I@^wm#bD ghtE;c A(lCNlfv');
define('LOGGED_IN_KEY',    ';NN.Nx)IS]Lddg{#uXuUB<o;Y7n>qzIbv/a&(7lHh>1hA{~e[WvS8F.qu,VOFGVd');
define('NONCE_KEY',        'Md!~)LSAq;xac*KRv{^N<~^$GB7wMTWsj>MwHJr&D.%S*m7uCy%]y$yJi}?aS$$F');
define('AUTH_SALT',        'p-qV-%kY_N`w/Iq+sl;MS:DC7_VqeYlSFll7$&oN#2=w8O+CN@vj3>UlCgZFNr!q');
define('SECURE_AUTH_SALT', 'Uf]0^L&PtbX_*jmh}IWi7f7}/gS*j4AD?E/^o({qxWj;)^i]n:)aCT[)eX<*!P%i');
define('LOGGED_IN_SALT',   'FeN$uxs$;OLLrS{PYZb#vDk>gcu)wscpQA>Fe!4Y>fVZKTaB/X?y^kbw6?1|tT:)');
define('NONCE_SALT',       'FBkUaZhjL{pUxD,mKH9M?)8@,9!b,FCzL]Bxm;(N!YXntc9R1DgYp(][Nsbs|KN=');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
