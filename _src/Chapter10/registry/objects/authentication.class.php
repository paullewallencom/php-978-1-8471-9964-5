<?php
/**
 * Authentication manager
 * 
 *
 * @version 1.0
 * @author Michael Peacock
 */
class authentication {

	private $userID;
	private $loggedIn = false;
	private $admin = false;
	
	private $groups = array();
	
	private $banned = false;
	private $username;
	private $justProcessed = false;
	
    public function __construct() 
    {
		
    }
    
    public function checkForAuthentication()
    {
    	
    	if( isset( $_SESSION['phpecomf_auth_session_uid'] ) && intval( $_SESSION['phpecomf_auth_session_uid'] ) > 0 )
    	{
    		$this->sessionAuthenticate( intval( $_SESSION['phpecomf_auth_session_uid'] ) );
    	}
    	elseif( isset(  $_POST['ecomf_auth_user'] ) &&  $_POST['ecomf_auth_user'] != '' && isset( $_POST['ecomf_auth_pass'] ) && $_POST['ecomf_auth_pass'] != '')
    	{
    		$this->postAuthenticate( PHPEcommerceFrameworkRegistry::getObject('db')->sanitizeData( $_POST['ecomf_auth_user'] ), md5( $_POST['ecomf_auth_pass'] ) );
    	}
	     //echo $this->userID;
    }
    
    private function sessionAuthenticate( $uid )
    {
    	
    	$sql = "SELECT u.ID, u.username, u.active, u.email, u.admin, u.banned, u.name, (SELECT GROUP_CONCAT( g.name SEPARATOR '-groupsep-' ) FROM groups g, group_memberships gm WHERE g.ID = gm.group AND gm.user = u.ID ) AS groupmemberships FROM users u WHERE u.ID={$uid}";
    	PHPEcommerceFrameworkRegistry::getObject('db')->executeQuery( $sql );
    	if( PHPEcommerceFrameworkRegistry::getObject('db')->numRows() == 1 )
    	{
    		$userData = PHPEcommerceFrameworkRegistry::getObject('db')->getRows();
    		if( $userData['active'] == 0 )
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'inactive';
    			$this->active = false;
    		}
    		elseif( $userData['banned'] != 0)
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'banned';
    			$this->banned = false;
    		}
    		else
    		{
    			$this->loggedIn = true;
    			$this->userID = $uid;
    			$this->admin = ( $userData['admin'] == 1 ) ? true : false;
    			$this->username = $userData['username'];
    			$this->name = $userData['name'];
    			
    			$groups = explode( '-groupsep-', $userData['groupmemberships'] );
    			$this->groups = $groups;
    		}
    		
    	}
    	else
    	{
    		$this->loggedIn = false;
    		$this->loginFailureReason = 'nouser';
    	}
    	if( $this->loggedIn == false )
    	{
    		$this->logout();
    	}
    }
    
    private function postAuthenticate( $u, $p )
    {
    	$this->justProcessed = true;
    	$sql = "SELECT u.ID, u.username, u.email, u.admin, u.banned, u.active, u.name, (SELECT GROUP_CONCAT( g.name SEPARATOR '-groupsep-' ) FROM groups g, group_memberships gm WHERE g.ID = gm.group AND gm.user = u.ID ) AS groupmemberships FROM users u WHERE u.username='{$u}' AND u.password_hash='{$p}'";
    	//echo $sql;
    	PHPEcommerceFrameworkRegistry::getObject('db')->executeQuery( $sql );
    	if( PHPEcommerceFrameworkRegistry::getObject('db')->numRows() == 1 )
    	{
    		$userData = PHPEcommerceFrameworkRegistry::getObject('db')->getRows();
    		if( $userData['active'] == 0 )
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'inactive';
    			$this->active = false;
    		}
    		elseif( $userData['banned'] != 0)
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'banned';
    			$this->banned = false;
    		}
    		else
    		{
    			$this->loggedIn = true;
    			$this->userID = $userData['ID'];
    			$this->admin = ( $userData['admin'] == 1 ) ? true : false;
    			$_SESSION['phpecomf_auth_session_uid'] = $userData['ID'];
    			
    			$groups = explode( '-groupsep-', $userData['groupmemberships'] );
    			$this->groups = $groups;
    		}
    		
    	}
    	else
    	{
    		$this->loggedIn = false;
    		$this->loginFailureReason = 'invalidcredentials';
    	}
    }
    
    function logout() 
	{
		$_SESSION['phpecomf_auth_session_uid'] = '';
	}
    
    
    public function getUserID()
    {
	    return $this->userID;
    }
    
    public function isLoggedIn()
    {
	    return $this->loggedIn;
    }
    
    public function isAdmin()
    {
    	return $this->admin;
    }
    
    public function getUsername()
    {
    	return $this->username;
    }
    
    public  function isMemberOfGroup( $group )
    {
	    if( in_array( $group, $this->groups ) )
	    {
		    return true;
	    }
	    else
	    {
		    return false;
	    }
    }
    
    public function justProcessed()
    {
	    return $this->justProcessed;
    }
    
}
?>