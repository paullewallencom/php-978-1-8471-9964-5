<?php

class Pagemodel {
	
	private $registry;
	private $valid = false;
	private $active = true;
	private $secure = false;
	private $pageheading;
	private $title;
	private $pagecontent;
	private $metakeywords;
	private $metadescription;
	private $metarobots;
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $urlPath )
	{
		$this->registry = $registry;
		$urlPath = $this->registry->getObject('db')->sanitizeData( $urlPath );
		
		$sql = "SELECT c.ID, c.active, c.secure, v.title, v.name, v.heading, v.content, v.metakeywords, v.metadescription, v.metarobots FROM content c, content_types t, content_versions v WHERE c.type=t.ID AND t.reference='page' AND c.path='{$urlPath}' AND v.ID=c.current_revision LIMIT 1";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() != 0 )
		{
			$this->valid = true;
			$pageData = $this->registry->getObject('db')->getRows();
			$this->active = $pageData['active'];
			$this->secure = $pageData['secure'];
			$this->pageheading = $pageData['heading'];
			$this->title = $pageData['title'];
			$this->pagecontent = $pageData['content'];
			$this->metakeywords = $pageData['metakeywords'];
			$this->metadescription = $pageData['metadescription'];
			$this->metarobots = $pageData['metarobots'];
			
		}
		
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
	public function isActive()
	{
		return $this->active;
	}
	
	public function isSecure()
	{
		return $this->secure;
	}
	
	public function getProperties()
	{
		$tor = array();
		foreach( $this as $field => $value )
		{
			if( !is_object( $value ) )
			{
				$tor[ $field ] = $value;
			}
		}
		return $tor;
	}	
	
	
}


?>