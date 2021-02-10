require([
     "dijit/registry"
    ,"dojo/dom"
    ,"dojo/dom-construct"
    ,"dijit/layout/BorderContainer"
    ,"dijit/Dialog"
    ,"dijit/layout/ContentPane"
    ,"dijit/form/Form"
    ,"dijit/form/TextBox"
    ,"dijit/form/Button"
],
function (registry,dom,domConstruct,BorderContainer,Dialog,ContentPane,Form,TextBox,Button) {

    var validatorButton = registry.byId('markValidatorProjectButton');
    
    if (validatorButton) {
        validatorButton.onClick = function () {
            var globalState = validatorButton.dispatcher.getGlobalState();
            var dialog = registry.byId("newDocumentMarkValidatorDlg");

            if(!dialog){
                dialog = new Dialog({
                    id: "newDocumentMarkValidatorDlg",
                    title: validatorButton.title,
                    style: "width: 210px; height: 170px;",
                    validatorButton: validatorButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newDocumentMarkValidatorDlg");
                });

                dialog.on('show', function () {
                    dom.byId('textBoxDateValidation').value = ""; //Date();
                    dom.byId('textBoxDateValidation').focus();
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

                //DateValidation: Un camp de text per poder escriure la data de validació
                var divDateValidation = domConstruct.create('div', {
                    className: 'divDateValidation'
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: validatorButton.DateLabel + '<br>'
                },divDateValidation);

                var dataValidacio = new TextBox({
                    id: 'textBoxDateValidation'
                }).placeAt(divDateValidation);
                dialog.textBoxDateValidation = dataValidacio;


                // Botons
                var botons = domConstruct.create('div', {
                    className: 'botons',
                    style: "text-align:center;"
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<br><br>'
                }, botons);

                new Button({
                    label: validatorButton.labelButtonAcceptar,

                    onClick: function(){
                        if (dataValidacio.value !== '') {
                            var page = globalState.pages[globalState.currentTabId];
                            var query = 'do=workflow' + 
                                        '&action=validation' + 
                                        '&data_validacio=' + dataValidacio.value +
                                        '&id=' + page.ns + 
                                        '&projectType=' + page.projectType;
                            validatorButton.sendRequest(query);
                            dialog.hide();
                        }
                    }
                }).placeAt(botons);

                // Botó cancel·lar
                new Button({
                    label: validatorButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(botons);

                form.startup();
            }
            dialog.show();
            return false;
        };
    }
});
