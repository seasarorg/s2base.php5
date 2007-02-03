;
;page   = "template name"            required
;      or "forward:action name"
;      or "exception:exception message"
;class  = "Validator Class Name"     required
;
;[param name]
;regexp = "^.{0,8}$"                 required
;msg    = "invalid value"            required
;

page   = "@@RETURN_PAGE@@"
class  = "@@ACTION_NAME@@ConfirmValidator"

@@PARAMS@@

[func]
regexp = "^create$|^update$|^delete$"
msg    = "invalid value"

