<?php

/**
 * Page object for our template manager
 *
 * @author Michael Peacock
 * @version 1.0
 */
class page {


	// page elements
	
	// page title
	private $title = '';
	// template tags
	private $tags = array();
	// tags which should be processed after the page has been parsed
	// reason: what if there are template tags within the database content, we must parse the page, then parse it again for post parse tags
	private $postParseTags = array();
	// template bits
	private $bits = array();
	// the page content
	private $content = "";
	
	/**
	 * Create our page object
	 */
    function __construct() { }
    
    /**
     * Get the page title from the page
     * @return String
     */
    public function getTitle()
    {
    	return $this->title;
    }
    
    /**
     * Set the page title
     * @param String $title the page title
     * @return void
     */
    public function setTitle( $title )
    {
	    $this->title = $title;
    }
    
    /**
     * Set the page content
     * @param String $content the page content
     * @return void
     */
    public function setContent( $content )
    {
	    $this->content = $content;
    }
    
    /**
     * Add a template tag, and its replacement value/data to the page
     * @param String $key the key to store within the tags array
     * @param String $data the replacement data (may also be an array)
     * @return void
     */
    public function addTag( $key, $data )
    {
	    $this->tags[$key] = $data;
    }
    
    /**
     * Get tags associated with the page
     * @return void
     */
    public function getTags()
    {
	    return $this->tags;
    }
    
    /**
     * Add post parse tags: as per adding tags
     * @param String $key the key to store within the array
     * @param String $data the replacement data
     * @return void
     */
    public function addPPTag( $key, $data )
    {
	    $this->postParseTags[$key] = $data;
    }
    
    /**
     * Get tags to be parsed after the first batch have been parsed
     * @return array
     */
    public function getPPTags()
    {
	    return $this->postParseTags;
    }
    
    /**
     * Add a template bit to the page, doesnt actually add the content just yet
     * @param String the tag where the template is added
     * @param String the template file name
     * @return void
     */
    public function addTemplateBit( $tag, $bit )
    {
	    $this->bits[ $tag ] = $bit;
    }
    
    /**
     * Get the template bits to be entered into the page
     * @return array the array of template tags and template file names
     */
    public function getBits()
    {
	    return $this->bits;
    }
    
    /**
     * Gets a chunk of page content
     * @param String the tag wrapping the block ( <!-- START tag --> block <!-- END tag --> )
     * @return String the block of content
     */
    public function getBlock( $tag )
    {
		preg_match ('#<!-- START '. $tag . ' -->(.+?)<!-- END '. $tag . ' -->#si', $this->content, $tor);
		
		$tor = str_replace ('<!-- START '. $tag . ' -->', "", $tor[0]);
		$tor = str_replace ('<!-- END '  . $tag . ' -->', "", $tor);
		
		return $tor;
    }
    
    public function getContent()
    {
	    return $this->content;
    }
  
}
?>