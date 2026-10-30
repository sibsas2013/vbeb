<?php
/**
 * Plugin Name: VBEB - Bridge for VikBooking and Elementor
 * Description: Design the VikBooking room details page with Elementor widgets.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: elementor, vikbooking
 * Author: S.I.B. s.a.s.
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vbeb-bridge-for-vikbooking-and-elementor
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Nessun accesso diretto.
}

define( 'VBEB_VERSION', '1.0.0' );
define( 'VBEB_MAIN_FILE', __FILE__ );
define( 'VBEB_PATH', plugin_dir_path( __FILE__ ) );
define( 'VBEB_URL', plugin_dir_url( __FILE__ ) );

/**
 * VBEB 7.616.0, su richiesta dell'utente: traduzioni dei testi propri del
 * plugin (dominio "vbeb-bridge-for-vikbooking-and-elementor"). Nel codice i testi sono in inglese; il file
 * languages/vbeb-it_IT riporta l'italiano.
 * VBEB free (wordpress.org), su richiesta dell'utente: anche i testi che
 * esistono in Elementor usano il dominio "vbeb-bridge-for-vikbooking-and-elementor" (wordpress.org accetta solo
 * il dominio del plugin). L'inglese resta quello di Elementor e le
 * traduzioni italiana e spagnola sono copiate dai file di lingua di
 * Elementor (elementor-it_IT.po, elementor-es_ES.po): i commenti "dominio
 * elementor" nei widget indicano questi testi.
 * Un file in wp-content/languages/plugins/ (per esempio fatto con Loco
 * Translate) ha la precedenza su quello del plugin.
 */
function vbeb_load_textdomain() {
	load_plugin_textdomain( 'vbeb-bridge-for-vikbooking-and-elementor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- traduzioni italiana e spagnola incluse nel plugin (cartella languages).
}
add_action( 'init', 'vbeb_load_textdomain', 0 );

/**
 * Impedisce l'attivazione del plugin se Elementor e/o VikBooking non sono
 * entrambi presenti e attivi. Riusa lo stesso rilevamento "morbido" di
 * VikBooking usato altrove nel plugin (nessun percorso di file indovinato,
 * che potrebbe risultare sbagliato e bloccare l'attivazione per errore).
 */
function vbeb_activation_check() {
	$elementor_active  = did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) || class_exists( '\Elementor\Plugin' );
	$vikbooking_active = defined( 'VIKBOOKING_VERSION' ) || class_exists( 'VikBooking' ) || function_exists( 'vikbooking_import_plugin_lang' );

	if ( ! $elementor_active || ! $vikbooking_active ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'This plugin cannot be activated because the required plugins (Elementor and VikBooking) are missing or inactive.', 'vbeb-bridge-for-vikbooking-and-elementor' ),
			esc_html__( 'Plugin activation error', 'vbeb-bridge-for-vikbooking-and-elementor' ),
			array( 'back_link' => true )
		);
	}
}
register_activation_hook( __FILE__, 'vbeb_activation_check' );

/**
 * Avviso in bacheca se Elementor non è attivo (requisito bloccante).
 */
