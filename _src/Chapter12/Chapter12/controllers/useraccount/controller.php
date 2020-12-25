<?php

class Useraccountcontroller {
	
	/**
	 * Registry object reference
	 */
	private $registry;
	private $order;
	
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
		{
			$urlBits = $this->registry->getURLBits();
			if( !isset( $urlBits[1] ) )
			{
				$this->mainUI();
			}
			else
			{
				
				switch( $urlBits[1] )
				{
					case 'update-account':
						$this->saveChangesToAccount();
						break;
					case 'change-password':
						$this->changePassword();
						break;	
					case 'view-order':
						echo $this->viewOrder( intval(  $urlBits[2] ) );
						break;
					case 'cancel-order':
						$this->cancelOrder( intval(  $urlBits[2] ) );
						break;
					case 'confirm-cancel-order':
						$this->confirmCancelOrder( intval(  $urlBits[2] ) );
						break;		
					default:
						$this->mainUI();
						break;				
				}
				
			}
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'Please login' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, only logged in users can manage their accounts' );
		}
		
	}
	
	private function mainUI()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'account/account.tpl.php','footer.tpl.php');
		$sql = "SELECT e.*, u.email FROM users_extra e, users u WHERE e.user_id=u.ID and u.ID=" .  $this->registry->getObject('authenticate')->getUserID();
		$this->registry->getObject('db')->executeQuery( $sql );
		$this->registry->getObject('template')->dataToTags( $this->registry->getObject('db')->getRows(), '');
		$this->listOrders();
	}
	
	private function listOrders()
	{
		$sql = "SELECT os.name as status_name, o.ID as order_id, (o.products_cost + o.shipping_cost) as cost, DATE_FORMAT(o.timestamp, '%D %b %Y') as order_placed FROM orders o, order_statuses os WHERE os.ID=o.status AND o.user_id=" . $this->registry->getObject('authenticate')->getUserID();
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$this->registry->getObject('template')->getPage()->addTag('orders', array('SQL', $cache ) );
		
	}
	
	private function viewOrder( $order )
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'account/order.tpl.php','footer.tpl.php');
		require_once( FRAMEWORK_PATH . 'models/order/model.php');
		$this->order = new Order( $this->registry, $order );
		if( $this->order->isValid() )
		{
			if( $this->order->getUser() == $this->registry->getObject('authenticate')->getUserID() )
			{
				$this->registry->getObject('template')->getPage()->addTag('order', $order );	
				$this->registry->getObject('template')->getPage()->addTag('items', array( 'SQL', $this->order->getItemsCache() ) );
				$this->registry->getObject('template')->getPage()->addTag('date_placed', $this->order->getDatePlaced() );
				$this->registry->getObject('template')->getPage()->addTag('status', $this->order->getStatusName() );
				$this->registry->getObject('template')->getPage()->addTag('pc', $this->order->getProductsCost() );
				$this->registry->getObject('template')->getPage()->addTag('sc', $this->order->getShippingCost() );
				$this->registry->getObject('template')->getPage()->addTag('toc', $this->order->getProductsCost() + $this->order->getShippingCost() );
			}
			else
			{
				$this->registry->redirectUser( 'useraccount', 'Invalid order', 'The order cannot be viewed as it is not tied to your account', $admin = false );
			}
		}
		else
		{
			$this->registry->redirectUser( 'useraccount', 'Invalid order', 'The order was not found', $admin = false );
		}
	}
	
	private function confirmCancelOrder( $orderId )
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'account/confirm-cancel.tpl.php','footer.tpl.php');
		require_once( FRAMEWORK_PATH . 'models/order/model.php');
		$this->order = new Order( $this->registry, $orderId );
		if( $this->order->isValid() )
		{
			if( $this->order->getUser() == $this->registry->getObject('authenticate')->getUserID() )
			{
				$this->registry->getObject('template')->getPage()->addTag('orderid', $orderId );
			}
			else
			{
				$this->registry->redirectUser( 'useraccount', 'Invalid order', 'The order was not cancelled as it was not tied to your account', $admin = false );
			}
		}
		else
		{
			$this->registry->redirectUser( 'useraccount', 'Invalid order', 'The order was not found', $admin = false );
		}
	}
	
	private function cancelOrder( $orderId )
	{
		require_once( FRAMEWORK_PATH . 'models/order/model.php');
		$this->order = new Order( $this->registry, $orderId );
		if( $this->order->isValid() )
		{
			if( $this->order->getUser() == $this->registry->getObject('authenticate')->getUserID() )
			{
				$this->order->cancelOrder('user');
				$this->registry->redirectUser( 'useraccount', 'Order cancelled', 'The order has been cancelled', $admin = false );
			}
			else
			{
				$this->registry->redirectUser( 'useraccount', 'Invalid order', 'The order was not cancelled as it was not tied to your account', $admin = false );
			}
		}
		else
		{
			$this->registry->redirectUser( 'useraccount', 'Invalid order', 'The order was not found', $admin = false );
		}
	}
	
	private function saveChangesToAccount()
	{
		// default delivery address
		$changes = array();
		$changes['default_shipping_name'] = $this->registry->getObject('db')->sanitizeData( $_POST['default_shipping_name'] );
		$changes['default_shipping_address'] = $this->registry->getObject('db')->sanitizeData( $_POST['default_shipping_address'] );
		$changes['default_shipping_address2'] = $this->registry->getObject('db')->sanitizeData( $_POST['default_shipping_address2'] );
		$changes['default_shipping_city'] = $this->registry->getObject('db')->sanitizeData( $_POST['default_shipping_city'] );
		$changes['default_shipping_postcode'] = $this->registry->getObject('db')->sanitizeData( $_POST['default_shipping_postcode'] );
		$changes['default_shipping_country'] = $this->registry->getObject('db')->sanitizeData( $_POST['default_shipping_country'] );
		$this->registry->getObject('db')->updateRecords( 'users_extra', $changes, 'user_id=' .  $this->registry->getObject('authenticate')->getUserID() );
		
		// email address
		// the format of the email address should be checked ideally
		$changes = array();
		$changes['email'] = $this->registry->getObject('db')->sanitizeData( $_POST['email'] );
		$this->registry->getObject('db')->updateRecords( 'users', $changes, 'ID=' .  $this->registry->getObject('authenticate')->getUserID() );
		$this->registry->redirectUser( 'useraccount', 'Account details changed', 'Your account details have been saved', $admin = false );
	}
	
	private function changePassword()
	{
		if( isset( $_POST['old_password'] ) )
		{
			$oldPassword = md5( $_POST['old_password'] );
			// check their new password is confirmed
			if( $_POST['new_password'] == $_POST['confirm_newpassword'] )
			{
				$uid = $this->registry->getObject('authenticate')->getUserID();
				$checkPwdSQL = "SELECT * FROM users WHERE ID={$uid} AND         password='{$oldPassword}'";
				$this->registry->getObject('db')->executeQuery( $checkPwdSQL );
				// check their old password is valid
				If( $this->registry->getObject('db')->numRows() == 1 )
				{
					$changes = array();
					$changes['password'] = md5( $_POST['new_password'] );
					// update their current password to the new password
					$this->registry->getObject('db')->updateRecords( 'users', $changes, 'ID=' . $uid );
					// output success message here
					$this->registry->redirectUser( 'useraccount', 'Password changed', 'Your password has been changed', $admin = false );
				}
				else
				{
					// do our error output here
					$this->registry->redirectUser( 'useraccount', 'Password incorrect', 'Your old password was not correct, so your password was not changed', $admin = false );
				}
			}
			else
			{
				// error output
				$this->registry->redirectUser( 'useraccount', 'Password match error', 'Your new password and confirmation of it were not the same, so your password was not changed', $admin = false );
			}
		}
		else
		{
			// display the form to allow the user to change their password
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'account/change-password.tpl.php','footer.tpl.php');
		}
		

	}


}

?>