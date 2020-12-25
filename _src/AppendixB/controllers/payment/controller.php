<?php

class Paymentcontroller {
	
	/**
	 * Registry object reference
	 */
	private $registry;
	
	private $paymentMethod;
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		$urlBits = $this->registry->getURLBits();
		switch( $urlBits[1] )
		{
			case 'payment-received':
				$this->paymentConfirmationNotice();
				break;
			case 'payment-cancelled':
				$this->paymentCancellationNotice();
				break;	
			default:
				$this->process();
				break;			
		}
	}
	
	private function process()
	{
		$urlBits = $this->registry->getURLBits();
		if( !isset( $urlBits[2] ) )
		{
			// display an error?
		}
		else
		{
			// get the order details
			$orderId = intval( $urlBits[2] );
			// we should really abstract this out into an order object
			$sql = "SELECT o.*, p.`key` as payment FROM orders o, payment_methods p WHERE p.ID=o.payment_method AND o.ID={$orderId}";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() > 0 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$method = $data['payment'];
				require_once FRAMEWORK_PATH . 'controllers/payment/methods/' . $method .'.php';
				$this->paymentMethod = new Paymentmethod();
				switch( $urlBits[1] )
				{
					case 'process-payment':
						$this->paymentCallback();
						break;
					case 'make-payment':
						$this->displayMakePayment( $orderId );
						break;	
				}
			}
			else
			{
				// display error
			}

		}
	}
	
	
	private function paymentConfirmationNotice()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'payment/confirmed.tpl.php','footer.tpl.php');
	}
	
	private function paymentCancellationNotice()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'payment/cancelled.tpl.php','footer.tpl.php');
	}
	
	private function paymentCallback()
	{
		$this->paymentMethod->processPayment();
	}
	
	/**
	 * Display the data / form / button to make a payment
	 */
	private function displayMakePayment( $orderId )
	{
		$paymentKey = $this->paymentMethod->getKey();
		// template tags please
		$this->paymentMethod->makePaymentScreen();
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'payment/'.$paymentKey.'.tpl.php','footer.tpl.php');
		$tags = $this->paymentMethod->getTags();
		$this->registry->getObject('template')->getPage()->addTag('siteurl', $this->registry->getSetting('payment.paypal.email') );
		$this->registry->getObject('template')->getPage()->addTag('reference', $orderId );
		$this->registry->getObject('template')->getPage()->addTag('sitename', $this->registry->getSetting('payment.paypal.email') );
		$this->registry->getObject('template')->getPage()->addTag('siteshortname', $this->registry->getSetting('payment.paypal.email') );
		
		$this->registry->getObject('template')->dataToTags( $tags, '');
		
	}
	
}

?>