function vbeb_admin_notice_missing_elementor() {
	if ( did_action( 'elementor/loaded' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><strong>VBEB</strong>: <?php esc_html_e( 'requires the Elementor plugin to be active. The widgets will not be available until Elementor is activated.', 'vbeb-bridge-for-vikbooking-and-elementor' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'vbeb_admin_notice_missing_elementor' );

/**
 * Carica il contesto dati camera e la pagina impostazioni.
 */
/**
 * Colori globali di Elementor nelle regole forzate (!important) dei widget VBEB.
 *
 * VBEB 7.662.0, su richiesta dell'utente. Molti widget VBEB scrivono i
 * colori con "!important", per vincere sulle regole forzate di VikBooking.
 * Con un colore globale Elementor riscrive la regola come
 * "proprieta: var(--e-global-color-...)" e il "!important" si perde, sia
 * nel foglio del sito (core/files/css/base.php) sia nell'editor
 * (editor.js, "replace(/(:)[^;]+(;?)/g, ...)"): vinceva di nuovo VikBooking.
 *
 * Qui, per i soli widget VBEB e per i soli colori globali, la regola viene
 * ristampata accanto al widget con il colore globale e il "!important"
 * originale, rispettando condizioni e dispositivi. Le impostazioni
 * interessate ridisegnano il widget quando cambiano (vedi sotto), cosi'
 * anche nell'editor la regola compare subito.
 */
function vbeb_regole_globali_forzate( $element ) {
	$globals = $element->get_settings( '__globals__' );
	if ( empty( $globals ) || ! is_array( $globals ) ) {
		return '';
	}

	$controls = $element->get_controls();
	$settings = $element->get_settings();
	$wrapper  = '.elementor .elementor-element.elementor-element-' . $element->get_id();
	$css      = '';

	$breakpoints = array();
	if ( isset( \Elementor\Plugin::$instance->breakpoints ) ) {
		foreach ( \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints() as $nome => $bp ) {
			$breakpoints[ $nome ] = $bp;
		}
	}

	foreach ( $globals as $nome => $chiave ) {
		if ( empty( $chiave ) || ! is_string( $chiave ) || 0 !== strpos( $chiave, 'globals/colors' ) || empty( $controls[ $nome ]['selectors'] ) ) {
			continue;
		}
		$parti = explode( '?id=', $chiave );
		if ( empty( $parti[1] ) ) {
			continue;
		}
		$control = $controls[ $nome ];
		if ( ! $element->is_control_visible( $control, $settings ) ) {
			continue;
		}
		$valore = 'var( --e-global-color-' . preg_replace( '/[^A-Za-z0-9_-]/', '', $parti[1] ) . ' )';

		$regole = '';
		foreach ( $control['selectors'] as $selettore => $proprieta ) {
			if ( false === strpos( $proprieta, '{{VALUE}}' ) || false === stripos( $proprieta, '!important' ) ) {
				continue;
			}
			$regole .= str_replace( '{{WRAPPER}}', $wrapper, $selettore ) . '{' . str_replace( '{{VALUE}}', $valore, $proprieta ) . '}';
		}
		if ( '' === $regole ) {
			continue;
		}

		// Impostazioni per dispositivo (es. "_tablet"): stessa media query di Elementor.
		if ( ! empty( $control['responsive']['max'] ) && isset( $breakpoints[ $control['responsive']['max'] ] ) ) {
			$regole = '@media (max-width:' . (int) $breakpoints[ $control['responsive']['max'] ]->get_value() . 'px){' . $regole . '}';
		} elseif ( ! empty( $control['responsive']['min'] ) && isset( $breakpoints[ $control['responsive']['min'] ] ) ) {
			$regole = '@media (min-width:' . (int) $breakpoints[ $control['responsive']['min'] ]->get_value() . 'px){' . $regole . '}';
		}
		$css .= $regole;
	}

	return '' === $css ? '' : '<style>' . $css . '</style>';
}

add_filter(
	'elementor/widget/render_content',
	function ( $content, $widget ) {
		if ( 0 !== strpos( get_class( $widget ), 'VBEB_' ) ) {
			return $content;
		}
		return vbeb_regole_globali_forzate( $widget ) . $content;
	},
	10,
	2
);

// Le impostazioni di colore con "!important" ridisegnano il widget quando
// cambiano: senza, nell'editor la regola qui sopra comparirebbe solo al
// ridisegno successivo.
add_action(
	'elementor/element/after_section_end',
	function ( $element, $section_id ) {
		if ( 0 !== strpos( get_class( $element ), 'VBEB_' ) ) {
			return;
		}
		foreach ( $element->get_controls() as $nome => $control ) {
			if ( ! isset( $control['section'] ) || $control['section'] !== $section_id || empty( $control['selectors'] ) || ! isset( $control['type'] ) || \Elementor\Controls_Manager::COLOR !== $control['type'] ) {
				continue;
			}
			foreach ( $control['selectors'] as $proprieta ) {
				if ( false !== strpos( $proprieta, '{{VALUE}}' ) && false !== stripos( $proprieta, '!important' ) ) {
					$element->update_control( $nome, array( 'render_type' => 'template' ) );
					break;
				}
			}
		}
	},
	10,
	2
);

/**
 * La traduzione di un template Elementor nella lingua della pagina.
 *
 * VBEB free, su richiesta dell'utente: con Polylang o WPML il template
 * tradotto e' una copia con un altro ID. VBEB salva l'ID del template
 * originale (Template > Dettaglio Camera, "Template del modulo" del widget
 * Pulsante informazioni): qui si chiede al plugin multilingua quale sia la
 * copia nella lingua corrente.
 *
 * - Polylang: pll_get_post() (polylang/src/api.php). Restituisce 0/false se
 *   la copia non esiste o se il tipo "elementor_library" non e' tradotto.
 * - WPML: il filtro "wpml_object_id", con il ripiego sull'originale se la
 *   copia manca (Polylang implementa anche questo filtro, ma si usa prima la
 *   sua funzione, che non dipende dall'impostazione PLL_WPML_COMPAT).
 *
 * Senza plugin multilingua, o senza copia tradotta pubblicata: l'originale.
 *
 * @param int $template_id ID del template scelto.
 * @return int
 */
function vbeb_template_lingua( $template_id ) {
	$template_id = (int) $template_id;
	if ( $template_id < 1 ) {
		return $template_id;
	}

	$tradotto = 0;
	if ( function_exists( 'pll_get_post' ) ) {
		$tradotto = (int) pll_get_post( $template_id );
	} elseif ( has_filter( 'wpml_object_id' ) ) {
		$tradotto = (int) apply_filters( 'wpml_object_id', $template_id, 'elementor_library', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro di WPML (anche Polylang lo implementa).
	}

	if ( $tradotto > 0 && $tradotto !== $template_id && 'publish' === get_post_status( $tradotto ) ) {
		return $tradotto;
	}

	return $template_id;
}

/**
 * Gli stili degli elementi v4 di Elementor per un template inserito da VBEB.
 *
 * VBEB 7.641.0, su richiesta dell'utente (per ora solo il template
 * "Dettaglio camera", override-templates/default_listing.php). Elementor
 * prepara il foglio con gli stili degli elementi v4 (Flexbox, Titolo e
 * Paragrafo atomici: modules/atomic-widgets/styles/atomic-styles-manager.php)
 * solo per la pagina aperta e per l'anteprima dell'editor
 * (includes/frontend.php, enqueue_styles(): "elementor/post/render" e poi
 * "elementor/frontend/after_enqueue_post_styles"). I template che VBEB
 * inserisce nelle pagine di VikBooking restavano fuori: la Flexbox perdeva,
 * per esempio, "Direzione: colonna" e tornava in riga.
 *
 * Qui si aggiunge il template alla lista. VikBooking di solito disegna la
 * pagina prima dell'intestazione (VikBookingBody::process() su "init"), e
 * allora Elementor lo trova gia' in lista quando prepara gli stili. Se invece
 * gli stili sono gia' stati preparati, si ripete la preparazione: il foglio
 * arriva in fondo alla pagina.
 *
 * @param int $template_id ID del template Elementor.
 * @return void
 */
function vbeb_stili_v4_template( $template_id ) {
	static $fatti = array();

	$template_id = (int) $template_id;
	if ( $template_id < 1 || isset( $fatti[ $template_id ] ) ) {
		return;
	}
	$fatti[ $template_id ] = true;

	do_action( 'elementor/post/render', $template_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- azione di Elementor.

	if ( did_action( 'elementor/frontend/after_enqueue_post_styles' ) ) {
		do_action( 'elementor/frontend/after_enqueue_post_styles' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- azione di Elementor.
	}
}

/**
 * Nell'editor di Elementor gli stili "una volta per pagina" si ripetono.
 *
 * VBEB 7.454.0. Molti widget stampano le loro regole fisse (e il foglio di
 * VikBooking, vbeb_stili_vikbooking_editor()) solo nel primo widget della
 * pagina. Nell'editor, cancellando quel widget, le regole sparivano anche
 * per gli altri (segnalazione dell'utente). Nell'editor e nella sua
 * anteprima ogni widget le stampa per conto suo; sul sito resta una volta
 * sola.
 *
 * @return bool
 */
function vbeb_editor_ripete() {
	if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance ) ) {
		return false;
	}
	$el = \Elementor\Plugin::$instance;
	if ( isset( $el->editor ) && $el->editor->is_edit_mode() ) {
		return true;
	}
	return isset( $el->preview ) && $el->preview->is_preview_mode();
}

/**
 * Il foglio di VikBooking e i suoi colori nell'intestazione dell'anteprima.
 *
 * VBEB 7.454.0: cosi' nell'editor non dipendono da nessun widget (prima
 * stavano solo dentro il primo widget VBEB, e cancellandolo sparivano per
 * tutti). Solo nell'anteprima dell'editor; sul sito li carica VikBooking.
 */
add_action(
	'elementor/preview/enqueue_styles',
	function () {
		if ( ! defined( 'VBO_SITE_URI' ) ) {
			return;
		}
		// VBEB free (Plugin Check): versione del foglio = versione di VikBooking.
		$versione = defined( 'VIKBOOKING_SOFTWARE_VERSION' ) ? VIKBOOKING_SOFTWARE_VERSION : VBEB_VERSION;
		wp_enqueue_style( 'vbeb-vikbooking-editor', VBO_SITE_URI . 'resources/vikbooking_styles.css', array(), $versione );

		// VBEB free (Plugin Check: i fogli vanno caricati con
		// wp_enqueue_style()): il foglio di jQuery UI di VikBooking e le icone
		// di VikBooking (Font Awesome 7), prima stampati con "<link>" dentro i
		// widget (vbeb_stili_vikbooking_editor(), vbeb_icone_vikbooking_editor()).
		// Le icone dopo il Font Awesome di Elementor, se registrato, come sul sito.
		wp_enqueue_style( 'vbeb-vikbooking-jquery-ui', VBO_SITE_URI . 'resources/jquery-ui.min.css', array(), $versione );
		$fogli = array( 'fontawesome.min.css', 'solid.min.css', 'regular.min.css' );
		if ( class_exists( 'VikBookingIcons' ) && ! empty( VikBookingIcons::$fa_default_styles ) && is_array( VikBookingIcons::$fa_default_styles ) ) {
			$fogli = VikBookingIcons::$fa_default_styles;
		}
		$dipendenze = array();
		foreach ( array( 'elementor-icons-fa-solid', 'elementor-icons-fa-regular', 'font-awesome' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) ) {
				$dipendenze[] = $handle;
			}
		}
		foreach ( $fogli as $foglio ) {
			wp_enqueue_style( 'vbeb-vikbooking-' . sanitize_key( str_replace( '.', '-', $foglio ) ), VBO_SITE_URI . 'resources/' . $foglio, $dipendenze, $versione );
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
					wp_add_inline_style( 'vbeb-vikbooking-editor', ':root {' . implode( '', $righe ) . '}' );
				}
			}
		}
	}
);

/**
 * VBEB 7.521.0, su richiesta dell'utente: i widget "VBEB Camera - Nome" e
 * "VBEB Camera - Categoria" senza margini e spaziatura interna di partenza, qualunque tag
 * HTML sia scelto, come il widget "Titolo" di Elementor
 * (".elementor-heading-title{margin:0;padding:0}", frontend.css). Senza,
 * i margini del tema e del reset di Elementor per h1-h6 e p
 * ("margin-block-start/end") restavano. Una classe batte quelle regole
 * (solo tag); le impostazioni di Elementor del widget (Avanzato) agiscono
 * sul contenitore del widget e restano libere.
 */
add_action(
	'elementor/frontend/after_enqueue_styles',
	function () {
		wp_add_inline_style(
			'elementor-frontend',
			'.vbeb-room-title,.vbeb-room-category{margin:0;padding:0;}'
			// VBEB 7.616.0, su richiesta dell'utente: come il widget Immagine
			// di Elementor (text-align: center sul widget), l'immagine della
			// camera parte centrata. Regola con una sola classe: "Allineamento"
			// del pannello ({{WRAPPER}} .vbeb-room-image) la sostituisce.
			. '.vbeb-room-image{text-align:center;}'
		);
	}
);

/**
 * VBEB 7.481.0, su richiesta dell'utente: il tipo di template "VBEB Popup"
 * (al posto dell'interruttore "VBEB - Anteprima" della 7.480.0 sui
 * template Sezione). Si sceglie in Template > Aggiungi nuovo, come "Popup"
 * o "Sezione". Impostazioni del template: "Tipo di popup" (per ora solo
 * "Modulo informazioni"); Stile: "Popup" (sfondo, bordo, raggio, ombra) e
 * "Sovrapponi" (sfondo dello sfondo scuro); Avanzato: Margine, Rientro, CSS
 * personalizzato (VBEB 7.484.0 e 7.485.0). La larghezza e' quella di
 * VikBooking per il tipo di popup.
 * Vuote: l'aspetto di VikBooking per quel popup, anche nell'anteprima
 * dell'editor; compilate: valgono nell'editor e sul sito. Vedi
 * includes/class-vbeb-popup-document.php.
 */
add_action(
	'elementor/documents/register',
	function ( $documents_manager ) {
		if ( ! class_exists( '\Elementor\Modules\Library\Documents\Library_Document' ) ) {
			return;
		}
		require_once VBEB_PATH . 'includes/class-vbeb-popup-document.php';
		$documents_manager->register_document_type( VBEB_Popup_Document::get_type(), VBEB_Popup_Document::get_class_full_name() );
	},
	// Prima dei tipi della Libreria di Elementor (priorita' 10): per i
	// template senza tipo salvato Elementor usa l'ultimo tipo registrato
	// per "elementor_library", che cosi' resta il suo.
	5
);

/**
 * VBEB 7.481.0: nell'anteprima dell'editor la larghezza del riquadro "VBEB
 * Popup" segue VikBooking (".vbdialog-inner" nelle media query di
 * vikbooking_styles.css: 60% sotto i 1600px, 80% sotto i 1080px). La
 * variabile e' usata solo dall'aspetto di base del tipo "Modulo
 * informazioni" (VBEB 7.485.0: nessuna impostazione di larghezza).
 */
add_action(
	'elementor/preview/enqueue_styles',
	function () {
		wp_register_style( 'vbeb-popup-anteprima', false, array(), VBEB_VERSION );
		wp_enqueue_style( 'vbeb-popup-anteprima' );
		wp_add_inline_style(
			'vbeb-popup-anteprima',
			'@media (max-width:1600px){:root{--vbeb-popup-w:60vw;}}@media (max-width:1080px){:root{--vbeb-popup-w:80vw;}}'
			// VBEB 7.487.0: il pulsante di chiusura dell'anteprima (compare
			// con "Mostra pulsante di chiusura", che gli da' il "content").
			. vbeb_popup_css_chiusura( 'body.elementor-editor-active .vbeb-popup::after' )
			// VBEB 7.494.0: la finestra dell'anteprima, come quella del popup
			// di Elementor Pro: fissa a tutto schermo, centra il riquadro
			// (anche con "Margine" a 0) e scorre se il riquadro e' piu' alto
			// dello schermo ("safe": la parte alta resta raggiungibile).
			. 'body.elementor-editor-active .vbeb-popup-modal{position:fixed;inset:0;z-index:9999;box-sizing:border-box;display:flex;justify-content:center;align-items:center;align-items:safe center;overflow-y:auto;overscroll-behavior:contain;padding:5vh 16px;}'
			. 'body.elementor-editor-active .vbeb-popup-modal>.vbeb-popup{position:relative;flex:0 0 auto;}'
		);
	}
);

/**
 * VBEB 7.494.0: nell'anteprima dell'editor di un template "VBEB Popup" il
 * contenitore del template (stampato da Elementor con il filtro
 * "the_content", Preview::builder_wrapper(), priorita' 999999) va dentro
 * ".vbeb-popup-modal", la finestra fissa che lo centra e porta lo sfondo
 * scuro, come il popup di Elementor Pro. Solo nell'anteprima; sul sito il
 * template sta nel popup di VikBooking.
 */
function vbeb_popup_anteprima_modale( $content ) {
	if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->preview ) ) {
		return $content;
	}
	$preview = \Elementor\Plugin::$instance->preview;
	$id      = (int) $preview->get_post_id();
	if ( ! $id || get_the_ID() !== $id || ! $preview->is_preview_mode( $id ) ) {
		return $content;
	}
	if ( 'vbeb-popup' !== get_post_meta( $id, '_elementor_template_type', true ) ) {
		return $content;
	}
	return '<div class="vbeb-popup-modal">' . $content . '</div>';
}
add_filter( 'the_content', 'vbeb_popup_anteprima_modale', 1000000 );

