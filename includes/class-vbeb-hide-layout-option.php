<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nasconde il campo "Style Layout" (layout_style) nella schermata di
 * modifica/creazione camera di VikBooking, perche' con questo plugin il
 * layout grafico e' gestito dal template Elementor (vedi
 * assets/js/vbeb-admin.js).
 *
 * VBEB free (revisione di wordpress.org): lo script e' un file caricato con
 * wp_enqueue_script(), solo nelle pagine di VikBooking.
 *
 * @return void
 */
function vbeb_hide_layout_style_field() {
	$pagina = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- sola lettura, per scegliere dove caricare lo script.

	if ( 'vikbooking' !== $pagina ) {
		return;
	}

	wp_enqueue_script( 'vbeb-admin', VBEB_URL . 'assets/js/vbeb-admin.js', array(), VBEB_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'vbeb_hide_layout_style_field' );
