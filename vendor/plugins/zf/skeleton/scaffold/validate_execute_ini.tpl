﻿;
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
action   = "@@ACTION_NAME@@"

[func : default]
validate = "regex"
regex.pattern = "/^create$|^update$|^delete$/"
regex.msg    = "invalid value"

