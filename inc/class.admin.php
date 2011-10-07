<?php
class IPS_Admin {
	
	/**
	 * Constructor PHP4 like
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	function IPS_Admin() {
		global $pagenow;
		
		add_filter("attachment_fields_to_edit", array(&$this, "insertIPSButton"), 10, 2);
		add_filter("media_send_to_editor", array(&$this, "sendToEditor"));
		
		if ( $pagenow == "media.php" )
			add_action("admin_head", array(&$this, "editMediaJs"), 50 );
		
		add_action( 'admin_init', array( &$this, 'checkJsPdfEdition' ) );
		add_action( 'admin_menu', array( &$this, 'addPluginMenu' ) );
		
		wp_enqueue_script( 'jquery' );
		
		// Add the tinyMCE button
		add_action( 'admin_init', array (&$this, 'addButtons' ) );
		add_action( 'wp_ajax_ips_shortcodePrinter', array( &$this, 'wp_ajax_fct' ) );
		
	}
	
	function addPluginMenu() {
		add_options_page( __('Options for Issuu PDF Sync', 'ips'), __('Issuu PDF Sync', 'ips'), 'manage_options', 'ips-options', array( &$this, 'displayOptions' ) );
	}
	
	/**
	 * Call the admin option template
	 * 
	 * @echo the form 
	 * @author Benjamin Niess
	 */
	function displayOptions() {
		if ( isset($_POST['save']) ) {
			$new_options = array();
			
			// Update existing
			foreach( (array) $_POST['ips'] as $key => $value ) {
				$new_options[$key] = stripslashes($value);
			}
			
			update_option( 'ips_options', $new_options );
		}
		
		if (isset($_POST['save']) ) {
			echo '<div class="message updated"><p>'.__('Options updated!', 'ips').'</p></div>';
		}
		
		$fields = get_option('ips_options');
		if ( $fields == false ) {
			$fields = array();
		}
		?>
		<div class="wrap" id="ips_options">
			<h2><?php _e('Issuu PDF Sync', 'ips'); ?></h2>
			
			<form method="post" action="">
				<table class="form-table describe media-upload-form">
					
					<tr><td colspan="2"><h3><?php _e('Issuu configuration', 'ips'); ?></h3></td></tr> 
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[issuu_api_key]"><span class="alignleft"><?php _e('Issuu API Key', 'ips'); ?>
							<br /><a href="http://issuu.com/" target="_blank"><?php _e('Get an Issuu API Key', 'ips'); ?></span>
						</label></th>
						<td><input id="ips[issuu_api_key]" type="text" class="text" name="ips[issuu_api_key]" value="<?php echo isset( $fields['issuu_api_key'] ) ? $fields['issuu_api_key'] : '' ; ?>" /></a>
						</td>
					</tr>
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[issuu_secret_key]"><span class="alignleft"><?php _e('Issuu private key', 'ips'); ?></span></label></th>
						<td><input id="ips[issuu_secret_key]" type="text" name="ips[issuu_secret_key]" value="<?php echo isset( $fields['issuu_secret_key'] ) ? $fields['issuu_secret_key'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[auto_upload]"><span class="alignleft"><?php _e('Automatically upload PDFs to Issuu', 'ips'); ?></span></label></th>
						<td><input id="ips[auto_upload]" type="checkbox" <?php checked( isset( $fields['auto_upload'] ) ? $fields['auto_upload'] : '' , 1 ); ?> name="ips[auto_upload]" value="1" /></td>
					</tr>
					
					<tr><td colspan="2"><h3><?php _e('Default embed code configuration', 'ips'); ?></h3></td></tr> 
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[layout]"><span class="alignleft"><?php _e('Layout', 'ips'); ?></span></label></th>
						<td>
							<p style="height:25px;"><input id="ips[layout]" type="radio" name="ips[layout]" value="1" <?php checked( isset( $fields['layout'] ) ? $fields['layout'] : 0 , 1 ); ?> /> <?php _e('Two up', 'ips'); ?><img src="<?php echo IPS_URL . '/images/layout-double-pages.png' ; ?>" height="16" style="margin-left:5px;" /></p>
							<p><input type="radio" name="ips[layout]" value="2" <?php checked( isset( $fields['layout'] ) ? $fields['layout'] : 0 , 2 ); ?> /> <?php _e('Single page', 'ips'); ?><img src="<?php echo IPS_URL . '/images/layout-single-page.png' ; ?>" height="16" style="margin-left:5px;" /></p>
						</td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[width]"><span class="alignleft"><?php _e('Width', 'ips'); ?></span></label></th>
						<td><input id="ips[width]" type="number" min="0" max="2000" name="ips[width]" value="<?php echo isset(  $fields['width'] ) ? (int)$fields['width'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[height]"><span class="alignleft"><?php _e('Height', 'ips'); ?></span></label></th>
						<td><input id="ips[height]" type="number" min="0" max="2000" name="ips[height]" value="<?php echo isset(  $fields['height'] ) ? (int)$fields['height'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[bgcolor]"><span class="alignleft"><?php _e('Background color', 'ips'); ?></span></label></th>
						<td># <input id="ips[bgcolor]" style="width:65px;" type="text" maxlength="6" name="ips[bgcolor]" value="<?php echo isset(  $fields['bgcolor'] ) ? $fields['bgcolor'] : ''; ?>" /></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[allow_full_screen]"><span class="alignleft"><?php _e('Allow full screen', 'ips'); ?></span></label></th>
						<td><input id="ips[allow_full_screen]" type="checkbox" <?php checked( isset( $fields['allow_full_screen'] ) ? $fields['allow_full_screen'] : '' , 1 ); ?> name="ips[allow_full_screen]" value="1" /></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[show_flip_buttons]"><span class="alignleft"><?php _e('Always show flip buttons', 'ips'); ?></span></label></th>
						<td><input id="ips[show_flip_buttons]" type="checkbox" <?php checked( isset( $fields['show_flip_buttons'] ) ? $fields['show_flip_buttons'] : '' , 1 ); ?> name="ips[show_flip_buttons]" value="1" /></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[autoflip]"><span class="alignleft"><?php _e('Auto flip', 'ips'); ?></span></label></th>
						<td>
							<input type="checkbox" id="ips[autoflip]" name="ips[autoflip]" value="1" <?php checked( isset( $fields['autoflip'] ) ? $fields['autoflip'] : 0 , 1 ); ?> />
						</td>
					</tr>  
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[flip_timelaps]"><span class="alignleft"><?php _e('Flip time laps', 'ips'); ?></span></label></th>
						<td><input id="ips[flip_timelaps]" type="number" step="100" min="1000" max="200000" name="ips[flip_timelaps]" value="<?php echo isset(  $fields['flip_timelaps'] ) ? (int)$fields['flip_timelaps'] : '6000'; ?>" />
							<p class="description"><?php _e('(in miliseconds - default : 6000)', 'ips'); ?></p>
						</td>
					</tr>
					
					<tr><td colspan="2"><h3><?php _e('How to use the shortocde ?', 'ips'); ?></h3></td></tr>
					
					<tr><td colspan="2">
						
						<ol>
							<li><?php _e('Click to the media button, choose a PDF document and click on the Issuu PDF button to insert the basic shortcode', 'ips'); ?></li>
							<li><?php _e('If you want to add params for a specific PDF, you can follow these examples:', 'ips'); ?></li>
						</ol>
						
					</td></tr>
					<tr><td colspan="2">
						<p><code><?php _e('[pdf issuu_pdf_id="id_of_your_PDF" width="500" height="300"]', 'ips'); ?></code></p>
						<p class="description"><?php _e('In this example, we want to specify a width and a height only for this PDF', 'ips'); ?></p>
						
						<p><code><?php _e('[pdf issuu_pdf_id="id_of_your_PDF" layout="browsing" autoFlip="true" autoFlipTime="4000"]', 'ips'); ?></code></p>
						<p class="description"><?php _e('In this other example, we want to specify the browsing layout (one page presentation) and we want the PDF pages to autoflip each 4 seconds', 'ips'); ?></p>
					
					
					<tr><td colspan="2"><h3><?php _e('Which params can be used with the shortcode ?', 'ips'); ?></h3></td></tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">issuu_pdf_id</span></label></th>
						<td><p class="description"><?php _e('The ISSUU PDF ID', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">width</span></label></th>
						<td><p class="description"><?php _e('The width of the animation in pixels', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">height</span></label></th>
						<td><p class="description"><?php _e('The height of the animation in pixels', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">layout</span></label></th>
						<td><p class="description"><?php _e('The layout of the animation. Possible values : "<strong>presentation</strong>" (double page), "<strong>browsing</strong>" (single page)', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">backgroundColor</span></label></th>
						<td><p class="description"><?php _e('The background color - In hexadecimal format - without "#" ', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">autoFlip</span></label></th>
						<td><p class="description"><?php _e('Enable or disable the Auto Flip feature. Possible values : "true", "false"', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">autoFlipTime</span></label></th>
						<td><p class="description"><?php _e('The timelaps for the page flipe in milliseconds', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">showFlipBtn</span></label></th>
						<td><p class="description"><?php _e('Allways show the right left flip buttons. Possible values : "true", "false"', 'ips'); ?></p></td>
					</tr>
					
					<tr valign="top" class="field">
						<th class="label" scope="row"><label><span class="alignleft">allowfullscreen</span></label></th>
						<td><p class="description"><?php _e('Allow the full screen mode (if not, open in a new window). Possible values : "true", "false"', 'ips'); ?></p></td>
					</tr>
					
					
				</table>
				
				<p class="submit">
					<input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes', 'ips') ?>" />
				</p>
			</form>
		</div>
		<?php
	}
	
	/*
	 * Send a WordPress PDF to Issuu webservice
	 * 
	 * @param $post_id the WP post id
	 * @return string the issuu document id | false 
	 * @author Benjamin Niess
	 */
	function sendPDFToIssuu( $post_id = 0 ){
		global $ips_options;
		
		if ( (int)$post_id == 0 )
			return false;
		
		if ( $this->hasApiKeys() == false )
			return false;
		
		// Get attachment infos
		$post_data = get_post( $post_id );
		
		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;
		
		// Prepare the MD5 signature for the Issuu Webservice
		$md5_signature = md5( $ips_options['issuu_secret_key'] . "actionissuu.document.url_uploadapiKey" . $ips_options['issuu_api_key'] . "formatjsonslurpUrl" . $post_data->guid . "title" . sanitize_title( $post_data->post_title ) );
		
		// Call the Webservice
		$url_to_call = "http://api.issuu.com/1_0?action=issuu.document.url_upload&apiKey=" . $ips_options['issuu_api_key'] . "&slurpUrl=" . $post_data->guid . "&format=json&title=" . sanitize_title( $post_data->post_title ) . "&signature=" . $md5_signature; 
		
		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );
		
		// Check if no sever error
		if( is_wp_error($response) || isset($response->errors) || $response == null ) {
			return false;
		}
		// Decode the Json
		$response = json_decode( $response['body'] );
		
		if ( empty( $response) )
			return false;
			
		// Check stat of the action
		
		if ( $response->rsp->stat == "fail" )
			return false;
		
		// Check if the publication id exists
		if ( !isset( $response->rsp->_content->document->documentId ) || empty( $response->rsp->_content->document->documentId ) )
			return false;
		
		// Update the attachment post meta with the Issuu PDF ID
		update_post_meta( $post_id, 'issuu_pdf_id', $response->rsp->_content->document->documentId );
		update_post_meta( $post_id, 'issuu_pdf_name', $response->rsp->_content->document->name );
		
		return $response->rsp->_content->document->documentId;
	}

	
	/*
	 * Delete an Issuu PDF from Issuu webservice
	 * 
	 * @param $post_id the WP post id
	 * @return true | false 
	 * @author Benjamin Niess
	 */
	function deletePDFFromIssuu( $post_id = 0 ){
		global $ips_options;
		
		if ( (int)$post_id == 0 )
			return false;
		
		// Get attachment infos
		$post_data = get_post( $post_id );
		
		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;
		
		$issuu_pdf_name = get_post_meta( $post_id, 'issuu_pdf_name', true );
		if ( empty( $issuu_pdf_name ) )
			return false;
		
		// Prepare the MD5 signature for the Issuu Webservice
		$md5_signature = md5( $ips_options['issuu_secret_key'] . "actionissuu.document.deleteapiKey" . $ips_options['issuu_api_key'] . "formatjsonnames" . $issuu_pdf_name );
		
		// Call the Webservice
		$url_to_call = "http://api.issuu.com/1_0?action=issuu.document.delete&apiKey=" . $ips_options['issuu_api_key'] . "&format=json&names=" . $issuu_pdf_name . "&signature=" . $md5_signature; 
		
		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );
		
		// Check if no sever error
		if( is_wp_error($response) || isset($response->errors) || $response == null ) {
			return false;
		}
		// Decode the Json
		$response = json_decode( $response['body'] );
		
		if ( empty( $response) )
			return false;
			
		// Check stat of the action
		if ( $response->rsp->stat == "fail" )
			return false;
		
		// Update the attachment post meta with the Issuu PDF ID
		delete_post_meta( $post_id, 'issuu_pdf_id' );
		delete_post_meta( $post_id, 'issuu_pdf_name' );
		update_post_meta( $post_id, 'disable_auto_upload', 1 );

		return true;
	}

	/**
	 * Inserts Issuu PDF Sync button into media library popup
	 * @return the amended form_fields structure
	 * @param $form_fields Object
	 * @param $post Object
	 */
	function insertIPSButton( $form_fields, $attachment ) {
		global $wp_version, $ips_options;
		
		if ( !isset( $form_fields ) || empty( $form_fields ) || !isset( $attachment ) || empty( $attachment ) )
			return $form_fields;
		
		$file = wp_get_attachment_url( $attachment->ID );
		
		// Only add the extra button if the attachment is a PDF file
		if ( $attachment->post_mime_type != 'application/pdf' )
			return $form_fields;
		
		// Allow plugin to stop the auto-insertion
		$check = apply_filters( 'insert-ips-button', true, $attachment, $form_fields );
		if ( $check !== true ) 
			return $form_fields;
		
		// Check on post meta if the PDF has already been uploaded on Issuu
		$issuu_pdf_id = get_post_meta( $attachment->ID, 'issuu_pdf_id', true );
		$disable_auto_upload = get_post_meta( $attachment->ID, 'disable_auto_upload', true );
		
		// Upload the PDF to Issuu if necessary and if the Auto upload feature is enabled
		if ( empty( $issuu_pdf_id ) && isset( $ips_options['auto_upload'] ) && $ips_options['auto_upload'] == 1 && $disable_auto_upload != 1)
			$issuu_pdf_id = $this->sendPDFToIssuu( $attachment->ID );
		
		if ( empty( $issuu_pdf_id ) )
			return $form_fields;
		
		$form_fields["url"]["html"] .= "<button type='button' class='button urlissuupdfsync issuu-pdf-" . $issuu_pdf_id . "' value='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]' title='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]'>" . _( 'Issuu PDF' ) . "</button>";
		
		return $form_fields;
	}

	/*
	 * Check if the Issuu API Key and Secret Key are entered
	 * @return true | false
	 * @author Benjamin Niess
	 */
	function hasApiKeys(){
		global $ips_options;
		
		if ( !isset( $ips_options['issuu_api_key'] ) || empty( $ips_options['issuu_api_key'] ) || !isset( $ips_options['issuu_secret_key'] ) || empty( $ips_options['issuu_secret_key'] ) )
			return false;
		
		return true;
	}

	/**
	 * Format the html inserted when the PDF button is used
	 * @param $html String
	 * @return String The pdf url
	 * @author Benjamin Niess
	 */
	function sendToEditor( $html ) {
		if( preg_match( '|\[pdf (.*?)\]|i', $html, $matches ) ) {
			if ( isset($matches[0]) ) {
				$html = $matches[0];
			}
		}
		return $html;
	}
	
	/*
	 * Check if an action is set on the $_GET var and call the PHP function corresponding 
	 * @return true | false
	 * @author Benjamin Niess
	 */
	function checkJsPdfEdition(){

		if ( !isset( $_GET['attachment_id'] ) || (int)$_GET['attachment_id'] == 0 || !isset( $_GET['action'] ) || empty( $_GET['action'] ) )
			return false;
		
		if ( $_GET['action'] == 'send_pdf' ){
			//check if the nonce is correct
			check_admin_referer( 'issuu_send_' . $_GET['attachment_id'] );
			
			die( $this->sendPDFToIssuu( $_GET['attachment_id'] ) );
		} elseif ( $_GET['action'] == 'delete_pdf' ){
			
			//check if the nonce is correct
			check_admin_referer( 'issuu_delete_' . $_GET['attachment_id'] );
			
			die( $this->deletePDFFromIssuu( $_GET['attachment_id'] ) );
		}
	}
	
	/*
	 * Print some JS code for the media.php page (for PDFs only)
	 * @author Benjamin Niess
	 */
	function editMediaJs(){
		global $ips_options;
		
		if ( !isset( $_GET['attachment_id'] ) || (int)$_GET['attachment_id'] <= 0 || !isset( $ips_options['issuu_api_key'] ) || empty( $ips_options['issuu_api_key'] ) || !isset( $ips_options['issuu_secret_key'] ) || empty( $ips_options['issuu_secret_key'] ) )
			return false;
			
		// Get attachment infos
		$post_data = get_post( $_GET['attachment_id'] );
		
		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;
		
		// Check on post meta if the PDF has already been uploaded on Issuu
		$issuu_pdf_id = get_post_meta( $_GET['attachment_id'], 'issuu_pdf_id', true );
		
		?>
		<script type="text/javascript">
			jQuery(function() {
				
				jQuery('#media-single-form .slidetoggle tbody tr').last().after('<tr class="reload_pdf"><th valign="top" scope="row" class="label"><label><span class="alignleft"><?php esc_attr_e( 'Issuu status', 'ips' ); ?></span><br class="clear"></label></th><td class="field"><?php 
					if ( !empty( $issuu_pdf_id ) ) : 
						?><p style="color:#00AA00;" id="admin_delete_pdf"><?php esc_attr_e( 'This PDF is already synchronised on Issuu', 'ips' ); ?> <br /><span class="trash"><a href="#" style="color:#BC0B0B;"><?php esc_attr_e( 'Click here to delete this PDF from Issuu', 'ips' ); ?></a></span></p><?php 
					else : 
						?><p style="color:#AA0000;" id="admin_send_pdf"><?php esc_attr_e( 'This PDF is not synchronised on Issuu', 'ips' ); ?> <br /><a href="#"><?php esc_attr_e( 'Click here to send this PDF to Issuu', 'ips' ); ?></a></p><?php 
					endif; 
				?></td></tr>');
				
				// Sending PDF
				jQuery('#admin_send_pdf a').click(function( e ) {
					e.preventDefault();
					if( !window.confirm( '<?php echo esc_js( __( 'Are you sure you want to send this PDF on Issuu ?', 'ips' ) ); ?>' ) ){ 
						return false;
					}
					jQuery('#admin_send_pdf').html('<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" /> <?php _e( 'Loading', 'ips' ); ?>...');
					jQuery('#admin_send_pdf').css( 'color', '#000000');
					jQuery.get('<?php echo str_replace( '&amp;', '&', wp_nonce_url( admin_url( 'media.php?attachment_id=' . $_GET['attachment_id'] . '&amp;action=send_pdf' ), 'issuu_send_' . $_GET['attachment_id'] ) ); ?>', function(data) {
						
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
					jQuery.get('<?php echo str_replace( '&amp;', '&', wp_nonce_url( admin_url( 'media.php?attachment_id=' . $_GET['attachment_id'] . '&amp;action=delete_pdf' ), 'issuu_delete_' . $_GET['attachment_id'] ) ); ?>', function(data) {
						
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
		<?php
	}

	/*
	 * The content of the javascript popin for the PDF insertion
	 * 
	 * @author Benjamin Niess
	 */
	function wp_ajax_fct(){
		?>
		<h2><?php _e('Select a PDF file', 'ips'); ?></h2>
		<select name="ips_pdf_list" id="ips_pdf_list">
			<?php
			$pdf_files = new WP_Query( array( 'post_type' => 'attachment', 'nopaging' => true, 'post_status' => 'publish' ) );
			if ( $pdf_files->have_posts() ) while ( $pdf_files->have_posts() ) : $pdf_files->the_post(); ?>
				<option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
			<?php endwhile; ?>
		</select>
		<h2>Choisissez la taille du player</h2>
		<p>Largeur : <br /><input type="number" min="100" max="1000" name="ips_width" id="ips_width" value="640" /> px</p>
		<p>Hauteur : <br /><input type="number" min="100" max="1000" name="ips_height" id="ips_height" value="480" /> px</p>
		<input name="insert_video_button" type="submit" class="button-primary" id="insert_video_button" tabindex="5" accesskey="p" value="Insérer la vidéo">
		<?php die();
	}

	/*
	 * Add buttons to the tiymce bar
	 * 
	 * @author Benjamin Niess
	 */
	function addButtons() {
		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return false;
		
		if ( get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', array (&$this,'addScriptTinymce' ) );
			add_filter('mce_buttons', array (&$this,'registerTheButton' ) );
		}
	}

	/*
	 * Add buttons to the tiymce bar
	 * 
	 * @author Benjamin Niess
	 */
	function registerTheButton($buttons) {
		array_push($buttons, "|", "ips");
		return $buttons;
	}

	/*
	 * Load the custom js for the tinymce button
	 * 
	 * @author Benjamin Niess
	 */
	function addScriptTinymce($plugin_array) {
		$plugin_array['ips'] = IPS_URL . '/js/tinymce.js';
		return $plugin_array;
	}
}
?>