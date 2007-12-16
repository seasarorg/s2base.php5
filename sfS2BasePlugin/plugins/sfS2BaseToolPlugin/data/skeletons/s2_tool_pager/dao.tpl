<?php
interface @@DAO_INTERFACE_NAME@@ {
    const BEAN = "@@ENTITY_CLASS_NAME@@";
    
    public function findByConditionDtoList(@@CONDITION_DTO_NAME@@ $dto);
    public function findAllList();
    //public function findAllArray();
    //public function update(@@ENTITY_CLASS_NAME@@ $entity);
    //public function insert(@@ENTITY_CLASS_NAME@@ $entity);
    //public function delete(@@ENTITY_CLASS_NAME@@ $entity);
}
