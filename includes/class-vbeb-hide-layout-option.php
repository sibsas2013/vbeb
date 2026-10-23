<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nasconde il campo "Style Layout" (layout_style) nella schermata di
 * modifica/creazione camera di VikBooking, perche' con questo plugin il
 * layout grafico e' gestito dal template Elementor, non serve piu' che
 * l'utente lo cambi da li'. Sempre attiva (non piu' configurabile dal
 * pannello Impostazioni, su richiesta esplicita dell'utente).
 *
 * Approccio scelto DELIBERATAMENTE non invasivo: nessun file di VikBooking
 * viene toccato (sopravvive quindi a qualsiasi aggiornamento del plugin
 * VikBooking). Nascondiamo il campo via JS/CSS lato browser, il valore
 * salvato in precedenza resta comunque quello che decide quale override
 * (classic/listing) viene attivato.
 */
function vbeb_hide_layout_style_field() {
	if ( ! is_admin() ) {
		return;
	}
	?>
	<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		// Cerca qualunque campo il cui "name" contenga "layout_style"
		// (select, radio, ecc. - non assumiamo il tag esatto).
		var fields = document.querySelectorAll('[name*="layout_style"]');
		if (!fields.length) {
			return; // Non siamo nella pagina giusta: nessun effetto, nessun rischio.
		}
		fields.forEach(function (field) {
			// Proviamo a nascondere l'intera riga (etichetta + campo),
			// risalendo al primo contenitore "di riga" plausibile.
			var row = field.closest(
				'tr, .form-group, .control-group, .vbo-param-container, .vbo-config-row, li'
			);
			if (row) {
				row.style.display = 'none';
			} else {
				// Fallback: nascondiamo almeno il campo stesso.
				field.style.display = 'none';
			}
		});
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'vbeb_hide_layout_style_field' );
