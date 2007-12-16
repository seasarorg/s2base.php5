methods: [post, get]
fields:
  offset:
    sfRegexValidator:
      match:        Yes
      match_error:  invalid offset
      pattern:      /^\d{1,4}$/

  keyword:
    sfRegexValidator:
      match:        Yes
      match_error:  invalid keyword
      pattern:      /^.{0,16}$/
