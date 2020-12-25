<?php
/**
 * PHPEcommerceFramework
 * Framework loader - acts as a single point of access to the Framework
 *
 * @version 1.0
 * @author Michael Peacock
 */
 
// first and foremost, start our sessions
session_start();

// setup some definitions
// The applications root path, so we can easily get this path from files located in other folders
define( "FRAMEWORK_PATH", dirname( __FILE__ ) ."/" );


// require our registry
require_once('registry/registry.class.php');
$registry = PHPEcommerceFrameworkRegistry::singleton();
$registry->getURLData();
// get database connection details
require_once('config.php');
// store core objects in the registry.
$registry->storeObject('mysql.database', 'db');
$registry->storeObject('template', 'template');
$registry->storeObject('authentication', 'authenticate');
$registry->storeSetting('default','view');
$registry->storeSetting('sitename','Juniper Theatricals Store');
$registry->storeSetting('siteshortname','JTS');
$registry->storeSetting('siteurl','http://localhost/book4/chapter11/');
$registry->storeSetting('payment.paypal.email','paypalemailaddress@junipertheatricals.test');
$registry->storeSetting('payment.currency','USD');
$registry->storeSetting('payment.testmode','NO');
$registry->storeSetting('payment.paypal.email','paypalemailaddress@junipertheatricals.test');
// create a database connection
$registry->getObject('db')->newConnection($config['db_ecom_host'], $config['db_ecom_user'], $config['db_ecom_pass'], $config['db_ecom_name']);
// check post data for users trying to login, and session data for users who are logged in
$registry->getObject('authenticate')->checkForAuthentication();


// set the default skin setting (we will store these in the database later...)
$registry->storeSetting('default', 'skin');
$registry->storeSetting(1, 'default_shipping_method');
// populate our page object from a template file
$registry->getObject('template')->buildFromTemplates('header.tpl.php', 'main.tpl.php');

// basket
require_once('controllers/basket/controller.php');
$basket = new Basketcontroller( $registry, false );
$basket->smallBasket();

$activeControllers = array();
$registry->getObject('db')->executeQuery('SELECT controller FROM controllers WHERE active=1');
while( $activeController = $registry->getObject('db')->getRows() )
{
	$activeControllers[] = $activeController['controller'];
}
$currentController = $registry->getURLBit( 0 );
if( in_array( $currentController, $activeControllers ) )
{
	require_once( FRAMEWORK_PATH . 'controllers/' . $currentController . '/controller.php');
	$controllerInc = $currentController.'controller';
	$controller = new $controllerInc( $registry, true );
}
else
{
	require_once( FRAMEWORK_PATH . 'controllers/page/controller.php');
	$controller = new Pagecontroller( $registry, true );
}


// parse it all, and spit it out
$registry->getObject('template')->parseOutput();
print $registry->getObject('template')->getPage()->getContent();


exit();

?>