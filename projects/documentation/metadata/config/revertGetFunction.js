require([
    "dijit/registry",
    "dojo/domReady!"
], function (registry) {
        
    var reversionButton = registry.byId('documentationReversion');

    var fOnClick = function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        this.setStandbyId(id);
    };

    var fGetQuery = function(){
        var globalState = this.dispatcher.getGlobalState();
        var id = globalState.getCurrentId();
        var ns = globalState.getContent(id).ns,
            rev = globalState.getContent(id).rev,
            pType = globalState.getContent(id).projectType,
            subSet = globalState.getContent(id).metaDataSubSet;

        var ret = (this.query) ? this.query : "do=revert";
        ret += "&id="+ns + "&projectType="+pType + "&rev="+rev;
        if (subSet)
            ret += "&metaDataSubSet="+subSet;
        return ret;
    };

    if (reversionButton){
        reversionButton.getQuery=fGetQuery;
        reversionButton.onClick =fOnClick;
    }
    
});
