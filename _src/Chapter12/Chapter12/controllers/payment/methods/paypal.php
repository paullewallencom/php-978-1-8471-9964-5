<?php
/**
 * Paypal payment processing
 * Modelled from: http://www.micahcarrick.com/04-19-2005/php-paypal-ipn-integration-class.html
 */
class Paymentmethod()
{
	
	private $registry; 
	private $methodKey = 'paypal';
	
	public function __construct(  PHPEcommerceFrameworkRegistry $registry )
	{
		$this->registry = $registry;
	}
	
	private function getKey()
	{
		return $this->methodKey;
	}
	
	private function makePaymentScreen()
	{
		$this->registry->getObject('template')->getPage()->addTag('payment.email', $this->registry->getSetting('payment.paypal.email') );
		$this->registry->getObject('template')->getPage()->addTag('payment.currency', $this->registry->getSetting('payment.currency') );
		$test = ( $this->registry->getSetting('payment.testmode') == 'ACTIVE' ) ? '.sandbox' : '';
		$this->registry->getObject('template')->getPage()->addTag('payment.test', $test );
	}
	
	private function processPayment()
	{
		$postback = '';
		foreach ( $_POST as $key => $value ) 
		{
			$postback .= $key . '=' . urlencode( stripslashes( $value ) ) . '&';
		}
		$postback .='cmd=_notify-validate';
		
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen( $postback ) . "\r\n\r\n";
		
		// live payment or test payment?
		if(  $this->registry->getSetting('payment.testmode') != 'ACTIVE' )
		{
			$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
		}
		else
		{
			$fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
		}
		
		
		if (!$fp) 
		{
			// debug point
		} 
		else 
		{
			$request = $header . $postback;
			
			fputs( $fp, $request );
			
			while ( ! feof( $fp ) ) 
			{
				$response = fgets ( $fp, 1024 );
				
				if (strcmp( $respose, "VERIFIED" ) == 0 ) 
				{
					// transaction is verified!
					$order = intval( $_POST['custom'] );
					$sql = "SELECT FORMAT( ( o.products_cost + o.shipping_cost ), 2 ) as order_cost, u.email as customer_email FROM orders o, users u WHERE u.ID=o.user_id AND o.ID={$order} LIMIT 1";
					$this->registry->getObject('db')->executeQuery( $sql );
					if( $this->registry->getObject('db')->numRows() == 1 )
					{
						// we have an order in our database
						$orderData = $this->registry->getObject('db')->getRows();
						$currency = $_POST['mc_currency'];
						$total = $_POST['mc_gross'];
						$email = $_POST['receiver_email'];
						if( $orderData['order_cost'] == $total && $currency == $this->registry->getSetting('payment.currency') && $email == $this->registry->getSetting('payment.paypal.email') )
						{
							if( $status == 'Completed' )
							{
								// update the order
								$changes = array( 'status' => 2 );
								$this->registry->getObject('db')->updateRecords('orders', $changes, 'ID=' . $order );
								// email the customer
								
								// email the administrator
							}
							elseif( $status == 'Reversed' )
							{
								// charge back
								// update the order
								
								// email the customer
								
								// email the administrator
							}
							elseif( $status == 'Refunded' )
							{
								// we refunded the payment
								// update the order
								
								// email the customer
								
								// email the administrator
							}
							else
							{
								// ...
							}
						}
						else
						{
							// amount incorrect or wasn't sent to us
						}
						
						
					}
					else
					{
						// error
					}
				}
			}
			fclose ($fp);
		}
		
		exit();
		
	}	
	
	
	
	
}

?>