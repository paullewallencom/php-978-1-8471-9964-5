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
// store core objects in the registry.
$registry->storeObject('mysql.database', 'db');
$registry->storeObject('template', 'template');
$registry->storeSetting('default','view');
// create a database connection
$registry->getObject('db')->newConnection('localhost', 'root', '', 'book4database');

//$registry->getObject('auth')->initialise();

// set the default skin setting (we will store these in the database later...)
$registry->storeSetting('default', 'skin');

// populate our page object from a template file
$registry->getObject('template')->buildFromTemplates('header.tpl.php', 'main.tpl.php');

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