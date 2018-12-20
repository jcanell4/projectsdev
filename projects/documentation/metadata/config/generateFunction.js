require([
        "dijit/registry",
        "dojo/domReady!"
], function (registry) {

    var fOnClick=function() {
        _ret="";
        var gState=this.dispatcher.getGlobalState();
        var id=gState.getCurrentId();
        if (id) {
            var ns=id;
            if (gState.currentTabId)
                ns=gState.getContent(gState.currentTabId).ns;
            var projectType=gState.getContent(id).projectType;
            if(this.query){
                _ret=this.query+"&";
            }
            _ret+="id="+ns+"&projectType="+projectType;
        }
    };

});