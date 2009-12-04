<?php
/**
 * @since 11/4/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * <##>
 * 
 * @since 11/4/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class Lynx_Model_Mark {
	
	private $id;
	private $userId;
	private $url;
	private $title;
	private $description;
	private $notes;
	private $tags;
	private $updateTime;
	private $others;
		
	/**
	 * Constructor
	 * 
	 * @param int $id
	 * @param string $url
	 * @param string $description
	 * @param string $notes
	 * @param string $updateTime
	 * @param int $others
	 * @return void
	 * @access public
	 * @since 11/4/09
	 */
	public function __construct ($id, $userId, $url, $title, $description, $notes, $updateTime, $others) {
		$this->id = $id;
		$this->userId = $userId;
		$this->url = $url;
		$this->title = $title;
		$this->description = $description;
		$this->notes = $notes;
		$this->updateTime = date('c', strtotime($updateTime));
		$this->tags = array();
		$this->others = $others;
	}
	
	/**
	 * Answer our properties. Using magic methods to prevent external setting.
	 * 
	 * @param string $name
	 * @return mixed
	 * @access public
	 * @since 11/4/09
	 */
	public function __get ($name) {
		if (!isset($this->$name))
			return null;
		return $this->$name;
	}
	
	/**
	 * Answer our properties. Using magic methods to prevent external setting.
	 * 
	 * @param string $name
	 * @return mixed
	 * @access public
	 * @since 11/4/09
	 */
	public function __isset ($name) {
		return isset($this->$name);
	}
	
	/**
	 * Update properties in preparation for saving.
	 * 
	 * @param string $name
	 * @param string $value
	 * @return void
	 * @access public
	 * @since 11/9/09
	 */
	public function __set ($name, $value) {
		switch ($name) {
			case 'url':
			case 'title':
			case 'description':
			case 'notes':
				$this->$name = $value;
				return;
			case 'tags':
				if (!is_array($value))
					throw new InvalidArgumentException("\$value must be an array of strings.");
				foreach ($value as $tag) {
					if (!is_string($tag))
						throw new InvalidArgumentException("\$value must be an array of strings, '$tag' found.");
				}
				$this->tags = array_values($value);
				return;
			default:
				throw new InvalidArgumentException("$name cannot be set.");
		}
	}
	
	/**
	 * Load a tag into this object from persistant storage.
	 * 
	 * @param string $tag
	 * @return void
	 * @access public
	 * @since 11/4/09
	 */
	public function loadTag ($tag) {
		if (!strlen($tag))
			throw new InvalidArgumentException("Tag is empty.");
		$this->tags[] = $tag;
	}
	
}

?>