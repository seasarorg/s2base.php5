﻿;
;page   = "error.tpl"
;
;[param name]
;regexp = "^.{0,8}$"
;msg    = "invalid value"
;

page   = "redirect:@@ACTION_NAME@@"

[func]
regexp = "^create$|^update$|^delete$"
msg    = "invalid value"
