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
    
    function dateNowDMY() {
        function pad(s) { return (s.length < 2 || s.toString().length < 2) ? '0' + s : s; }
        var d = new Date(Date.now());
        return [pad(d.getDate()), pad(d.getMonth()+1), d.getFullYear()].join('-');
    };

    if (validatorButton) {
        validatorButton.onClick = function () {
            var globalState = validatorButton.dispatcher.getGlobalState();
            var dialog = registry.byId("newMarkValidatorDlg");

            if (!dialog){
                dialog = new Dialog({
                    id: "newMarkValidatorDlg",
                    title: validatorButton.title,
                    style: "width: 210px; height: 170px;",
                    validatorButton: validatorButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newMarkValidatorDlg");
                });

                dialog.on('show', function () {
                    //var d = new Date(Date.now());
                    //dom.byId('textBoxDateValidation').value = d.getDate() + "-" + (d.getMonth()+1) + "-" + d.getFullYear();
                    dom.byId('textBoxDateValidation').value = dateNowDMY();
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
                var divBotons = domConstruct.create('div', {
                    className: 'divBotons',
                    style: "text-align:center;"
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<br><br>'
                }, divBotons);

                new Button({
                    label: validatorButton.labelButtonAcceptar,

                    onClick: function(){
                        if (dataValidacio.value !== '') {
                            var page = globalState.pages[globalState.currentTabId];
                            var query = 'do=workflow' + 
                                        '&action=markvalidator' + 
                                        '&data_validacio=' + dataValidacio.value +
                                        '&id=' + page.ns + 
                                        '&projectType=' + page.projectType;
                            validatorButton.sendRequest(query);
                            dialog.hide();
                        }
                    }
                }).placeAt(divBotons);

                // Botó cancel·lar
                new Button({
                    label: validatorButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(divBotons);

                form.startup();
            }
            dialog.show();
            return false;
        };
    }
    
});
