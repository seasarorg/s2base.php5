;
;page   = "error.tpl"
;
;[param name]
;regexp = "^.{0,8}$"
;msg    = "invalid value"
;

page   = "@@RETURN_PAGE@@"

@@PARAMS@@

[func]
regexp = "^create$|^update$|^delete$"
msg    = "invalid value"

