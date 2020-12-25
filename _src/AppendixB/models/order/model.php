<?php

class Order{
	
	private $valid = false;
	private $id;
	private $user;
	private $status;
	private $statusName;
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
	private $datePlaced;
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $id )
	{
		$this->registry = $registry;
		$sql = "SELECT o.timestamp, DATE_FORMAT( o.timestamp, '%D %b %Y') as date_placed, o.user_id, o.status, o.comment, os.name as statusName, o.delivery_comment, o.shipping_name, o.shipping_address, o.shipping_address2, o.shipping_city, o.shipping_postcode, o.shipping_country, o.products_cost, o.shipping_cost, o.voucher_code, os.name as order_stauts, p.name as payment_method, s.name as shipping_method " .
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
			$this->datePlaced = $orderData['date_placed'];
			$this->statusName = $orderData['statusName'];
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
	
	public function getItemsCache()
	{
		return $this->itemsCache;
	}
	
	public function getDatePlaced()
	{
		return $this->datePlaced;
	}
	
	public function getStatusName()
	{
		return $this->statusName;
	}
	
	public function getProductsCost()
	{
		return $this->productsCost;
	}
	
	public function getShippingCost()
	{
		return $this->shippingCost;
	}
	
	
	
	public function isValid()
	{
		return $this->valid;
	}
	
	public function getUser()
	{
		return $this->user;
	}	
	
	public function cancelOrder( $initiatedBy )
	{
		// is the order pending payment or dispatch i.e. cancellable?
		if( $this->status == 1 || $this->status == 2 )
		{
			$changes = array( 'status' => 4 );
			$this->registry->getObject('db')->updateRecords('orders', $changes, 'ID=' . $this->id );
			if( $initiatedBy == 'user' )
			{
				// email the administrator
				
				// email the customer confirmation
				
				// refund the payment?
			}
			elseif( $initiatedBy == 'admin' )
			{
				// email the customer
				
				// refund the payment?
			}
			return true;
		}
		else
		{
			// order isnt cancallable
			return false;
		}
		
	}
	
	
}

?>