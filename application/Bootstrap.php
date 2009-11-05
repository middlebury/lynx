<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default_',
            'basePath'  => dirname(__FILE__),
        ));
        return $autoloader;
    }
    
    protected function _initConfig () {
    	$registry = Zend_Registry::getInstance();
		$registry->config = new Zend_Config($this->getOptions(), true);
		return $registry->config;
    }
    
	protected function _initDb()
	{
		if ($this->hasPluginResource('db')) {
			$resource = $this->getPluginResource('db');
			$db = $resource->getDbAdapter();
		
			$registry = Zend_Registry::getInstance();
			$registry->db = $db;
			return $resource;
		}
	}
	
	protected function _initCas()
	{
		if ($this->hasPluginResource('cas')) {
			$resource = $this->getPluginResource('cas');
			$resource->init();
			return $resource;
		}
	}
}