/**
 * VBEB 7.487.0: il pulsante di chiusura dei popup "VBEB Popup", con
 * l'immagine di VikBooking (resources/images/fancybox_sprite.png e
 * fancybox_sprite@2x.png per gli schermi retina, la stessa del pulsante del
 * popup "+" dei risultati di ricerca) e le stesse misure e posizione
 * (jquery.fancybox.css, riga 100: 36x36px, 18px fuori dall'angolo in alto
 * a destra).
 *
 * @return string Regole CSS, senza tag.
 */
function vbeb_popup_css_chiusura( $sel ) {
	if ( ! defined( 'VBO_SITE_URI' ) ) {
		return '';
	}
	$uno = VBO_SITE_URI . 'resources/images/fancybox_sprite.png';
	$due = VBO_SITE_URI . 'resources/images/fancybox_sprite@2x.png';
	return $sel . '{position:absolute;top:-18px;right:-18px;width:36px;height:36px;cursor:pointer;z-index:8040;background-image:url(\'' . esc_url( $uno ) . '\');background-position:0 0;background-repeat:no-repeat;}'
		. '@media only screen and (-webkit-min-device-pixel-ratio:1.5),only screen and (min-resolution:144dpi){' . $sel . '{background-image:url(\'' . esc_url( $due ) . '\');background-size:44px 152px;}}';
}

