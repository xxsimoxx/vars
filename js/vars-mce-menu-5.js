(function() {

tinymce.PluginManager.add('vars_mce_menu', function( editor, url ) {

	editor.ui.registry.addMenuButton( 'vars_mce_menu', {
		text:  'vars',
		icon:  'template',
		fetch: function (callback) {
			callback($vars_dynmenu);
		}
	});

	return {
		getMetadata: function () {
			return {
				name: 'Vars',
				url: 'https://software.gieffeedizioni.it/plugin/vars/',
			};
		}
	}

});

})();