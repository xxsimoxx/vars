function enableSave (){
	var savebutton = jQuery( '#vars-submit' );
	savebutton.prop( "disabled", false );
	savebutton.val( objectL10n.save );
}

jQuery( '.vars-key, .vars-value, .doeverywhere, .whocanedit, .cleanup' ).on(
	'change textInput input',
	function() {
		enableSave();
	}
);

jQuery( '#vars-form' ).submit(
	function( ) {
		var vars = new Object();
		jQuery( '.vars-keyvalue' ).each(
			function(){
				var key   = jQuery( this ).find( '.vars-key' ).val();
				var value = jQuery( this ).find( '.vars-value' ).val();
				vars[key] = value;
			}
		);
		jQuery( this ).append( '<input type="hidden" name="allvars" value=" ' + jQuery.param( vars ) + '">' );
	}
);

jQuery( '.vars-delete' ).click(
	function(){
		jQuery( this ).closest( "tr" ).hide( 'slow', function(){ jQuery( this ).closest( "tr" ).remove(); } );
		enableSave();
	}
);

jQuery( '.vars-add' ).click(
	function(){
		jQuery(
			'<tr valign="top" class="vars-keyvalue"><td><input type="text" size="20" class="vars-key" placeholder="' + objectL10n.name + '" /></td><td><input type="text" size="100" class="vars-value" placeholder="' + objectL10n.content + '" /></td><td><a class="dashicons dashicons-trash vars-delete"></a></td></tr>'
		).hide().appendTo( ".form-table" ).show( "slow" );
		enableSave();
		jQuery( '.vars-delete' ).click(
			function(){
				jQuery( this ).closest( "tr" ).hide( 'slow', function(){ jQuery( this ).closest( "tr" ).remove(); } );
				enableSave();
			}
		);
	}
);
