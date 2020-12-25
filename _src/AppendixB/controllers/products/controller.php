<?php
/**
 * Products Controller
 * 
 * @author Michael Peacock
 * @version 1.0
 */
class Productscontroller{
	
	/**
	 * Registry object reference
	 */
	private $registry;
	
	/**
	 * Product model object reference
	 */
	private $model;
	
	// Filter count: to count how many attributes by association must match
	private $filterCount=0;
	// SQL statement parts where products are associated with attributes
	private $filterAssociations = array();
	// SQL statement parts where products are filtered by their own direct properties i.e. price, weight.
	private $filterDirect = array();
	// Array of filter attribute types
	private $filterTypes = array();
	// Array of filter attribute values
	private $filterValues = array();
	// our SQL statement for filtered products
	private $filterSQL = '';
	
	/**
	 * Controller constructor - direct call to false when being embedded via another controller
	 * @param PHPEcommerceFrameworkRegistry $registry our registry
	 * @param bool $directCall - are we calling it directly via the framework (true), or via another controller (false)
	 */
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		
		if( $directCall == true )
		{
			$urlBits = $this->registry->getURLBits();
			$this->filterProducts( $urlBits );
			if( !isset( $urlBits[1] ) )
			{
				$this->listProducts();
			}
			else
			{
				switch( $urlBits[1] )
				{
					case 'view':
						$this->viewProduct();
						break;
					case 'stockalert':
						$this->informCustomerWhenBackInStock();
						break;	
					case 'search':
						$this->searchProducts();
						break;
					default:
						$this->listProducts();
						break;		
				}
			}
			
		}
	}
	
	/**
	 * View a product
	 * @return void
	 */
	private function viewProduct()
	{
		
		$pathToRemove = 'products/view/';
		$productPath = str_replace( $pathToRemove, '', $this->registry->getURLPath() );
		
		require_once( FRAMEWORK_PATH . 'models/products/model.php');
		$this->model = new Product( $this->registry, $productPath );
		if( $this->model->isValid() )
		{
			$productData = $this->model->getData();
			if( $productData['stock'] == 0 )
			{
				$this->registry->getObject('template')->addTemplateBit( 'stock', 'outofstock.tpl.php' );
			}
			elseif( $productData['stock'] > 0 )
			{
				$this->registry->getObject('template')->addTemplateBit( 'stock', 'instock.tpl.php' );
			}
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'stock', '' );
			}
			
			$this->relatedProducts( $productData['ID'] );
			$this->registry->getObject('template')->dataToTags( $productData, 'product_' );
			$this->registry->getObject('template')->getPage()->addTag( 'product_path', $productPath );
			$this->registry->getObject('template')->getPage()->addTag( 'metakeywords', $productData['metakeywords'] );
			$this->registry->getObject('template')->getPage()->addTag( 'metadescription', $productData['metadescription'] );
			$this->registry->getObject('template')->getPage()->addTag( 'metarobots', $productData['metarobots'] );
			$this->registry->getObject('template')->getPage()->setTitle('Viewing product ' . $productData['name'] );
			if( $this->model->hasAttributes() )
			{
				$attrdata = $this->model->getAttributes();
				$attrs = array_keys( $attrdata );
				$temp = array();
				$aftertags =  array();
				foreach( $attrs as $attribute )
				{
					$temp[] = array( 'attribute_name' => $attribute );
					$vtemp = array();
					foreach( $attrdata[ $attribute ] as $key => $value )
					{
						$vtemp[] = array('value_id'=> $value['attrid'], 'value_name'=>$value['attrvalue']);
					}
					$cache = $this->registry->getObject('db')->cacheData( $vtemp );
					$aftertags[] = array( 'cache'=>$cache, 'tag' => 'values_' . $attribute);
				}
				$cache = $this->registry->getObject('db')->cacheData( $temp );
				$this->registry->getObject('template')->getPage()->addTag( 'attributes', array('DATA', $cache ) );
				foreach( $aftertags as $key => $data )
				{
					$this->registry->getObject('template')->getPage()->addTag( $data['tag'], array('DATA', $data['cache'] ) );
				
				}
				if( $this->model->hasCustomTextInputs() )
				{
					if( $this->model->allowUploads() )
					{
						$fieldsdata = $this->model->getCustomTextInputs();
						$tags = array();
						foreach( $fieldsdata as $fieldkey => $name )
						{
							$tags[] = array('fieldkey' => $fieldkey, 'fieldname' => $name );
						}
						$cache = $this->registry->getObject('db')->cacheData( $tags );
						$this->registry->getObject('template')->getPage()->addTag('fields', array( 'DATA', $cache ) );
						$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'product-attributes-custom-upload.tpl.php', 'footer.tpl.php');
					}
					else
					{
						$fieldsdata = $this->model->getCustomTextInputs();
						$tags = array();
						foreach( $fieldsdata as $fieldkey => $name )
						{
							$tags[] = array('fieldkey' => $fieldkey, 'fieldname' => $name );
						}
						$cache = $this->registry->getObject('db')->cacheData( $tags );
						$this->registry->getObject('template')->getPage()->addTag('fields', array( 'DATA', $cache ) );
						$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'product-attributes-custom.tpl.php', 'footer.tpl.php');
					}
				}
				elseif( $this->model->allowUploads() )
				{
					$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'product-attributes-uploads.tpl.php', 'footer.tpl.php');
				}
				else
				{
					$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'product-attributes.tpl.php', 'footer.tpl.php');
				}
				
			}
			else
			{
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'product.tpl.php', 'footer.tpl.php');
			}
			
			
			
		}
		else
		{
			$this->productNotFound();
		}
	}
	
	/**
	 * Display invalid product page
	 * @return void
	 */
	private function productNotFound()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-product.tpl.php', 'footer.tpl.php');
	}
	
	private function informCustomerWhenBackInStock()
	{
		$pathToRemove = 'products/stockalert/';
		$productPath = str_replace( $pathToRemove, '', $this->registry->getURLPath() );
		
		require_once( FRAMEWORK_PATH . 'models/products/model.php');
		$this->model = new Product( $this->registry, $productPath );
		if( $this->model->isValid() )
		{
			$pdata = $this->product->getData();
			$alert = array();
			$alert['product'] = $pdata['ID'];
			$alert['customer'] = $this->registry->getObject('db')->sanitizeData( $_POST['stock_name'] );
			$alert['email'] = $this->registry->getObject('db')->sanitizeData( $_POST['stock_email'] );
			$alert['processed'] = 0;
			$this->registry->getObject('db')->insertRecords('product_stock_notification_requests', $alert );
			
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Stock alert saved');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Thank you for your interest in this product, we will email you when it is back in stock.');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}
		else
		{
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Invalid product');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Unfortunately, we could not find the product you requested.');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}
	}	
	
	private function listProducts()
	{
		if( $filterSQL == '' )
		{
			$sql = "SELECT p.price as product_price, v.name as product_name, c.path as product_path FROM content c, content_versions v, content_types_products p WHERE  p.content_version=v.ID AND v.ID=c.current_revision AND c.active=1 ";
		}
		else
		{
			$sql = $filterSQL;
		}
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$this->registry->getObject('template')->getPage()->addTag( 'products', array( 'SQL', $cache ) );	
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'list-products.tpl.php', 'footer.tpl.php');
		$this->generateFilterOptions();
	}	
	
	private function searchProducts()
	{
		// check to see if the user has actually submitted the search form
		if( isset( $_POST['product_search'] ) && $_POST['product_search'] != '' )
		{
			// clean up the search phrase
			$searchPhrase = $this->registry->getObject('db')->sanitizeData( $_POST['product_search'] );
			$this->registry->getObject('template')->getPage()->addTag( 'query', $_POST['product_search'] );
			// perform the search, and cache the results, ready for the results template
			$sql = "SELECT v.name, c.path, IF(v.name LIKE '%{$searchPhrase}%', 0, 1) as priority, IF(v.content LIKE '%{$searchPhrase}%', 0, 1) as priorityb FROM content c, content_versions v, content_types t WHERE v.ID=c.current_revision AND c.type=t.ID AND t.reference='product' AND c.active=1 AND ( v.name LIKE '%{$searchPhrase}%' OR v.content LIKE '%{$searchPhrase}%' ) ORDER BY priority, priorityb ";
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			if( $this->registry->getObject('db')->numRowsFromCache( $cache ) == 0 )
			{
				// no results from the cached query, display the no results template
			}
			else
			{
				// some results were found, display them on the results page
				// IMPROVEMENT: paginated results
				$this->registry->getObject('template')->getPage()->addTag( 'results', array( 'SQL', $cache ) );
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'products-searchresults.tpl.php', 'footer.tpl.php');
			}
		}
		else
		{
			// search form not submitted, so just display the search box page 
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'products-searchform.tpl.php', 'footer.tpl.php');
		}
	}
	
	private function relatedProducts( $currentProduct )
	{
		$relatedProductsSQL = "SELECT ".
								" IF(rp.productA<>{$currentProduct},v.name,vn.name) as product_name, IF(rp.productA<>{$currentProduct},c.path,cn.path) as product_path, rp.productA, rp.productB, c.path as cpath, cn.path as cnpath, c.ID as cid, cn.ID as cnid ".
								" FROM ".
								" content c, content cn, product_relevant_products rp, content_versions v, content_versions vn".
								" WHERE ".
									" (rp.productA={$currentProduct} OR rp.productB={$currentProduct}) ".
									" AND c.ID=rp.productA ".
									" AND cn.ID=rp.productB ".
									" AND v.ID=c.current_revision ".
									" AND vn.ID=cn.current_revision ".
								" ORDER BY RAND() ".
								" LIMIT 5";
		$relatedProductsCache = $this->registry->getObject('db')->cacheQuery( $relatedProductsSQL );
		$this->registry->getObject('template')->getPage()->addTag('relatedproducts', array( 'SQL', $relatedProductsCache ) );
		
	}
	
	private function generateFilterOptions()
	{
		// 1. Query the database for attribute types
		$attrTypesSQL = "SELECT reference, name FROM product_filter_attribute_types";
		$this->registry->getObject('db')->executeQuery( $attrTypesSQL );
		if( $this->registry->getObject('db')->numRows() != 0 )
		{
			$attributeValues = array();
			$attributeTypes = array();
			while( $attributeTypeData = $this->registry->getObject('db')->getRows() )
			{
				$attributeValues[ $attributeTypeData['reference'] ] = array();
				$attributeTypes[] = array( 'filter_attr_reference' => $attributeTypeData['reference'], 'filter_attr_name' => $attributeTypeData['name'] );
			}
			// 2. cache the results of this query
			$attributeTypesCache = $this->registry->getObject('db')->cacheData( $attributeTypes );
			// 3.	The cache is associated with a template tag 
			$this->registry->getObject('template')->getPage()->addTag( 'filter_attribute_types', array( 'DATA', $attributeTypesCache ) );
			
			// 4.	We query the database for all attribute types, ordering by their own order
			$attrValuesSQL = "SELECT v.name as attrName, t.reference as attrType, v.ID as attrID FROM product_filter_attribute_values v, product_filter_attribute_types t WHERE t.ID=v.attributeType ORDER BY v.order ASC";
			$this->registry->getObject('db')->executeQuery( $attrValuesSQL );
			if( $this->registry->getObject('db')->numRows() != 0 )
			{
				// 5.	We iterate through the results, putting each value into an array for its corresponding attribute type.
				while( $attributeValueData = $this->registry->getObject('db')->getRows() )
				{
					$data = array();
					$data['attribute_value'] = $attributeValueData['attrName'];
					$data['attribute_URL_extra'] = 'filter/' . $attributeValueData['attrType'] . '/' . $attributeValueData['attrID'];
					$attributeValues[ $attributeValueData['attrType'] ][] = $data;
				}
			}
			// 6.	For each attribute type, we cache the array, and assign it to a template tag, allowing each group of values to populate the appropriate list for the attribute type.
			foreach( $attributeValues as $type => $data )
			{
				//echo '<pre>' . print_r( $attributeValues, true ) . '</pre>';
				$cache = $this->registry->getObject('db')->cacheData( $data );
				$this->registry->getObject('template')->getPage()->addPPTag( 'attribute_values_' . $type, array( 'DATA', $cache ) );
			}
			
		}
	}
	
	/**
	 * Generate an SQL statement for filtering products, based on URL paramaters
	 * @param array $bits the bits contained within the URL
	 * @return void
	 */
	private function filterProducts( $bits )
	{
		// get our attribute types
		$attributeTypesSQL = "SELECT ID, reference, name, ProductContainedAttribute FROM  product_filter_attribute_types ";
		$this->registry->getObject('db')->executeQuery( $attributeTypesSQL );
		while( $type = $this->registry->getObject('db')->getRows() )
		{
			$this->filterTypes[ $type['reference'] ] = array( 'ID' => $type['ID'], 'reference'=>$type['reference'], 'ProductContainedAttribute'=>$type['ProductContainedAttribute'] );
		}
		
		// get our attribute values
		$attributeValuesSQL = "SELECT ID, name, lowerValue, upperValue FROM product_filter_attribute_values";
		$this->registry->getObject('db')->executeQuery( $attributeValuesSQL );
		while( $value = $this->registry->getObject('db')->getRows() )
		{
			$this->filterValues[ $value['ID'] ] = array( 'ID' => $value['ID'], 'name' => $value['name'], 'lowerValue' => $value['lowerValue'], 'upperValue' => $value['upperValue'] );
		}
		
		// process the URL
		foreach( $bits as $position => $bit )
		{
			// if we find filter in the URL
			if( $bit == 'filter' )
			{
				// send the nex two bits to the addToFilter method
				$this->addToFilter( $bits[ $position+1], $bits[ $position+2] );
			}
		}
		
		// assume no filter requests
		$somethingToFilter = false;
		// basic filter query
		$sql = "SELECT p.price as product_price, v.name as product_name, c.path as product_path  FROM content c, content_types t, content_versions v, content_types_products p WHERE v.ID=c.current_revision AND c.active=1 AND p.content_version=v.ID AND t.reference='product' AND c.type=t.ID ";
		if( !empty( $this->filterAssociations ) )
		{
			// we have some filter requests
			$somethingToFilter = true;
			// build the query
			$sqla = " AND ( SELECT COUNT( * ) FROM product_filter_attribute_associations pfaa WHERE ( ";
			$assocs = implode( " AND ", $this->filterAssociations );
			$sqla .= $assocs;
			$sqla .= " )AND pfaa.product = c.ID )={$this->filterCount}";
			$sql .= $sqla;
		}
		if( !empty( $this->filterDirect ) )
		{
			// we have some filter requests
			$somethingToFilter = true;
			// build the query
			$sql .= " AND ";
			$assocs = implode( " AND ", $this->filterDirect );
			$sql .= $assocs;
		}
		
		if( $somethingToFilter )
		{
			// since we have some filter requests, store the query.
			$this->filterSQL = $sql;
		} 
	}
	
	/**
	 * Add SQL chunks to our filter arrays, to help build our query, based on actual filter requests in the URL
	 * @param String $filterType the reference of the attribute type we are filtering by
	 * @param int $filterValue the ID of the attribute value
	 * @return void
	 */
	private function addToFilter( $filterType, $filterValue )
	{
		if( $this->filterTypes[ $filterType ]['ProductContainedAttribute'] == 1 )
		{
			$lower = $this->filterValues[ $filterValue ]['lowerValue'];
			$upper = $this->filterValues[ $filterValue ]['upperValue'];
			$sql = " p.{$filterType} >= {$lower} AND p.{$filterType} < {$upper}";
			$this->filterDirect[] = $sql;
		}
		else
		{
			$this->filterCount++;
			$sql = " pfaa.attribute={$filterValue} ";
			$this->filterAssociations[] = $sql;
		}
	}
	
}


?>