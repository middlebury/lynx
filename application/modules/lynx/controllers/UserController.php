<?php

/** Zend_Controller_Action */
class Lynx_UserController extends Zend_Controller_Action
{
    public function indexAction()
    {
//     	$this->_forward('index', 'catalogs');
    }
    
    /**
     * List the users links
     */
    public function listAction () {
    	$registry = Zend_Registry::getInstance();
		
    	Lynx_Model_Manager_Abstract::setConfiguration(new Zend_Config($registry->options));
    	$manager = new Lynx_Model_Manager_Authenticated();
    	
    	$this->view->paginator = Zend_Paginator::factory($manager->getAllMarks());
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    }
}
