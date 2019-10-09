(function() {
        tinymce.PluginManager.add('cpvars_mce_menu', function( editor ) {
           editor.addButton( 'cpvars_mce_menu', {
                 text: 'CPvars',
                 icon: 'code',
                 type: 'menubutton',
                 menu: $cpvars_dynmenu
              });
        });
})();