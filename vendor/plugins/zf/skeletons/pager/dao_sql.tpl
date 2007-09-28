select * from @@TABLE_NAME@@
/*BEGIN*/WHERE
  /*IF dto.keyword != null*/
@@WHERE_CONDITION@@
  /*END*/
/*END*/
;
