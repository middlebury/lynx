<?php

class Default_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    protected $_view;

    public function init()
    {
        // Return view so bootstrap will store it in the registry
        return $this->getView();
    }

    public function getView()
    {
        if (null === $this->_view) {
            $options = $this->getOptions();
            $title   = '';
            if (array_key_exists('title', $options)) {
                $title = $options['title'];
                unset($options['title']);
            }

			$view = new Zend_View($options);
			$view->doctype('XHTML1_STRICT');
			
			$view->headTitle($title);
			
			$view->headLink()->appendStylesheet('http://web.middlebury.edu/development/tools/2d/Stylesheets/2d.css');
			$view->headLink()->appendStylesheet('http://web.middlebury.edu/development/tools/2d/Stylesheets/2dFlex.css', 'screen', null, array('title' => 'flex'));
			$view->headLink()->appendStylesheet('http://web.middlebury.edu/development/tools/2d/Stylesheets/2dFixed.css', 'screen', null, array('title' => 'fixed'));
			$view->headLink()->appendStylesheet('http://web.middlebury.edu/development/tools/2d/StyleSheets/Menu.css');
			
			$view->headLink()->appendStylesheet($this->getBaseUrl().'css/AppStyles.css');
			$view->headLink()->appendStylesheet($this->getBaseUrl().'css/MenuStyles.css');
			
			$view->headScript()->appendFile('http://web.middlebury.edu/development/tools/2d/JavaScript/StyleSwitcher.js');
			
// 			$view->headScript()->appendFile($this->getBaseUrl().'js/analytics.js');
			
			$viewRenderer =
				Zend_Controller_Action_HelperBroker::getStaticHelper(
					'ViewRenderer'
				);
			$viewRenderer->setView($view);
            

            $this->_view = $view;
        }
        return $this->_view;
    }
    
    public function getBaseUrl() {
    	return preg_replace('/([^\/]*)$/', '', $_SERVER['PHP_SELF'] );
    }
}