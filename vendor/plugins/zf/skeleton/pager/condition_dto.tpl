<?php
class @@CONDITION_DTO_NAME@@ extends S2Dao_DefaultPagerCondition {
    private $keyword = null;
    public function setKeyword($val) {
        $this->keyword = $val;
    }
    public function getKeyword() {
        return $this->keyword;
    }
    public function getKeywordLike() {
        return '%' . $this->keyword . '%';
    }
}
?>
