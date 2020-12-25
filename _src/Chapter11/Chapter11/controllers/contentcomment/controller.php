<?php 


class contentcommentcontroller {
	
	public function __construct(  PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->regisry = $regisry;
		$bits = $this->regisry->getURLBits();
		$this->saveComment( intval( $bits[1] ) );
	}
	
	private function saveComment( $contentID )
	{
		$insert = array();
		$insert['content'] = $contentID;
		$insert['authorName'] = strip_tags($this->registry->getObject('db')->sanitizeData( $_POST['comment_name'] ) );
		$insert['authorEmail'] = $this->registry->getObject('db')->sanitizeData( $_POST['comment_email'] );
		$insert['comment'] = strip_tags( $this->registry->getObject('db')->sanitizeData( $_POST['comment'] ) );
		$insert['IPAddress'] = $_SERVER['REMOTE_ADDR'];
		
		$valid = true;
		if( $_POST['comment_name'] == '' || $_POST['comment_email'] == '' || $_POST['comment'] == '' )
		{
			$valid = false;
		} 
		if( $valid == true )
		{
			$this->registry->getObject('db')->insertRecords( 'content_comments', $insert );
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Review added');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Your review has been added');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}
		else
		{
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Error');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Either your name, email address or review was empty, please try again');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}
	}
	
	
}


?>