/**
 * VBEB 7.487.0: CSS dei popup "VBEB Popup" sul sito, una volta per pagina.
 * Il riquadro di VikBooking (".vbdialog-reqinfo", con l'altezza massima del
 * "Pulsante informazioni") non scorre piu' lui: scorre il contenitore
 * interno ".vbeb-popup-scroll", e il riquadro lascia uscire il pulsante di
 * chiusura.
 *
 * @return string
 */
function vbeb_popup_css_sito() {
	static $fatto = false;
	if ( $fatto ) {
		return '';
	}
	$fatto = true;
	return '<style id="vbeb-popup-sito">'
		. '.vbeb-reqinfo-wrap .vbdialog-inner.vbdialog-reqinfo.vbeb-popup{overflow:visible;display:flex;flex-direction:column;}'
		. '.vbeb-popup>.vbeb-popup-scroll{flex:1 1 auto;min-height:0;overflow-y:auto;overscroll-behavior:contain;}'
		. vbeb_popup_css_chiusura( '.vbeb-popup>.vbeb-popup-close' )
		. '</style>';
}

/**
 * VBEB 7.481.0: i template "VBEB Popup" di un tipo (per esempio "reqinfo"),
 * per i menu a tendina dei widget.
 *
 * @param string $tipo Tipo di popup.
 * @return array ID => titolo.
 */
