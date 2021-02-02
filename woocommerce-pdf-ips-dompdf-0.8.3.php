<?php
/**
 * Plugin Name: WooCommerce PDF Invoices & Packing Slips dompdf 0.8.3
 * Plugin URI: http://www.wpovernight.com
 * Description: Uses the 0.8.3 release of dompdf instead of the latest version bundled with the general release
 * Version: 1.0.0
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'WCPDF_Custom_PDF_Maker_dompdf_0_8_3' ) ) :

class WCPDF_Custom_PDF_Maker_dompdf_0_8_3 {
	public $html;
	public $settings;

	public function __construct( $html, $settings = array() ) {
		$this->html = $html;

		$default_settings = array(
			'paper_size'		=> 'A4',
			'paper_orientation'	=> 'portrait',
			'font_subsetting'	=> false,
		);
		$this->settings = $settings + $default_settings;
	}

	public function output() {
		if ( empty( $this->html ) ) {
			return;
		}
		
		require_once __DIR__ . '/vendor/autoload.php';

		// set options
		$options = new \Dompdf\Options( apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'defaultFont'				=> 'dejavu sans',
			'tempDir'					=> WPO_WCPDF()->main->get_tmp_path('dompdf'),
			'logOutputFile'				=> WPO_WCPDF()->main->get_tmp_path('dompdf') . "/log.htm",
			'fontDir'					=> WPO_WCPDF()->main->get_tmp_path('fonts'),
			'fontCache'					=> WPO_WCPDF()->main->get_tmp_path('fonts'),
			'isRemoteEnabled'			=> true,
			'isFontSubsettingEnabled'	=> $this->settings['font_subsetting'],
			// HTML5 parser requires iconv
			'isHtml5ParserEnabled'		=> ( isset(WPO_WCPDF()->settings->debug_settings['use_html5_parser']) && extension_loaded('iconv') ) ? true : false,
		) ) );

		// instantiate and use the dompdf class
		$dompdf = new \Dompdf\Dompdf( $options );
		$dompdf->loadHtml( $this->html );
		$dompdf->setPaper( $this->settings['paper_size'], $this->settings['paper_orientation'] );
		$dompdf = apply_filters( 'wpo_wcpdf_before_dompdf_render', $dompdf, $this->html );
		$dompdf->render();
		$dompdf = apply_filters( 'wpo_wcpdf_after_dompdf_render', $dompdf, $this->html );

		return $dompdf->output();
	}
}

endif; // class_exists

add_filter( 'wpo_wcpdf_pdf_maker', function ( $class ) {
	$class = 'WCPDF_Custom_PDF_Maker_dompdf_0_8_3';
	return $class;
});

if ( version_compare( PHP_VERSION, '7.1', '>=' ) ) {
	add_action( 'admin_notices', function() {
		$message = sprintf(
			'<div class="notice notice-warning"><p>%s</p><p><a href="%s">%s</a></p></div>',
			__( 'Since your PHP version is equal to 7.1 or higher, you no longer need the DOMPDF 0.8.3 add-on.', 'woocommerce-pdf-invoices-packing-slips' ),
			esc_url_raw( network_admin_url( 'plugins.php?s=dompdf+0.8.3' ) ),
			__( 'You can safely remove it here', 'woocommerce-pdf-invoices-packing-slips' )
		);
	
		echo $message;
	} );
}