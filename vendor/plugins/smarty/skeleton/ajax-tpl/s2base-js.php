s2base = {}

s2base.ajaxGetUpdate = function(url, target_id, form_id){
    new Ajax.Updater( {success: target_id},
        url, {
        method: "get",
        parameters: s2base.getFormParams(form_id),
        onComplete:function(httpObj){
            Behaviour.apply()
        },
        onFailure:function(httpObj){
            $(target_id).innerHTML = s2base.getRequestError(httpObj)
        }
    }
    );
}

s2base.ajaxPostUpdate = function(url, target_id, form_id, params){
    params = s2base.mergeParam(s2base.getFormParams(form_id), params)
    new Ajax.Updater( {success: target_id},
        url, {
        method: "post",
        parameters: params,
        onComplete:function(httpObj){
            Behaviour.apply()
        },
        onFailure:function(httpObj){
            $(target_id).innerHTML = s2base.getRequestError(httpObj)
        }
    }
    );
}

s2base.getRequestError = function(httpObj) {
    msg = httpObj.status + ' : ' + httpObj.statusText
    return msg
}

s2base.mergeParam = function(param, mergeParam) {
    ret = null
    if (param) {
        ret = param
    }
    if (mergeParam) {
        if (ret) {
            ret = ret + '&' + mergeParam
        } else {
            ret = mergeParam
        }
    }
    return ret
}

s2base.getFormParams = function(form_id) {
    if (form_id) {
        return Form.serialize(form_id)
    }
    return null
}

