<?php

class contentratingcontroller{

	public function __construct(  PHPEcommerceFrameworkRegistry $registry, $directCall )
	{
		$this->regisry = $regisry;
		$bits = $this->regisry->getURLBits();
		$this->saveRating( intval( $bits[1] ), intval( $bits[2] ) );
	}
	
	private function saveRating( $contentID, $rating )
	{
		if( $this->regisry->getObject('authenticate')->isLoggedIn() )
		{
			$u = $this->registry->getObject('authenticate')->getUserID();
			$sql = "SELECT ID from content_ratings WHERE contentID={$contentID} AND userID={$u}";
		}
		else
		{
			$when = strtotime("-30 days");
			$when = date( 'Y-m-d h:i:s', $when );
			$s = session_id();
			$ip = $_SERVER['REMOTE_ADDR'];
			$sql = "SELECT ID FROM content_ratings WHERE content_id={$contentID} AND userID=0 AND sessionID='{$s}' AND IPAddress='{$ip}' AND timestamp > '{$when}'";
			
		}
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->regisry->getObject('db')->numRows() == 1 )
		{
			// update
			$data = $this->registry->getObject('db')->getRows();
			$update = array();
			$update['rating'] = $rating;
			$update['timestamp'] = date('Y-m-d h:i:s');
			$this->registry->getObject('db')->updateRecords( 'content_ratings', $update, 'ID=' . $data['ID']);
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Rating changed');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Your rating has been changed');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}
		else
		{
			// insert
			$rating = array();
			$rating['rating'] = $rating;
			$rating['contentID'] = $contentID;
			$rating['sessionID'] = session_id();
			$rating['userID'] = ( $this->registry->getObject('authenticate')->isLoggedIn() == true ) ? $this->registry->getObject('authenticate')->getUserID() : 0;
			$rating['IPAddress'] = $_SERVER['REMOTE_ADDR'];
			$this->registry->getObject('db')->insertRecords( 'content_ratings', $rating );
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Rating saved');
			$this->registry->getObject('template')->getPage()->addTag('message_heading', 'Your rating has been saved');
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
		}
	}

}



?>