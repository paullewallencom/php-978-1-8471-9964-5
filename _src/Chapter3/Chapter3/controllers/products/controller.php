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
			$this->viewProduct();
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
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'product.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->setTitle('Viewing product' . $productData['name'] );
			
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
	
}


?>