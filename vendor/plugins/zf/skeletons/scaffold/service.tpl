<?php
class @@CLASS_NAME@@ {
    private $@@DAO_PROPERTY@@;

    public function __construct(){}

    public function getById($id){
        return $this->@@DAO_PROPERTY@@->findById($id);
    }

    public function getByConditionDto(S2Dao_DefaultPagerCondition $dto){
        return $this->@@DAO_PROPERTY@@->findByConditionDtoList($dto);
    }

    public function createByDto(@@ENTITY_CLASS_NAME@@ $dto) {
        return $this->@@DAO_PROPERTY@@->insert($dto);
    }

    public function updateByDto(@@ENTITY_CLASS_NAME@@ $dto) {
        return $this->@@DAO_PROPERTY@@->update($dto);
    }

    public function deleteByDto(@@ENTITY_CLASS_NAME@@ $dto) {
        return $this->@@DAO_PROPERTY@@->delete($dto);
    }

    public function set@@DAO_NAME@@(@@DAO_NAME@@ $dao){
        $this->@@DAO_PROPERTY@@ = $dao;
    } 
}
