<?php

class Categories{
	
	
	private $registry;
	private $subcatsCache = 0;
	private $productsCache = 0;
	private $numSubcats=0;
	private $isValid = false;
	private $numProducts = 0;
	private $name;
	private $title;
	private $content;
	private $metakeywords;
	private $metadescription;
	private $metarobots;
	private $active;
	private $secure;
	
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $catPath )
	{
		//
	}
	
	private function getCategory()
	{
		$sql = "SELECT c.ID, c.active, c.secure, v.title, v.name, v.heading, v.content, v.metakeywords, v.metadescription, v.metarobots, ( SELECT COUNT(*) FROM content cn, content_types ct WHERE ct.ID=cn.type AND ct.reference='category' AND cn.parent=c.ID ) as num_subcats, ( SELECT COUNT(*) FROM content cn, content_types_products_in_categories pic WHERE cn.active=1 AND cn.ID=pic.product_id AND pic.category_id=c.ID ) as num_products FROM content c, content_types t, content_versions v WHERE c.type=t.ID AND t.reference='category' AND c.path='{$urlPath}' AND v.ID=c.current_revision LIMIT 1";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			$this->isValid = true;
			$data = $this->registry->getObject('db')->getRows();
			$this->numSubcats = $data['num_subcats'];
			$this->numProducts = $data['num_products'];
			if( $this->numSubcats != 0 )
			{
				$catid = $data['ID'];
				$sql = "SELECT v.name as category_name, c.path as category_path FROM content c, content_versions v, WHERE c.parent={$catid} AND v.ID=c.current_revision AND c.active=1 ";
				$cache = $this->registry->getObject('db')->cacheQuery( $sql );
				$this->subCats = $cache;
			}
			if( $this->numProducts != 0 )
			{
				$catid = $data['ID'];
				$sql = "SELECT p.price as product_price, v.name as product_name, c.path as product_path, FROM content c, content_versions v, content_types_products p, content_types_products_in_categories pic WHERE pic.product_id=c.ID AND pic.category_id={$catid} AND p.current_id=v.ID AND v.ID=c.current_revision AND c.active=1 ";
				$cache = $this->registry->getObject('db')->cacheQuery( $sql );
				$this->productsCache = $cache;
			}
			
			$this->name = $data['name'];
			$this->title = $data['title'];
			$this->content = $data['content'];
			$this->title = $data['title'];
			$this->metakeywords = $data['metakeywords'];
			$this->metadescription = $data['metadescription'];
			$this->metarobots = $data['metarobots'];
			$this->active = $data['active'];
			$this->secure = $data['secure'];
			$this->heading = $data['heading'];
			
			
			
		}
		
		
	}
	
	public function isValid()
	{
		return $this->isValid;
	}
	
	public function isEmpty()
	{
		return ($this->numProducts == 0) ? true : false;
	}
	
	public function numSubcats()
	{
		return $this->numSubcats;
	}
	
	public function getProperties()
	{
		$tor = array();
		$tor['title'] = $this->title;
		$tor['name'] = $this->name;
		$tor['content'] = $this->content;
		$tor['heading'] = $this->heading;
		$tor['metakeywords'] = $this->metakeywords;
		$tor['metadescription'] = $this->metadescription;
		$tor['metarobots'] = $this->metarobots;
		return $tor;
	}
	
	public function getSubCatsCache()
	{
		return $this->subcatsCache;
	}
	
	public function getProductsCache()
	{
		return $this->productsCache;
	}
	
		
	
}

?>