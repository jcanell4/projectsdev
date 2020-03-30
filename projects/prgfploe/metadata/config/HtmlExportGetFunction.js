require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var prgfploeHtmlExportButton = registry.byId('prgfploeHtmlExport');
       
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_iocexport");
            this.setStandbyId(id + "_iocexport");
        };

        var fGetQuery=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            var ns = this.dispatcher.getGlobalState().getContent(id)["ns"];             
            var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
            var ret = "id="+ns + "&projectType="+projectType + "&mode=xhtml";
            return ret;
        };
       
        if (prgfploeHtmlExportButton){
            prgfploeHtmlExportButton.getQuery=fGetQuery;
            prgfploeHtmlExportButton.set("hasTimer", true);
            prgfploeHtmlExportButton.onClick =fOnClick;
        }
});
