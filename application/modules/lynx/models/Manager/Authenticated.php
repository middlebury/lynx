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
		
		// Initialize CAS
		$config = self::getConfiguration();
		
		if (!isset($config->cas->host))
			throw new Exception('Configuration Error: no cas.host');
		if (!isset($config->cas->port))
			throw new Exception('Configuration Error: no cas.port');
		if (!isset($config->cas->path))
			throw new Exception('Configuration Error: no cas.path');
		
		phpCAS::client(CAS_VERSION_2_0, $config->cas->host, intval($config->cas->port), $config->cas->path, false);
		if (isset($config->cas->sever_cert))
			phpCAS::setCasServerCACert($config->cas->sever_cert);
		else
			phpCAS::setNoCasServerValidation();
		
		// Authenticate
		phpCAS::forceAuthentication();
		
		if (isset($config->cas->attras->first_name) && isset($config->cas->attras->last_name))
			$displayName = phpCAS::getAttribute($config->cas->attras->first_name).' '.phpCAS::getAttribute($config->cas->attras->last_name);
		else
			$displayName = phpCAS::getUser();
		$this->userId = $this->getUserId(phpCAS::getUser(), $displayName);
		$this->userDisplayName = $displayName;
	}
	
	private $userId;
	private $userDisplayName;
	
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
	
}

?>