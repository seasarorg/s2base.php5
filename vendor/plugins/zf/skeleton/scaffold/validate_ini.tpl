;
;page   = "error.tpl"                required
;      or "forward:action name"
;      or "exception:exception message"
;class  = "Validator Class Name"     required
;
;[param name]
;regexp = "^.{0,8}$"                 required
;msg    = "invalid value"            required
;

page   = "forward:@@RETURN_ACTION_NAME@@"
class  = "RegexpValidator"

[@@PARAM_KEY@@]
regexp = "^.{1,8}$"
msg    = "invalid value"

