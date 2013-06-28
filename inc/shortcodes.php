<?php

/*
 * The ISSUU PDF shortcode. Usage doc on the admin pannel
 */
add_shortcode( 'pdf','issuu_pdf_embeder' );

function issuu_pdf_embeder( $atts, $content = null ) {
	global $ips_options;
	
	if ( isset( $ips_options['layout'] ) && $ips_options['layout'] == 2 )
		$layout = "presentation";
	else
		$layout = "browsing";
	
	extract( shortcode_atts( array( 
		'issuu_pdf_id'    => null, 
		'width'           => $ips_options['width'], 
		'height'          => $ips_options['height'], 
		'layout'          => $layout,
		'backgroundColor' => $ips_options['bgcolor'],
		'autoFlipTime'    => $ips_options['flip_timelaps'],
		'autoFlip'        => ( isset( $ips_options['autoflip'] ) && $ips_options['autoflip'] == 1 ) ? 'true' : 'false', 
		'showFlipBtn'     => ( isset( $ips_options['show_flip_buttons'] ) && $ips_options['show_flip_buttons'] == 1 ) ? 'true' : 'false', 
		'allowfullscreen' => ( isset( $ips_options['allow_full_screen'] ) && $ips_options['allow_full_screen'] == 1 ) ? 'true' : 'false',
		'customLayout'    => ( isset( $ips_options['custom_layout'] ) && $ips_options['custom_layout'] != 'default' ) ? $ips_options['custom_layout'] : false 
	), $atts ) ); 
	
	// Check if the required param is set
	if ( empty( $issuu_pdf_id ) ) {
		return false;
	}
	
	// Parameters
	$parameters = array(
		'mode'            => 'embed',
		'backgroundColor' => empty( $backgroundColor ) ? false : $backgroundColor,
		'viewMode'        => $layout,
		'showFlipBtn'     => $showFlipBtn,
		'documentId'      => $issuu_pdf_id,
		'autoFlipTime'    => $autoFlipTime,
		'autoFlip'        => $autoFlip,
		'loadingInfoText' => __( 'Loading...', 'ips' )
	);

	if ( isset( $customLayout ) && ! empty( $customLayout ) ) {
		if ( is_dir( TEMPLATEPATH . '/issuu-skins/' . $customLayout ) ) {
			$parameters['layout' ] = get_bloginfo( 'template_directory' ) . '/issuu-skins/' . $customLayout . '/layout.xml';

			$layout_embed = ' layout="' . ( get_bloginfo( 'template_directory' ) . '/issuu-skins/' . $customLayout . '/layout.xml' ) . '"';
		} else {
			$parameters['layout' ] = IPS_URL . '/issuu-skins/' . $customLayout . '/layout.xml';

			$layout_embed = ' layout="' . ( IPS_URL . '/issuu-skins/' . $customLayout . '/layout.xml' ) . '"';
		}
	} else {
		$layout_url   = '';
		$layout_embed = '';
	}

	$issuu_swf_url = 'http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf';	
	$issuu_swf_url = add_query_arg( $parameters, $issuu_swf_url );

	$flashvars = build_query( $parameters );

	// Start to get the content to return it at the end
	ob_start(); ?>
	
		<div>
			<object style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px" >
				<param name="movie" value="<?php echo esc_attr( $issuu_swf_url ); ?>" />
				<param name="allowfullscreen" value="<?php echo $allowfullscreen; ?>" />
				<param name="wmode" value="transparent" />
				<param name="menu" value="false" />
				<embed src="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf" <?php echo $layout_embed; ?> type="application/x-shockwave-flash" allowfullscreen="<?php echo $allowfullscreen; ?>" wmode="transparent" menu="false" style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px" flashvars="<?php echo esc_attr( $flashvars ); ?>" />
			</object>
		</div>
		
		<?php do_action( 'after-ips-shortcode', $issuu_pdf_id ); ?>
	 
	<?php

	// Return the shortcode content
	$content = ob_get_contents();
	ob_end_clean();
	
	return $content;
}
