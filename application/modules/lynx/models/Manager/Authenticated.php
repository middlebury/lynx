<?php
/**
 * @since 11/3/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once('CAS.php');

/**
 * <##>
 * 
 * @since 11/3/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class Lynx_Model_Manager_Authenticated
	extends Lynx_Model_Manager_Abstract
{
		
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/3/09
	 */
	public function __construct () {
		parent::__construct();
		
		// Authenticate
		phpCAS::forceAuthentication();
		
		$config = Zend_Registry::get('config');
		
		if (isset($config->resources->cas->attras->first_name) && isset($config->resources->cas->attras->last_name))
			$displayName = phpCAS::getAttribute($config->resources->cas->attras->first_name).' '.phpCAS::getAttribute($config->resources->cas->attras->last_name);
		else
			$displayName = phpCAS::getUser();
		$this->userId = $this->getUserId(phpCAS::getUser(), $displayName);
		$this->userDisplayName = $displayName;
	}
	
	private $userId;
	private $userDisplayName;
	
	/**
	 * Answer all links
	 * 
	 * @return array of Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getAllMarks () {
		$select = $this->getDb()->select()
			->from('mark',
				array('id', 'fk_user', 'description', 'notes'))
			->join('url', 'mark.fk_url = url.id',
				array('url', 'title'))
			->joinLeft('tag', 'tag.fk_mark = mark.id',
				array('tag'));
		
		$this->addUserRestriction($select);
		$stmt = $select->query();
		$marks = array();
		foreach ($stmt->fetchAll() as $row) {
			$id = $row['id'];
			
			// Create the mark if needed.
			if (!isset($marks[$id]))
				$marks[$id] = new Lynx_Model_Mark($id, $row['fk_user'], $row['url'], $row['title'], $row['description'], $row['notes']);
			
			// Populat a tag if exists
			if (!is_null($row['tag']))
				$marks[$id]->loadTag($row['tag']);
		}
		
		return $marks;
	}
	
	/**
	 * Answer all links for a given tag
	 * 
	 * @param string $tag
	 * @return array of Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getMarksForTag ($tag) {
		$select = $this->getDb()->select()
			->from('mark',
				array('id', 'fk_user', 'description', 'notes'))
			->join('url', 'mark.fk_url = url.id',
				array('url', 'title'))
			->joinLeft('tag', 'tag.fk_mark = mark.id',
				array('tag'))
			->where('tag = ?', $tag);
		
		$this->addUserRestriction($select);
		$stmt = $select->query();
		$marks = array();
		foreach ($stmt->fetchAll() as $row) {
			$id = $row['id'];
			
			// Create the mark if needed.
			if (!isset($marks[$id]))
				$marks[$id] = new Lynx_Model_Mark($id, $row['fk_user'], $row['url'], $row['title'], $row['description'], $row['notes']);
			
			// Populat a tag if exists
			if (!is_null($row['tag']))
				$marks[$id]->loadTag($row['tag']);
		}
		
		return $marks;
	}
	
	/**
	 * Add a restriction to a particular user.
	 * 
	 * @param Zend_Db_Select $select
	 * @return Zend_Db_Select
	 * @access protected
	 * @since 11/4/09
	 */
	protected function addUserRestriction (Zend_Db_Select $select) {
		return $select->where('mark.fk_user = ?', $this->userId);
	}
	
	/**
	 * Answer an array of all of the tags.
	 * 
	 * @return Zend_Tag_ItemList
	 * @access public
	 * @since 11/5/09
	 */
	public function getTags () {
		$select = $this->getDb()->select()
			->from('mark',
				array())
			->joinLeft('tag', 'tag.fk_mark = mark.id',
				array('title' => 'tag', 'weight' => 'COUNT(fk_mark)'))
			->where('tag IS NOT NULL')
			->group('tag')
			->order('tag');
		$this->addUserRestriction($select);
		$stmt = $select->query();
		$list = new Zend_Tag_ItemList();
		foreach ($stmt->fetchAll() as $row) {
			$list[] = new Zend_Tag_Item($row);
		}
		return $list;
	}
	
}

?>