<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/Kohana/Core'.EXT;

if (is_file(APPPATH.'classes/Kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/Kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/Kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('America/Chicago');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Optionally, you can enable a compatibility auto-loader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(array('Kohana', 'auto_load_lowercase'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

/**
 * Set the mb_substitute_character to "none"
 *
 * @link http://www.php.net/manual/function.mb-substitute-character.php
 */
mb_substitute_character('none');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

if (isset($_SERVER['SERVER_PROTOCOL']))
{
	// Replace the default protocol.
	HTTP::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV'])) {
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}

// I must use realpath here because if the script is symlinked to a different location,
// that would throw off the SCRIPT_FILENAME path.
// This is because apache sees the symlinked path, but php will see the actual file path.
$rpdr = pathinfo(realpath($_SERVER['SCRIPT_FILENAME']), PATHINFO_DIRNAME);
if ($rpdr != '/') $rpdr .= '/'; // Append a slash if it's not the root dir itself.

// The web path is simplier
$rwdr = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);
if ($rwdr != '/') $rwdr .= '/'; // Append a slash if it's not the root dir itself.

/**
 * The physical directory of the Core Plus installation.
 * DOES have a trailing slash.
 *
 * Example: /home/someone/public_html/myinstall/
 * @var string
 */
if (!defined('ROOT_PDIR')) define('ROOT_PDIR', $rpdr);

/**
 * The location of the root installation based on the browser get string.
 * DOES have a trailing slash.
 *
 * Example: /~someone/myinstall/
 * @var string
 */
if (!defined('ROOT_WDIR')) define('ROOT_WDIR', $rwdr);

// Cleanup
unset($rpdr, $rwdr);

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
Kohana::init(array(
	'base_url'   => ROOT_WDIR,
	'profile'	 => ( Kohana::$environment == Kohana::DEVELOPMENT ) ? TRUE : FALSE,
	'index_file' => FALSE,
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

Session::$default = 'cookie';


/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	'database'      => MODPATH.'database',      // Database access
	'orm'           => MODPATH.'orm',           // Object Relationship Mapping
	'kostache'      => MODPATH.'kostache',
	'email'         => MODPATH.'kohana-email',
	// 'auth'       => MODPATH.'auth',       // Basic authentication
	// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool   
	// 'image'      => MODPATH.'image',      // Image manipulation
	// 'minion'     => MODPATH.'minion',     // CLI Tasks
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
));

/**
 * Cookie Salt
 * @see  http://kohanaframework.org/3.3/guide/kohana/cookies
 * 
 * If you have not defined a cookie salt in your Cookie class then
 * uncomment the line below and define a preferrably long salt.
 */
if(Kohana::$config->load('beans')->get('cookie_salt')){
	Cookie::$salt = Kohana::$config->load('beans')->get('cookie_salt');
}
else{
	Cookie::$salt = 'snakeoilsnakeoilsnakeoilsnakeoil';
}

// Include our configured routes.
require APPPATH . 'routes' . EXT;
