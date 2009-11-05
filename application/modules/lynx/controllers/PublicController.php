<?php

/** Zend_Controller_Action */
class Lynx_PublicController extends Zend_Controller_Action
{
    public function indexAction()
    {
    	$this->_forward('recent');
    }
    

    public function recentAction () {
    	$manager = new Lynx_Model_Manager_Public();
    	$this->view->paginator = Zend_Paginator::factory($manager->getAllLinksByTime());
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    	$this->render('list');
    }

    public function popularAction () {
    	$manager = new Lynx_Model_Manager_Public();
    	$this->view->paginator = Zend_Paginator::factory($manager->getAllLinksByPopularity());
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    	$this->render('list');
    }
    
    public function tagsAction () {
    	$manager = new Lynx_Model_Manager_Public();
    	$this->view->cloud = new Zend_Tag_Cloud(array('itemList' => $manager->getTags()));
    	$this->render('tags', null, true);
    }
}
