<?php

class Product{
	
	private $registry;
	private $ID;
	private $name;
	private $SKU;
	private $description;
	private $price;
	private $weight;
	private $image;
	private $stock;
	private $heading;
	private $metakeywords;
	private $metadescription;
	private $metarobots;
	private $active;
	private $secure;
	private $hasAttributes = false;
	private $activeProduct = false;
	private $attributes = array();
	private $allowUpload = false;
	private $customTextInputs = '';
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $productPath )
	{
		$this->registry = $registry;
		if( $productPath != '' )
		{
			$productPath = $this->registry->getObject('db')->sanitizeData( $productPath );
			$productQuery = "SELECT v.name as product_name, c.ID as product_id, p.allow_upload as allow_upload, p.custom_text_inputs as custom_text_inputs, (SELECT GROUP_CONCAT( a.name,'--AV--', av.ID, '--AV--', av.name SEPARATOR '---ATTR---' ) FROM product_attribute_values av, product_attribute_value_association ava, product_attributes a WHERE a.ID = av.attribute_id AND av.ID=ava.attribute_id AND ava.product_id=c.ID ORDER BY ava.order ) AS attributes, p.image as product_image, p.stock as product_stock, p.weight as product_weight, p.price as product_price, p.SKU as product_sku, p.featured as product_featured, v.heading as product_heading, v.content as product_description, v.metakeywords as metakeywords, v.metarobots as metarobots, v.metadescription as metadescription FROM content_versions v, content c, content_types t, content_types_products p WHERE c.active=1 AND c.secure=0 AND c.type=t.ID AND t.reference='product' AND p.content_version=v.ID AND v.ID=c.current_revision AND c.path='{$productPath}'";
			$this->registry->getObject('db')->executeQuery( $productQuery );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				
				// tells the controller we have a product!
				$this->activeProduct = true;
				// grab the product data, and associate it with the relevant fields for this object
				$data = $this->registry->getObject('db')->getRows();
				if( $data['attributes'] != '' )
				{
					$this->hasAttributes = true;
					$attrs = explode('---ATTR---', $data['attributes'] );
					foreach( $attrs as $atr )
					{
						$value = explode( '--AV--', $atr );
						$this->attributes[ $value[0] ][] = array( 'attrid' => $value[1], 'attrvalue' => $value[2] );
						
					}
					
				}
				
				if( $data['allow_upload'] == 1)
				{
					$this->allowUpload = true;
				}
				
				
				$this->ID = $data['product_id'];
				$this->name = $data['product_name'];
				$this->price = $data['product_price'];
				$this->weight = $data['product_weight'];
				$this->image = $data['product_image'];
				$this->heading = $data['product_heading'];
				$this->description = $data['product_description'];
				$this->SKU = $data['product_sku'];
				$this->stock = $data['product_stock'];
				// secure and active were set in the query, we will probably want to change this later
				$this->secure = 0;
				$this->active = 1;
				$this->metakeywords = $data['metakeywords'];
				$this->metadescription = $data['metadescription'];
				$this->metarobots = $data['metarobots'];
				if( $data['custom_text_inputs'] != '' )
				{
					$this->customTextInputs = unserialize( $data['custom_text_inputs'] );
				}
				
			}
		}
		else
		{
			// here we may want to do something else...
		}
		
	}
	
	public function allowUploads()
	{
		return $this->allowUpload();
	}
	
	public function getCustomTextInputs()
	{
		returh $this->customTextInputs();
	}
	
	public function hasCustomTextInputs()
	{
		return ( $this->customTextInputs != '' ) ? true : false;
	}
	
	public function hasAttributes()
	{
		return $this->hasAttributes;
	}
	
	public function getAttributes()
	{
		return $this->attributes;
	}
	
	public function isValid()
	{
		return $this->activeProduct;
	}
	
	public function getData()
	{
		$data = array();
		foreach( $this as $field => $fdata )
		{
			if( ! is_object( $fdata ) )
			{
				$data[ $field ] = $fdata;
			}
			
		}
		return $data;
	}
	
	/*
		Also useful: getters and setters for various fields, as well as a save method, to update a database entry
	*/
		
}

?>