function vbeb_popup_elenco( $tipo ) {
	$elenco = array();

	if ( ! post_type_exists( 'elementor_library' ) ) {
		return $elenco;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			// VBEB free: tutte le lingue (Polylang altrimenti filtra con la
			// lingua scelta nella barra); la scelta per lingua e' fatta qui sotto.
			'lang'           => 'all',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery -- elenco di template, letto solo nel pannello.
				array(
					'key'   => '_elementor_template_type',
					'value' => 'vbeb-popup',
				),
			),
		)
	);

	// VBEB free, segnalato dall'utente: con Polylang o WPML, nell'editor solo
	// i template nella lingua del template aperto (piu' quelli senza lingua).
	// Fuori dall'editor, o senza plugin multilingua, tutti.
	$in_editor = ( function_exists( 'vbeb_editor_ripete' ) && vbeb_editor_ripete() )
		|| ( function_exists( 'vbeb_id_editor_ajax' ) && vbeb_id_editor_ajax() > 0 );
	$lingua    = ( $in_editor && function_exists( 'vbeb_lingua_template_editor' ) )
		? str_replace( '-', '_', vbeb_lingua_template_editor() )
		: '';

	foreach ( $posts as $post ) {
		if ( '' !== $lingua && function_exists( 'vbeb_lingua_contenuto' ) ) {
			$lingua_post = vbeb_lingua_contenuto( $post->ID );
			if ( '' !== $lingua_post && $lingua_post !== $lingua ) {
				continue;
			}
		}
		$impostazioni = get_post_meta( $post->ID, '_elementor_page_settings', true );
		$tipo_tpl     = ( is_array( $impostazioni ) && ! empty( $impostazioni['vbeb_popup_tipo'] ) ) ? $impostazioni['vbeb_popup_tipo'] : 'reqinfo';
		if ( $tipo_tpl === $tipo ) {
			$elenco[ (string) $post->ID ] = $post->post_title;
		}
	}

	return $elenco;
}

