<?php

class Basketcontroller {
	
	/**
	 * Registry object reference
	 */
	private $registry;
	private $contents;
	private $embedded = false;
	
	private $basket;
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		
		require_once( FRAMEWORK_PATH . 'models/basket/model.php');
		$this->basket = new Basket( $this->registry );
		$this->basket->checkBasket();
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();
			if( !isset( $urlBits[1] ) )
			{
				$this->viewBasket();
			}
			else
			{
				switch( $urlBits[1] )
				{
					case 'view':
						$this->viewBasket();
						break;	
					case 'add-product':
						echo $this->addProduct( $urlBits[2], 1);
						break;
					case 'update':
						$this->updateBasket();
						break;
					case 'remove-product':
						$this->removeProduct( intval(  $urlBits[2] ) );
						break;		
					default:
						$this->viewBasket();
						break;				
				}
			}
			
		}
	}
	
	/**
	 * Add product to the basket
     * @param String productPath the product reference
     * @param int $quantity the quantity of the product
     * @return String a message for the controller
     */	
	public function addProduct( $productPath, $quantity=1 )
	{
		// have we run the checkBasket method yet?
		if( ! $this->basket->isChecked == true ) { $this->basket->checkBasket(); }
		
		$response = $this->basket->addProduct( $productPath, $quantity );
		echo $response;
		if( $response == 'success' )
		{
			$this->registry->redirectUser('products/view/' . $productPath, 'Product added', 'The product has been added to your basket', false );
		}
		elseif( $response == 'stock' )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'Out of stock' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, that product is out of stock, and could not be added to your basket.' );
		}
		elseif( $response == 'noproduct' )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'Product not found' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, that product was not found.' );
		}
		
	}
	
	public function viewBasket()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'viewbasket.tpl.php','footer.tpl.php');
		
		$contents = $this->basket->getContents();
		
		$products = array();
		
		foreach( $contents as $reference => $data )
		{
			$data['basket_id'] = $data['basket'];
			$data['basket'] = '';
			$products[] = $data;
			
		}
		
		$basketCache = $this->registry->getObject('db')->cacheData( $products );
		$this->registry->getObject('template')->getPage()->addTag( 'products', array( 'DATA', $basketCache ) );
		$this->registry->getObject('template')->getPage()->addTag( 'basket_subtotal', $this->basket->getCost() );
		$this->registry->getObject('template')->getPage()->addTag( 'basket_total', $this->basket->getCost() );
		
	}
	
	/**
	 * Small basket - prepare small embedded basket
	 * @return void
	 */
	public function smallBasket()
	{
		if( $this->basket->isChecked() == false ) { $this->basket->checkBasket(); }
		// set our embedded property
		$this->embedded = true;
		// check that the basket is not empty
		if( $this->basket->isEmpty() == false )
		{
			// basket isn't empty so use the basket template, and set the numBasketItems and basketCost template variables
			$this->registry->getObject('template')->addTemplateBit('basket',  'basket.tpl.php');
			$this->registry->getObject('template')->getPage()->addPPTag('numBasketItems', $this->basket->getNumProducts() );
			$this->registry->getObject('template')->getPage()->addPPTag('basketCost', $this->basket->getCost());
			
		}
		else
		{
			// basket is empty - so use the empty basket template
			$this->registry->getObject('template')->addTemplateBit('basket', 'basket-empty.tpl.php');
		}
	
	}
	
	/** 
	 * Update the shopping basket 
	 */
	private function updateBasket()
	{
		if( ! $this->basket->isChecked == true ) { $this->basket->checkBasket(); }
		foreach( $this->basket->getContents() as $pid => $data )
		{
			// get the product rows basket ID
			$bid = $data['basket'];
			if( intval( $_POST['qty_' . $bid ] ) == 0 )
			{
				$this->basket->removeProduct( $bid );
			}
			else
			{
				$this->basket->updateProductQuantity( $bid, intval( $_POST['qty_' . $bid] ) );
			}
		}

		// save the extra processing by marking embedded as false
		$this->embedded = false;
		$this->registry->redirectUser('basket', 'Basket updated', 'Your shopping basket has been updated', false );

	}
	
	public function removeProduct( $bid )
	{
		$this->basket->removeProduct( $bid );
		$this->registry->redirectUser('basket' , 'Product removed', 'The product has been removed from your basket', false );
		
	}


	
	

	
	
	
	
}

?>