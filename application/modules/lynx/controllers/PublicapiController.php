<?php

require_once(dirname(__FILE__).'/PublicController.php');

class Lynx_PublicapiController
	extends Lynx_PublicController
{
	public function init() {
		parent::init();
		$this->_helper->layout->disableLayout();
		$this->getResponse()->setHeader('Content-Type', 'text/xml');
		
		print '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
	}

	/**
	 * List the public links by popularity
	 */
	public function popularAction () {
		if ($this->_getParam('tag'))
			$this->view->links = $this->manager->getLinksForTagByPopularity($this->_getParam('tag'));
		else
			$this->view->links = $this->manager->getAllLinksByPopularity();
		
		$this->view->matches = count($this->view->links);
		
		$start = intval($this->_getParam('start'));
		$results = intval($this->_getParam('results'));
		if (!$results)
			$results = null;
		if ($start || $results)
			$this->view->links = array_slice($this->view->links, $start, $results);
		
		$this->render('posts');
	}
	
	/**
	 * List the public links by time
	 */
	public function recentAction () {
		if ($this->_getParam('tag'))
			$this->view->links = $this->manager->getLinksForTagByTime($this->_getParam('tag'));
		else
			$this->view->links = $this->manager->getAllLinksByTime();
		
		$this->view->matches = count($this->view->links);
		
		$start = intval($this->_getParam('start'));
		$results = intval($this->_getParam('results'));
		if (!$results)
			$results = null;
		if ($start || $results)
			$this->view->links = array_slice($this->view->links, $start, $results);
		
		$this->render('posts');
	}
	
	/**
	 * List the public tags
	 */
	public function gettagsAction () {
		$this->view->tags = $this->manager->getTags();
		$this->render('apitags', null, true);
	}
}