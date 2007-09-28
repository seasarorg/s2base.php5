[default]
action   = "@@RETURN_ACTION@@"

@@PARAMS@@

[func : default]
validate = "regex"
regex.pattern = "/^create$|^update$|^delete$/"
regex.msg    = "invalid value"

