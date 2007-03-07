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

[default]
action   = "@@RETURN_ACTION_NAME@@"

[@@PARAM_KEY@@ : default]
validate = "regex"
regex.pattern = "/^\d{1,8}$/"
regex.msg    = "invalid value"

