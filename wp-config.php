<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'intranetatmb' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'F,1},}WSG-,jy|I~D:`hM/4oXW{x &&a71qRO;EtEBqOUBL77aZY7&|=[29uJ%!2' );
define( 'SECURE_AUTH_KEY',  'a<%#hK~/()[MZ}xJ$SbT+jL[T+*L[YO7xDr/l[T:K_oOj#BGWb/b`$+`gw5>DJ:B' );
define( 'LOGGED_IN_KEY',    '0qkZy<ojtz`L^]<x_]({Q~v:{~n(3.y#CdWG[#Y@B?u5T&x0T!|O;}JRX]xdI(<o' );
define( 'NONCE_KEY',        'I2hP=#PIh_0g*.L0cmiT!x[eBJ7^0%Ljbz-g|4cmt%PXuzG !v/0DtC=.@s[.-ZI' );
define( 'AUTH_SALT',        'U5UN2K8g9?[b`GLl=XPFS< N+:iN^B7AA;oLSt=g-LvLH#TMVd$)#;&Ck+Q;5]{?' );
define( 'SECURE_AUTH_SALT', ':lTUVQFvOUlc?O#5*6mEa)E6asa`I6OjKF%(3:[]BMbn0VjJgricIM/._1Ad6ED|' );
define( 'LOGGED_IN_SALT',   'k^ujb^/MlQvs|u3(eCRh,b{N0zcc1%J~*AEi/(a=cHK r0MW~6t Tj%^yb&A#;.H' );
define( 'NONCE_SALT',       '5]&,rXf e<4fIg)_4BrL%ZO5H&5%A raO}R3{<]9viX9$B2bINQ/5]oxB->;7 }L' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');
