/**
 * VBEB - Bridge for VikBooking and Elementor
 * Bacheca: nasconde il campo "Style Layout" (layout_style) nella schermata
 * di modifica e creazione camera di VikBooking.
 *
 * Con VBEB il layout grafico della pagina camera lo decide il template
 * Elementor: il campo non serve piu'. Nessun file di VikBooking viene
 * toccato; il valore salvato resta quello che decide quale layout
 * (classic/listing) usa VikBooking.
 *
 * VBEB free (revisione di wordpress.org): prima questo codice era stampato
 * in un blocco <script>; ora e' in questo file, caricato con
 * wp_enqueue_script() solo nelle pagine di VikBooking.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	'use strict';

	// Qualunque campo il cui "name" contenga "layout_style".
	var fields = document.querySelectorAll( '[name*="layout_style"]' );
	if ( ! fields.length ) {
		return;
	}

	fields.forEach( function ( field ) {
		// Si nasconde l'intera riga (etichetta e campo), risalendo al primo
		// contenitore "di riga" plausibile; altrimenti il solo campo.
		var row = field.closest( 'tr, .form-group, .control-group, .vbo-param-container, .vbo-config-row, li' );
		if ( row ) {
			row.style.display = 'none';
		} else {
			field.style.display = 'none';
		}
	} );
} );
