<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе
 * установки. Необязательно использовать веб-интерфейс, можно
 * скопировать файл в "wp-config.php" и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define('DB_NAME', 'educoin_platform');

/** Имя пользователя MySQL */
define('DB_USER', 'root');

/** Пароль к базе данных MySQL */
define('DB_PASSWORD', '');

/** Имя сервера MySQL */
define('DB_HOST', 'localhost');

/** Кодировка базы данных для создания таблиц. */
define('DB_CHARSET', 'utf8mb4');

/** Схема сопоставления. Не меняйте, если не уверены. */
define('DB_COLLATE', '');

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '#*k:P8(SGDm89nZ1NPaO_Gr%IW*=KC9]{b^%n?Wc*>l5?u#_)|zZeZU2FIh@R2Mn');
define('SECURE_AUTH_KEY',  'ujorEGrUJm<[5@W?Os#E^$K_-Ew<qV%aGQVyu},wpk35Sa9:hlZGZ)t[-t]F.u7~');
define('LOGGED_IN_KEY',    'ptMn}V+X&JLy7tAz9AF&DA0S8;+sOA2c*<C7hjM;Q|=Fz]X(Z0q9{otqyNu+Vmb7');
define('NONCE_KEY',        'U44$J,ca((&ROEIp}o5i0.s6`|W;o=K2UJ1hhJw/mA<Tm-#wZ Vwl#++SHcx#^_V');
define('AUTH_SALT',        'Dy8=wkFNUvlYM;:y7*9:sSpLeM|,tjX8.owiQiVwU~+iOe46Sz:2#a>G]E20)a7?');
define('SECURE_AUTH_SALT', '?Un[Sr>9};QFL~j]cgrfgJF@mBU5O*L}FFP$gQ3}Nl;MYR4a@<*g.Tz;70_$n38C');
define('LOGGED_IN_SALT',   '%YO=!|[ lgk7]iT3$iv_?yPlcoOp!Q&z5i.)<-mKUL[{I:J(YBX&hB[82._`!zlm');
define('NONCE_SALT',       '+%`CLm1BPzIn2F,<G@Q0i*jXA0d{{Ee@,#9M5U%sUo$x@eFYIw#Tw@N,qQH}a#3)');

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix  = 'myedc_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 * 
 * Информацию о других отладочных константах можно найти в Кодексе.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
 
/* AlexeyDenisiuk: START */
//define('WP_DEBUG', false);

// this mode will redirecting everyone to '/maintenance.php' page
define( 'MAINTENANCE_MODE', false );
// this mode will for everyone except 1 person with next ID
define( 'MAINTENANCE_MODE_ALLOWED_USER_ID', 1 );

ini_set( 'log_errors','On' );
ini_set( 'display_errors','Off' );
ini_set( 'error_reporting', E_ALL );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SAVEQUERIES', false );
define( 'SCRIPT_DEBUG', false );

// disable post revisions
define( 'AUTOSAVE_INTERVAL', 300 );
define( 'WP_POST_REVISIONS', false );

// disable theme and plugin editors from admin panel
define( 'DISALLOW_FILE_EDIT', true );

// SMTP
define( 'WPMS_ON', true );
define( 'WPMS_SMTP_PASS', 's78sf01199XxRmCb' );
/* AlexeyDenisiuk: END */

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Инициализирует переменные WordPress и подключает файлы. */
require_once(ABSPATH . 'wp-settings.php');
