<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// |                                                                      |
// |     http://www.apache.org/licenses/LICENSE-2.0                       |
// |                                                                      |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
// | either express or implied. See the License for the specific language |
// | governing permissions and limitations under the License.             |
// +----------------------------------------------------------------------+
// | Authors: ueyama                                                       |
// +----------------------------------------------------------------------+
//
// $Id: S2Base_Prado.class.php 172 2007-2-12 10:04:24Z ueyama $
/**
 * S2Base.PHP5 PRADO plugin Core Module
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 0.1.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 0.1.0
 * @package    
 * @author     ueyama
 */

/**
 * PRADO Page Service for Cooperating with S2Container.PHP5
 */
class S2TPageService extends TPageService
{
	/**
	 * File extension for Seasar class files.
	 */
	const SEASAR_CLASS_FILE_EXT='.class.php';

	/**
	 * @var TPage the requested page
	 */
	private $_page=null;

	/**
	 * @var array list of initial page property values
	 */
	private $_properties;

	/**
	 * @return TPage the requested page
	 */
	public function getRequestedPage()
	{
		return $this->_page;
	}

	/**
	 * Initializes page context.
	 * Page context includes path alias settings, namespace usages,
	 * parameter initialization, module loadings, page initial properties
	 * and authorization rules.
	 * @param TPageConfiguration
	 */
	protected function initPageContext($pageConfig)
	{
		// initial page properties (to be set when page runs)
		$this->_properties=$pageConfig->getProperties();

		parent::initPageContext($pageConfig);
	}

	/**
	 * Runs the service.
	 * This will create the requested page, initializes it with the property values
	 * specified in the configuration, and executes the page.
	 */
	public function run()
	{
		Prado::trace("Running page service",'S2TPageService');
		$this->_page = $this->createPage($this->getRequestedPagePath());
		$this->runPage($this->_page,$this->_properties);
	}

	/**
	 * Executes a page.
	 * @param TPage the page instance to be run
	 * @param array list of initial page properties
	 */
	protected function runPage($page,$properties)
	{
		foreach($properties as $name=>$value)
			$page->setSubProperty($name,$value);
		$page->run($this->getResponse()->createHtmlWriter());
	}
	
    /**
	 * Create Page Component
     * 
     * @param String $path Page path
     * @return TPage|null 
     * 
	 *   1) with PRADO Framework and S2Container
	 *   2) with PRADO Framework
	 *   3) (Default Page)
     */
	protected function createPage($path)
	{
		$path=$this->getBasePath().'/'.strtr($path,'.','/');
		$hasTemplateFile=is_file($path.self::PAGE_FILE_EXT);
		$hasSeasarClassFile=is_file($path.self::SEASAR_CLASS_FILE_EXT);
		$hasClassFile=is_file($path.Prado::CLASS_FILE_EXT);

		$page = null;
		if($hasSeasarClassFile) {
			// get page from S2Container
			if($className = $this->getClassName($path, self::SEASAR_CLASS_FILE_EXT)) {
				$cantainer = $this->createContainer($className);
				$page=$this->getPage($cantainer, $className);
			}else{
				throw new TConfigurationException('pageservice_pageclass_unknown',$className);
			}
		}
		elseif($hasClassFile)
		{
			// get page from PRADO
			if($className = $this->getClassName($path, Prado::CLASS_FILE_EXT)) {
				$page=Prado::createComponent($className);
			}else{
				throw new TConfigurationException('pageservice_pageclass_unknown',$className);
			}
		}
		else
		{
			// get TPage
			$className=$this->getBasePageClass();
			$page=Prado::createComponent($className);
		}
		$page->setPagePath($this->getRequestedPagePath());
		if($hasTemplateFile) {
			$page->setTemplate($this->getTemplateManager()->getTemplateByFileName($path.self::PAGE_FILE_EXT));
		}else{
			throw new THttpException(404,'pageservice_page_unknown',$this->getRequestedPagePath());			
		}

		return $page;
	}
	
	/**
	 * create S2Container by page class name
     * @param String $className Class Name
     * @return S2Container 
	 */
	protected function createContainer($className)
	{
		$pageDiconPath = $this->getBasePath().'/../dicon/'.$className.'.dicon';
		S2Container_SingletonS2ContainerFactory::$INITIALIZE = false;
		if(is_file($pageDiconPath))
		{
			$container=S2Container_SingletonS2ContainerFactory::getContainer($pageDiconPath);
		}else{
			throw new TConfigurationException('pageservice_dicon_file_not_exist',$className);
		}
		$container->init();
		return $container;
	}
	
	/**
	 * get page class name by page class file name and extention.
     * @param String $path path of PageFile
     * @param String $ext extension of PageFile
     * @return S2Container 
	 */
	protected function getClassName($path, $ext)
	{
		$className=basename($path);
		if(!class_exists($className,false))
			include_once($path.$ext);
		if(class_exists($className,false)) {
			return $className;
		}else{
			return false;
		}
	}

	/**
	 * get Page Component from S2Container
     * @param S2Container S2Coontainer instance
     * @param String $className Class Name
     * @return instance of Page Class 
	 */
	protected function getPage($container, $className)
	{
		if($container->hasComponentDef($className)){
			// Class extending TPage
			return $container->getComponent($className);
		}else{
			// TPage
			return Prado::createComponent($this->getBasePageClass());
		}
	}
}
?>
