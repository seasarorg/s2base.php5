;
; Zend Config INI format
; http://framework.zend.com/manual/ja/zend.config.adapters.ini.html
;
; ��default�ץ��������ϥѥ�᡼�����ڹ��ܤ����������ޤ���
; ���������ѤΥ��������Ȥ��ƻ��Ѥ��ޤ�����exception�׹��ܤ����ꤵ��Ƥ���
; ���ϡ��㳰������ͥ�褵��ޤ��������������Ǥ�դǤ���(��ά��)
;
; [default]
; module     = "���ڤ˼��Ԥ�������������⥸�塼��̾����ꤷ�ޤ���"
;               default : ���ߤΥ⥸�塼��̾
; controller = "���ڤ˼��Ԥ������������襳��ȥ�����̾����ꤷ�ޤ���"
;               default : ���ߤΥ���ȥ�����̾
; action     = "���ڤ˼��Ԥ������������襢�������̾����ꤷ�ޤ���"
;               default : ���ߤΥ��������̾
; break      = "1�Ĥθ��ڤ����Ԥ��������Ǹ��ڽ�����λ���뤫�ɤ�����boole�ͤ����ꤷ�ޤ���(true | false)"
;               default : false
; exception  = "���ڤ˼��Ԥ��������㳰�򥹥������ޤ����㳰��å������򵭽Ҥ��ޤ���"
;
;
;�֥ꥯ�����ȥѥ�᡼��̾�ץ��������ϡ�default����������Ѿ����嵭������ܤ�����Ѥ��ޤ���
; validate���ܤ�����Ǥ��븡�ڥ����פϼ��ˤʤ�ޤ������줾�졢Zend_Validate_*** ���饹�����Ѥ���ޤ���
;   - alnum   (Zend_Validate_Alnum)
;   - alpha   (Zend_Validate_Alpha)
;   - date    (Zend_Validate_Date)
;   - float   (Zend_Validate_Float)
;   - int     (Zend_Validate_Int)
;   - ip      (Zend_Validate_Ip)
;   - regex   (Zend_Validate_Regex)
;
; [�ꥯ�����ȥѥ�᡼��̾ : default]
; validate      = "����޶��ڤ�Ǹ��ڥ����פ����ꤷ�ޤ���( regex, , )"
; regex.pattern = "���ڥ�����̾��prefix�Ȥ��ƳƸ��ڥ����פ�ɬ�פʹ��ܤ����ꤷ�ޤ���"
; regex.msg     = "���ڥ�����̾��prefix�Ȥ��ƳƸ��ڥ����פθ��ڼ��ԥ�å����������ꤷ�ޤ���"
;
; ��)
; [default]
; action = "bar"
;
; [foo : default]
; validate      = "regex"
; regex.pattern = "/^\d+$/"
; regex.msg     = "invalid value."
;