<?php
/**
 * Stampa filtrata dell'HTML generato da VikBooking.
 *
 * VBEB free, revisione di wordpress.org ("Proper escaping of outputs"): i
 * widget della camera ristampano l'HTML che VikBooking genera per la pagina
 * camera (modulo di prenotazione, calendario delle disponibilita', modulo
 * informazioni, mappa, descrizioni, prezzo, icone dei servizi). Prima di
 * stamparlo VBEB lo passa da wp_kses() con un elenco di tag e attributi
 * ammessi (quelli dei contenuti di WordPress piu' i campi dei moduli).
 * wp_kses() togliera' gli script: VBEB li separa prima e li stampa con le
 * funzioni di WordPress (wp_print_inline_script_tag() e
 * wp_print_script_tag()), dopo il markup, nello stesso ordine.
 *
 * @package VBEB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Segnaposto del template Elementor dentro l'HTML di VikBooking (vedi
 * VBEB_Reqinfo_Widget::con_template()).
 */
define( 'VBEB_INSERTO_POPUP', 'VBEB_INSERTO_POPUP_7F3A9C' );

/**
 * Tag e attributi ammessi nell'HTML di VikBooking.
 *
 * @return array
 */
function vbeb_kses_tag_vikbooking() {
	static $tag = null;

	if ( null !== $tag ) {
		return $tag;
	}

	$tag = wp_kses_allowed_html( 'post' );

	// Campi dei moduli (prenotazione, informazioni, calendario).
	$moduli = array(
		'form'     => array(),
		'input'    => array(),
		'select'   => array(),
		'option'   => array(),
		'optgroup' => array(),
		'textarea' => array(),
		'button'   => array(),
		'label'    => array(),
		'fieldset' => array(),
		'legend'   => array(),
		'iframe'   => array(),
		'noscript' => array(),
	);
	foreach ( $moduli as $nome => $attributi ) {
		if ( ! isset( $tag[ $nome ] ) ) {
			$tag[ $nome ] = $attributi;
		}
	}

	// Icone SVG (servizi delle camere).
	$svg = array( 'xmlns' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'fill-rule' => true, 'clip-rule' => true, 'd' => true, 'cx' => true, 'cy' => true, 'r' => true, 'rx' => true, 'ry' => true, 'x' => true, 'y' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'points' => true, 'transform' => true, 'opacity' => true, 'focusable' => true, 'preserveaspectratio' => true );
	foreach ( array( 'svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon', 'title', 'defs', 'use' ) as $nome ) {
		if ( ! isset( $tag[ $nome ] ) ) {
			$tag[ $nome ] = $svg;
		}
	}

	// Attributi usati da VikBooking, ammessi su tutti i tag.
	$comuni = array(
		'class'           => true,
		'id'              => true,
		'style'           => true,
		'title'           => true,
		'role'            => true,
		'tabindex'        => true,
		'hidden'          => true,
		'lang'            => true,
		'dir'             => true,
		'data-*'          => true,
		'aria-label'      => true,
		'aria-hidden'     => true,
		'aria-expanded'   => true,
		'aria-controls'   => true,
		'aria-describedby' => true,
		'aria-labelledby' => true,
		'name'            => true,
		'value'           => true,
		'type'            => true,
		'checked'         => true,
		'selected'        => true,
		'disabled'        => true,
		'readonly'        => true,
		'required'        => true,
		'multiple'        => true,
		'placeholder'     => true,
		'min'             => true,
		'max'             => true,
		'step'            => true,
		'maxlength'       => true,
		'size'            => true,
		'rows'            => true,
		'cols'            => true,
		'autocomplete'    => true,
		'for'             => true,
		'action'          => true,
		'method'          => true,
		'enctype'         => true,
		'novalidate'      => true,
		'accept-charset'  => true,
		'target'          => true,
		'rel'             => true,
		'href'            => true,
		'src'             => true,
		'srcset'          => true,
		'sizes'           => true,
		'loading'         => true,
		'alt'             => true,
		'width'           => true,
		'height'          => true,
		'colspan'         => true,
		'rowspan'         => true,
		'frameborder'     => true,
		'allow'           => true,
		'allowfullscreen' => true,
		// Gestori degli eventi scritti da VikBooking nel suo HTML (per
		// esempio onsubmit del modulo di prenotazione, onclick del modulo
		// informazioni, onchange del calendario).
		'onclick'         => true,
		'onchange'        => true,
		'onfocus'         => true,
		'onblur'          => true,
		'onsubmit'        => true,
		'oninput'         => true,
		'onkeyup'         => true,
	);
	foreach ( $tag as $nome => $attributi ) {
		$tag[ $nome ] = array_merge( is_array( $attributi ) ? $attributi : array(), $comuni );
	}

	return $tag;
}

/**
 * Protocolli ammessi nei link dell'HTML di VikBooking: quelli di WordPress
 * piu' "javascript", usato da VikBooking nei link che aprono e chiudono il
 * modulo informazioni (href="javascript: void(0);").
 *
 * @return array
 */
function vbeb_kses_protocolli_vikbooking() {
	return array_merge( wp_allowed_protocols(), array( 'javascript' ) );
}

/**
 * Separa gli script dal markup.
 *
 * @param string $html HTML di VikBooking.
 * @return array { 'markup' => string, 'script' => array di array( 'attr' => array, 'js' => string ), 'stili' => array di string }
 */
