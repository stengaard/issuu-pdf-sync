(function() {
	tinymce.create('tinymce.plugins.ips', {
		init : function(ed, url) {
			jQuery( '#insert_issuu_pdf' ).live( "click", function( e ) {
				e.preventDefault();
				
				ed.execCommand(
					'mceInsertContent',
					false,
					ips_create_shortcode()
				);
				
				tb_remove();
			} );
			ed.addButton('ips', {
				title : 'Issuu PDF Sync',
				image : url+'/../images/issuu_logo.png',
				onclick : function() {
					tb_show('Issuu PDF Sync', ajaxurl+'?action=ips_shortcodePrinter&width=600&height=700');
				}
			});
		},
	});
	tinymce.PluginManager.add('ips', tinymce.plugins.ips);
})();

function ips_create_shortcode() {
	var inputs = jQuery('#ips_shortcode_generator').serializeArray();
	var shortcode = ' [pdf ';
	for( var a in inputs ) {
		if( inputs[a].value == "" )
			continue;
			
		inputs[a].name = inputs[a].name.replace( 'ips_', '' );
		shortcode += ' '+inputs[a].name+'="'+inputs[a].value+'"';
	}
	
	shortcode += ' ] ';
	
	return shortcode;
}