<?php

class Checkoutcontroller {
	
	/**
	 * Registry object reference
	 */
	private $registry;
	private $basket;
	
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		
		require_once( FRAMEWORK_PATH . 'models/basket/model.php');
		$this->basket = new Basket( $this->registry );
		$this->basket->checkBasket();
		if( $directCall == true )
		{
			if( $this->authenticationCheck() )
			{
				if( $this->basket->isEmpty() )
				{
					$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
					$this->registry->getObject('template')->getPage()->addTag('header', 'Basket empty' );
					$this->registry->getObject('template')->getPage()->addTag('message', 'The basket is currently empty' );
				}
				else
				{
					$urlBits = $this->registry->getURLBits();
					if( !isset( $urlBits[1] ) )
					{
						$this->setDelivery();
					}
					else
					{
						
						switch( $urlBits[1] )
						{
							case 'select-payment-method':
								$this->selectPayment();
								break;	
							case 'confirm':
								echo $this->reviewOrder();
								break;
							case 'save-order':
								$this->confirmOrder();
								break;
							case 'remove-product':
								$this->removeProduct( intval(  $urlBits[2] ) );
								break;		
							default:
								$this->setDelivery();
								break;				
						}
						
					}
				}
				
			}

		}
	}
	
	private function setDelivery()
	{
		if( isset( $_POST['set_delivery_address'] ) )
		{
			// save delivery address
			$this->basket->setDeliveryAddress(
												$this->registry->getObject('db')->sanitizeData( $_POST['address_name'] ),
												$this->registry->getObject('db')->sanitizeData( $_POST['address_lineone'] ),
												$this->registry->getObject('db')->sanitizeData( $_POST['address_linetwo'] ),
												$this->registry->getObject('db')->sanitizeData( $_POST['address_city'] ),
												$this->registry->getObject('db')->sanitizeData( $_POST['address_postcode'] ),
												$this->registry->getObject('db')->sanitizeData( $_POST['address_country'] )
											);
			$this->registry->redirectUser('checkout/select-payment-method/', 'Delivery address saved', 'Your delivery address has been saved', false );
			
		}
		else
		{

			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'checkout/delivery.tpl.php','footer.tpl.php');
			$address = $this->basket->getDeliveryAddress();
			if( ! empty( $address ) )
			{
				$this->registry->getObject('template')->getPage()->addTag('address_name', $address['address_name']);
				$this->registry->getObject('template')->getPage()->addTag('address_lineone', $address['address_lineone']);
				$this->registry->getObject('template')->getPage()->addTag('address_linetwo', $address['address_linetwo']);
				$this->registry->getObject('template')->getPage()->addTag('address_city', $address['address_city']);
				$this->registry->getObject('template')->getPage()->addTag('address_postcode', $address['address_postcode']);
				$this->registry->getObject('template')->getPage()->addTag('address_country', $address['address_country'] );
				
			}
		}
	}
	
	private function selectPayment()
	{
		if( isset( $_POST['payment_method'] ) )
		{
			$method = intval( $_POST['payment_method']);
			$this->basket->setPaymentMethod( $method );
			
			$this->registry->redirectUser('checkout/confirm/', 'Payment method saved', 'Your preferred payment method has been saved', false );
				
		}
		else
		{
			$this->registry->getObject('template')->getPage()->addTag('pagetitle', 'Select your payment method');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'checkout/payment.tpl.php','footer.tpl.php');
			
			
			$methods_sql = "SELECT name as method_name, ID as method_id FROM payment_methods";
			$this->registry->getObject('db')->executeQuery( $methods_sql );
			$methods = array();
			$selected = $this->basket->getPaymentMethod();
			while( $method = $this->registry->getObject('db')->getRows() )
			{
				if( $method['method_id'] == $id )
				{
					$method['selected'] = "selected='selected'";
				}
				else
				{
					$method['selected'] = '';
				}
				$methods[] = $method;
			}
			
			$methodsCache = $this->registry->getObject('db')->cacheData( $methods );
			$this->registry->getObject('template')->getPage()->addTag( 'payment_methods', array( 'DATA', $methodsCache ) );

			
		}
	}
	
	private function reviewOrder()
	{
		$da = $this->basket->getDeliveryAddress();
		if( empty( $da ) )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'No delivery address' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, we dont have a delivery address recorded for this order.' );
		}
		elseif( $this->basket->getPaymentMethod() == 0 )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'No payment method' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, we dont have a payment method associated with this order' );
		}
		else
		{
			$this->nerve->shared['tags']['pagetitle'] ='Confirm your order';
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'checkout/confirm.tpl.php','footer.tpl.php');
			
			
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
			$this->registry->getObject('template')->getPage()->addTag( 'basket_total', $this->basket->getTotal() );
			$this->registry->getObject('template')->getPage()->addPPTag('shippingCost', $this->basket->getShippingCost());
			

			foreach( $this->basket->getDeliveryAddress() as $da => $al )
			{
				$this->registry->getObject('template')->getPage()->addTag($da, $al);
			}
			
			$this->registry->getObject('db')->executeQuery("SELECT name FROM payment_methods WHERE ID=" . $this->basket->getPaymentMethod() );
			$d = $this->registry->getObject('db')->getRows();
			$this->registry->getObject('template')->getPage()->addTag( 'payment_method', $d['name'] );
			
			$this->registry->getObject('db')->executeQuery("SELECT name FROM shipping_methods WHERE ID=" . $this->basket->getShippingMethod() );
			$d = $this->registry->getObject('db')->getRows();
			$this->registry->getObject('template')->getPage()->addTag( 'shipping_method', $d['name'] );
		}
	}
	
	private function confirmOrder()
	{
		$da = $this->basket->getDeliveryAddress();
		if( empty( $da ) )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'No delivery address' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, we dont have a delivery address recorded for this order.' );
		}
		elseif( $this->basket->getPaymentMethod() == 0 )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'No payment method' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, we dont have a payment method associated with this order' );
		}
		else
		{

			$deliveryAddress = $this->basket->getDeliveryAddress();
			$voucher = $this->basket->getVoucherCode();
			$productCost = $this->basket->getCost();
			$shippingCost = $this->basket->getShippingCost();
			$contents = $this->basket->getContents();
			
			$order = array();
			$order['user_id'] =  $this->registry->getObject('authenticate')->getUserID();
			$order['ip'] = $_SERVER['REMOTE_ADDR'];
			$order['status'] = 1;
			$order['shipping_method'] = $this->basket->getShippingMethod();
			$order['payment_method'] = $this->basket->getPaymentMethod();
			$order['shipping_name'] = $deliveryAddress['address_name'];
			$order['shipping_address'] = $deliveryAddress['address_lineone'];
			$order['shipping_address2'] = $deliveryAddress['address_linetwo'];
			$order['shipping_city'] = $deliveryAddress['address_city'];
			$order['shipping_postcode'] = $deliveryAddress['address_postcode'];
			$order['shipping_country'] = $deliveryAddress['address_country'];
			$order['products_cost'] = $productCost;
			$order['shipping_cost'] = $shippingCost;
			$order['voucher_code'] = $voucher;
			
			$this->registry->getObject('db')->insertRecords( 'orders', $order );
			$order_id = $this->registry->getObject('db')->lastInsertID();
			$attributes = $this->basket->getBasketAttributes();
			$inserts = array();
			$order_items_sql = "INSERT INTO orders_items (order_id, product_id, qty, uploaded_file, custom_text_values, standard) VALUES ";
			foreach( $contents as $data )
			{
				if( isset( $attributes[ $data['basket'] ] ) )
				{
					$i = array();
					$i['attribute_id'] = $attributes[ $data['basket'] ];
					$i['order_item_id'] = $data['product'];
					$inserts[] = $i;
				} 
				
				$order_items_sql .= "( " . $order_id . ", " . $data['product'] . ", " . $data['quantity'] .", '" . $data['file'] . "', '" . $data['custom_text_values'] ."', " . $data['standard'] . " ), ";
			}
			$order_items_sql = substr_replace( $order_items_sql, '', -2 );
			$this->registry->getObject('db')->executeQuery( $order_items_sql );
			
			// attributes
			
			if( count( $inserts ) > 0 )
			{
				$attributes_sql = "INSERT INTO orders_items_attribute_value_association (order_item_id, attribute_id) VALUES ";
				foreach( $inserts as $data )
				{
					$attributes_sql .= "(". $data['order_item_id'] . ", " . $data['attribute_id'] . " ), ";
				}
				$attributes_sql = substr_replace( $attributes_sql, '', -2 );
				$this->registry->getObject('db')->executeQuery( $attributes_sql );
			}
			
			
			$this->registry->getObject('db')->deleteRecords('basket_contents', 'user_id=' . $this->registry->getObject('authenticate')->getUserID(), '');
			// we could email the administrator and the customer here?
			
			
			$this->registry->redirectUser('orders/payment/' . $order_id, 'Order placed', 'You are now being taken to the payment page', false );
			
		}
	}
	
	private function authenticationCheck()
	{

		if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
		{
							
			if( $this->registry->getObject('authenticate')->justProcessed() == true )
			{
				// store the basket in the user account
				$this->basket->transferToUser(  $this->registry->getObject('authenticate')->getUserID() );
				// check the basket, to ensure the user has some products in their basket after logging in
				$this->basket->checkBasket();
				
			}
			$this->setDelivery();
			
			return true;
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'checkout/loginreg.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('pagetitle', 'Login or sign up' );
			return false;
		}
	}


}

?>