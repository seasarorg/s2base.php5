<?php
interface @@CLASS_NAME@@ {
    const BEAN = "@@ENTITY_NAME@@";
    
    public function findById($@@UNIQUE_KEY_NAME@@);
    public function findByConditionDtoList(@@CONDITION_DTO_NAME@@ $dto);
    public function update(@@ENTITY_NAME@@ $entity);
    public function insert(@@ENTITY_NAME@@ $entity);
    public function delete(@@ENTITY_NAME@@ $entity);
}
?>
