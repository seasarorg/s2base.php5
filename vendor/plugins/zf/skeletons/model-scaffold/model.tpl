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
        $dto->setCount($this->getCount($select));
        return $this->fetchAll($where, null, $dto->getLimit(), $dto->getOffset());
    }

    public function updateById(array $data) {
        parent::update($data, $this->getPrimaryWhereClause($data));
    }

    public function deleteById($data) {
        parent::delete($this->getPrimaryWhereClause($data));
    }

    private function getCount(Zend_Db_Select $select) {
        $select->from($this->_name, array('count(*) as total'));
        $stmt = $select->query();
        $result = $stmt->fetchAll();
        return $result[0]['total'];
    }

    private function getPrimaryWhereClause(array $data) {
        $columns = array_keys($data);
        $diff = array_diff($this->_primary, $columns);
        if (count($diff) !== 0) {
            throw new Exception('primary key not found. [' . implode(',', $diff) . ']');
        }
        $select = $this->_db->select();
        foreach($this->_primary as $pk) {
            $select->where("$pk = ?", $data[$pk]);
        }
        return $select->getPart(Zend_Db_Select::WHERE);
    }
}
