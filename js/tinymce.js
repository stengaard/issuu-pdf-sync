(function() {
	tinymce.create('tinymce.plugins.ips', {
		init : function(ed, url) {
			jQuery( '#insert_video_button' ).live( "click", function( e ) {
				e.preventDefault();
				
				ed.execCommand(
					'mceInsertContent',
					false,
					ips_create_shortcode()
				);
				
				tb_remove();
			} );
			ed.addButton('ips', {
				title : 'Insert a PDF',
				image : url+'/../images/issuu_logo.png',
				onclick : function() {
					tb_show('', ajaxurl+'?action=ips_shortcodePrinter');
				}
			});
		},
	});
	tinymce.PluginManager.add('ips', tinymce.plugins.ips);
})();

function ips_create_shortcode() {
	return '[ips_video video_id="' + jQuery( '#ips_videos_list' ).val() + '" width="' + jQuery( '#ips_width' ).val() + '" height="' + jQuery( '#ips_height' ).val() + '"]';
}