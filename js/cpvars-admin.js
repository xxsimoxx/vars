jQuery(".cpvars-key, .cpvars-value, .doeverywhere, .cleanup, .doeval").change(function() {
	jQuery("#cpvars-submit").prop("disabled", false);
	jQuery("#cpvars-submit").val(objectL10n.save);
});

jQuery("#cpvars-form").submit( function(eventObj) {
	var vars = new Object();
	jQuery(".cpvars-keyvalue").each(function(){
		var key = jQuery(this).find('.cpvars-key').val();
		var value = jQuery(this).find('.cpvars-value').val();
		vars[key] = value;
	});
	jQuery(this).append('<input type="hidden" name="allvars" value=" ' + jQuery.param(vars) + '">');
}); 

jQuery('.cpvars-delete').click(function(){
	jQuery(this).closest("tr").hide('slow', function(){ jQuery(this).closest("tr").remove(); });
	jQuery("#cpvars-submit").prop("disabled", false);
	jQuery("#cpvars-submit").val(objectL10n.save);
});

jQuery('.cpvars-add').click(function(){
	jQuery('<tr valign="top" class="cpvars-keyvalue"><td><input type="text" size="20" class="cpvars-key" placeholder="'+objectL10n.name+'" /></td><td><input type="text" size="100" class="cpvars-value" placeholder="'+objectL10n.content+'" /></td><td><a class="dashicons dashicons-trash cpvars-delete"></a></td></tr>'
	).hide().appendTo(".form-table").show("slow");
	jQuery("#cpvars-submit").prop("disabled", false);
	jQuery("#cpvars-submit").val(objectL10n.save);
	jQuery('.cpvars-delete').click(function(){
		jQuery(this).closest("tr").hide('slow', function(){ jQuery(this).closest("tr").remove(); });
		jQuery("#cpvars-submit").prop("disabled", false);
		jQuery("#cpvars-submit").val(objectL10n.save);
	});
});