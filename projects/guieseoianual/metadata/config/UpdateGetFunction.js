require([
    "dijit/registry",
    "dojo/domReady!"
], function (registry) {

    var guieseoianualUpdateDataProjectButton = registry.byId('guieseoianualUpdateDataProject');

    var fGetQuery=function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        var ns = this.dispatcher.getGlobalState().getContent(id)["ns"];
        var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"];
        var ret = "id="+ns + "&projectType="+projectType;
        return ret;
    };

    if (guieseoianualUpdateDataProjectButton){
        guieseoianualUpdateDataProjectButton.getQuery=fGetQuery;
    }
});
