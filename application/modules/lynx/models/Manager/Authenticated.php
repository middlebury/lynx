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
	 * Create a new bookmark values should have already been filtered by the form.
	 * 
	 * @param string $url
	 * @param optional string $description
	 * @param optional string $notes
	 * @param optional array $tags
	 * @return Lynx_Model_Mark
	 * @access public
	 * @since 11/6/09
	 */
	public function createMark ($url, $description = '', $notes = '', array $tags = array()) {
		if (!strlen($url))
			throw new InvalidArgumentException('No $url specified');
		if (!is_array($tags))
			throw new InvalidArgumentException('$tags must be an array.');
		foreach ($tags as $tag) {
			if (!preg_match('/^[a-z0-9_]+$/', $tag))
				throw new InvalidArgumentException('Tag \''.$tag.'\' is not valid only letters, numbers and underscores are allowed.');
		}
		
		$tags = array_unique($tags);
		
		$db = $this->getDb();
		$db->beginTransaction();
		
		try {
			// Get/Insert the URL
			$urlId = $this->getUrlId($url);
			
			// Insert the mark
			$db->insert('mark', array(
				'fk_url' => $urlId, 
				'fk_user' => $this->userId,
				'description' => $description,
				'notes' => $notes,
				'create_time' => new Zend_Db_Expr('NOW()')));
			$markId = $db->lastInsertId();
			
			// Insert the tags
			foreach ($tags as $tag) {
				$db->insert('tag', array('fk_mark' => $markId, 'tag' => $tag));
			}
			
			$db->commit();
		
			return $this->getMark($markId);
		} catch (Zend_Db_Statement_Exception $e) {
			$db->rollback();
			if ($e->getCode() == 23000)
				throw new Exception('Already exists.');
			else
				throw $e;
		} catch (Exception $e) {
			$db->rollback();
			throw $e;
		}
	}
	
	/**
	 * Get/Insert the URL
	 * 
	 * @param string $url
	 * @param optional string $title
	 * @return int
	 * @access private
	 * @since 11/9/09
	 */
	private function getUrlId ($url, $title = null) {
		if (!is_string($url) || !strlen($url))
			throw new InvalidArgumentException('Url must be a non-zero-length string.');
		
		$db = $this->getDb();
		$urlId = $db->fetchOne(
			$db->select()
				->from('url', array('id'))
				->where('url = ?', array($url)));
		if (!$urlId) {
			$db->insert('url', array('url' => $url, 'title' => $title));
			$urlId = $db->lastInsertId();
		}
		
		return $urlId;
	}
	
	/**
	 * Save updates to a mark.
	 * 
	 * @param Lynx_Model_Mark $mark
	 * @return Lynx_Model_Mark Return the mark to allow for usage.
	 * @access public
	 * @since 11/9/09
	 */
	public function saveMark (Lynx_Model_Mark $mark) {
		if ($mark->userId != $this->userId)
			throw new Exception('Cannot change marks for other users.');
		
		$oldMark = $this->getMark($mark->id);
		
		$db = $this->getDb();
		$db->beginTransaction();
		try {
			// Changes to the mark
			$markChanges = array();
			
			if ($oldMark->url != $mark->url)
				$markChanges['fk_url'] = $this->getUrlId($mark->url);
			if ($oldMark->description != $mark->description)
				$markChanges['description'] = $mark->description;
			if ($oldMark->notes != $mark->notes)
				$markChanges['notes'] = $mark->notes;
			
			if (count($markChanges))
				$db->update('mark', $markChanges, array('id = ?' => $mark->id));
			
			// Changes to tags.
			$remove = array_diff($oldMark->tags, $mark->tags);
			$add = array_diff($mark->tags, $oldMark->tags);
			
			foreach ($remove as $tag) {
				$db->delete('tag', array('fk_mark = ?' => $mark->id, 'tag = ?' => $tag));
			}
			foreach ($add as $tag) {
				$db->insert('tag', array('fk_mark' => $mark->id, 'tag' => $tag));
			}
			
			$db->commit();
			return $mark;
		} catch (Zend_Db_Statement_Exception $e) {
			$db->rollback();
			throw $e;
		}
	}
	
	/**
	 * Save updates to a mark.
	 * 
	 * @param string $markId
	 * @return void
	 * @access public
	 * @since 11/9/09
	 */
	public function deleteMark ($markId) {
		$mark = $this->getMark($markId);
		
		if ($mark->userId != $this->userId)
			throw new Exception('Cannot delete marks for other users.');
		
		$this->getDb()->delete('mark', array('id = ?' => $mark->id));
	}
	
	/**
	 * Answer a bookmark
	 * 
	 * @param int $id
	 * @return Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getMark ($id) {
		$select = $this->getDb()->select()
			->from('mark',
				array('id', 'fk_user', 'description', 'notes'))
			->join('url', 'mark.fk_url = url.id',
				array('url', 'title'))
			->joinLeft('tag', 'tag.fk_mark = mark.id',
				array('tag'))
			->where('mark.id = ?', array($id));
		
		$this->addUserRestriction($select);
		$stmt = $select->query();
		$marks = $this->getMarksFromStatement($stmt);
		if (!count($marks))
			throw new Exception("Mark not found with id '$id'.");
		return current($marks);
	}
	
	/**
	 * Answer marks from a statement
	 * 
	 * @param PDOStatement $stmt
	 * @return array
	 * @access protected
	 * @since 11/5/09
	 */
	protected function getMarksFromStatement (Zend_Db_Statement $stmt) {
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
				array('tag'))
			->order('create_time DESC');
		
		$this->addUserRestriction($select);
		$stmt = $select->query();
		return $this->getMarksFromStatement($stmt);
	}
	
	/**
	 * Answer links matching search criteria
	 * 
	 * @return array of Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getMarksBySearch ($searchString) {
		if (!is_string($searchString))
			throw new InvalidArgumentException('$searchString must be a string.');
			
		$terms = explode(' ', $searchString);
		array_walk($terms, 'trim');
		
		$stringTerms = array();
		$tagTerms = array();
		foreach ($terms as $term) {
			if (preg_match('/^\[(.+)\]$/', $term, $matches)) {
				$tagTerms[] = $matches[1];
			} else {
				$stringTerms[] = $term;
			}
		}
		$tagTerms = array_unique($tagTerms);
				
		$select = $this->getDb()->select()
			->from('mark',
				array('id', 'fk_user', 'description', 'notes'))
			->join('url', 'mark.fk_url = url.id',
				array('url', 'title'))
			->joinLeft(array('tag0' => 'tag'), 'tag0.fk_mark = mark.id',
				array('tag'));
		
		
		if (count($stringTerms)) {
			$select->joinLeft('mark_fulltext', 'mark_fulltext.fk_mark = mark.id', array());
			$select->columns(array('score' => 'MATCH mark_fulltext AGAINST('.$this->getDb()->quote(implode(' ', $stringTerms)).')'));
			$select->where('MATCH mark_fulltext AGAINST(?)', implode(' ', $stringTerms));
			$select->order('score DESC');
		}
		
		$select->order('create_time DESC');
		
		// When matching tags, strictly limit to matches.
		$i = 1;
		foreach ($tagTerms as $tag) {
			$name = 'tag'.$i;
			$select->join(array($name => 'tag'), $name.'.fk_mark = mark.id', array());
			$select->where($name.'.tag = ?', $tag);
			$i++;
		}
			
		
		$this->addUserRestriction($select);
		
// 		var_dump($select->__toString());
		
		$stmt = $select->query();
		return $this->getMarksFromStatement($stmt);
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
		return $this->getMarksFromStatement($stmt);
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