var actions = {
    '#hoge' : function(el){
        el.onclick = function(){
            s2base.ajaxGetUpdate('?act=index', 'result')
            return false
        }
    },
    '#foo' : function(el){
        el.onclick = function(){
            location.href = 'http://www.google.com'
            return false
        }
    },
    '#bar' : function(el){
        el.onclick = function(){
            alert('Genaretaed by S2Base')
            return false
        }
    }
}

Behaviour.register(actions)

