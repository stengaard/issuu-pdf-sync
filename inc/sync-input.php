<?php if ( !empty( $issuu_document_id ) ) : ?>

	<span style="color:#00AA00;" id="admin_delete_pdf">
		<?php _e( 'This PDF is already synchronised on Issuu', 'ips' ); ?><br />

		<span class="trash">
			<?php 
			
			printf(
				'<a href="#" style="color:#BC0B0B;">%s</a>',
				__( 'Click here to delete this PDF from Issuu', 'ips' )
			);
			
			?>
		</span>
	</span>

<?php else : ?>

	<span style="color:#AA0000;" id="admin_send_pdf">
		<?php _e( 'This PDF is not synchronised on Issuu', 'ips' ); ?><br />
		
		<?php 
		
		printf(
			'<a href="#">%s</a>',
			__( 'Click here to send this PDF to Issuu', 'ips' )
		);
	
		?>
	</span>

<?php endif; ?>

<script type="text/javascript">
	jQuery(function() {

		// Sending PDF
		jQuery('#admin_send_pdf a').click(function( e ) {
			e.preventDefault();
			if( !window.confirm( '<?php echo esc_js( __( 'Are you sure you want to send this PDF on Issuu ?', 'ips' ) ); ?>' ) ){ 
				return false;
			}
			jQuery('#admin_send_pdf').html('<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" /> <?php _e( 'Loading', 'ips' ); ?>...');
			jQuery('#admin_send_pdf').css( 'color', '#000000');
			jQuery.get('<?php echo str_replace( '&amp;', '&', wp_nonce_url( admin_url( 'media.php?attachment_id=' . $attachment_id . '&amp;action=send_pdf' ), 'issuu_send_' . $attachment_id ) ); ?>', function(data) {
				
				if ( data == false ){
					jQuery('#admin_send_pdf').html('<?php echo esc_js( __( 'An error occured during synchronisation with Issuu', 'ips' ) ); ?>');
					jQuery('#admin_send_pdf').css( 'color', '#AA0000');
				}else {
					jQuery('#admin_send_pdf').html('<?php echo esc_js( __( 'Your PDF is now on Issuu !', 'ips' ) ); ?>');
					jQuery('#admin_send_pdf').css( 'color', '#00AA00');
				};
			});
		});
		
		// Deleting PDF
		jQuery('#admin_delete_pdf a').click(function( e ) {
			e.preventDefault();
			if( !window.confirm( '<?php echo esc_js( __('Are you sure you want to delete this PDF from Issuu ?', 'ips' ) ); ?>' ) ){ 
				return false;
			}
			jQuery('#admin_delete_pdf').html('<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" /> <?php esc_attr_e( 'Loading', 'ips' ); ?>...');
			jQuery('#admin_delete_pdf').css( 'color', '#000000');
			jQuery.get('<?php echo str_replace( '&amp;', '&', wp_nonce_url( admin_url( 'media.php?attachment_id=' . $attachment_id . '&amp;action=delete_pdf' ), 'issuu_delete_' . $attachment_id ) ); ?>', function(data) {
				
				if ( data == true ){
					jQuery('#admin_delete_pdf').html('<?php echo esc_js( __( 'Your PDF has been successfuly deleted', 'ips' ) ); ?>');
					jQuery('#admin_delete_pdf').css( 'color', '#00AA00');
				}else {
					jQuery('#admin_delete_pdf').html('<?php echo esc_js( __( 'An error occured during PDF deletion', 'ips' ) ); ?>');
					jQuery('#admin_delete_pdf').css( 'color', '#AA0000');
				};
			});
			e.preventDefault();
		});
	});
</script>