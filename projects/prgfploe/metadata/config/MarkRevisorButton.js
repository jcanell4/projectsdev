require([
     "dijit/registry"
    ,"dojo/dom"
    ,"dojo/dom-construct"
    ,"dijit/layout/BorderContainer"
    ,"dijit/Dialog"
    ,"dijit/layout/ContentPane"
    ,"dijit/form/Form"
    ,"dijit/form/Button"
],
function (registry,dom,domConstruct,BorderContainer,Dialog,ContentPane,Form,Button) {

    var revisorButton = registry.byId('markRevisorProjectButton');
    
    if (revisorButton) {
        revisorButton.onClick = function () {
            var globalState = revisorButton.dispatcher.getGlobalState();
            var dialog = registry.byId("newDocumentMarkRevisorDlg");

            if (!dialog){
                dialog = new Dialog({
                    id: "newDocumentMarkRevisorDlg",
                    title: revisorButton.title,
                    style: "width: 210px; height: 170px;",
                    revisorButton: revisorButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newDocumentMarkRevisorDlg");
                });

                var bc = new BorderContainer({
                    style: "width: 200px; height: 120px;"
                });

                // create a ContentPane as the center pane in the BorderContainer
                var cpCentre = new ContentPane({
                    region: "center"
                });
                bc.addChild(cpCentre);

                // put the top level widget into the document, and then call startup()
                bc.placeAt(dialog.containerNode);

                // Un formulari dins del contenidor
                var divForm = domConstruct.create('div', {
                    className: 'divform'
                },cpCentre.containerNode);

                var form = new Form().placeAt(divForm);

                //MessageText: Un missatge de text informatiu
                var divMessageText = domConstruct.create('div', {
                    className: 'divMessageText'
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: revisorButton.labelText + '<br>'
                },divMessageText);


                // Botons
                var divBotons = domConstruct.create('div', {
                    className: 'divBotons',
                    style: "text-align:center;"
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<br><br>'
                }, divBotons);

                new Button({
                    label: revisorButton.labelButtonAcceptar,

                    onClick: function(){
                        var page = globalState.pages[globalState.currentTabId];
                        var query = 'do=workflow' + 
                                    '&action=markrevisor' + 
                                    '&id=' + page.ns + 
                                    '&projectType=' + page.projectType;
                        revisorButton.sendRequest(query);
                        dialog.hide();
                    }
                }).placeAt(divBotons);

                // Botó cancel·lar
                new Button({
                    label: revisorButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(divBotons);

                form.startup();
            }
            dialog.show();
            return false;
        };
    }
});
