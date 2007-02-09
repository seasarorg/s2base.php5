<?php
class @@CLASS_NAME@@ 
    implements @@INTERFACE_NAME@@ {
    private $@@DAO_PROPERTY@@;

    public function __construct(){}

    public function getWithLimit($limit) {
        $arrayobject = $this->@@DAO_PROPERTY@@->findAllList();
        $limit = $arrayobject->offsetExists($limit) ? $limit : $arrayobject->count();
        $dtos = new ArrayObject();
        $iterator = $arrayobject->getIterator();
        while ($iterator->key() < $limit) {
            $dtos->append($iterator->current());
            $iterator->next();
        }
        return $dtos;
    }

    public function set@@DAO_NAME@@(@@DAO_NAME@@ $dao){
        $this->@@DAO_PROPERTY@@ = $dao;
    } 
}
?>
