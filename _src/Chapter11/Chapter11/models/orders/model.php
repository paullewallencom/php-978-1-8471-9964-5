<?php

class Order{
	
	private $valid = false;
	private $id;
	private $user;
	private $status;
	private $created;
	private $updated;
	private $deliveryAddress = array();
	private $items = array();
	private $itemsCache;
	private $shippingMethod;
	private $paymentMethod;
	private $comment;
	private $deliveryNote;
	private $shippingCost;
	private $productsCost;
	private $orderCost;
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $id )
	{
		$this->registry = $registry;
		$sql = "SELECT o.timestamp, o.user_id, o.comment, o.delivery_comment, o.shipping_name, o.shipping_address, o.shipping_address2, o.shipping_city, o.shipping_postcode, o.shipping_country, o.products_cost, o.shipping_cost, o.voucher_code, os.name as order_stauts, p.name as payment_method, s.name as shipping_method " .
				"FROM orders o, payment_methods p, order_statuses os, shipping_methods s ".
				"WHERE o.ID={$id} AND os.ID=o.status AND s.ID=o.shipping_method AND p.ID=o.payment_method " .
				"LIMIT 1";
				
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			$this->valid = true;
			$orderData = $this->registry->getObject('db')->getRows();
			$this->id = $id;
			$this->status = $orderData['status'];
			$this->created = $orderData['timestamp'];
			$this->deliveryAddress = array( $orderData['shipping_name'], $orderData['shipping_address'], $orderData['shipping_address2'], $orderData['shipping_city'], $orderData['shipping_postcode'], $orderData['shipping_country'] );
			$this->productsCost = $orderData['products_cost'];
			$this->shippingCost = $orderData['shipping_cost'];
			$this->orderCost = $this->productsCost + $this->shippingCost;
			$this->shippingMethod = $orderData['shipping_method'];
			$this->paymentMethod = $orderData['payment_method'];
			$this->user = $orderData['user_id'];
			$this->comment = $orderData['comment'];
			$this->deliveryNote = $orderData['delivery_comment'];
			
			// items
			$sql = "SELECT ctp.price, (ctp.price*i.qty) as cost, i.qty, v.name, i.product_id FROM content_types_products ctp, orders_items i, content c, content_versions v WHERE i.order_id={$this->id} AND c.ID=i.product_id AND v.ID=current_revision AND ctp.content_version=v.ID";
			$this->itemsCache = $this->registry->getObject('db')->cacheQuery( $sql );
		}
		else
		{
			$this->valid = false;
		}
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
	public function getUser()
	{
		return $this->user;
	}	
	
	
}

?>