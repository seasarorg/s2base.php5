[default]
action   = "@@ACTION_NAME@@"

[func : default]
validate = "regex"
regex.pattern = "/^create$|^update$|^delete$/"
regex.msg    = "invalid value"

