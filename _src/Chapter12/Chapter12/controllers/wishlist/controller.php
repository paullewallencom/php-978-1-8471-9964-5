<?php

class Wishlistcontroller {

	public function __construct(  PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();
			$this->filterProducts( $urlBits );
			if( !isset( $urlBits[1] ) )
			{
				$this->viewList();
			}
			else
			{
				switch( $urlBits[1] )
				{
					case 'view':
						$this->viewSpecificList( intval( $urlBits[2] ) );
						break;
					case 'add':
						$this->addProduct(  $urlBits[2]  );
						break;
					default:
						$this->viewList();
						break;		
				}
			}
			
		}
	}
	
	/**
	 * Add a product to a users wish-list
	 * @param String $productPath the product path
	 * @return void
	 */
	private function addProduct( $productPath )
	{
		// check product path is a valid and active product
		$pathToRemove = 'wishlist/add/';
		$productPath = str_replace( $pathToRemove, '', $this->registry->getURLPath() );
		require_once( FRAMEWORK_PATH . 'models/products/model.php');
		$this->product = new Product( $this->registry, $productPath );
		if( $this->product->isValid()
		{
			// check if user is logged in or not
			if( $this->registry->getObject('authenticate')->loggedIn() == true )
			{
				// insert the wish
				$wish = array();
				$pdata = $this->product->getData();
				$wish['product'] = $pdata['ID'];
				$wish['quantity'] = 1;
				$wish['user'] = $this->registry->getObject('authenticate')->getUserID();
				$this->registry->getObject('db')->insertRecords('wish_list_products', $wish );
				// inform the user
				$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Product added to your wish list');
				$this->registry->getObject('template')->getPage()->addTag('message_heading', 'A ' . $pdata['name'] .' has been added to your wish list');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
			}
			else
			{
				// insert the wish
				$wish = array();
				$wish['sessionID'] = session_id();
				$wish['user'] = 0;
				$wish['IPAddress'] = $_SERVER['REMOTE_ADDR'];
				$pdata = $this->product->getData();
				$wish['product'] = $pdata['ID'];
				$wish['quantity'] = 1;
				$this->registry->getObject('db')->insertRecords('wish_list_products', $wish );
				// inform the user
				$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Product added to your wish list');
				$this->registry->getObject('template')->getPage()->addTag('message_heading', 'A ' . $pdata['name'] .' has been added to your wish list');
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
			}
		}
		else
		{
			// we can't insert the wish, so inform the user
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Invalid product');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Unfortunately, the product you tried to add to your wish list was invalid, and was not added, please try again');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}	
		
	}
	
	private function viewList()
	{
		$s = session_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		$uid = $this->regisry->getObject('authenticate')->getUserID();
		if( $this->registry->getObject('authenticate')->loggedIn() )
		{
			$when = strtotime("-1 week");
			$when = date("Y-m-d h:i:s", $when);
			$sql = "SELECT p.price as product_price, v.name as product_name, c.path as product_path FROM content c, content_versions v, content_types_products p, wish_list_items w WHERE c.ID=w.product AND p.content_version=v.ID AND v.ID=c.current_revision AND c.active=1 AND ( w.user='{$uid}' OR ( w.sessionID='{$s}' AND w.IPAddress='{$ip}' AND w.dateadded > '{$when}'  ) )";
		}
		else
		{
			$sql = "SELECT p.price as product_price, v.name as product_name, c.path as product_path FROM content c, content_versions v, content_types_products p, wish_list_items w WHERE c.ID=w.product AND p.content_version=v.ID AND v.ID=c.current_revision AND c.active=1 AND w.user=0 AND ( w.sessionID='{$s}' AND w.IPAddress='{$ip}' AND w.dateadded > '{$when}'   )";
		}
		
		$cache = $this->registry->getObject('database')->cacheQuery( $sql );
		if( $this->registry->getObject('database')->numRowsFromCache( $cache ) == 0 )
		{
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'No products');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Unfortunately, there are no products in your wish-list at this time.');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}
		else
		{
			$this->registry->getObject('template')->getPage()->addTag( 'wishes', array( 'SQL', $cache ) );
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'wishlist.tpl.php', 'footer.tpl.php');
		}
		
	}	
	
	
	
	
}

?>