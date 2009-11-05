<?php
/**
 * @since 11/3/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * The manager for accessing lynx
 * 
 * @since 11/3/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
abstract class Lynx_Model_Manager_Abstract {
	
	private $db;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/3/09
	 */
	public function __construct () {
		// initialize the database
		$this->db = Zend_Registry::get('db');
		if (!$this->db)
			throw new Exception('No db registered');
	}
	
	/**
	 * Answer the database connection
	 * 
	 * @return Zend_Db
	 * @access public
	 * @since 11/3/09
	 */
	public function getDb () {
		return $this->db;
	}
	
	/**
	 * Answer the user id for a given external id and display name
	 * 
	 * @param string $login
	 * @param string $displayName
	 * @return int
	 * @access public
	 * @since 11/3/09
	 */
	public function getUserId ($login, $displayName) {
		$select = $this->db->query('SELECT id, display_name FROM user WHERE login = :login', array(':login' => $login));
		$id = $select->fetchColumn(0);
		if ($id) {
			// Update the display name if needed.
			if ($select->fetchColumn(1) != $displayName) {
				$this->db->update('user', array('display_name' => $displayName), array('id = ?' => $id));
			}
			
			return $id;
		} else {
			// Insert a new user row
			$this->db->insert('user', array('login' => $login, 'display_name' => $displayName));
			return $this->db->lastInsertId();
		}
	}
	
	/**
	 * Answer all links
	 * 
	 * @return array of Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getAllMarks () {
		$select = $this->db->select()
			->from('mark',
				array('id', 'fk_user', 'description', 'notes'))
			->join('url', 'mark.fk_url = url.id',
				array('url'))
			->joinLeft('tag', 'tag.fk_mark = mark.id',
				array('tag'));
		
		$this->addUserRestriction($select);
		
		$stmt = $this->db->query($select);
		$marks = array();
		foreach ($stmt->fetchAll() as $row) {
			$id = $row['id'];
			
			// Create the mark if needed.
			if (!isset($marks[$id]))
				$marks[$id] = new Lynx_Model_Mark($id, $row['fk_user'], $row['url'], $row['description'], $row['notes']);
			
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
		// Do nothing by default
		return $select;
	}
}

?>