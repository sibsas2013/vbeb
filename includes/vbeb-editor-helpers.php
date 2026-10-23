<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * VBEB free: funzioni comuni ai widget per l'anteprima nell'editor,
 * prese da includes/class-vbeb-operators-widgets.php della versione completa.
 */


/**
 * Gli stili di VikBooking, per un widget disegnato nell'editor.
 *
 * VBEB 7.74.0. Estratta dall'anteprima degli strumenti, dove era nata
 * fra la 7.72.1 e la 7.73.1, per poterla usare anche altrove.
 *
 * SERVONO DUE COSE, E NESSUNA DELLE DUE ARRIVA DA SOLA:
 *
 * - IL FOGLIO DI STILE. VikBooking lo carica quando disegna una sua
 *   pagina; nell'editor la pagina la disegna Elementor. Si collega il
 *   file vero sul server, non una copia delle sue regole: cosi'
 *   l'anteprima segue gli aggiornamenti del plugin.
 * - LE VARIABILI DEI COLORI. Le classi del "colore preferito" nel
 *   foglio non hanno colori propri: li leggono da variabili CSS che
 *   VikBooking dichiara a parte, con uno stile in linea generato dalle
 *   impostazioni della struttura. Senza, le regole restano valide ma
 *   puntano a valori inesistenti, e il colore ricade sul predefinito
 *   del browser - testo nero, pulsanti trasparenti.
 *
 * Si rigenera il solo blocco delle variabili, con la stessa
 * trasformazione del nome che usa VikBooking. Le regole restano le sue.
 *
 * @return string HTML da anteporre al widget, vuoto se non disponibile.
 */
function vbeb_stili_vikbooking_editor() {
	static $gia = false;

	// Una volta sola per pagina: con piu' widget nello stesso template,
	// ripetere il foglio e le variabili non servirebbe a nulla.
	if ( $gia && ! vbeb_editor_ripete() ) {
		return '';
	}

	$out = '';

	if ( defined( 'VBO_SITE_URI' ) ) {
		$out .= '<link rel="stylesheet" href="' . esc_url( VBO_SITE_URI . 'resources/vikbooking_styles.css' ) . '" />';
	}

	if ( class_exists( 'VikBooking' ) && method_exists( 'VikBooking', 'getPreferredColors' ) ) {
		$colori = VikBooking::getPreferredColors();

		if ( is_array( $colori ) && ! empty( $colori ) ) {
			$righe = array();

			foreach ( $colori as $chiave => $valore ) {
				if ( '' === $valore || null === $valore ) {
					continue;
				}

				$righe[] = '--vbo-pref-' . preg_replace( '/[^a-z0-9]/', '', strtolower( $chiave ) ) . ': ' . $valore . ';';
			}

			if ( ! empty( $righe ) ) {
				$out .= '<style>:root {' . implode( '', $righe ) . '}</style>';
			}
		}
	}

	if ( '' !== $out ) {
		$gia = true;
	}

	return $out;
}

/**
 * Le icone di VikBooking (Font Awesome 7), nell'anteprima degli strumenti.
 *
 * VBEB 7.176.0. Segnalato dall'utente con l'ispezione del front-end:
 * nello strumento "Gestione attivita'" l'icona del calendario nel
 * riquadro di "Iscriviti" si vede sul sito ma non nell'editor.
 *
 * LA CAUSA. VikBooking usa Font Awesome 7.2.0 e carica i suoi tre fogli -
 * fontawesome, solid e regular (site/helpers/icons.php, loadAssets()) -
 * quando disegna una sua pagina. Nell'editor la pagina la disegna
 * Elementor e quei fogli non arrivano: le icone dello strumento restano
 * affidate al Font Awesome 5 che Elementor carica per conto suo
 * (assets/lib/font-awesome/css/all.min.css). Le altre icone si vedono
 * perche' i loro nomi - "fa-tasks", "fa-user-check", "fa-user-plus" -
 * esistono anche nella 5. Quella del calendario no: VikBooking la
 * traduce in "far fa-calendar-days", un nome nato con la 6, che nella 5
 * non c'e'. Nessuna regola le assegna un simbolo, e resta il vuoto.
 *
 * Sul sito, dall'ispezione dell'utente, i due Font Awesome convivono: il
 * 7 arriva dopo e ha l'ultima parola. Qui si fa lo stesso, collegando i
 * file veri sul server nell'ordine di VikBooking; stampati dentro il
 * widget, arrivano dopo i fogli di Elementor, come sul sito.
 *
 * SOLO NELL'ANTEPRIMA DEGLI STRUMENTI OPERATORE, dove il problema si e'
 * presentato e dove il sito carica gli stessi fogli; non nel foglio comune
 * a tutti i widget. Una volta sola per pagina.
 *
 * @return string HTML da anteporre al widget.
 */
function vbeb_icone_vikbooking_editor() {
	static $gia = false;

	if ( ( $gia && ! vbeb_editor_ripete() ) || ! defined( 'VBO_SITE_URI' ) ) {
		return '';
	}
	$gia = true;

	$fogli = array( 'fontawesome.min.css', 'solid.min.css', 'regular.min.css' );

	if ( class_exists( 'VikBookingIcons' ) && ! empty( VikBookingIcons::$fa_default_styles ) && is_array( VikBookingIcons::$fa_default_styles ) ) {
		$fogli = VikBookingIcons::$fa_default_styles;
	}

	$out = '';

	foreach ( $fogli as $foglio ) {
		$out .= '<link rel="stylesheet" href="' . esc_url( VBO_SITE_URI . 'resources/' . $foglio ) . '" />';
	}

	return $out;
}