function vbeb_separa_script( $html ) {
	$script = array();

	$markup = preg_replace_callback(
		'#<script\b([^>]*)>(.*?)</script\s*>#is',
		function ( $m ) use ( &$script ) {
			$attr = array();
			if ( preg_match_all( '#([a-zA-Z_:][-a-zA-Z0-9_:.]*)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+)))?#', $m[1], $coppie, PREG_SET_ORDER ) ) {
				foreach ( $coppie as $c ) {
					$nome = strtolower( $c[1] );
					if ( isset( $c[4] ) && '' !== $c[4] ) {
						$valore = $c[4];
					} elseif ( isset( $c[3] ) && '' !== $c[3] ) {
						$valore = $c[3];
					} elseif ( isset( $c[2] ) ) {
						$valore = $c[2];
					} else {
						$valore = true;
					}
					$attr[ $nome ] = is_string( $valore ) ? html_entity_decode( $valore, ENT_QUOTES ) : $valore;
				}
			}
			// Il tipo "text/javascript" e' quello predefinito.
			if ( isset( $attr['type'] ) && 'text/javascript' === strtolower( (string) $attr['type'] ) ) {
				unset( $attr['type'] );
			}
			$script[] = array(
				'attr' => $attr,
				'js'   => $m[2],
			);
			return '';
		},
		(string) $html
	);

	// Eventuali blocchi <style> (per esempio dentro una descrizione):
	// wp_kses() ne lascerebbe il testo visibile.
	$stili  = array();
	$markup = preg_replace_callback(
		'#<style\b[^>]*>(.*?)</style\s*>#is',
		function ( $m ) use ( &$stili ) {
			$stili[] = $m[1];
			return '';
		},
		(string) $markup
	);

	return array(
		'markup' => (string) $markup,
		'script' => $script,
		'stili'  => $stili,
	);
}

/**
 * Stampa l'HTML di VikBooking: il markup filtrato con wp_kses(), poi i suoi
 * script con le funzioni di WordPress.
 *
 * @param string $html    HTML di VikBooking.
 * @param string $inserto HTML di un template Elementor da stampare al posto
 *                        del segnaposto VBEB_INSERTO_POPUP (facoltativo).
 * @return void
 */
function vbeb_stampa_html_vikbooking( $html, $inserto = '' ) {
	$parti = vbeb_separa_script( $html );

	if ( '' !== $inserto && false !== strpos( $parti['markup'], VBEB_INSERTO_POPUP ) ) {
		$pezzi = explode( VBEB_INSERTO_POPUP, $parti['markup'], 2 );
		echo wp_kses( $pezzi[0], vbeb_kses_tag_vikbooking(), vbeb_kses_protocolli_vikbooking() );
		// Contenuto di un template Elementor ("VBEB Popup"), generato da
		// Elementor con get_builder_content_for_display(), che ne cura gia'
		// l'escape: come ogni contenuto di Elementor non si filtra.
		echo $inserto; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenuto del template generato da Elementor.
		echo wp_kses( $pezzi[1], vbeb_kses_tag_vikbooking(), vbeb_kses_protocolli_vikbooking() );
	} else {
		echo wp_kses( str_replace( VBEB_INSERTO_POPUP, '', $parti['markup'] ), vbeb_kses_tag_vikbooking(), vbeb_kses_protocolli_vikbooking() );
	}

	foreach ( $parti['stili'] as $css ) {
		vbeb_aggiungi_stile( $css );
	}

	foreach ( $parti['script'] as $s ) {
		if ( ! empty( $s['attr']['src'] ) ) {
			$attr        = $s['attr'];
			$attr['src'] = esc_url_raw( (string) $attr['src'] );
			wp_print_script_tag( $attr );
		} elseif ( '' !== trim( $s['js'] ) ) {
			wp_print_inline_script_tag( $s['js'], $s['attr'] );
		}
	}
}

/**
 * Come vbeb_stampa_html_vikbooking(), ma restituisce l'HTML invece di
 * stamparlo (per i widget che compongono il loro HTML prima di stamparlo).
 *
 * @param string $html HTML di VikBooking.
 * @return string
 */
function vbeb_html_vikbooking_filtrato( $html ) {
	ob_start();
	vbeb_stampa_html_vikbooking( $html );
	return (string) ob_get_clean();
}

/**
 * Un colore CSS valido, o una stringa vuota.
 *
 * Accetta i colori esadecimali (#abc, #aabbcc, #aabbccdd) e le forme rgb(),
 * rgba(), hsl(), hsla() con soli numeri, percentuali, virgole, barre e spazi.
 *
 * @param mixed $valore Colore salvato in VikBooking.
 * @return string
 */
function vbeb_colore_css( $valore ) {
	$valore = trim( (string) $valore );

	if ( preg_match( '/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $valore ) ) {
		return $valore;
	}

	if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\(\s*[0-9.,%\/\s]+\)$/i', $valore ) ) {
		return $valore;
	}

	return '';
}

/**
 * Aggiunge un blocco di CSS alla pagina con wp_add_inline_style(), su un
 * foglio vuoto registrato per l'occasione. Chiamata durante il disegno della
 * pagina, WordPress lo stampa nel footer (print_late_styles()).
 *
 * @param string $css CSS.
 * @return void
 */
function vbeb_aggiungi_stile( $css ) {
	static $n = 0;

	$css = trim( wp_strip_all_tags( (string) $css ) );
	if ( '' === $css ) {
		return;
	}

	++$n;
	$handle = 'vbeb-stile-' . $n;
	wp_register_style( $handle, false, array(), VBEB_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
