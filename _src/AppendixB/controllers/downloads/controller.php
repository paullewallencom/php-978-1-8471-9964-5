<?php

class Downloadscontroller {
	
	/**
	 * Registry object reference
	 */
	private $registry;
	
	
	public function __construct( PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
		{
			$this->listDownloads();
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php','footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('header', 'Please login' );
			$this->registry->getObject('template')->getPage()->addTag('message', 'Sorry, only logged in users can see the download area' );
		}
		
	}
	
	private function listDownloads()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'downloads.tpl.php','footer.tpl.php');
		$u = $this->registry->getObject('authenticate')->getUserID();
		$sql = "SELECT p.name, d.file FROM content c, content_types_products p, download_access d WHERE c.ID=d.product AND p.content_version=c.current_revision AND d.user_id={$u}";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$this->registry->getObject('template')->getPage()->addTag('downloads', array( 'SQL', $cache ) );
	}


}

?>