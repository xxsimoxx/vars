(function() {
	tinymce.PluginManager.add(
		'vars_mce_menu',
		function( editor ) {
			editor.addButton(
				'vars_mce_menu',
				{
					text: 'vars',
					icon: 'template',
					type: 'menubutton',
					menu: $vars_dynmenu
				}
			);
		}
	);
})();