/**
 * VBEB 7.488.0, su richiesta dell'utente: il messaggio dopo l'invio della
 * richiesta informazioni, impostato nel widget "VBEB Camera - Modulo
 * informazioni" (Contenuto e Stile > "Messaggio inviato").
 *
 * VikBooking, all'invio (site/controller.php, riga 1768), mette nella coda
 * dei messaggi di sistema JText::_('VBOROOMREQINFOSENTOK'), che passa da
 * __('Information Request Successfully Sent!', 'vikbooking')
 * (libraries/language/site.php, riga 1281), e ricarica la pagina; il
 * messaggio viene stampato senza escape (libraries/html/system/messages.php).
 * Solo nella richiesta di invio fatta dal widget (campo nascosto
 * "vbeb_ri_ok"), il filtro "gettext" cambia quel testo: il testo del widget
 * (o quello di VikBooking se vuoto) dentro <span class="vbeb-reqinfo-msg">,
 * che permette allo stile del widget di riconoscere solo questo messaggio.
 * Gli altri messaggi di VikBooking non cambiano.
 */
function vbeb_reqinfo_messaggio( $translation, $text, $domain ) {
	if ( 'vikbooking' !== $domain || 'Information Request Successfully Sent!' !== $text ) {
		return $translation;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- la verifica e' quella di VikBooking (token di sessione); qui si cambia solo il testo del messaggio.
	if ( ! isset( $_POST['reqinfotoken'], $_POST['reqmess'], $_POST['vbeb_ri_ok'] ) || ! is_string( $_POST['vbeb_ri_ok'] ) ) {
		return $translation;
	}
	$testo = sanitize_textarea_field( wp_unslash( $_POST['vbeb_ri_ok'] ) );
	// phpcs:enable
	if ( '' === trim( $testo ) ) {
		$testo = $translation;
	}
	return '<span class="vbeb-reqinfo-msg">' . nl2br( esc_html( $testo ) ) . '</span>';
}
add_filter( 'gettext', 'vbeb_reqinfo_messaggio', 10, 3 );

/**
 * Le etichette del widget "VBEB Camera - Modulo informazioni" nell'e-mail.
 *
 * VBEB 7.478.0, su richiesta dell'utente. VikBooking scrive l'e-mail della
 * richiesta informazioni con le sue traduzioni fisse ("Nome completo: ...",
 * "e-Mail: ...", "Messaggio:", site/controller.php, riga 1765) e la invia con
 * wp_mail(). Se il modulo ha mandato le etichette scelte nel widget (campi
 * nascosti "vbeb_ri_lbl[...]"), qui sostituiscono quei nomi, un attimo prima
 * dell'invio. Solo per l'invio di una richiesta informazioni (campi
 * "reqinfotoken" e "reqmess" presenti), solo all'inizio di riga, una volta
 * ciascuno; il resto dell'e-mail non cambia.
 *
 * @param array $args Argomenti di wp_mail().
 * @return array
 */
function vbeb_reqinfo_etichette_email( $args ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- la verifica e' quella di VikBooking (token di sessione); qui si cambia solo il testo dell'e-mail.
	if ( ! isset( $_POST['reqinfotoken'], $_POST['reqmess'] ) || ! class_exists( 'JText' ) ) {
		return $args;
	}

	// VBEB 7.479.0, su richiesta dell'utente: l'oggetto con il testo del
	// Titolo del widget al posto della parte fissa di VikBooking
	// ("Richiesta informazioni per %s", VBOROOMREQINFOSUBJ): resta il nome
	// della camera, come nel titolo del modulo (vedi titolo() del widget).
	if ( ! empty( $_POST['vbeb_ri_subj'] ) && is_string( $_POST['vbeb_ri_subj'] ) && ! empty( $args['subject'] ) && is_string( $args['subject'] ) ) {
		$testo   = sanitize_text_field( wp_unslash( $_POST['vbeb_ri_subj'] ) );
		$modello = (string) JText::_( 'VBOROOMREQINFOSUBJ' );
		$pos     = strpos( $modello, '%s' );
		if ( '' !== $testo && false !== $pos ) {
			$prima = substr( $modello, 0, $pos );
			$dopo  = substr( $modello, $pos + 2 );
			if ( 0 === strpos( $args['subject'], $prima ) && ( '' === $dopo || substr( $args['subject'], -strlen( $dopo ) ) === $dopo ) ) {
				$nome            = substr( $args['subject'], strlen( $prima ), strlen( $args['subject'] ) - strlen( $prima ) - strlen( $dopo ) );
				$args['subject'] = $testo . ' ' . $nome . $dopo;
			}
		}
	}

	if ( empty( $_POST['vbeb_ri_lbl'] ) || ! is_array( $_POST['vbeb_ri_lbl'] ) || empty( $args['message'] ) || ! is_string( $args['message'] ) ) {
		return $args;
	}

	$scelte = map_deep( wp_unslash( $_POST['vbeb_ri_lbl'] ), 'sanitize_text_field' );
	// phpcs:enable

	$chiavi = array(
		'nome'      => 'VBOROOMREQINFONAME',
		'email'     => 'VBOROOMREQINFOEMAIL',
		'messaggio' => 'VBOROOMREQINFOMESS',
	);

	foreach ( $chiavi as $campo => $chiave ) {
		if ( empty( $scelte[ $campo ] ) || ! is_string( $scelte[ $campo ] ) ) {
			continue;
		}
		$nuova     = sanitize_text_field( $scelte[ $campo ] );
		$originale = (string) JText::_( $chiave );
		if ( '' === $nuova || '' === $originale || $originale === $chiave ) {
			continue;
		}
		$args['message'] = preg_replace( '/^' . preg_quote( $originale, '/' ) . ':/m', str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), $nuova ) . ':', $args['message'], 1 );
	}

	return $args;
}
add_filter( 'wp_mail', 'vbeb_reqinfo_etichette_email' );

