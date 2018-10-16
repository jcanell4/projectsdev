require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var htmlExportButton = registry.byId('documentation_htmlExport');
       
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_iocexport");
            this.setStandbyId(id + "_iocexport");
        };

        var fGetQuery=function(){
            var globalState = this.dispatcher.getGlobalState();
            var id = globalState.getCurrentId();
            var ns = globalState.getContent(id).ns;
            var projectType = globalState.getContent(id)["projectType"]; 
            var ret = "id="+ns + "&projectType="+projectType + "&mode=xhtml";
            return ret;
        };
       
        if (htmlExportButton){
            htmlExportButton.getQuery=fGetQuery;
            htmlExportButton.set("hasTimer", true);
            htmlExportButton.onClick =fOnClick;
        }
});
