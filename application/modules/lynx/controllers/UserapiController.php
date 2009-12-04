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
		if ($this->_getParam('tag'))
			$this->view->marks = $this->manager->getMarksForTag($this->_getParam('tag'));
		else if ($this->_getParam('query'))
			$this->view->marks = $this->manager->getMarksBySearch($this->_getParam('query'));
		else
			$this->view->marks = $this->manager->getAllMarks();
		
		$this->view->matches = count($this->view->marks);
		
		$start = intval($this->_getParam('start'));
		$results = intval($this->_getParam('results'));
		if (!$results)
			$results = null;
		if ($start || $results)
			$this->view->marks = array_slice($this->view->marks, $start, $results);
		
		$this->render('posts');
	}
	
	/**
	 * List the users links
	 */
	public function gettagsAction () {
		$this->view->tags = $this->manager->getTags();
		$this->render('tags');
	}
	
	/**
	 * Add/update a post
	 * 
	 * @access public
	 * @since 12/4/09
	 */
	public function addpostAction () {
		$this->_helper->viewRenderer->setNoRender();
		
		// Run our values through the Mark-form for cleaning/validation.
		$form    = new Lynx_Form_Mark();
		$params = array(
				'url' => $this->_getParam('url'),
				'description' => $this->_getParam('description'),
				'description' => $this->_getParam('description'),
				'notes' => $this->_getParam('extended'),
				'tags' => $this->_getParam('tags'),
			);
		if ($form->isValid($params)) {
			$values = $form->getValues();
			try {
				try {
					$mark = $this->manager->createMark($values['url'], $values['description'], $values['notes'], $form->getTags());
					print "\n<result code='done'/>";
				} catch (Exception $e) {
					// If the error was that the mark already exists, update it.
					if ($e->getCode() == 23000) {
						$mark = $this->manager->getMarkByUrl($values['url']);
						$mark->description = $values['description'];
						$mark->notes = $values['notes'];
						$mark->tags = $form->getTags();
						$this->manager->saveMark($mark);
						print "\n<result code='done'/>";
					} else {
						print "\n<result code='Unable to save: ".$e->getMessage()."'/>";
						throw $e;
					}
				}
			} catch (Exception $e) {
				print "\n<result code='Unable to save: ".$e->getMessage()."'/>";
				throw $e;
			}
		} else {
			print "\n<result code=\"Unable to save. ";
			foreach ($form->getMessages() as $name => $message) {
				foreach ($message as $code => $message) {
					print htmlentities("\n\t$name: $message.");
				}
			}
			print "\"/>";
		}
		
	}
}