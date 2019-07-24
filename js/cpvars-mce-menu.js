(function() {
        tinymce.PluginManager.add('cpvars_mce_menu', function( editor, url ) {
           editor.addButton( 'cpvars_mce_menu', {
                 text: 'CPvars',
                 icon: false,
                 type: 'menubutton',
                 menu: $cpvars_dynmenu
              });
        });
})();