<?php

require_once(dirname(__FILE__).'/UserController.php');

class Lynx_UserapiController
	extends Lynx_UserController
{
	public function init() {
		parent::init();
		$this->_helper->layout->disableLayout();
		$this->getResponse()->setHeader('Content-Type', 'text/xml');
		
		print '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
	}

	/**
	 * List the users links
	 */
	public function allpostsAction () {
		$this->view->marks = $this->manager->getAllMarks();
		$this->render('posts');
	}
}