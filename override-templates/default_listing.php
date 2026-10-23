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

// JSON-decode room parameters
$rparams = json_decode($this->room['params'], true);

$currencysymb = VikBooking::getCurrencySymb();

// photo gallery
$gallery_data = [];
if (!empty($this->room['moreimgs'])) {
	$moreimages = explode(';;', $this->room['moreimgs']);
	$imgcaptions = json_decode($this->room['imgcaptions'], true);
	$usecaptions = is_array($imgcaptions);
	foreach ($moreimages as $iind => $mimg) {
		if (empty($mimg)) {
			continue;
		}
		$img_alt = $usecaptions && !empty($imgcaptions[$iind]) ? $imgcaptions[$iind] : substr($mimg, 0, strpos($mimg, '.'));
		array_push($gallery_data, [
			'big' => VBO_SITE_URI . 'resources/uploads/big_' . $mimg,
			'thumb' => VBO_SITE_URI . 'resources/uploads/thumb_' . $mimg,
			'alt' => $img_alt,
			'caption' => $usecaptions && !empty($imgcaptions[$iind]) ? $imgcaptions[$iind] : "",
		]);
	}
}

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
// quella di VikBooking. Fino alla 7.347.0 non lo era: servizi, pulsante
// "Richiedi informazioni", calendario disponibilita', prezzo, modulo di
// prenotazione e i loro contenitori erano tolti da questo file sempre (li
// mettono i widget nel template), anche quando il template non c'era
// (segnalato dall'utente). Ora, in quel caso, si disegna direttamente il
// file originale di VikBooking, con lo stesso $this: resta identico anche
// dopo un aggiornamento di VikBooking. Il resto di questo file vale solo
// con un template Elementor.
if (!$vbeb_use_custom && defined('VBO_SITE_PATH')) {
	$vbeb_originale = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'roomdetails' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default_listing.php';
	if (is_file($vbeb_originale)) {
		include $vbeb_originale;
		return;
	}
}

if ($vbeb_use_custom) {
	// Popoliamo subito il contesto: viene letto sia dal blocco nome/galleria
	// che dal blocco descrizione qui sotto (stesso rendering, chiamato una sola volta).
	VBEB_Room_Context::$room   = $this->room;
	VBEB_Room_Context::$vbo_tn = $this->vbo_tn;
}

// VBEB: catturiamo l'HTML reale del calendario disponibilita' (stessa
// funzione originale, con accesso alle prenotazioni esistenti) per
// renderlo disponibile al widget "VikBooking Calendario Disponibilita'".
// Non viene piu' stampato piu' sotto per evitare la doppia visualizzazione:
// se non inserisci quel widget nel template, il calendario non comparira'.
if (class_exists('VBEB_Room_Context')) {
	VBEB_Room_Context::$avcalendars_html = $this->loadTemplate('avcalendars');
	VBEB_Room_Context::$bookingform_html = $this->loadTemplate('bookingform');
	VBEB_Room_Context::$reqinfo_html     = $this->loadTemplate('reqinfo');
	// VBEB 7.592.0: anche la mappa ("Dove sarai"), per il widget "VBEB
	// Camera - Mappa"; non si stampa piu' in fondo alla pagina.
	VBEB_Room_Context::$geomap_html      = $this->loadTemplate('geomap');
}

?>

<div class="vbrdetboxtop vbo-listing-details-wrap">

<?php if ($vbeb_use_custom) : ?>

	<?php
	// === VBEB: rendering del template Elementor al posto di nome/galleria/descrizione ===
	// VBEB 7.641.0: anche gli stili degli elementi v4 (Flexbox, ecc.) del template.
	if (function_exists('vbeb_stili_v4_template')) {
		vbeb_stili_v4_template($vbeb_template_id);
	}
	echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($vbeb_template_id, true);
	?>

