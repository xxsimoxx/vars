(function() {

tinymce.PluginManager.add('vars_mce_menu', function( editor, url ) {
	console.log('add');

	editor.ui.registry.addButton('vars_simo', {
		text: 'Simo',
		onAction: function () {
			editor.insertContent('&nbsp;<em>You clicked menu item simo!</em>');
		}
	});
	console.log('add2');

	editor.ui.registry.addMenuButton( 'vars_mce_menu', {
		text: 'vars',
		// icon: 'code',
	
	
		fetch: function (callback) {
		console.log('fetch');

			var items = [
				{
					type: 'menuitem',
					text: 'Menu item 1',
					onAction: function () {
						editor.insertContent('&nbsp;<em>You clicked menu item 1!</em>');
					}
				},
				{
					type: 'menuitem',
					text: 'Menu item 2',
					onAction: function () {
						editor.insertContent('&nbsp;<em>You clicked menu item 2!</em>');
					}
				},
			];
			callback(items);
		}
		  
		  
	});

	return {
		getMetadata: function () {
			return {
				name: 'Custom plugin',
				url: 'https://example.com/docs/customplugin'
			};
		}
	}



});



})();