[default]
action   = "@@RETURN_ACTION_NAME@@"

[@@PARAM_KEY@@ : default]
validate = "regex"
regex.pattern = "/^\d{1,8}$/"
regex.msg    = "invalid value"

