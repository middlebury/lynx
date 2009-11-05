<?php

/** Zend_Controller_Action */
class Lynx_PublicController extends Zend_Controller_Action
{
    public function indexAction()
    {
    	$this->_forward('recent');
    }
    
    public function init() {
		$this->manager = new Lynx_Model_Manager_Public();
	}

    public function recentAction () {
    	$this->view->paginator = Zend_Paginator::factory($this->manager->getAllLinksByTime());
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    	$this->render('list');
    }

    public function popularAction () {
    	$this->view->paginator = Zend_Paginator::factory($this->manager->getAllLinksByPopularity());
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    	$this->render('list');
    }
    
    public function tagsAction () {
    	$tags = $this->manager->getTags();
    	foreach ($tags as $tag) {
    		$tag->setParam('url', $this->_helper->url('viewtag', 'public', 'lynx', array('tag' => $tag->getTitle())));
    	}
    	$this->view->cloud = new Zend_Tag_Cloud(array('itemList' => $tags));
    	$this->render('tags', null, true);
    }
    
    public function viewtagAction () {
    	$this->view->paginator = Zend_Paginator::factory($this->manager->getLinksForTagByPopularity($this->_getParam('tag')));
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    	$this->render('list');
    }
}
