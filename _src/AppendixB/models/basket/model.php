<?php

class Basket {
	
	private $registry;
	private $basketChecked = false;
	private $basketEmpty = true;
	private $contents = array();
	private $numProducts = 0;
	private $cost = 0;
	private $productIDs = array();
	private $shippingMethodID = 0;
	private $shippingCost = 0;
	private $seliveryAddress;
	private $paymentMethodID = 0; 
	private $discountCode = 0;
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry )
	{
		$this->registry = $registry;
	}	
	
	/**
	 * Checks for the users basket contents
	 * @return void
	 */
	public function checkBasket()
	{
		
		// get the shipping method
		if( isset( $_SESSION['shipping_method'] ) )
		{
			// user-selected
			$this->shippingMethodID = intval( $_SESSION['shipping_method'] );
		}
		else
		{
			// system default
			$this->shippingMethodID = $this->registry->getSetting('default_shipping_method');
		}
		if( isset( $_SESSION['customer_delivery_address'] ) )
		{
			$this->deliveryAddress = unserialize($_SESSION['customer_delivery_address']);
			
			//echo $_SESSION['eagle_delivery'];
		}
		elseif( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			$dq = "SELECT * FROM users_extra WHERE user_id=" . $this->registry->getObject('authenticate')->getUserID();
			$this->registry->getObject('db')->executeQuery( $dq );
			$d = $this->registry->getObject('db')->getRows();
			$this->setDeliveryAddress( $d['default_shipping_name'], $d['default_shipping_address'], $d['default_shipping_address2'], $d['default_shipping_city'], $d['default_shipping_postcode'], $d['default_shipping_country']);
		}
		
		if( isset( $_SESSION['payment_method'] ) )
		{
			$this->paymentMethodID = intval($_SESSION['payment_method']);
		}
		else
		{
			$_SESSION['payment_method'] = $this->paymentMethodID;
		}
		
	
		
		
		// set out basket checked variable - this is to prevent this function being called unneccessarily
		// if we run this on page load to generate a mini-basket, we don't need to reload it to display the main basket!
		$this->basketChecked = true;
		
		// get user identifiable data
		$session_id = session_id();
		$ip_address = $_SERVER ['REMOTE_ADDR'];
		
		// if the customer is logged in, our query is different
		if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
		{
			// they are logged in, get their ID
			$u = $this->registry->getObject('authenticate')->getUserID();
			$sql = "SELECT b.ID as basket_id, b.standard as basket_standard, b.uploaded_file, b.custom_text_values, b.quantity as product_quantity, c.ID as product_id, v.name as product_name, p.stock as product_stock, p.weight as product_weight, p.price as product_price, p.SKU as product_sku FROM content_versions v, content c, content_types t, content_types_products p, basket_contents b WHERE c.active=1 AND c.secure=0 AND c.type=t.ID AND t.reference='product' AND p.content_version=v.ID AND v.ID=c.current_revision AND c.ID=b.product_id AND b.user_id={$u}";
		}
		else
		{
			$sql = "SELECT b.ID as basket_id, b.standard as basket_standard, b.uploaded_file, b.custom_text_values, b.quantity as product_quantity, c.ID as product_id, v.name as product_name, p.stock as product_stock, p.weight as product_weight, p.price as product_price, p.SKU as product_sku FROM content_versions v, content c, content_types t, content_types_products p, basket_contents b WHERE c.active=1 AND c.secure=0 AND c.type=t.ID AND t.reference='product' AND p.content_version=v.ID AND v.ID=c.current_revision AND c.ID=b.product_id AND b.user_id=0 AND b.session_id='{$session_id}' AND b.ip_address='{$ip_address}'";
			
		}
		// do the query
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			while( $contents = $this->registry->getObject('db')->getRows() )
			{
				$this->productIDs[] = $contents['product_id'];
			}
		}
		$weightCosts = $this->getShippingWeightCosts();
		$shippingCosts = $this->getShippingProductCosts( $this->productIDs );
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			// we have some products in our basket
			// set the relevant variable
			$this->basketEmpty = false;
			while( $contents = $this->registry->getObject('db')->getRows() )
			{
				// for each product, add them to the basket object
				if( $contents['basket_standard'] == 1 )
				{
					$this->contents[ 'standard-' . $contents['product_id'] ] = array( 'file'=>$contents['uploaded_file'], 'standard'=>$contents['basket_standard'], 'custom_text_values'=>$contents['custom_text_values'], 'unitcost' => $contents['product_price'], 'subtotal' => ($contents['product_price'] * $contents['product_quantity']), 'weight' => $contents['product_weight'], 'quantity' => $contents['product_quantity'], 'product' => $contents['product_id'], 'basket' => $contents['basket_id'], 'name' => $contents['product_name'] );
				}
				else
				{
					// with customised products, we DONT want them grouped, so we need a random string!
					$r = $this->randomString(8);
					$this->contents[ 'customizable-' . $r . '-' . $contents['product_id'] ] = array(  'file'=>$contents['uploaded_file'], 'standard'=>$contents['basket_standard'], 'custom_text_values'=>$contents['custom_text_values'], 'unitcost' => $contents['product_price'], 'subtotal' => ($contents['product_price'] * $contents['product_quantity']), 'weight' => $contents['product_weight'], 'quantity' => $contents['product_quantity'], 'product' => $contents['product_id'], 'basket' => $contents['basket_id'], 'name' => $contents['product_name'] );
				}
				
				// shipping costs: product based
				
				$this->shippingCost = $this->shippingCost + ( $shippingCosts[ $contents['product_id'] ] * $contents['product_quantity'] );
				
				// shipping costs: weight based
				$currentWeight = 0;
				
				while( $weightFound == false )
				{
					if( $contents['product_weight'] >= $weightCosts[$currentWeight]['weight'] )
					{
						$weightFound = true;
						$this->shippingCost = $this->shippingCost + ( $weightCosts[$currentWeight]['cost'] * $contents['product_quantity'] );
					}
					else
					{
						if( count( $weightCosts ) == $currentWeight )
						{
							// we don't want to do this forever!	
							$weightFound = true;
						}
						else
						{
							$currentWeight++;
						}
					}
				}
				
				// shipping costs: shipping rules
				$this->considerShippingRules();
				
				$this->numProducts = $this->numProducts + $contents['product_quantity'];
				$this->cost = $this->cost + ( $contents['product_price'] * $contents['product_quantity'] );
			}
		}
	}
	
	private function randomString( $length=8 )
	{
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	    $string = '';    
	    for ($i = 0; $i < $length; $i++ ) 
	    {
	        $string .= $characters[mt_rand(0, strlen($characters))];
	    }
	    return $string;
	}
	
	/**
	 * Add product to the shopping basket
	 * @param String the product reference
	 * @param int quantity of the product
	 */
	public function addProduct( $productPath, $quantity=1 )
	{
		// check the product exists
		$productQuery = "SELECT v.name as product_name, c.ID as product_id, p.allow_upload as allow_upload, p.stock as product_stock, p.weight as product_weight, p.price as product_price, p.SKU as product_sku, p.featured as product_featured, v.heading as product_heading, v.content as product_description, v.metakeywords as metakeywords, v.metarobots as metarobots, v.metadescription as metadescription FROM content_versions v, content c, content_types t, content_types_products p WHERE c.active=1 AND c.secure=0 AND c.type=t.ID AND t.reference='product' AND p.content_version=v.ID AND v.ID=c.current_revision AND c.path='{$productPath}'";
		$this->registry->getObject('db')->executeQuery( $productQuery );
		
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			// we have a product
			// get the ID, etc
			$data = $this->registry->getObject('db')->getRows();
			
			// are we posting data i.e. adding a customised product to the basket?
			if( ( count( $_POST ) > 0 ) )
			{		
				// create a new product object
				require_once( FRAMEWORK_PATH . 'models/products/model.php');
				$this->product = new Product( $this->registry, $productPath );
				
				if( $this->product->isValid() )
				{
					// check stock levels
					if( $data['product_stock'] == -1 || $data['product_stock'] >= $quantity )
					{
						// add product
						// insert the new listing into the basket
						$s = session_id();
						$u = ( $this->registry->getObject('authenticate')->isLoggedIn() ) ? $this->registry->getObject('authenticate')->getUserID() : 0;
						$ip = $_SERVER['REMOTE_ADDR'];
						$item = array( 'session_id' => $s, 'standard'=>0, 'user_id' => $u, 'product_id' => $data['product_id'], 'quantity' => $quantity, 'ip_address' => $ip );
							
						// check for custom text inputs
						if( $this->product->hasCustomTextInputs() )
						{
							$custominputs = array();
							foreach( $this->product->getCustomTextInputs() as $input => $name )
							{
								$custominputs[ $input ] = $_POST['custominput_' . $input ];
							}
							$custominputs = serialize( $custominputs );
							$item['custom_text_values'] = $custominputs;
						}
						// check for uploaded files
						if( $this->product->allowUploads() )
						{
							$filetypes = array('pdf','doc','docx','jpg');
							$maxfilesize = 3000000;
							if(isset( $_FILES['customfile'] ) )
							{
								if( !in_array( $_FILES['customfile']['type'], $filetypes) )
								{
									// we could inform the user of an error here; perhaps add product then tell them?
								}
								else
								{
									if( $_FILES['customfile']['size'] < $maxfilesize )
									{
										// we could inform the user of an error here; perhaps add product then tell them?
									}
									else
									{
										$newname = time() . $_FILES['customfile']['name'];
										$newpath = FRAMEWORK_PATH .'user_uploads/' . $newname;
										move_uploaded_file($_FILES['customfile']['tmp_name'], $newpath);
										$item['uploaded_file'] = $newname;
									}
								}
							}
						}
						
						// insert the record
						$this->registry->getObject('db')->insertRecords( 'basket_contents', $item );
						$bid = $this->registry->getObject('db')->lastInsertID();
						
						// check for attributes
						if( $this->product->hasAttributes() )
						{
							foreach( $this->product->getAttributes() as $attributeType => $attributeData )
							{
								$insert = array();
								$insert['basket_id'] = $bid;
								$insert['attribute_id'] = intval( $_POST['attribute_' . $attributeType] );
								$this->registry->getObject('db')->insertRecords( 'basket_attribute_value_association', $insert );
							}

						}
						
						
						// add the product to the contents array
						$this->contents[ 'customizable-' . $data['product_id'] ] = array( 'unitcost' => $data['product_price'], 'subtotal' => ($data['product_price'] * $quantity ), 'weight' => $data['product_weight'], 'quantity' => $quantity, 'product' => $data['product_id'], 'basket' => $bid, 'name' => $data['product_name'] );
						// return that all was successful
						return 'success';
					}
					else
					{
						// error message
						return 'stock';
					}
				}
			}
			else
			{
				// check if it already in the basket
				if( array_key_exists( 'standard-' . $data['product_id'], $this->contents ) == true )
				{
					// check stock
					if( $data['product_stock'] == -1 || $data['product_stock'] >= ( $this->contents['standard-' . $data['product_id']]['quantity'] + $quantity ) )
					{
						$product = $data['product_id'];
						// increment the quantity
						$this->contents['standard-' . $data['product_id']]['quantity'] = $this->contents['standard-' . $data['product_id']]['quantity']+$quantity;
						// update the database
						$this->registry->getObject('db')->updateRecords('basket_contents', array('quantity'=> $this->contents[ 'standard-' . $product]['quantity'] ), 'ID = ' . $this->contents['standard-'.$product]['basket'] );
						return 'success';
					}
					else
					{
						// error message
						return 'stock';
					}
				}
				else
				{
					if( $data['product_stock'] == -1 || $data['product_stock'] >= $quantity )
					{
						// add product
						// insert the new listing into the basket
						$s = session_id();
						$u = ( $this->registry->getObject('authenticate')->isLoggedIn() ) ? $this->registry->getObject('authenticate')->getUserID() : 0;
						$ip = $_SERVER['REMOTE_ADDR'];
						$item = array( 'session_id' => $s, 'standard'=>1, 'user_id' => $u, 'product_id' => $data['product_id'], 'quantity' => $quantity, 'ip_address' => $ip );
						$this->registry->getObject('db')->insertRecords( 'basket_contents', $item );
						$bid = $this->registry->getObject('db')->lastInsertID();
						// add the product to the contents array
						$this->contents[ 'standard-' . $data['product_id'] ] = array( 'unitcost' => $data['product_price'], 'subtotal' => ($data['product_price'] * $quantity ), 'weight' => $data['product_weight'], 'quantity' => $quantity, 'product' => $data['product_id'], 'basket' => $bid, 'name' => $data['product_name'] );
						// return that all was successful
						return 'success';
					}
					else
					{
						// error message
						return 'stock';
					}
				}	
			}
		}
		else
		{
			// product does not exist: Error message
			return 'noproduct';
		}
	}
	
	/**
	 * Transfer the basket to another user
	 * @param int user id
	 * @return bool
	 */
	public function transferToUser( $user )
	{
		$changes = array( 'user_id' => $user );
		$s = session_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		$this->registry->getObject('db')->updateRecords( 'basket_contents', $changes, " SESSION_ID='{$s}' AND ip_address='{$ip}' " );
		return true;
	}

	public function removeProduct( $basketItemId )
	{
		$s = session_id();
		$u = ( $this->registry->getObject('authenticate')->isLoggedIn() ) ? $this->registry->getObject('authenticate')->getUserID() : 0;
		$ip = $_SERVER['REMOTE_ADDR'];
		$this->registry->getObject('db')->deleteRecords( 'basket_contents', " session_id='{$s}' AND user_id={$u} AND ID={$basketItemId} AND ip_address='{$ip}' ", 1 );
	}
	
	public function updateProductQuantity( $basketItemId, $quantity )
	{
		$s = session_id();
		$u = ( $this->registry->getObject('authenticate')->isLoggedIn() ) ? $this->registry->getObject('authenticate')->getUserID() : 0;
		$ip = $_SERVER['REMOTE_ADDR'];
		$changes = array( 'quantity' => $quantity );
		$this->registry->getObject('db')->updateRecords( 'basket_contents', $changes, " session_id='{$s}' AND user_id={$u} AND ID={$basketItemId} AND ip_address='{$ip}' ");
	}
	
	private function getShippingWeightCosts()
	{
		$shipping_weights_sql = "SELECT cost, lower_weight FROM shipping_costs_weight WHERE shipping_id={$this->shippingMethodID} ORDER BY lower_weight DESC";
		$this->registry->getObject('db')->executeQuery( $shipping_weights_sql );
		$weights = array();
		while( $data = $this->registry->getObject('db')->getRows() )
		{
			$weights[ $data['lower_weight'] ] = $data['cost'];
		}
		return $weights;
			
	}
	
	private function getShippingProductCosts( $productIDs )
	{
		$shipping_costs = array();
		if( count( $productIDs ) > 0 )
		{
			$pids = implode( ',', $productIDs );
			$shipping_costs_sql = "SELECT product_id, cost FROM shipping_costs_product WHERE shipping_id={$this->shippingMethodID} AND product_id IN ( " . $pids . " )";
			$this->registry->getObject('db')->executeQuery( $shipping_costs_sql );
			while( $scosts = $this->registry->getObject('db')->getRows() )
			{
				$shipping_costs[ $scosts['product_id'] ] = $scosts['cost'];
			}
		}
		return $shipping_costs;
	}
	
	/**
	 * Takes any shipping rules into account with regards to the shipping costs
	 * @return void
	 */
	private function considerShippingRules()
	{
		// get the rules
		$rules_sql = "SELECT * FROM shipping_rules WHERE shipping_id={$this->shippingMethodID} ORDER BY `order`";
		$this->registry->getObject('db')->executeQuery( $rules_sql );
		// go through them
		while( $rule = $this->registry->getObject('db')->getRows() )
		{
			// rule depends on the shipping cost
			if( $rule['match_type'] == 'shipping' )
			{
				$match = false;
				$match_operator = $rule['match_operator'];
				// check to see our shipping cost meets the rule
				if( $match_operator == '==' ) { if( $this->shippingCost == $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '<>' ) { if( $this->shippingCost <> $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '>=' ) { if( $this->shippingCost >= $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '<=' ) { if( $this->shippingCost <= $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '>' ) { if( $this->shippingCost > $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '<' ) { if( $this->shippingCost < $rule['match_amount'] ) { $match = true; }  }
				
				if( $match == true )
				{
					// set the shipping cost based on the rule operator and the rule amount
					$rule_operator = $rule['rule_operator'];
					if( $rule_operator == '=' ) { $this->shippingCost = $rule['rule_amount']; }
					elseif( $rule_operator == '+' ) { $this->shippingCost = $this->shippingCost + $rule['rule_amount']; }
					elseif( $rule_operator == '-' ) { $this->shippingCost = $this->shippingCost - $rule['rule_amount']; }
					elseif( $rule_operator == '*' ) { $this->shippingCost = $this->shippingCost * $rule['rule_amount']; }
					elseif( $rule_operator == '/' ) { $this->shippingCost = $this->shippingCost / $rule['rule_amount']; }
					
				}
			}
			elseif( $rule['match_type'] == 'products' )
			{
				// rule depends on the basket cost
				$match = false;
				$match_operator = $rule['match_operator'];
				// check to see our basket cost meets the rule
				if( $match_operator == '==' ) { if( $this->shippingCost == $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '<>' ) { if( $this->cost <> $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '>=' ) { if( $this->cost >= $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '<=' ) { if( $this->cost <= $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '>' ) { if( $this->cost > $rule['match_amount'] ) { $match = true; }  }
				elseif( $match_operator == '<' ) { if( $this->cost < $rule['match_amount'] ) { $match = true; }  }
				
				if( $match == true )
				{
					// set the shipping cost based on the rule operator and the rule amount
					$rule_operator = $rule['rule_operator'];
					if( $rule_operator == '=' ) { $this->shippingCost = $rule['rule_amount']; }
					elseif( $rule_operator == '+' ) { $this->shippingCost = $this->shippingCost + $rule['rule_amount']; }
					elseif( $rule_operator == '-' ) { $this->shippingCost = $this->shippingCost - $rule['rule_amount']; }
					elseif( $rule_operator == '*' ) { $this->shippingCost = $this->shippingCost * $rule['rule_amount']; }
					elseif( $rule_operator == '/' ) { $this->shippingCost = $this->shippingCost / $rule['rule_amount']; }
					
				}
			}
			
		}
	}
	
	public function getBasketAttributes()
	{
		// get user identifiable data
		$session_id = session_id();
		$ip_address = $_SERVER ['REMOTE_ADDR'];
		
		// if the customer is logged in, our query is different
		if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
		{
			// they are logged in, get their ID
			$u = $this->registry->getObject('authenticate')->getUserID();
			
			$sql = "SELECT a.attribute_id, b.product_id, a.basket_id FROM basket_attribute_value_association a, basket_contents b WHERE b.product_id=a.basket_id AND b.user_id={$u}";
		}
		else
		{
			$sql = "SELECT a.attribute_id, b.product_id, a.basket_id FROM basket_attribute_value_association a, basket_contents b WHERE b.product_id=a.basket_id AND b.user_id=0 AND b.session_id='{$session_id}' AND b.ip_address='{$ip_address}'";
		}
		
		$this->registry->getObject('db')->executeQuery( $sql );
		$attributes = array();
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			while( $data = $this->registry->getObject('db')->getRows() )
			{
				$attributes[ $data['basket_id'] ] = $data['attribute_id'];
			}
		}
		return $attributes;
		
		
	}
	
	public function getTotal()
	{
		return number_format($this->cost + $this->shippingCost, 2);
	}
	
	public function getShippingMethod()
	{
		return $this->shippingMethodID;
	}
	
	public function setShippingMethod( $id )
	{
		$this->shippingMethodID = $id;
	}
	
	public function isEmpty()
	{
		return $this->basketEmpty;
	}
	
	public function getContents()
	{
		return $this->contents;
	}
	
	public function getShippingCost()
	{
		return number_format( $this->shippingCost, 2);
	}
	
	public function getCost()
	{
		return number_format($this->cost, 2);
	}
	
	public function isChecked()
	{
		return $this->basketChecked;
	}
	
	public function setContents( $contents )
	{
		$this->contents = $contents;
	}
	
	public function getNumProducts()
	{
		return $this->numProducts;
	}
	
	public function setDeliveryAddress( $name, $line1, $line2, $city, $postcode, $country )
	{
		$this->deliveryAddress = array('address_name'=>$name,'address_lineone'=>$line1, 'address_linetwo'=>$line2, 'address_city' => $city, 'address_postcode' => $postcode, 'address_country' => $country );
		// serialise the address into session too
		
		$_SESSION['customer_delivery_address'] = serialize( $this->deliveryAddress );
	}
	
	public function getDeliveryAddress()
	{
		return $this->deliveryAddress;
	}
	
	public function getPaymentMethod()
	{
		return $this->paymentMethodID;
	}
	
	public function setPaymentMethod( $method )
	{
		$this->paymentMethodID = $method;
		$_SESSION['payment_method'] = $method;
		
	}
	
	public function getVoucherCode()
	{
		return $this->discountCode;
	}
	

	
	
}

?>