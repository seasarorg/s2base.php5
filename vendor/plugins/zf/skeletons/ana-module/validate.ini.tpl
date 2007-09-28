[default]
action = "login"

[identity : default]
validate      = "regex"
regex.pattern = "/^\w{4,16}$/"

[credential : default]
validate      = "regex"
regex.pattern = "/^.{4,16}$/"
