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
			$checkNull = 'products';
			$productPath = str_replace( $checkNull, '', $this->registry->getURLPath() );
			if( $productPath == '' )
			{
				$this->listProducts();
			}
			else
			{
				$this->viewProduct();
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
			$this->registry->getObject('template')->dataToTags( $productData, 'product_' );
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
	
	private function listProducts()
	{
		$sql = "SELECT p.price as product_price, v.name as product_name, c.path as product_path FROM content c, content_versions v, content_types_products p WHERE  p.content_version=v.ID AND v.ID=c.current_revision AND c.active=1 ";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$this->registry->getObject('template')->getPage()->addTag( 'products', array( 'SQL', $cache ) );	
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'list-products.tpl.php', 'footer.tpl.php');
	}	
	
}


?>