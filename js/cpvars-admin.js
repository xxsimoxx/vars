function enableSave (){
	var savebutton = jQuery('#cpvars-submit');
	savebutton.prop("disabled", false);
	savebutton.val(objectL10n.save);
}

jQuery('.cpvars-key, .cpvars-value, .doeverywhere, .whocanedit, .cleanup').on('change textInput input',function() {
	enableSave ();
});

jQuery('#cpvars-form').submit( function( ) {
	var vars = new Object();
	jQuery('.cpvars-keyvalue').each(function(){
		var key = jQuery(this).find('.cpvars-key').val();
		var value = jQuery(this).find('.cpvars-value').val();
		vars[key] = value;
	});
	jQuery(this).append('<input type="hidden" name="allvars" value=" ' + jQuery.param(vars) + '">');
}); 

jQuery('.cpvars-delete').click(function(){
	jQuery(this).closest("tr").hide('slow', function(){ jQuery(this).closest("tr").remove(); });
	enableSave ();
});

jQuery('.cpvars-add').click(function(){
	jQuery('<tr valign="top" class="cpvars-keyvalue"><td><input type="text" size="20" class="cpvars-key" placeholder="'+objectL10n.name+'" /></td><td><input type="text" size="100" class="cpvars-value" placeholder="'+objectL10n.content+'" /></td><td><a class="dashicons dashicons-trash cpvars-delete"></a></td></tr>'
	).hide().appendTo(".form-table").show("slow");
	enableSave ();
	jQuery('.cpvars-delete').click(function(){
		jQuery(this).closest("tr").hide('slow', function(){ jQuery(this).closest("tr").remove(); });
		enableSave ();
	});
});