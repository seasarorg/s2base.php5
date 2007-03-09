;
; Zend Config INI format
; http://framework.zend.com/manual/ja/zend.config.adapters.ini.html
;
; 「default」セクションはパラメータ検証項目から除外されます。
; 共通設定用のセクションとして使用します。「exception」項目が設定されている
; 場合は、例外処理が優先されます。全項目設定は任意です。(省略可)
;
; [default]
; module     = "検証に失敗した場合の遷移先モジュール名を指定します。"
;               default : 現在のモジュール名
; controller = "検証に失敗した場合の遷移先コントローラ名を指定します。"
;               default : 現在のコントローラ名
; action     = "検証に失敗した場合の遷移先アクション名を指定します。"
;               default : 現在のアクション名
; break      = "1つの検証が失敗した時点で検証処理を終了するかどうかをboole値で設定します。(true | false)"
;               default : false
; exception  = "検証に失敗した場合に例外をスローします。例外メッセージを記述します。"
;
;
;「リクエストパラメータ名」セクションは、defaultセクションを継承し上記設定項目を引き継ぎます。
; validate項目に設定する検証タイプは、Zend/Validate/***.php の「***」になります。
;   Alpha.php ---> alpha
;   Regex.php ---> regex
;
; [リクエストパラメータ名 : default]
; validate      = "カンマ区切りで検証タイプを設定します。( regex, alpha, , )"
; regex.pattern = "検証タイプ名をprefixとして各検証タイプに必要な項目を設定します。"
; regex.msg     = "検証タイプ名をprefixとして各検証タイプの検証失敗メッセージを設定します。"
;
; 例)
; [default]
; action = "bar"
;
; [foo : default]
; validate      = "regex"
; regex.pattern = "/^\d+$/"
; regex.msg     = "hoge"
;

[default]
action   = "@@ACTION_NAME@@"

[func : default]
validate = "regex"
regex.pattern = "/^create$|^update$|^delete$/"
regex.msg    = "invalid value"