require_once VBEB_PATH . 'includes/class-vbeb-room-context.php';
// VBEB 7.30.0: camera di esempio nell'editor di Elementor.
require_once VBEB_PATH . 'includes/class-vbeb-editor-preview.php';
// VBEB free: fogli e icone di VikBooking nell'anteprima dell'editor.
require_once VBEB_PATH . 'includes/vbeb-editor-helpers.php';
require_once VBEB_PATH . 'includes/class-vbeb-admin-settings.php';
require_once VBEB_PATH . 'includes/class-vbeb-hide-layout-option.php';
require_once VBEB_PATH . 'includes/class-vbeb-override-installer.php';

/**
 * VBEB 7.616.0, su richiesta dell'utente: in VikBooking (Globale >
 * Configurazione > Impostazioni principali) "Prenotazione abilitata" e'
 * accesa? Stessa lettura di VikBooking (VikBooking::allowBooking()). Lo usa
 * il widget "VBEB Camera - Modulo prenotazione" per il valore di partenza.
 * Se VikBooking non e' attivo o la lettura non riesce: vero.
 *
 * @return bool
 */
function vbeb_vikbooking_booking_enabled() {
	static $attiva = null;

	if ( null !== $attiva ) {
		return $attiva;
	}

	$attiva = true;

	try {
		if ( class_exists( 'VikBooking' ) && method_exists( 'VikBooking', 'allowBooking' ) ) {
			$attiva = (bool) VikBooking::allowBooking();
		}
	} catch ( \Throwable $e ) {
		$attiva = true;
	}

	return $attiva;
}

function vbeb_register_widget_category( $elements_manager ) {
	// VBEB 7.629.0, su richiesta dell'utente: "Widget generici" sopra a
	// "Dettaglio camera" (Elementor mostra le categorie nell'ordine di
	// registrazione). Nella versione free contiene solo "VBEB Calendario popup".
	$elements_manager->add_category(
		'vbeb-search',
		array(
			'title' => esc_html__( 'VBEB - General Widgets', 'vbeb-bridge-for-vikbooking-and-elementor' ),
			'icon'  => 'fa fa-search',
		)
	);
	$elements_manager->add_category(
		'vikbooking-bridge',
		array(
			'title' => esc_html__( 'VBEB - Room Details', 'vbeb-bridge-for-vikbooking-and-elementor' ),
			'icon'  => 'fa fa-plug',
		)
	);
}
/*
 * VBEB 7.176.0: rimossa la toppa della 7.159.0, che ricaricava i fogli di
 * Font Awesome di VikBooking nelle pagine admin di VikBooking. Partiva da
 * un'ipotesi sbagliata: l'icona del calendario mancava nell'EDITOR di
 * Elementor, non nell'amministrazione, dove lo strumento non compare
 * nemmeno. La cura vera e' in vbeb_icone_vikbooking_editor()
 * (includes/vbeb-editor-helpers.php).
 */

