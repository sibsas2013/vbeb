<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2025 e4j - E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 *
 * ============================================================
 * MODIFICATO da VikBooking Elementor Bridge.
 * Senza un template Elementor selezionato nelle impostazioni del
 * plugin si disegna il file originale di VikBooking, invariato
 * (VBEB 7.348.0). Con un template Elementor, il template prende il
 * posto di nome, galleria foto e descrizione; servizi, pulsante
 * "Richiedi informazioni", calendario disponibilita', prezzo e modulo
 * di prenotazione non vengono stampati qui ma dai widget dedicati
 * inseriti nel template.
 * ============================================================
 */

defined('ABSPATH') or die('No script kiddies please!');

/*
 * VBEB free: il file contiene solo il percorso con il template Elementor.
 * Senza template si disegna il file originale di VikBooking (vedi sotto);
 * le parti di quel file copiate qui per il caso "nessun template" (nome,
 * galleria, descrizione) non venivano mai usate e sono state tolte.
 */


// === VBEB: verifica se e' stato selezionato un template Elementor per il layout "listing" ===
$vbeb_template_id = (int) get_option('vbeb_template_listing', 0);
$vbeb_use_custom  = false;

if ($vbeb_template_id > 0 && did_action('elementor/loaded') && class_exists('VBEB_Room_Context') && class_exists('\Elementor\Plugin')) {
	// VBEB: non basta che l'ID sia impostato - il template potrebbe essere
	// stato eliminato definitivamente dopo il salvataggio dell'impostazione.
	// Controlliamo che il post esista ancora davvero e sia pubblicato,
	// altrimenti la pagina camera resterebbe vuota (Elementor non genera
	// un errore per un post inesistente, restituisce semplicemente nulla).
	$vbeb_template_post = get_post($vbeb_template_id);
	if ($vbeb_template_post && 'publish' === $vbeb_template_post->post_status && 'elementor_library' === $vbeb_template_post->post_type) {
		$vbeb_use_custom = true;
		// VBEB free: con Polylang o WPML, la copia del template nella lingua
		// della pagina (se esiste ed e' pubblicata). L'impostazione salvata
		// resta quella dell'originale.
		if (function_exists('vbeb_template_lingua')) {
			$vbeb_template_id = vbeb_template_lingua($vbeb_template_id);
		}
	} else {
		// Il template salvato non esiste piu': ripuliamo subito
		// l'impostazione, cosi' anche il pannello Impostazioni torna a
		// riflettere davvero "Usa il Template originale di VikBooking".
		update_option('vbeb_template_listing', 0);
	}
}

// === VBEB 7.348.0: template originale di VikBooking ===
// Senza un template Elementor la pagina camera deve essere identica a
// quella di VikBooking: si disegna direttamente il file originale di
// VikBooking, con lo stesso $this, e resta identico anche dopo un suo
// aggiornamento.
if (!$vbeb_use_custom) {
	$vbeb_originale = defined('VBO_SITE_PATH') ? VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'roomdetails' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default_listing.php' : '';
	if ('' !== $vbeb_originale && is_file($vbeb_originale)) {
		include $vbeb_originale;
	}
	return;
}

// Il contesto della camera, letto dai widget VBEB del template.
VBEB_Room_Context::set_values(
	array(
		'room'   => $this->room,
		'vbo_tn' => $this->vbo_tn,
	)
);

// VBEB: l'HTML vero di calendario disponibilita', modulo di prenotazione,
// pulsante "Richiedi informazioni" e mappa, generato da VikBooking con il
// suo $this (accesso alle prenotazioni esistenti, logica di invio): lo
// ristampano i widget dedicati del template, quindi qui non si stampa.
VBEB_Room_Context::set_values(
	array(
		'avcalendars_html' => $this->loadTemplate('avcalendars'),
		'bookingform_html' => $this->loadTemplate('bookingform'),
		'reqinfo_html'     => $this->loadTemplate('reqinfo'),
		'geomap_html'      => $this->loadTemplate('geomap'),
	)
);

// VBEB 7.641.0: anche gli stili degli elementi v4 (Flexbox, ecc.) del template.
if (function_exists('vbeb_stili_v4_template')) {
	vbeb_stili_v4_template($vbeb_template_id);
}
?>
<div class="vbrdetboxtop vbo-listing-details-wrap">
<?php
// HTML del template generato da Elementor, che ne cura gia' l'escape.
echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($vbeb_template_id, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenuto del template generato da Elementor.
?>
</div>
