<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE components PUBLIC "-//SEASAR//DTD S2Container//EN"
"http://www.seasar.org/dtd/components21.dtd">
<components>
    <include path="%S2BASE_PHP5_ROOT%/app/commons/dicon/dao.dicon"/>
    <component class="@@SERVICE_CLASS@@">
        <!-- <aspect></aspect> -->
    </component>
    <component class="@@DAO_CLASS@@">
        <aspect>dao.interceptor</aspect>
    </component>
</components>
