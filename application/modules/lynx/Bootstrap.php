<?php

class Lynx_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initLynxAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(array(
			'namespace' => 'Lynx',
			'basePath' => dirname(__FILE__),
		));
		return $autoloader;
	}
}