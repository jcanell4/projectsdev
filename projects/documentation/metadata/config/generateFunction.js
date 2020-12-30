require([
    "dijit/registry",
    "dojo/domReady!"
], function (registry) {

    var generateButton = registry.byId('generateProjectButton');

    var fOnClick = function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        this.setStandbyId(id);
    };

    var fGetQuery = function() {
        var gState = this.dispatcher.getGlobalState();
        var id = gState.getCurrentId();
        var ns = (gState.currentTabId) ? gState.getContent(gState.currentTabId).ns : id;
        var projectType = gState.getContent(id).projectType;
        var ret = (this.query) ? this.query : "do=generate";
        ret += "&id="+ns + "&projectType="+projectType;
        return ret;
    };

    if (generateButton){
        generateButton.getQuery=fGetQuery;
        generateButton.onClick =fOnClick;
    }

});