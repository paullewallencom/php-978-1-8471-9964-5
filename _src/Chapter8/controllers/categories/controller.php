<?php
/**
 * Categories Controller
 * 
 * @author Michael Peacock
 * @version 1.0
 */
class Categories{
	
	/**
	 * Registry object reference
	 */
	private $registry;
	
	/**
	 * Categories model object reference
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
			$this->viewCategory();
		}
	}
	
	/**
	 * View a category
	 * @return void
	 */
	private function viewCategory()
	{
		
		$pathToRemove = 'categories/view/';
		$categoryPath = str_replace( $pathToRemove, '', $this->registry->getURLPath() );
		
		require_once( FRAMEWORK_PATH . 'models/categories/model.php');
		$this->model = new Category( $this->registry, $categoryPath );
		if( $this->model->isValid() )
		{
			if( $this->model->isEmpty() && $this->model->numSubcats() == 0 )
			{
				$this->emptyCategory();
			}
			else
			{
				$categoryData = $this->model->getProperties();
				$this->registry->getObject('template')->dataToTags( $categoryData, 'category_' );
				$this->registry->getObject('template')->getPage()->addTag( 'metakeywords', $categoryData['metakeywords'] );
				$this->registry->getObject('template')->getPage()->addTag( 'metadescription', $categoryData['metadescription'] );
				$this->registry->getObject('template')->getPage()->addTag( 'metarobots', $categoryData['metarobots'] );
				$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'category.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->getPage()->setTitle('Viewing category ' . $categoryData['name'] );
				if(  $this->model->numSubcats() == 0 )
				{
					$this->registry->getObject('template')->getPage()->addTag( 'subcats', '' );
				}
				else
				{
					$this->registry->getObject('template')->addTemplateBit( 'subcats', 'subcategories.tpl.php' );
					$this->registry->getObject('template')->getPage()->addTag( 'subcatslist', array('SQL', $this->model->getSubCatsCache() ) );
				}
				if(  $this->model->isEmpty() )
				{
					$this->registry->getObject('template')->getPage()->addTag( 'catproducts', '' );
				}
				else
				{
					$this->registry->getObject('template')->addTemplateBit( 'catproducts', 'categoryproducts.tpl.php' );
					$this->registry->getObject('template')->getPage()->addTag( 'productslist', array('SQL', $this->model->getProductsCache() ) );
				}
			}
			
		}
		else
		{
			$this->categoryNotFound();
		}
	}
	
	/**
	 * Display invalid category page
	 * @return void
	 */
	private function categoryNotFound()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'invalid-category.tpl.php', 'footer.tpl.php');
	}		
	
	
	private function emptyCategory()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'empty-category.tpl.php', 'footer.tpl.php');
	}
	
}


?>