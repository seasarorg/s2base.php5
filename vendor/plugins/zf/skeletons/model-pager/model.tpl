<?php
class @@MODEL_CLASS@@ extends Zend_Db_Table_Abstract {
    protected $_name = '@@TABLE_NAME@@';
    protected $_primary = '@@PRIMARY_KEY@@';

    public function getByConditionDto(@@CONDITION_DTO_NAME@@ $dto) {
        $select = $this->_db->select();
        $where = null;
        if (trim($dto->getKeyword()) != '') {
@@WHERE_CLAUSE@@
            $where = implode(' ', $select->getPart(Zend_Db_Select::WHERE));
        }
        $dto->setCount($this->getPagerCount($select));
        return $this->fetchAll($where, null, $dto->getLimit(), $dto->getOffset());
    }

    private function getPagerCount(Zend_Db_Select $select) {
        $select->from($this->_name, array('total' => 'count(*)'));
        $result = $select->query()->fetchAll();
        return $result[0]['total'];
    }
}
