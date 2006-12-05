;
;page   = "error.tpl"                required
;      or "forward:action name"
;class  = "Validator Class Name"     required
;
;[param name]
;regexp = "^.{0,8}$"                 required
;msg    = "invalid value"            required
;

page   = "forward:@@ACTION_NAME@@"
class  = "RegexpValidator"

[func]
regexp = "^create$|^update$|^delete$"
msg    = "invalid value"

