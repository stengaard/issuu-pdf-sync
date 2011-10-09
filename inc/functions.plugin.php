<?php 
function IPS_Install(){
	
	//enable default features on plugin activation
	$ips_options = get_option ( 'ips_options' );
	if ( empty( $ips_options ) )
		update_option( 'ips_options', array( 'allow_full_screen' => 1, 'auto_upload' => 1, 'add_ips_button' => 1, 'width' => 640, 'height' => 480, 'layout' => 1, 'autoflip' => 0, 'show_flip_buttons' => 0, 'bgcolor' => 'FFFFFF', 'flip_timelaps' => 6000 ) );
}

?>