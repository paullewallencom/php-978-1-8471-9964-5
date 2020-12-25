<?php
/**
 * The PCARegistry object
 * Implements the Registry and Singleton design patterns
 * Building a PHP Ecommerce Framework
 *
 * @version 1.0
 * @author Michael Peacock
 */
 
class PHPEcommerceFrameworkRegistry {
	
	/**
	 * The array of objects being stored within the registry
	 * @access private
	 */
	private static $objects = array();
	
	/**
	 * The array of settings being stored within the registry
	 * @access private
	 */
	private static $settings = array();
	
	
	/**
	 * The instance of the registry
	 * @access private
	 */
	private static $instance;
	
	private static $urlPath;
	private static $urlBits = array();
	
	/**
	 * Private constructor to prevent it being created directly
	 * @access private
	 */
	private function __construct()
	{
	
	}
		
	/**
	 * singleton method used to access the object
	 * @access public
	 * @return 
	 */
	public static function singleton()
	{
		if( !isset( self::$instance ) )
		{
			$obj = __CLASS__;
			self::$instance = new $obj;
		}
		
		return self::$instance;
	}
	
	/**
	 * prevent cloning of the object: issues an E_USER_ERROR if this is attempted
	 */
	public function __clone()
	{
		trigger_error( 'Cloning the registry is not permitted', E_USER_ERROR );
	}
	
	/**
	 * Stores an object in the registry
	 * @param String $object the name of the object
	 * @param String $key the key for the array
	 * @return void
	 */
	public function storeObject( $object, $key )
	{
		if( strpos( $object, 'database' ) !== false )
		{
			$object_a = str_replace( '.database', 'database', $object);
			$object = str_replace( '.database', '', $object);
			require_once('databaseobjects/' . $object . '.database.class.php');
			$object = $object_a;
		}
		else
		{
			require_once('objects/' . $object . '.class.php');
		}
		
		self::$objects[ $key ] = new $object( self::$instance );
	}
	
	/**
	 * Gets an object from within the registry
	 * @param String $key the array key used to store the object
	 * @return object - the object
	 */
	public function getObject( $key )
	{
		if( is_object ( self::$objects[ $key ] ) )
		{
			return self::$objects[ $key ];
		}
	}
	
	/**
	 * Stores a setting in the registry
	 * @param String $data the setting we wish to store
	 * @param String $key the key for the array to access the setting
	 * @return void
	 */
	public function storeSetting( $data, $key )
	{
		self::$settings[ $key ] = $data;
	}
	
	/**
	 * Gets a setting from the registry
	 * @param String $key the key used to store the setting
	 * @return String the setting
	 */
	public function getSetting( $key )
	{
		return self::$settings[ $key ];
	}
	
	
	/**
	 * Gets data from the current URL
	 * @return void
	 */
	public function getURLData()
	{
		$urldata = (isset($_GET['page'])) ? $_GET['page'] : '' ;
		self::$urlPath = $urldata;
		if( $urldata == '' )
		{
			self::$urlBits[] = 'home';
			self::$urlPath = 'home';
		}
		else
		{
			$data = explode( '/', $urldata );
			while ( !empty( $data ) && strlen( reset( $data ) ) === 0 ) 
			{
		    	array_shift( $data );
		    }
		    while ( !empty( $data ) && strlen( end( $data ) ) === 0) 
		    {
		        array_pop($data);
		    }
			self::$urlBits = $this->array_trim( $data );
		}
	}
	
	public function redirectUser( $urlPath, $header, $message, $admin = false)
	{
		self::getObject('template')->buildFromTemplates('redirect.tpl.php');
		self::getObject('template')->getPage()->addTag( 'header', $header );
		self::getObject('template')->getPage()->addTag( 'message', $message );
		if( $admin != true )
		{
			self::getObject('template')->getPage()->addTag('url', $urlPath );
		}
		else
		{
			//
		}
	}
	
	public function getURLBits()
	{
		return self::$urlBits;
	}
	
	public function getURLBit( $whichBit )
	{
		return self::$urlBits[ $whichBit ];
	}
	
	public function getURLPath()
	{
		return self::$urlPath;
	}
	
	private function array_trim( $array ) 
	{
	    while ( ! empty( $array ) && strlen( reset( $array ) ) === 0) 
	    {
	        array_shift( $array );
	    }
	    
	    while ( !empty( $array ) && strlen( end( $array ) ) === 0) 
	    {
	        array_pop( $array );
	    }
	    
	    return $array;
	}
	
}

?>