
[default]

[required : default]
required = on

[alnum : default]
validate   = "alnum"
;alnum.msg_NOT_ALNUM    = "'%value%' has not only alphabetic and digit characters"
;alnum.msg_STRING_EMPTY ="'%value%' is an empty string"
alnum.msg_NOT_ALNUM    = "'%value%' はアルファベット、または数値ではありません。"
alnum.msg_STRING_EMPTY = "'%value%' は空文字列です。"

[alpha : default]
validate   = "alpha"
;alpha.msg_NOT_ALPHA    = "'%value%' has not only alphabetic characters"
;alpha.msg_STRING_EMPTY = "'%value%' is an empty string"
alpha.msg_NOT_ALPHA    = "'%value%' はアルファベットではありません。"
alpha.msg_STRING_EMPTY = "'%value%' は空文字列です。"

[date : default]
validate   = "date"
;date.msg_NOT_YYYY_MM_DD = "'%value%' is not of the format YYYY-MM-DD"
;date.msg_INVALID        = "'%value%' does not appear to be a valid date"
date.msg_NOT_YYYY_MM_DD = "'%value%' は日付フォーマット(YYYY-MM-DD)ではありません。"
date.msg_INVALID        = "'%value%' は日付データではないようです。"

[float : default]
validate   = "float"
;float.msg_NOT_FLOAT = "'%value%' does not appear to be a float"
float.msg_NOT_FLOAT = "'%value%' はfloatではないようです。"

[int : default]
validate   = "int"
;int.msg_NOT_INT = "'%value%' does not appear to be an integer"
int.msg_NOT_INT = "'%value%' はintegerではないようです。"

[ip : default]
validate   = "ip"
;ip.msg_NOT_IP_ADDRESS = "'%value%' does not appear to be a valid IP address"
ip.msg_NOT_IP_ADDRESS = "'%value%' はIP Addressではないようです。"

[notempty : default]
validate   = "notempty"
;notempty.msg_IS_EMPTY = "Value is empty, but a non-empty value is required"
notempty.msg_IS_EMPTY = "空の値は許可されていません。"

[regex : default]
validate   = "regex"
;regex.msg_NOT_MATCH = "'%value%' does not match against pattern '%pattern%'"
regex.msg_NOT_MATCH = "'%value%' は正規表現 '%pattern%' にマッチしません。"
regex.pattern = "/^\d+$/"

[between : default]
validate   = "between"
between.msg_NOT_BETWEEN_STRICT = "'%value%' は '%min%'から'%max%'の範囲に含まれません。"
between.min = 15
between.max = 20

[between_inclusive : default]
validate = "between"
between.msg_NOT_BETWEEN = "'%value%' は '%min%以上'、 '%max%以下'の範囲に含まれません。"
between.min = 15
between.max = 20
between.inclusive = on

[greater_than : default]
validate   = "greater_than"
greater_than.msg_NOT_GREATER = "'%value%' は '%min%'より大きくありません。"
greater_than.min = 5

[less_than : default]
validate   = "less_than"
less_than.msg_NOT_LESS = "'%value%' は '%max%'より小さくありません。"
less_than.max = 5

[strlen_short : default]
validate   = "strlen"
strlen.msg_TOO_SHORT = "文字列 '%value%' は 文字列長'%min%'より短いです。"
strlen.msg_TOO_LONG  = "文字列 '%value%' は 文字列長'%max%'より長いです。"
strlen.min = 3
strlen.max = 6

[strlen_long : default]
validate   = "strlen"
strlen.msg_TOO_SHORT = "文字列 '%value%' は 文字列長'%min%'より短いです。"
strlen.msg_TOO_LONG  = "文字列 '%value%' は 文字列長'%max%'より長いです。"
strlen.min = 3
strlen.max = 6

[email : default]
validate   = "email"
;email.msg_INVALID ="'%value%' is not a valid email address in the basic format local-part@hostname"
email.msg_INVALID ="%value%は不正なメールアドレスです。"

[host : default]
validate   = "host"
;host.msg_INVALID_HOSTNAME = "'%value%' does not match the expected structure for a DNS hostname"
host.msg_INVALID_HOSTNAME = "%value%はDNSの構造にマッチしないようです。"

[mb_strlen_short : default]
validate   = "mb_strlen"
mb_strlen.msg_TOO_SHORT = "文字列 '%value%' は 文字列長'%min%'より短いです。"
mb_strlen.msg_TOO_LONG  = "文字列 '%value%' は 文字列長'%max%'より長いです。"
mb_strlen.min = 2
mb_strlen.max = 4
mb_strlen.encoding = "UTF-8"

[mb_strlen_long : default]
validate   = "mb_strlen"
mb_strlen.msg_TOO_SHORT = "文字列 '%value%' は 文字列長'%min%'より短いです。"
mb_strlen.msg_TOO_LONG  = "文字列 '%value%' は 文字列長'%max%'より長いです。"
mb_strlen.min = 2
mb_strlen.max = 4
mb_strlen.encoding = "UTF-8"

[array : default]
validate   = "array"
array.msg_NOT_ARRAY = "値が配列ではありません。"
array.max = 4

[array_max : default]
validate   = "array"
array.msg_NOT_LESS = "配列のサイズが'%max%'より大きいです。"
array.max = 0

[param_equals_2 : default]
validate   = "param_equals"
param_equals.msg_NOT_MATCH = "'%value%' は、リクエストパラメータ'%param%'の値と等しくありません。"
param_equals.param = "param_equals_1"
