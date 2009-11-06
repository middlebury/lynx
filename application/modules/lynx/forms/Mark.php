<?php
/**
 * @since 11/6/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(APPLICATION_PATH.'/resources/Validate/Url.php');

/**
 * <##>
 * 
 * @since 11/6/09
 * @package lynx
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class Lynx_Form_Mark
	extends Zend_Form
{
		
	public function init() {
		$this->setMethod('post');
		
		$url = $this->createElement('text', 'url', array(
			'label'      => 'Link/URL:',
			'required'   => true,
			'value'      => 'http://',
			'size'       => 92,
		));
		$url->addValidator(new Default_Resource_Validate_Url);
		$this->addElement($url);
		
		$this->addElement('text', 'description', array(
			'label'      => 'Short Description:',
			'required'   => true,
			'size'       => 92,
			'filters'    => array('StringTrim', 'StripTags'),
			'validators' => array(
				array('validator' => 'StringLength', 'options' => array(2, 255))
				)
		));
		
		$this->addElement('textarea', 'notes', array(
			'label'      => 'Notes:',
			'required'   => false,
			'filters'    => array('StringTrim', 'StripTags'),
		));
		
		$tags = $this->createElement('text', 'tags', array(
			'label'      => 'Tags:',
			'required'   => false,
			'size'       => 92,
			'filters'    => array('StringTrim'),
		));
		$tags->addFilter(new Zend_Filter_Word_CamelCaseToSeparator('_'));
		$tags->addFilter(new Zend_Filter_Word_DashToSeparator('_'));
		$tags->addFilter(new Zend_Filter_Word_SeparatorToSeparator('.', '_'));
		$tags->addFilter(new Zend_Filter_StringToLower());
		$tags->addFilter(new Zend_Filter_PregReplace('/[^a-z0-9_]/', ' '));
		$this->addElement($tags);
		
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));

	}
	
}

?>