<?php
class @@CLASS_NAME@@ 
    implements @@INTERFACE_NAME@@ {

    public function __construct(){}

    public function getByConditionDto(S2Dao_DefaultPagerCondition $dto){
        if (false === ($contents = file(__FILE__)) ) {
            throw new Exception('file error occured.');
        }

        $count = count($contents);
        $dto->setCount($count);

        $offset = $dto->getOffset();
        $limit = $dto->getLimit() + $offset;
        $limit = $limit < $count ? $limit : $count;
        $result = array();
        for ($i=$offset; $i<$limit; $i++) {
            $result[] = preg_replace('/\n/','',$contents[$i]);
        }
        return $result;
    }
}
?>
