<?php

/**
 * pager actions.
 *
 * @package    test
 * @subpackage pager
 * @author     Your name here
 * @version    SVN: $Id:$
 */
class @@ACTION_CLASS_NAME@@ extends sfAction
{
  /**
   * @see sfAction::execute()
   *
   */
  public function execute()
  {
      $listLimit = 10;
      $pageLimit = 5;
      $support = new S2Dao_PagerSupport($listLimit, '@@CONDITION_DTO_NAME@@', '@@CONDITION_DTO_SESSION_KEY@@');
      $conditionDto = $support->getPagerCondition();
      if ($this->hasRequestParameter('offset') and !$this->getRequest()->hasError('offset')){
          $conditionDto->setOffset($this->getRequestParameter('offset'));
      }
      if ($this->hasRequestParameter('keyword') and !$this->getRequest()->hasError('keyword')){
          $conditionDto->setKeyword($this->getRequestParameter('keyword'));
          $conditionDto->setOffset(0);
      }
      $this->dtos = $this->@@DAO_PROPERTY_NAME@@->findByConditionDtoList($conditionDto);

      $helper = new S2Dao_PagerViewHelper($conditionDto, $pageLimit);
      $this->helper = $helper;

      $begin = $helper->getDisplayPageIndexBegin();
      $end   = $helper->getDisplayPageIndexEnd();
      $pageIndex = array();
      for ( $i = $begin; $i <= $end; $i++ ) {
          $pageIndex[] = $i;
      }
      $this->pageIndex = $pageIndex;
      $this->keyword   = $conditionDto->getKeyword() == null ? 'none' : $conditionDto->getKeyword();
  }

  /**
   * @see sfAction::handleError()
   */
  public function handleError() {
      $this->execute();
      $this->setTemplate('@@ACTION_NAME@@');
      return sfView::SUCCESS;
  }

  private $@@DAO_PROPERTY_NAME@@ = null;
  public function set@@DAO_INTERFACE_NAME@@(@@DAO_INTERFACE_NAME@@ $dao)
  {
      $this->@@DAO_PROPERTY_NAME@@ = $dao;
  }
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '@@ACTION_NAME@@.inc.php');
S2ContainerApplicationContext::$CLASSES['@@ACTION_CLASS_NAME@@'] = '@@ACTION_CLASS_NAME@@.class.php';
