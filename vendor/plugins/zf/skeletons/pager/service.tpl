<?php
class @@CLASS_NAME@@ {
    private $@@DAO_PROPERTY@@;

    public function __construct(){}

    public function getByConditionDto(S2Dao_DefaultPagerCondition $dto){
        return $this->@@DAO_PROPERTY@@->findByConditionDtoList($dto);
    }

    public function set@@DAO_NAME@@(@@DAO_NAME@@ $dao){
        $this->@@DAO_PROPERTY@@ = $dao;
    } 
}
