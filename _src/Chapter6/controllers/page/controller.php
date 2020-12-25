<?php

class Pagecontroller {

	private $registry;
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $directCall == true )
		{
			$this->viewPage();
		}
	}
	
	private function viewPage()
	{
		require_once( FRAMEWORK_PATH . 'models/page/model.php');
		// Page model needs different class name, as page is used for the template handler!
		$this->model = new Pagemodel( $this->registry, $this->registry->getURLPath() );
		if( $this->model->isValid() )
		{
			$pageData = $this->model->getProperties();
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'main.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->dataToTags( $pageData, '' );
			$this->registry->getObject('template')->getPage()->setTitle( $pageData['title'] );
			
		}
		else
		{
			$this->pageNotFound();
		}
	}
	
	private function pageNotFound()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', '404.tpl.php', 'footer.tpl.php');
	}
	
	private function pageRequiresLogin()
	{
		// TODO
	}
	
	private function pageDisabled()
	{
		// TODO
	}
		
	
}


?>