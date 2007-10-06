[default]

@@PARAMS@@

[func : default]
validate = "regex"
regex.pattern = "/^create$|^update$|^delete$/"
exception     = "invalid value [func]"