<?php else : ?>

	<div class="vblistroomnamediv">
		<h3><?php echo $this->room['name']; ?></h3>
		<span class="vblistroomcat"><?php echo VikBooking::sayCategory($this->room['idcat'], $this->vbo_tn); ?></span>
	</div>

	<div class="vbroomimgdesc">
	<?php
	if (!empty($this->room['img'])) {
		?>
		<div class="vikfx-gallery-container vikfx-roomdetails-gallery-container">
			<div class="vikfx-gallery-fade-container">
				<img src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $this->room['img']; ?>" alt="<?php echo htmlspecialchars($this->room['name']); ?>" class="vikfx-gallery-image vblistimg"/>
			<?php
			if ($gallery_data) {
				?>
				<div class="vikfx-gallery-navigation-controls">
					<div class="vikfx-gallery-navigation-controls-prevnext">
						<a href="javascript: void(0);" class="vikfx-gallery-previous-image"><?php VikBookingIcons::e('chevron-left'); ?></a>
						<a href="javascript: void(0);" class="vikfx-gallery-next-image"><?php VikBookingIcons::e('chevron-right'); ?></a>
					</div>
				</div>
				<?php
			}
			?>
			</div>
		<?php
		if ($gallery_data) {
			?>
			<div class="vikfx-gallery">
			<?php
			$printed = 0;
			$lim_photos = 4;
			foreach ($gallery_data as $mimg) {
				?>
				<a href="<?php echo $mimg['big']; ?>" target="_blank" style="<?php echo ++$printed > $lim_photos ? 'display: none;' : ''; ?>">
					<img src="<?php echo $mimg['thumb']; ?>" alt="<?php echo $this->escape($mimg['alt']); ?>" title="<?php echo $this->escape($mimg['caption']); ?>"/>
				</a>
				<?php
			}

			if (count($gallery_data) > $lim_photos) {
				?>
				<div class="vbo-listing-details-allphotos">
					<a href="JavaScript: void(0);" class="vikfx-gallery-open"><?php VikBookingIcons::e('images'); ?> <?php echo JText::translate('VBO_ALL_PHOTOS'); ?></a>
				</div>
				<?php
			}
			?>
			</div>
			<?php
		}
		?>
		</div>
	<?php
	}
	?>
	</div>

<?php endif; // === /VBEB conditional (nome + galleria) === ?>

	<?php if (!$vbeb_use_custom) : ?>
		<?php
		// === VBEB: testo descrizione, sostituito dal template Elementor se selezionato (vedi sopra) ===
		// description
		if (VBOPlatformDetection::isWordPress()) {
			/**
			 * @wponly 	we try to parse any shortcode inside the HTML description of the room
			 */
			echo do_shortcode(wpautop($this->room['info']));
		} else {
			//BEGIN: Joomla Content Plugins Rendering
			JPluginHelper::importPlugin('content');
			$myItem = JTable::getInstance('content');
			$myItem->text = $this->room['info'];
			$objparams = [];
			if (class_exists('JEventDispatcher')) {
				$dispatcher = JEventDispatcher::getInstance();
				$dispatcher->trigger('onContentPrepare', ['com_vikbooking.roomdetails', &$myItem, &$objparams, 0]);
			} else {
				/**
				 * @joomla4only
				 */
				$dispatcher = JFactory::getApplication();
				if (method_exists($dispatcher, 'triggerEvent')) {
					$dispatcher->triggerEvent('onContentPrepare', ['com_vikbooking.roomdetails', &$myItem, &$objparams, 0]);
				}
			}
			$this->room['info'] = $myItem->text;
			//END: Joomla Content Plugins Rendering
			echo $this->room['info'];
		}
		?>
	<?php endif; // === /VBEB conditional (descrizione) === ?>

	<?php
	// VBEB: servizi/amenities e bottone "Richiedi Informazioni" rimossi
	// da qui intenzionalmente - gestiti dai widget Elementor dedicati
	// inseriti nel template, per evitare la doppia visualizzazione.
	// VBEB: i div "vbo-rdet-descprice-block" e "vbo-rdet-desc-cont" sono
	// stati rimossi intenzionalmente (erano solo contenitori vuoti senza
	// stile proprio, lasciati in pagina inutilmente). Il calendario
	// stagionale sottostante resta invariato.
	?>

	<?php
	// VBEB: i div "vbo-roomdet-calscontainer" e "vbo-roomdet-calscontainer-inner"
	// sono stati rimossi intenzionalmente (senza stile CSS proprio,
	// restavano in pagina come markup superfluo). Il calendario stagionale
	// sottostante resta invariato.

	// VBEB 7.367.0: calendario delle stagioni non piu' stampato qui:
	// lo mostra il widget "VBEB Camera - Prezzi stagionali" inserito nel
	// template (HTML catturato sopra), come il calendario disponibilita'.

	// VBEB: calendario disponibilita' rimosso da qui intenzionalmente -
	// gestito dal widget Elementor "VikBooking Calendario Disponibilita'"
	// inserito nel template, per evitare la doppia visualizzazione.
	?>

	<?php
	// VBEB: blocco prezzo e form di prenotazione rimossi da qui
	// intenzionalmente - gestiti dai widget Elementor "VikBooking Prezzo
	// Camera" e "VikBooking Form Prenotazione" inseriti nel template, per
	// evitare la doppia visualizzazione.
	?>

	<?php

	// VBEB 7.592.0: mappa non piu' stampata qui: la mostra il widget
	// "VBEB Camera - Mappa" inserito nel template (HTML catturato sopra).
?>

</div>
