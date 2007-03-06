<?php
interface @@CLASS_NAME@@ {
    public function getById($id);
    public function getByConditionDto(S2Dao_DefaultPagerCondition $dto);
    public function createByDto(@@ENTITY_CLASS_NAME@@ $dto);
    public function updateByDto(@@ENTITY_CLASS_NAME@@ $dto);
    public function deleteByDto(@@ENTITY_CLASS_NAME@@ $dto);
}
?>
