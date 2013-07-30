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

		add_filter( 'attachment_fields_to_edit', array( &$this, 'insertIPSButton' ), 10, 2 );
		add_filter( 'media_send_to_editor', array( &$this, 'sendToEditor' ) );

		if ( $pagenow == 'media.php' )
			add_action( 'admin_head', array( &$this, 'editMediaJs' ), 50 );

		add_action( 'admin_init', array( &$this, 'checkJsPdfEdition' ) );
		add_action( 'admin_menu', array( &$this, 'addPluginMenu' ) );

		add_action( 'admin_init', array( &$this, 'init' ) );

		// Add the tinyMCE button
		add_action( 'admin_init', array( &$this, 'addButtons' ) );
		add_action( 'wp_ajax_ips_shortcodePrinter', array( &$this, 'wp_ajax_fct' ) );

	}

	function init() {
		wp_enqueue_script( 'jquery' );
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
		global $ips_options;
		if ( isset($_POST['save']) ) {
			check_admin_referer( 'ips-update-options' );
			$new_options = array();

			// Update existing
			foreach( (array) $_POST['ips'] as $key => $value ) {
				$new_options[$key] = stripslashes($value);
			}

			update_option( 'ips_options', $new_options );
			$ips_options = get_option ( 'ips_options' );
		}

		if (isset($_POST['save']) ) {
			echo '<div class="message updated"><p>'.__('Options updated!', 'ips').'</p></div>';
		}

		if ( $ips_options == false ) {
			$ips_options = array();
		}
		?>
		<div class="wrap" id="ips_options">
			<h2><?php _e('Issuu PDF Sync', 'ips'); ?></h2>

			<form method="post" action="#">
				<table class="form-table describe media-upload-form">

					<tr><td colspan="2"><h3><?php _e('Issuu configuration', 'ips'); ?></h3></td></tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[issuu_api_key]"><span class="alignleft"><?php _e('Issuu API Key', 'ips'); ?>
							<br /><a href="http://issuu.com/" target="_blank"><?php _e('Get an Issuu API Key', 'ips'); ?></span>
						</label></th>
						<td><input id="ips[issuu_api_key]" type="text" class="text" name="ips[issuu_api_key]" value="<?php echo isset( $ips_options['issuu_api_key'] ) ? esc_attr( $ips_options['issuu_api_key'] ) : '' ; ?>" /></a>
						</td>
					</tr>
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[issuu_secret_key]"><span class="alignleft"><?php _e('Issuu private key', 'ips'); ?></span></label></th>
						<td><input id="ips[issuu_secret_key]" type="text" name="ips[issuu_secret_key]" value="<?php echo isset( $ips_options['issuu_secret_key'] ) ? esc_attr( $ips_options['issuu_secret_key'] ) : ''; ?>" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[auto_upload]"><span class="alignleft"><?php _e('Automatically upload PDFs to Issuu', 'ips'); ?></span></label></th>
						<td><input id="ips[auto_upload]" type="checkbox" <?php checked( isset( $ips_options['auto_upload'] ) ? (int) $ips_options['auto_upload'] : '' , 1 ); ?> name="ips[auto_upload]" value="1" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[add_ips_button]"><span class="alignleft"><?php _e('Add the Issuu PDF Sync button to TinyMCE', 'ips'); ?></span></label></th>
						<td><input id="ips[add_ips_button]" type="checkbox" <?php checked( isset( $ips_options['add_ips_button'] ) ? (int) $ips_options['add_ips_button'] : '' , 1 ); ?> name="ips[add_ips_button]" value="1" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[access]"><span class="alignleft"><?php _e( 'Access', 'ips' ); ?></span></label></th>
						<td>
							<?php

							$access = 'public';
							if ( isset( $ips_options['access'] ) ) {
								$access = $ips_options['access'];
							}

							?>
							<select id="ips[access]" name="ips[access]">
								<option value="public" <?php selected( $access, 'public' ); ?>><?php _e( 'Public', 'ips' ); ?></option>
								<option value="private" <?php selected( $access, 'private' ); ?>><?php _e( 'Private', 'ips' ); ?></option>
							</select>
						</td>
					</tr>

					<tr><td colspan="2"><h3><?php _e('Default embed code configuration', 'ips'); ?></h3></td></tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[layout]"><span class="alignleft"><?php _e('Layout', 'ips'); ?></span></label></th>
						<td>
							<p style="height:25px;"><input id="ips-layout-two-up" type="radio" name="ips[layout]" value="1" <?php checked( isset( $ips_options['layout'] ) ? (int) $ips_options['layout'] : 0 , 1 ); ?> /> <label for="ips-layout-two-up"><?php _e('Two up', 'ips'); ?></label><img src="<?php echo IPS_URL . '/images/layout-double-pages.png' ; ?>" height="16" style="margin-left:5px;" /></p>
							<p><input type="radio" id="ips-layout-single" name="ips[layout]" value="2" <?php checked( isset( $ips_options['layout'] ) ? (int) $ips_options['layout'] : 0 , 2 ); ?> /> <label for="ips-layout-single"><?php _e('Single page', 'ips'); ?></label><img src="<?php echo IPS_URL . '/images/layout-single-page.png' ; ?>" height="16" style="margin-left:5px;" /></p>
						</td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[custom_layout]"><span class="alignleft"><?php _e('Custom layout', 'ips'); ?></span></label></th>
						<td>
							<p style="height:150px;"><input id="ips-custom-layout-default" type="radio" name="ips[custom_layout]" value="default" <?php checked( !isset( $ips_options['custom_layout'] ) || $ips_options['custom_layout'] == 'default' ? true : false, true ); ?> /> <label for="ips-custom-layout-default"><?php _e('Default', 'ips'); ?></label><img src="<?php echo IPS_URL . '/images/default.png' ; ?>" height="100" style="margin-left:15px;" /></p>
							<?php $skins = array( 'basicBlue', 'crayon', 'whiteMenu' );
							foreach ( $skins as $skin ) : ?>
								<p><input type="radio" id="ips-custom-layout-<?php echo $skin; ?>" name="ips[custom_layout]" value="<?php echo $skin; ?>" <?php checked( isset( $ips_options['custom_layout'] )  && $ips_options['custom_layout'] == $skin ? true : false , true ); ?> /> <label for="ips-custom-layout-<?php echo $skin; ?>"><?php echo $skin; ?></label><img src="<?php echo IPS_URL . '/images/sample_' . $skin . '.jpg' ; ?>" height="100" style="margin-left:5px;" /></p>
							<?php endforeach; ?>
						</td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[width]"><span class="alignleft"><?php _e('Width', 'ips'); ?></span></label></th>
						<td><input id="ips[width]" type="number" min="0" max="2000" name="ips[width]" value="<?php echo isset(  $ips_options['width'] ) ? (int)$ips_options['width'] : ''; ?>" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[height]"><span class="alignleft"><?php _e('Height', 'ips'); ?></span></label></th>
						<td><input id="ips[height]" type="number" min="0" max="2000" name="ips[height]" value="<?php echo isset(  $ips_options['height'] ) ? (int)$ips_options['height'] : ''; ?>" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[bgcolor]"><span class="alignleft"><?php _e('Background color', 'ips'); ?></span></label></th>
						<td># <input id="ips[bgcolor]" style="width:65px;" type="text" maxlength="6" name="ips[bgcolor]" value="<?php echo isset(  $ips_options['bgcolor'] ) ? esc_attr( $ips_options['bgcolor'] ) : ''; ?>" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[allow_full_screen]"><span class="alignleft"><?php _e('Allow full screen', 'ips'); ?></span></label></th>
						<td><input id="ips[allow_full_screen]" type="checkbox" <?php checked( isset( $ips_options['allow_full_screen'] ) ? (int) $ips_options['allow_full_screen'] : '' , 1 ); ?> name="ips[allow_full_screen]" value="1" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[show_flip_buttons]"><span class="alignleft"><?php _e('Always show flip buttons', 'ips'); ?></span></label></th>
						<td><input id="ips[show_flip_buttons]" type="checkbox" <?php checked( isset( $ips_options['show_flip_buttons'] ) ? (int) $ips_options['show_flip_buttons'] : '' , 1 ); ?> name="ips[show_flip_buttons]" value="1" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[autoflip]"><span class="alignleft"><?php _e('Auto flip', 'ips'); ?></span></label></th>
						<td>
							<input type="checkbox" id="ips[autoflip]" name="ips[autoflip]" value="1" <?php checked( isset( $ips_options['autoflip'] ) ? (int) $ips_options['autoflip'] : 0 , 1 ); ?> />
						</td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips[flip_timelaps]"><span class="alignleft"><?php _e('Flip time laps', 'ips'); ?></span></label></th>
						<td><input id="ips[flip_timelaps]" type="number" step="100" min="1000" max="200000" name="ips[flip_timelaps]" value="<?php echo isset(  $ips_options['flip_timelaps'] ) ? (int) $ips_options['flip_timelaps'] : '6000'; ?>" />
							<p class="description"><?php _e('(in miliseconds - default : 6000)', 'ips'); ?></p>
						</td>
					</tr>
						<td>
							<p class="submit">
								<?php wp_nonce_field( 'ips-update-options'); ?>
								<input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes', 'ips') ?>" />
							</p>
						</td>
					<tr>

					</tr>

					<tr><td colspan="2"><h3><?php _e('How to insert a PDF Flipbook ?', 'ips'); ?></h3></td></tr>

					<tr><td colspan="2">

						<ol>
							<li><?php _e('Make sure that the "Automatically upload PDFs to Issuu" box is checked or that you\'ve manually send the PDF to the Issuu website.', 'ips'); ?></li>
							<li>
								<?php _e('Click to Issuu button on the TinyMCE main bar', 'ips'); ?><br />
								<img src="<?php echo IPS_URL; ?>/screenshot-6.png" />
							</li>
							<li>
								<?php _e('Select your PDF file in the dropdown list and add some specific params if you need. Note that you can change the default settings in the settings page', 'ips'); ?><br />
								<img src="<?php echo IPS_URL; ?>/screenshot-7.png" width="510" style="padding: 10px;" />
							</li>
							<li>
								<?php _e('Click to the insert button and then the shortcode will be generated. You can easily cut and past this shortcode everywhere in your content', 'ips'); ?><br />
								<img src="<?php echo IPS_URL; ?>/screenshot-8.png" />
							</li>
						</ol>

					</td></tr>

					<tr><td colspan="2"><h3><?php _e('How to manually use the shortcode ? (advanced usage)', 'ips'); ?></h3></td></tr>
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

		$access = 'public';
		if ( isset( $ips_options['access'] ) ) {
			$access = $ips_options['access'];
		}

		// Parameters
		$parameters = array(
			'access'   => $access,
			'action'   => 'issuu.document.url_upload',
			'apiKey'   => $ips_options['issuu_api_key'],
			'format'   => 'json',
			'name'     => $post_data->post_name,
			'slurpUrl' => $post_data->guid,
			'title'    => sanitize_title( $post_data->post_title )
		);

		// Sort request parameters alphabetically (e.g. foo=1, bar=2, baz=3 sorts to bar=2, baz=3, foo=1)
		ksort( $parameters );

		// Prepare the MD5 signature for the Issuu Webservice
		$string = $ips_options['issuu_secret_key'];

		foreach ( $parameters as $key => $value ) {
			$string .= $key . $value;
		}

		$md5_signature = md5( $string );

		// Call the Webservice
		$parameters['signature'] = $md5_signature;

		$url_to_call = add_query_arg( $parameters, 'http://api.issuu.com/1_0' );

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
		$document = $response->rsp->_content->document;

		update_post_meta( $post_id, 'issuu_pdf_id', $document->documentId );
		update_post_meta( $post_id, 'issuu_pdf_username', $document->username );
		update_post_meta( $post_id, 'issuu_pdf_name', $document->name );

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

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			if ( !isset( $form_fields ) || empty( $form_fields ) || !isset( $attachment ) || empty( $attachment ) )
				return $form_fields;
		}

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
		$issuu_pdf_username = get_post_meta( $attachment->ID, 'issuu_pdf_username', true );
		$issuu_pdf_name = get_post_meta( $attachment->ID, 'issuu_pdf_name', true );
		$disable_auto_upload = get_post_meta( $attachment->ID, 'disable_auto_upload', true );

		$issuu_url = sprintf( 'http://issuu.com/%s/docs/%s', $issuu_pdf_username, $issuu_pdf_name );

		// Upload the PDF to Issuu if necessary and if the Auto upload feature is enabled
		if ( empty( $issuu_pdf_id ) && isset( $ips_options['auto_upload'] ) && $ips_options['auto_upload'] == 1 && $disable_auto_upload != 1)
			$issuu_pdf_id = $this->sendPDFToIssuu( $attachment->ID );

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			if ( empty( $issuu_pdf_id ) )
				return $form_fields;

			$form_fields["url"]["html"] .= "<button type=\"button\" class='button urlissuupdfsync issuu-pdf-" . $issuu_pdf_id . "' data-link-url=\"[pdf issuu_pdf_id=" . $issuu_pdf_id . "]\" title='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]'>" . _( 'Issuu PDF' ) . "</button>";
		} else {
			$form_fields['issuu_pdf_sync_id'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document ID', 'isp' ),
				'value'        => $issuu_pdf_id
			);

			$form_fields['issuu_pdf_username'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document Username', 'isp' ),
				'value'        => $issuu_pdf_username
			);

			$form_fields['issuu_pdf_name'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document Name', 'isp' ),
				'value'        => $issuu_pdf_name
			);

			$form_fields['issuu_pdf_url'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document URL', 'isp' ),
				'value'        => $issuu_url
			);

			$form_fields['issuu_pdf_sync_auto_upload'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Auto Upload', 'isp' ),
				'value'        => $disable_auto_upload
			);

			$form_fields['issuu_pdf_sync'] = array(
				'show_in_edit'   => true,
				'label'          => __( 'Issuu PDF Sync', 'isp' ),
				'value'          => $disable_auto_upload,
				'input'          => 'issuu_pdf_sync',
				'issuu_pdf_sync' => $this->get_sync_input( $attachment->ID, $issuu_pdf_id )
			);
		}

		return $form_fields;
	}

	function get_sync_input( $attachment_id, $issuu_document_id ) {
		$input = '';

		ob_start();

		include dirname( __FILE__ ) . '/sync-input.php';

		$input = ob_get_contents();

		ob_end_clean();

		return $input;
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
		global $ips_options, $wp_styles;
		if ( !empty($wp_styles->concat) ) {
			$dir = $wp_styles->text_direction;
			$ver = md5("$wp_styles->concat_version{$dir}");

			// Make the href for the style of box
			$href = $wp_styles->base_url . "/wp-admin/load-styles.php?c={$zip}&dir={$dir}&load=media&ver=$ver";
			echo "<link rel='stylesheet' href='" . esc_attr( $href ) . "' type='text/css' media='all' />\n";
		}

		?>
		<h3 class="media-title"><?php _e('Insert an Issuu PDF Flipbook', 'ips'); ?></h3>

		<form name="ips_shortcode_generator" id="ips_shortcode_generator">
			<div id="media-items">
				<div class="media-item media-blank">

					<table class="describe" style="width:100%;"><tbody>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_layout"><?php _e('Select a PDF file', 'ips'); ?></th>
							<td>
								<select name="issuu_pdf_id" id="issuu_pdf_id">
									<?php

									$pdf_files = new WP_Query( array(
										'post_type'      => 'attachment',
										'posts_per_page' => 100,
										'post_status'    => 'any',
										'meta_query'     => array(
											array(
												'key'     => 'issuu_pdf_id',
												'value'   => '',
												'compare' => '!='
											)
										)
									) );

									if ( $pdf_files->have_posts() ) while ( $pdf_files->have_posts() ) : $pdf_files->the_post(); ?>

										<option value="<?php echo get_post_meta( get_the_ID(), 'issuu_pdf_id', true ); ?>"><?php echo substr( get_the_title(), 0, 35 ); ?></option>

									<?php endwhile; ?>
								</select>
							</td>
						</tr>
						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_layout"><span class="alignleft"><?php _e('Layout', 'ips'); ?></span></label></th>
							<td>
								<p style="height:25px;"><input id="ips_layout" type="radio" name="ips_layout" value="1" <?php checked( isset( $ips_options['layout'] ) ? $ips_options['layout'] : 0 , 1 ); ?> /> <?php _e('Two up', 'ips'); ?><img src="<?php echo IPS_URL . '/images/layout-double-pages.png' ; ?>" height="16" style="margin-left:5px;" /></p>
								<p><input type="radio" name="ips_layout" value="2" <?php checked( isset( $ips_options['layout'] ) ? $ips_options['layout'] : 0 , 2 ); ?> /> <?php _e('Single page', 'ips'); ?><img src="<?php echo IPS_URL . '/images/layout-single-page.png' ; ?>" height="16" style="margin-left:5px;" /></p>
							</td>
						</tr>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_width"><span class="alignleft"><?php _e('Width', 'ips'); ?></span></label></th>
							<td><input id="ips_width" type="number" min="0" max="2000" name="ips_width" value="<?php echo isset(  $ips_options['width'] ) ? (int)$ips_options['width'] : ''; ?>" /> px</td>
						</tr>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_height"><span class="alignleft"><?php _e('Height', 'ips'); ?></span></label></th>
							<td><input id="ips_height" type="number" min="0" max="2000" name="ips_height" value="<?php echo isset(  $ips_options['height'] ) ? (int)$ips_options['height'] : ''; ?>" /> px</td>
						</tr>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_bgcolor"><span class="alignleft"><?php _e('Background color', 'ips'); ?></span></label></th>
							<td># <input id="ips_bgcolor" style="width:65px;" type="text" maxlength="6" name="ips_bgcolor" value="<?php echo isset(  $ips_options['bgcolor'] ) ? $ips_options['bgcolor'] : ''; ?>" /></td>
						</tr>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_allow_full_screen"><span class="alignleft"><?php _e('Allow full screen', 'ips'); ?></span></label></th>
							<td><input id="ips_allow_full_screen" name="ips_allow_full_screen_"  type="checkbox" <?php checked( isset( $ips_options['allow_full_screen'] ) ? $ips_options['allow_full_screen'] : '' , 1 ); ?> value="1" /></td>
						</tr>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_show_flip_buttons"><span class="alignleft"><?php _e('Always show flip buttons', 'ips'); ?></span></label></th>
							<td><input id="ips_show_flip_buttons" name="ips_show_flip_buttons" type="checkbox" <?php checked( isset( $ips_options['show_flip_buttons'] ) ? $ips_options['show_flip_buttons'] : '' , 1 ); ?> value="1" /></td>
						</tr>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_autoflip"><span class="alignleft"><?php _e('Auto flip', 'ips'); ?></span></label></th>
							<td>
								<input type="checkbox" id="ips_autoflip" name="ips_autoflip" value="1" <?php checked( isset( $ips_options['autoflip'] ) ? $ips_options['autoflip'] : 0 , 1 ); ?> />
							</td>
						</tr>

						<tr valign="top" class="field">
							<th class="label" scope="row"><label for="ips_flip_timelaps"><span class="alignleft"><?php _e('Flip time laps', 'ips'); ?></span></label></th>
							<td><input id="ips_flip_timelaps" type="number" step="100" min="1000" max="200000" name="ips_flip_timelaps" value="<?php echo isset(  $ips_options['flip_timelaps'] ) ? (int)$ips_options['flip_timelaps'] : '6000'; ?>" />
								<p class="description"><?php _e('(in miliseconds - default : 6000)', 'ips'); ?></p>
							</td>
						</tr>

						<tr valign="top" class="field">
							<td>
								<input name="insert_issuu_pdf" type="submit" class="button-primary" id="insert_issuu_pdf" tabindex="5" accesskey="p" value="<?php _e('Insert the PDF', 'ips') ?>">
							</td>
						</tr>

					</tbody></table>
				</div>
			</div>

		</form>
		<?php die();
	}

	/*
	 * Add buttons to the tiymce bar
	 *
	 * @author Benjamin Niess
	 */
	function addButtons() {
		global $ips_options;

		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return false;

		// Does the admin want to display the Issuu button ?
		if ( !isset( $ips_options['add_ips_button'] ) || (int)$ips_options['add_ips_button'] != 1 )
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