/**
 * VBEB 7.131.0: gli interruttori del plugin tagliano la parola al proprio bordo.
 *
 * Richiesto dall'utente. L'interruttore di Elementor e' largo 54px, e ogni
 * parola ha a disposizione meta' dello spazio, senza nulla che la tagli
 * (assets/css/editor.css). Negli interruttori di Elementor la parola lunga
 * compare da acceso, a sinistra, e l'eccesso finisce sotto il pallino
 * bianco, che lo copre. Nei nostri, con "Mostra"/"Nascondi", la parola
 * lunga compare da spento, a destra, e l'eccesso usciva dal bordo.
 *
 * Una sola regola, sui soli controlli con la classe "vbeb-switch": la
 * parola resta dentro il bordo arrotondato, anche se in parte nascosta,
 * come chiesto. Gli interruttori di Elementor non vengono toccati.
 *
 * Aggiunta al foglio di stile del pannello, "elementor-editor", cosi' vale
 * solo nell'editor e non pesa sul sito.
 */
add_action(
	'elementor/editor/after_enqueue_styles',
	function () {
		wp_add_inline_style( 'elementor-editor', '.elementor-control.vbeb-switch .elementor-switch{overflow:hidden;}'
			// VBEB 7.459.0, su richiesta dell'utente: nei riquadri dei campi
			// del widget "VBEB Camera - Modulo informazioni" (elenco con la
			// classe "vbeb-fields-list") l'icona "eicon-plus-circle-o" sul
			// lato destro del nome, per far capire che si aprono. Sta dentro
			// il nome, che e' la parte cliccabile.
			. '.elementor-control.vbeb-fields-list .elementor-repeater-row-item-title{display:flex;align-items:center;gap:8px;}'
			. '.elementor-control.vbeb-fields-list .elementor-repeater-row-item-title::after{content:"\\e8f7";font-family:eicons;font-weight:400;font-style:normal;line-height:1;margin-left:auto;flex-shrink:0;}' );
	}
);

add_action( 'elementor/elements/categories_registered', 'vbeb_register_widget_category' );

/**
 * Polylang: le richieste AJAX dell'editor di Elementor nella lingua della
 * bacheca.
 *
 * VBEB free, segnalato dall'utente: con Polylang le impostazioni dei widget
 * nel pannello comparivano a volte nella lingua dell'ultimo template aperto
 * (contenuto in italiano, impostazioni in inglese e viceversa). Polylang
 * tratta ogni richiesta AJAX come una richiesta del sito, nella lingua del
 * cookie "pll_language" (lasciato dall'anteprima), a meno che la richiesta
 * porti "pll_ajax_backend" (polylang/src/class-polylang.php,
 * is_ajax_on_front()). Nelle pagine di amministrazione lo aggiunge lo script
 * di Polylang; l'editor di Elementor non lo carica, e le richieste con cui
 * Elementor chiede le impostazioni dei widget restavano "del sito". Qui lo
 * si aggiunge alle richieste AJAX dell'editor, come fa Polylang.
 */
add_action(
	'elementor/editor/after_enqueue_scripts',
	function () {
		if ( ! function_exists( 'pll_current_language' ) ) {
			return;
		}
		wp_add_inline_script(
			'elementor-editor',
			'(function(){var $=window.jQuery;if(!$||!$.ajaxPrefilter){return;}$.ajaxPrefilter(function(o){if(o&&typeof o.url==="string"&&o.url.indexOf("admin-ajax.php")!==-1&&o.url.indexOf("pll_ajax_backend")===-1){o.url+=(o.url.indexOf("?")===-1?"?":"&")+"pll_ajax_backend=1";}});})();',
			'before'
		);
	}
);

/**
 * Registra i widget di VBEB free (compatibile con le API nuove e vecchie di
 * Elementor).
 */
function vbeb_register_widgets( $widgets_manager ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	require_once VBEB_PATH . 'includes/class-vbeb-title-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-category-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-roomimage-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-gallery-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-photogallery-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-description-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-smalldescription-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-price-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-amenities-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-calendar-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-roommap-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-bookingform-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-reqinfo-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-reqinfoform-widget.php';
	require_once VBEB_PATH . 'includes/class-vbeb-calpopup-widget.php';

	$widgets = array(
		'VBEB_Title_Widget',
		'VBEB_Category_Widget',
		'VBEB_Roomimage_Widget',
		'VBEB_Gallery_Widget',
		'VBEB_Photogallery_Widget',
		'VBEB_Description_Widget',
		'VBEB_Smalldescription_Widget',
		'VBEB_Price_Widget',
		'VBEB_Amenities_Widget',
		'VBEB_Calendar_Widget',
		'VBEB_Roommap_Widget',
		'VBEB_Bookingform_Widget',
		'VBEB_Reqinfo_Widget',
		'VBEB_Reqinfoform_Widget',
		'VBEB_Calpopup_Widget',
	);

	// Elementor moderno usa "register()", "register_widget_type()" e' solo
	// il ripiego per le versioni vecchie.
	foreach ( $widgets as $classe ) {
		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( new $classe() );
		} else {
			$widgets_manager->register_widget_type( new $classe() );
		}
	}
}
add_action( 'elementor/widgets/register', 'vbeb_register_widgets' );
add_action( 'elementor/widgets/widgets_registered', 'vbeb_register_widgets' );
