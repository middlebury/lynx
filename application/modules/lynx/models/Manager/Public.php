<?php
/**
 * @since 11/3/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * <##>
 * 
 * @since 11/3/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class Lynx_Model_Manager_Public
	extends Lynx_Model_Manager_Abstract
{
	/**
	 * Answer all links
	 * 
	 * @return array of Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getAllLinksByTime () {
		$query = 
"SELECT
	meta_url.*,
	tag
FROM
	(	SELECT 
			url.*,
			MAX(mark.create_time) AS create_time,
			COUNT(mark.id) AS num_marks
		FROM 
			url 
			INNER JOIN mark on url.id = mark.fk_url
		GROUP BY url.id
		ORDER BY create_time DESC
	) AS meta_url
	LEFT JOIN mark ON meta_url.id = mark.fk_url
	LEFT JOIN tag ON tag.fk_mark = mark.id";
		
		
		$stmt = $this->getDb()->query($query);
		return $this->getLinksFromStatement($stmt);
	}
	
	/**
	 * Answer all links
	 * 
	 * @return array of Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getAllLinksByPopularity () {
		$query = 
"SELECT
	meta_url.*,
	tag
FROM
	(	SELECT 
			url.*,
			MAX(mark.create_time) AS create_time,
			COUNT(mark.id) AS num_marks
		FROM 
			url 
			INNER JOIN mark on url.id = mark.fk_url
		GROUP BY url.id
		ORDER BY num_marks DESC, create_time DESC
	) AS meta_url
	LEFT JOIN mark ON meta_url.id = mark.fk_url
	LEFT JOIN tag ON tag.fk_mark = mark.id";
		
		
		$stmt = $this->getDb()->query($query);
		return $this->getLinksFromStatement($stmt);
	}
	
	/**
	 * Answer all links
	 * 
	 * @param string $tag
	 * @return array of Lynx_Marks
	 * @access public
	 * @since 11/4/09
	 */
	public function getLinksForTagByPopularity ($tag) {
		$query = 
"SELECT
	meta_url.*,
	tag
FROM
	(	SELECT 
			url.*,
			MAX(mark.create_time) AS create_time,
			COUNT(mark.id) AS num_marks
		FROM 
			url 
			INNER JOIN mark on url.id = mark.fk_url
		GROUP BY url.id
		ORDER BY num_marks DESC, create_time DESC
	) AS meta_url
	LEFT JOIN mark ON meta_url.id = mark.fk_url
	LEFT JOIN tag ON tag.fk_mark = mark.id
WHERE
	tag = ?";
		
		
		$stmt = $this->getDb()->prepare($query);
		$stmt->execute(array($tag));
		return $this->getLinksFromStatement($stmt);
	}
	
	/**
	 * Answer links from a statement
	 * 
	 * @param PDOStatement $stmt
	 * @return array
	 * @access protected
	 * @since 11/5/09
	 */
	protected function getLinksFromStatement (Zend_Db_Statement $stmt) {
		$links = array();
		foreach ($stmt->fetchAll() as $row) {
			$id = $row['id'];
			
			// Create the mark if needed.
			if (!isset($links[$id]))
				$links[$id] = new Lynx_Model_Link($id, $row['url'], $row['title'], $row['num_marks']);
			
			// Populat a tag if exists
			if (!is_null($row['tag']))
				$links[$id]->loadTag($row['tag']);
		}
		
		return $links;
	}
	
	/**
	 * Answer an array of all of the tags.
	 * 
	 * @return Zend_Tag_ItemList
	 * @access public
	 * @since 11/5/09
	 */
	public function getTags () {
		$stmt = $this->getDb()->query("SELECT tag AS title, COUNT(fk_mark) AS weight FROM tag GROUP BY tag ORDER BY tag");
		$list = new Zend_Tag_ItemList();
		foreach ($stmt->fetchAll() as $row) {
			$list[] = new Zend_Tag_Item($row);
		}
		return $list;
	}
}