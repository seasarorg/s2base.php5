<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2006 the Seasar Foundation and the Others.            |
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
// $Id: S2Base_Prado.class.php 172 2006-12-01 10:04:24Z ueyama $
/**
 * S2Base.PHP5 PRADO plugin Core Module
 * 
 * @copyright  2005-2006 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 0.1.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 0.1.0
 * @package    
 * @author     ueyama
 */

/**
 * PRADO Page Service for Cooperating with S2Container.PHP5
 * (abstract class)
 */
abstract class S2TPageService extends TPageService
{
	/**
	 * File extension for Seasar class files.
	 */
	const SEASAR_CLASS_FILE_EXT='.class.php';

	/**
	 * File extension for Seasar class files.
	 */
	const SEASAR_PAGE_FILE_EXT='.html';

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

		throw new THttpException(501,'pageservice_register_mistake',$this->getRequestedPagePath());

	}

	/**
	 * Runs the service.
	 * This will create the requested page, initializes it with the property values
	 * specified in the configuration, and executes the page.
	 */
	protected function _run($page_ext)
	{
		Prado::trace("Running page service",'System.Web.Services.TPageService');
		$path=$this->getBasePath().'/'.strtr($this->getRequestedPagePath(),'.','/');

		if(is_file($path.$page_ext))
		{
			// set create page class and page template(***.page)
			$this->_page = $this->createPage($path);
			$this->_page->setTemplate($this->getTemplateManager()->getTemplateByFileName($path.$page_ext));
		}
		else
			throw new THttpException(404,'pageservice_page_unknown',$this->getRequestedPagePath());

		$this->_page->run($this->getResponse()->createHtmlWriter());
	}

    /**
	 * Create Page Component
     * 
     * @param String $path Page path
     * @return TPage|null 
     * 
	 *   1) with PRADO Framework
	 *   2) with PRADO Framework and S2Container
	 *   3) (Default Page)
     */
	private function createPage($path)
	{
			$page = null;
			if(is_file($path.self::SEASAR_CLASS_FILE_EXT))
			{
				// get page from S2Container
				if($className = $this->getClassName($path, self::SEASAR_CLASS_FILE_EXT)) {
					$cantainer = $this->createContainer($className);
					$page=$this->getPage($cantainer, $className);

				}else{
					throw new TConfigurationException('pageservice_pageclass_unknown',$className);
				}

			}
			elseif(is_file($path.Prado::CLASS_FILE_EXT))
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
			// initialize page properties with those set in configurations
			foreach($this->_properties as $name=>$value)
				$page->setSubProperty($name,$value);

			return $page;
	}

	/**
	 * get page class name by page class file name and extention.
	 */
	private function getClassName($path, $ext)
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
	 * create Default Page Container
	 */
	protected function createDefaultPageContainer($className)
	{
		$pageDiconPath = $this->getBasePath().'/../dicon/'.$className.'.dicon';
		S2Container_SingletonS2ContainerFactory::$INITIALIZE = false;
		if(is_file($pageDiconPath))
		{
			$container=S2Container_SingletonS2ContainerFactory::getContainer($pageDiconPath);
		}else{
			throw new TConfigurationException('pageservice_dicon_file_not_exist',$className);
		}
		return $container;
	}

	/**
	 * create S2Container by page class name
	 */
	abstract protected function createContainer($className);

	/**
	 * get Page Component from S2Container
	 */
	abstract protected function getPage($container, $className);

}


/**
 * PRADO Page Service for Cooperating with S2Container.PHP5
 * (Default Impl class)
 */
class S2DefaultTPageService extends S2TPageService
{

	/**
	 * Runs the service.
	 * This will create the requested page, initializes it with the property values
	 * specified in the configuration, and executes the page.
	 */
	public function run()
	{
		$this->_run(self::PAGE_FILE_EXT);
	}

	/**
	 * create S2Container by page class name
	 */
	protected function createContainer($className)
	{
		$container = $this->createDefaultPageContainer($className);
		$container->init();
		return $container;
	}

	/**
	 * get Page Component from S2Container
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
