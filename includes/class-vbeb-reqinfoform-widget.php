<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Background;

/**
 * VBEB 7.253.0: "VBEB Camera - Modulo informazioni".
 *
 * Richiesto dall'utente. Il modulo della richiesta informazioni di
 * VikBooking, quello che si apre nel popup del pulsante "Richiedi
 * informazioni" (site/views/roomdetails/tmpl/default_reqinfo.php).
 *
 * COME SI USA. Si mette in un template di tipo Sezione, insieme a cio' che
 * si vuole (titoli, testi, immagini), e si sceglie il template nella
 * sezione "Modulo" del widget "VBEB Camera - Pulsante informazioni": il
 * popup di VikBooking mostra il template al posto del suo contenuto.
 * Apertura e chiusura del popup (clic fuori, Esc) restano quelle di
 * VikBooking.
 *
 * NIENTE DI RICOSTRUITO: il widget stampa il titolo e il modulo che
 * VikBooking genera per la camera (l'HTML catturato dall'override della
 * scheda camera, VBEB_Room_Context::$reqinfo_html), con il suo gettone di
 * sicurezza, i campi dei termini e l'eventuale reCAPTCHA. Nell'editor lo
 * stesso HTML lo genera la vista di VikBooking per la camera di anteprima
 * (vbeb_html_camera_anteprima()).
 *
 * LO STILE DI BASE e' quello di VikBooking, che lo lega al riquadro del
 * popup (".vbdialog-reqinfo label", "input", ...). Fuori dal popup (editor
 * o widget messo direttamente in una pagina) il modulo prende la stessa
 * classe, senza la posizione fissa del popup, cosi' ha lo stesso aspetto.
 */
class VBEB_Reqinfoform_Widget extends Widget_Base {

	/**
	 * Vero mentre "Pulsante informazioni" disegna il template nel popup.
	 *
	 * @var bool
	 */
	public static $nel_popup = false;

	public function get_name() {
		return 'vbeb_room_reqinfoform_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Information Form', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'informazioni', 'reqinfo', 'modulo', 'contatto', 'camera', 'popup' );
	}

	/**
	 * Titolo e modulo dall'HTML di VikBooking.
	 *
	 * @param string $html HTML del sotto-template "reqinfo".
	 * @return array Chiavi "titolo" e "form" (vuote se mancano).
	 */
	public static function parti( $html ) {
		$parti = array(
			'titolo' => '',
			'form'   => '',
		);

		if ( ! is_string( $html ) || '' === $html ) {
			return $parti;
		}

		if ( preg_match( '/<div class="vbdialog-inner vbdialog-reqinfo">\s*<h3>(.*?)<\/h3>/s', $html, $m ) ) {
			$parti['titolo'] = $m[1];
		}

		if ( preg_match( '/<form\b.*?<\/form>/s', $html, $m ) ) {
			$parti['form'] = $m[0];
		}

		return $parti;
	}

	/**
	 * La parte del titolo che VikBooking mette prima del nome della camera.
	 *
	 * VBEB 7.264.0. Il titolo e' JText::sprintf('VBOROOMREQINFOTITLE',
	 * nome) (default_reqinfo.php, riga 36): cio' che sta prima di "%s" nel
	 * testo tradotto.
	 *
	 * @return string
	 */
	public static function prefisso_vikbooking() {
		// VBEB 7.267.0: anche nel pannello (vedi traduzione()).
		$modello = self::traduzione( 'VBOROOMREQINFOTITLE', '' );
		$pos     = strpos( $modello, '%s' );

		return false === $pos ? '' : trim( substr( $modello, 0, $pos ) );
	}

	/**
	 * Il titolo, con il testo del pannello al posto della parte prima del
	 * nome della camera.
	 *
	 * VBEB 7.264.0. Il nome si ricava dal titolo di VikBooking togliendo le
	 * parti fisse del testo tradotto (prima e dopo "%s"): resta quello che
	 * VikBooking ha scritto, tradotto. Se il testo del pannello e' vuoto, o
	 * il titolo non corrisponde al modello, resta il titolo di VikBooking.
	 *
	 * @param string $titolo_vbo Titolo di VikBooking.
	 * @param string $testo      Testo del pannello.
	 * @return string
	 */
	public static function titolo( $titolo_vbo, $testo ) {
		$testo = trim( $testo );

		if ( '' === $titolo_vbo || '' === $testo || ! class_exists( 'JText' ) ) {
			return $titolo_vbo;
		}

		$modello = vbeb_vbo_testo( 'VBOROOMREQINFOTITLE' );
		$pos     = strpos( $modello, '%s' );

		if ( false === $pos ) {
			return $titolo_vbo;
		}

		$prima = substr( $modello, 0, $pos );
		$dopo  = substr( $modello, $pos + 2 );

		if ( 0 !== strpos( $titolo_vbo, $prima ) || ( '' !== $dopo && substr( $titolo_vbo, -strlen( $dopo ) ) !== $dopo ) ) {
			return $titolo_vbo;
		}

		$nome = substr( $titolo_vbo, strlen( $prima ), strlen( $titolo_vbo ) - strlen( $prima ) - strlen( $dopo ) );

		return esc_html( $testo ) . ' ' . $nome . $dopo;
	}

	/**
	 * Una traduzione del sito di VikBooking, anche nel pannello.
	 *
	 * VBEB 7.267.0, segnalato dall'utente con uno screenshot: nel pannello
	 * dell'editor le etichette mostravano le chiavi ("VBOROOMREQINFONAME").
	 * Il pannello si costruisce in una richiesta dell'amministrazione, dove
	 * VikBooking carica solo le traduzioni del back-end: prima si aggancia
	 * quelle del sito (vbeb_lingua_sito_in_editor(), come per i widget
	 * nell'anteprima). Se la chiave resta senza traduzione, il testo di
	 * ripiego.
	 *
	 * @param string $chiave  Chiave di VikBooking.
	 * @param string $ripiego Testo se manca la traduzione.
	 * @return string
	 */
	private static function traduzione( $chiave, $ripiego ) {
		if ( function_exists( 'vbeb_lingua_sito_in_editor' ) ) {
			vbeb_lingua_sito_in_editor();
		}

		$testo = class_exists( 'JText' ) ? (string) JText::_( $chiave ) : '';

		return ( '' === $testo || $testo === $chiave ) ? $ripiego : $testo;
	}

	/**
	 * I tre campi del modulo di VikBooking: identificativo e traduzione.
	 *
	 * @return array
	 */
	private static function campi_modulo() {
		return array(
			'nome'      => array( 'id' => 'reqname', 'testo' => 'VBOROOMREQINFONAME', 'titolo' => esc_html__( 'Full Name', 'vbeb' ) ),
			'email'     => array( 'id' => 'reqemail', 'testo' => 'VBOROOMREQINFOEMAIL', 'titolo' => esc_html__( 'e-Mail', 'vbeb' ) ),
			'messaggio' => array( 'id' => 'reqmess', 'testo' => 'VBOROOMREQINFOMESS', 'titolo' => esc_html__( 'Message', 'vbeb' ) ),
			// VBEB 7.457.0: anche la casella dei termini (una o piu',
			// ".vbdialog-reqinfo-formentry-ckbox"), solo etichetta e larghezza.
			'termini'   => array( 'id' => 'vbof', 'testo' => 'ORDER_TERMSCONDITIONS', 'titolo' => esc_html__( 'Terms and Conditions', 'vbeb' ) ),
		);
	}

	/**
	 * Il modulo con i nomi e i segnaposto scelti nel pannello.
	 *
	 * VBEB 7.266.0. Si cambiano solo il testo dell'etichetta
	 * ("<label for="reqname">") e l'attributo "placeholder" del campo con
	 * quell'identificativo (input o textarea); tutto il resto resta
	 * l'HTML di VikBooking.
	 *
	 * @param string $form     HTML del modulo.
	 * @param array  $settings Impostazioni del widget.
	 * @return string
	 */
	/**
	 * Se i termini del modulo hanno una pagina collegata in VikBooking.
	 *
	 * VBEB 7.463.0. Il modulo mostra fino a due campi personalizzati di
	 * tipo casella obbligatori (roomdetails/view.html.php, riga 175): se
	 * almeno uno ha la pagina "popup" ("poplink"), il suo testo e' un link.
	 *
	 * @return bool
	 */
	private static function termini_con_link() {
		static $esito = null;

		if ( null !== $esito ) {
			return $esito;
		}

		global $wpdb;
		$tabella = $wpdb->prefix . 'vikbooking_custfields';
		$esito   = false;

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tabella ) ) === $tabella ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- verifica che la tabella di VikBooking esista.
			$link  = $wpdb->get_col( $wpdb->prepare( "SELECT `poplink` FROM %i WHERE `type`='checkbox' AND `required`=1 ORDER BY `ordering` ASC LIMIT 2", $tabella ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- tabella di VikBooking, una lettura per richiesta (risultato tenuto in $esito).
			$esito = false;
			foreach ( (array) $link as $l ) {
				if ( '' !== trim( (string) $l ) ) {
					$esito = true;
				}
			}
		}

		return $esito;
	}

	/**
	 * Le etichette scelte nel widget come campi nascosti del modulo.
	 *
	 * VBEB 7.478.0. Solo quelle scritte (anche con le impostazioni di
	 * prima); vuote, l'e-mail resta con i testi di VikBooking.
	 *
	 * @param string $form     HTML del modulo.
	 * @param array  $settings Impostazioni del widget.
	 * @param array  $grezzi   Impostazioni salvate (anche quelle di prima).
	 * @return string
	 */
	private static function etichette_nascoste( $form, $settings, $grezzi = array() ) {
		$voci = array();
		if ( ! empty( $settings['form_fields'] ) && is_array( $settings['form_fields'] ) ) {
			foreach ( $settings['form_fields'] as $voce ) {
				if ( ! empty( $voce['field_key'] ) ) {
					$voci[ $voce['field_key'] ] = $voce;
				}
			}
		}

		$campi = '';
		foreach ( array( 'nome', 'email', 'messaggio' ) as $chiave ) {
			$nome = isset( $voci[ $chiave ]['field_label'] ) ? trim( (string) $voci[ $chiave ]['field_label'] ) : '';
			if ( '' === $nome && isset( $grezzi[ 'field_' . $chiave . '_label' ] ) ) {
				$nome = trim( (string) $grezzi[ 'field_' . $chiave . '_label' ] );
			}
			if ( '' !== $nome ) {
				$campi .= '<input type="hidden" name="vbeb_ri_lbl[' . $chiave . ']" value="' . esc_attr( $nome ) . '" />';
			}
		}

		// VBEB 7.479.0: anche il testo del Titolo, per l'oggetto dell'e-mail.
		$titolo = isset( $settings['title_text'] ) ? trim( (string) $settings['title_text'] ) : '';
		if ( '' !== $titolo ) {
			$campi .= '<input type="hidden" name="vbeb_ri_subj" value="' . esc_attr( $titolo ) . '" />';
		}

		// VBEB 7.488.0: il testo del messaggio dopo l'invio (vuoto: quello di
		// VikBooking). Il campo c'e' sempre: dice anche che il messaggio
		// viene da questo widget, per il suo stile (vbeb_reqinfo_messaggio()).
		$avviso = isset( $settings['notice_text'] ) ? trim( (string) $settings['notice_text'] ) : '';
		$campi .= '<input type="hidden" name="vbeb_ri_ok" value="' . esc_attr( $avviso ) . '" />';

		return preg_replace( '/(<form\b[^>]*>)/', '${1}' . str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), $campi ), $form, 1 );
	}

	private static function campi( $form, $settings, $grezzi = array() ) {
		// VBEB 7.457.0: le impostazioni stanno nell'elenco "form_fields" (un
		// elemento per campo, riconosciuto da "field_key"). Se un testo e'
		// vuoto, si guarda il valore delle impostazioni di prima
		// (field_<campo>_label/_placeholder), rimasto nei dati salvati.
		$voci = array();
		if ( ! empty( $settings['form_fields'] ) && is_array( $settings['form_fields'] ) ) {
			foreach ( $settings['form_fields'] as $voce ) {
				if ( ! empty( $voce['field_key'] ) ) {
					$voci[ $voce['field_key'] ] = $voce;
				}
			}
		}

		foreach ( self::campi_modulo() as $chiave => $campo ) {
			$voce  = isset( $voci[ $chiave ] ) ? $voci[ $chiave ] : array();
			$nome  = isset( $voce['field_label'] ) ? trim( (string) $voce['field_label'] ) : '';
			$segno = isset( $voce['field_placeholder'] ) ? trim( (string) $voce['field_placeholder'] ) : '';
			if ( '' === $nome && isset( $grezzi[ 'field_' . $chiave . '_label' ] ) ) {
				$nome = trim( (string) $grezzi[ 'field_' . $chiave . '_label' ] );
			}
			if ( '' === $segno && isset( $grezzi[ 'field_' . $chiave . '_placeholder' ] ) ) {
				$segno = trim( (string) $grezzi[ 'field_' . $chiave . '_placeholder' ] );
			}
			// La classe dell'elemento dell'elenco sul contenitore del campo,
			// per la larghezza ("{{CURRENT_ITEM}}").
			$classe = ! empty( $voce['_id'] ) ? ' elementor-repeater-item-' . sanitize_html_class( $voce['_id'] ) : '';

			if ( 'termini' === $chiave ) {
				// VBEB 7.461.0: "Testo accettazione" (l'Etichetta di prima,
				// se era stata scritta, finche' questo e' vuoto).
				$accetta = isset( $voce['field_acceptance'] ) ? trim( (string) $voce['field_acceptance'] ) : '';
				if ( '' !== $accetta ) {
					$nome = $accetta;
				}
				if ( '' !== $classe ) {
					$form = str_replace( 'class="vbdialog-reqinfo-formentry vbdialog-reqinfo-formentry-ckbox"', 'class="vbdialog-reqinfo-formentry vbdialog-reqinfo-formentry-ckbox' . esc_attr( $classe ) . '"', $form );
				}
				// VBEB 7.463.0: con la pagina dei termini (link "<a id="vbof...">")
				// valgono "Testo link" (il testo del link) e "Testo prima del
				// link" (uno <span>, subito prima; VBEB 7.464.0: era <a>);
				// senza, "Testo accettazione" sull'etichetta
				// "<label id="vbof...">".
				$link_testo = isset( $voce['field_link_text'] ) ? trim( (string) $voce['field_link_text'] ) : '';
				$prima      = isset( $voce['field_link_before'] ) ? trim( (string) $voce['field_link_before'] ) : '';
				$sost       = function ( $t ) {
					return str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), $t );
				};

				if ( '' !== $link_testo || '' !== $prima ) {
					$form = preg_replace_callback(
						'/(<a\b[^>]*\bid="vbof\d*"[^>]*>)(.*?)(<\/a>)/s',
						function ( $m ) use ( $link_testo, $prima ) {
							$dentro = '' !== $link_testo ? esc_html( $link_testo ) : $m[2];
							$prima_html = '' !== $prima ? '<span class="vbeb-terms-before">' . nl2br( esc_html( $prima ) ) . '</span> ' : '';
							return $prima_html . $m[1] . $dentro . $m[3];
						},
						$form
					);
				}

				if ( '' !== $nome ) {
					$form = preg_replace( '/(<label\b[^>]*\bid="vbof\d*"[^>]*>).*?(<\/label>)/s', '${1}' . $sost( nl2br( esc_html( $nome ) ) ) . '${2}', $form );
				}
				continue;
			}

			$id = preg_quote( $campo['id'], '/' );

			if ( '' !== $classe ) {
				$form = preg_replace( '/(<div class="vbdialog-reqinfo-formentry)("\s*>\s*<label for="' . $id . '")/', '${1}' . esc_attr( $classe ) . '${2}', $form, 1 );
			}

			if ( '' !== $nome ) {
				$form = preg_replace( '/(<label for="' . $id . '"[^>]*>).*?(<\/label>)/s', '${1}' . str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), esc_html( $nome ) ) . '${2}', $form, 1 );
			}

			if ( '' !== $segno ) {
				$attributo = esc_attr( $segno );
				$form      = preg_replace_callback(
					'/<(input|textarea)\b[^>]*\bid="' . $id . '"[^>]*>/',
					function ( $m ) use ( $attributo ) {
						return preg_replace( '/\bplaceholder="[^"]*"/', 'placeholder="' . $attributo . '"', $m[0], 1 );
					},
					$form,
					1
				);
			}
		}

		return $form;
	}

	/*
	 * VBEB 7.511.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i suoi testi (dominio "elementor"), cosi' la
	 * traduzione arriva dai file di lingua di Elementor. Quelle che esistono
	 * solo in Elementor Pro (Segnaposto, Dimensione Input, Marcatura
	 * richiesta, Colore marcatura) e quelle solo di VBEB restano in italiano
	 * fino alla fase delle traduzioni.
	 */
	protected function register_controls() {

		$w = '{{WRAPPER}} .vbeb-reqinfoform';

		/* ================= CONTENUTO - TITOLO =================
		 * VBEB 7.264.0, su richiesta dell'utente con uno screenshot: tolta
		 * la sezione "Modulo" (nota e "Mostra il titolo di VikBooking"), al
		 * suo posto "Titolo" con il testo e il tag HTML, come il Titolo di
		 * Elementor senza il link. Il testo sostituisce solo la parte che
		 * VikBooking mette prima del nome della camera (vedi titolo()).
		 */
		$prima = self::prefisso_vikbooking();

		$this->start_controls_section( 'section_content_title', array( 'label' => esc_html__( 'Heading', 'vbeb' ), 'tab' => Controls_Manager::TAB_CONTENT ) );

		$this->add_control(
			'title_text',
			array(
				'label'       => esc_html__( 'Title', 'vbeb' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'placeholder' => '' !== $prima ? $prima : __( 'Request information for', 'vbeb' ),
			)
		);

		$this->add_control(
			'title_text_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				// VBEB free, su richiesta dell'utente: nota tradotta (dominio "vbeb").
				'raw'             => '' !== $prima
					? sprintf(
						/* translators: %s: testo di VikBooking prima del nome della camera. */
						esc_html__( 'The text entered here replaces only the part before the room name ("%s"): the room name always comes from VikBooking. Leave empty to use the VikBooking text.', 'vbeb' ),
						esc_html( $prima )
					)
					: esc_html__( 'The text entered here replaces only the part before the room name: the room name always comes from VikBooking. Leave empty to use the VikBooking text.', 'vbeb' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'     => esc_html__( 'HTML Tag', 'vbeb' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'separator' => 'before',
				'options'   => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
			)
		);

		$this->end_controls_section();

		/* ================= CONTENUTO - CAMPI =================
		 * VBEB 7.266.0, su richiesta dell'utente: nome (etichetta) e
		 * segnaposto dei tre campi del modulo. VikBooking li scrive nel
		 * template con le sue traduzioni (VBOROOMREQINFONAME, ...EMAIL,
		 * ...MESS, default_reqinfo.php, righe 43-54) e nel suo pannello non
		 * si possono cambiare. Vuoti, restano i testi di VikBooking; il resto
		 * del campo (nome del parametro, obbligatorieta', e-mail gia'
		 * compilata) non cambia. Vedi campi().
		 */
		$this->start_controls_section( 'section_content_fields', array( 'label' => esc_html__( 'Form Fields', 'vbeb' ), 'tab' => Controls_Manager::TAB_CONTENT ) ); // VBEB 7.456.0: era "Campi".

		/*
		 * VBEB 7.457.0, su richiesta dell'utente: i campi come quelli del
		 * widget Modulo di Elementor, un riquadro che si apre per campo
		 * (Nome completo, e-Mail, Messaggio, Termini e condizioni), senza
		 * aggiungere, copiare, eliminare o spostare. Dentro: Etichetta,
		 * Segnaposto (non per i termini) e Larghezza colonna (in percentuale,
		 * anche per tablet e mobile). I campi stanno affiancati quando le
		 * larghezze lo permettono (".vbdialog-reqinfo-formcont" flessibile,
		 * vedi render()).
		 */
		$elenco = new \Elementor\Repeater();

		$elenco->add_control( 'field_key', array( 'type' => Controls_Manager::HIDDEN, 'default' => '' ) );
		$elenco->add_control( 'field_title', array( 'type' => Controls_Manager::HIDDEN, 'default' => '' ) );

		$elenco->add_control(
			'field_label',
			array(
				'label'       => esc_html__( 'Label', 'vbeb' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'VikBooking text', 'vbeb' ),
				// VBEB 7.461.0: non per i termini, che hanno "Testo
				// accettazione".
				'condition'   => array( 'field_key!' => 'termini' ),
			)
		);

		// VBEB 7.463.0, su richiesta dell'utente: le impostazioni dei
		// termini dipendono da VikBooking. Senza pagina dei termini, "Testo
		// accettazione" (7.461.0: il testo accanto alla casella); con la
		// pagina, "Testo prima del link" e "Testo link" (vedi
		// termini_con_link() e campi()).
		if ( ! self::termini_con_link() ) {
			$elenco->add_control(
				'field_acceptance',
				array(
					'label'       => esc_html__( 'Acceptance Text', 'vbeb' ),
					'type'        => Controls_Manager::TEXTAREA,
					'default'     => '',
					'placeholder' => esc_html__( 'VikBooking text', 'vbeb' ),
					'condition'   => array( 'field_key' => 'termini' ),
				)
			);
		} else {
			$elenco->add_control(
				'field_link_before',
				array(
					'label'     => esc_html__( 'Text Before the Link', 'vbeb' ),
					'type'      => Controls_Manager::TEXTAREA,
					'default'   => '',
					'condition' => array( 'field_key' => 'termini' ),
				)
			);

			$elenco->add_control(
				'field_link_text',
				array(
					'label'       => esc_html__( 'Link Text', 'vbeb' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => '',
					'label_block' => true,
					'placeholder' => esc_html__( 'VikBooking text', 'vbeb' ),
					'condition'   => array( 'field_key' => 'termini' ),
				)
			);
		}

		$elenco->add_control(
			'field_placeholder',
			array(
				'label'       => esc_html__( 'Placeholder', 'vbeb' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'VikBooking text', 'vbeb' ),
				'condition'   => array( 'field_key!' => 'termini' ),
			)
		);

		// Toglie dalla larghezza la sua parte dello spazio fra le colonne
		// (--vbeb-ri-gap), cosi' i campi la cui somma fa 100% stanno sulla
		// stessa riga.
		$elenco->add_responsive_control(
			'field_width',
			array(
				'label'      => esc_html__( 'Column Width', 'vbeb' ) . ' (%)', // VBEB 7.475.0: con l'unita' nel nome.
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array( '%' => array( 'min' => 10, 'max' => 100 ) ),
				'default'    => array( 'unit' => '%', 'size' => 100 ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'flex: 0 0 calc({{SIZE}}% - var(--vbeb-ri-gap, 10px) * (100 - {{SIZE}}) / 100); max-width: calc({{SIZE}}% - var(--vbeb-ri-gap, 10px) * (100 - {{SIZE}}) / 100);',
				),
			)
		);

		// VBEB 7.460.0, su richiesta dell'utente: "Righe" del messaggio
		// (VBEB 7.467.0: dopo "Larghezza colonna")
		// (l'altezza della casella di testo), 5 di base come prima. Vedi
		// render().
		$elenco->add_control(
			'field_rows',
			array(
				'label'     => esc_html__( 'Rows', 'vbeb' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5,
				'min'       => 1,
				'max'       => 30,
				'condition' => array( 'field_key' => 'messaggio' ),
			)
		);

		$predefiniti = array();
		foreach ( self::campi_modulo() as $chiave => $campo ) {
			$originale     = self::traduzione( $campo['testo'], $campo['titolo'] );
			$predefiniti[] = array(
				'field_key'   => $chiave,
				'field_title' => '' !== $originale ? $originale : $campo['titolo'],
			);
		}

		$this->add_control(
			'form_fields',
			array(
				'type'         => Controls_Manager::REPEATER,
				'fields'       => $elenco->get_controls(),
				'default'      => $predefiniti,
				// VBEB 7.458.0, su richiesta dell'utente: il nome del riquadro e'
				// l'Etichetta scelta, come nel widget Modulo di Elementor; vuota,
				// il testo di VikBooking.
				// VBEB 7.461.0: "Termini e condizioni" ha sempre questo nome.
				// VBEB free, segnalato dall'utente: il nome e' tradotto (dominio
				// "vbeb"), prima era scritto in italiano.
				'title_field'  => "{{{ 'termini' === field_key ? '" . esc_js( __( 'Terms and Conditions', 'vbeb' ) ) . "' : ( field_label ? field_label : field_title ) }}}",
				// VBEB 7.459.0: classe per l'icona "apri" nel pannello (vedi
				// vbeb.php, foglio dell'editor).
				'classes'      => 'vbeb-fields-list',
				'item_actions' => array(
					'add'       => false,
					'duplicate' => false,
					'remove'    => false,
					'sort'      => false,
				),
			)
		);

		// VBEB 7.466.0, su richiesta dell'utente: tolta la nota sotto i
		// riquadri dei campi (trascritta nella lista "note widget").

		/*
		 * VBEB 7.468.0, su richiesta dell'utente: come nel widget Modulo di
		 * Elementor, "Dimensione Input" (le misure di Elementor,
		 * ".elementor-field-textual.elementor-size-*": dimensione del testo,
		 * altezza minima, spaziatura interna, raggio), "Etichetta" (mostra o
		 * nasconde le etichette di Nome, e-Mail e Messaggio) e "Marcatura
		 * richiesta" (l'asterisco rosso dopo le etichette dei campi
		 * obbligatori: tutti e tre). Interruttori Si/No.
		 */
		$campi_testo = $w . ' .vbdialog-reqinfo-formentry input[type="text"], ' . $w . ' .vbdialog-reqinfo-formentry input[type="email"], ' . $w . ' .vbdialog-reqinfo-formentry textarea';
		$etich_campi = $w . ' .vbdialog-reqinfo-formentry:not(.vbdialog-reqinfo-formentry-ckbox):not(.vbdialog-reqinfo-formentry-captcha):not(.vbdialog-reqinfo-formsubmit) > label';

		$this->add_control(
			'input_size',
			array(
				'label'                => esc_html__( 'Input Size', 'vbeb' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'sm',
				'separator'            => 'before',
				'options'              => array(
					'xs' => esc_html__( 'Extra Small', 'vbeb' ),
					'sm' => esc_html__( 'Small', 'vbeb' ),
					'md' => esc_html__( 'Medium', 'vbeb' ),
					'lg' => esc_html__( 'Large', 'vbeb' ),
					'xl' => esc_html__( 'Extra Large', 'vbeb' ),
				),
				'selectors_dictionary' => array(
					'xs' => 'font-size: 13px; min-height: 33px; padding: 4px 12px; border-radius: 2px;',
					'sm' => 'font-size: 15px; min-height: 40px; padding: 5px 14px; border-radius: 3px;',
					'md' => 'font-size: 16px; min-height: 47px; padding: 6px 16px; border-radius: 4px;',
					'lg' => 'font-size: 18px; min-height: 59px; padding: 7px 20px; border-radius: 5px;',
					'xl' => 'font-size: 20px; min-height: 72px; padding: 8px 24px; border-radius: 6px;',
				),
				'selectors'            => array( $campi_testo => '{{VALUE}}' ),
			)
		);

		$this->add_control(
			'show_labels',
			array(
				'label'        => esc_html__( 'Label', 'vbeb' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb' ),
				'label_off'    => esc_html__( 'No', 'vbeb' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'show_labels_off',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'condition' => array( 'show_labels!' => 'yes' ),
				'selectors' => array( $etich_campi => 'display: none;' ),
			)
		);

		$this->add_control(
			'mark_required',
			array(
				'label'        => esc_html__( 'Required Mark', 'vbeb' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb' ),
				'label_off'    => esc_html__( 'No', 'vbeb' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array( 'show_labels' => 'yes' ),
			)
		);

		$this->add_control(
			'mark_required_on',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'condition' => array(
					'show_labels'   => 'yes',
					'mark_required' => 'yes',
				),
				'selectors' => array( $etich_campi . '::after' => 'content: "*"; color: red; padding-left: .2em;' ),
			)
		);

		$this->end_controls_section();

		/* ================= CONTENUTO - MESSAGGIO INVIATO =================
		 * VBEB 7.488.0, su richiesta dell'utente: il messaggio che VikBooking
		 * mostra in cima alla pagina dopo l'invio della richiesta (messaggio
		 * di sistema "VBOROOMREQINFOSENTOK"). Vale solo per quel messaggio:
		 * vedi vbeb_reqinfo_messaggio() nel file principale.
		 */
		$this->start_controls_section( 'section_content_notice', array( 'label' => esc_html__( 'Message', 'vbeb' ), 'tab' => Controls_Manager::TAB_CONTENT ) ); // VBEB 7.491.0: era "Messaggio inviato".

		$this->add_control(
			'notice_text',
			array(
				'label'       => esc_html__( 'Message Sent Successfully', 'vbeb' ), // VBEB 7.489.0: era "Testo del messaggio".
				'type'        => Controls_Manager::TEXT, // VBEB 7.490.0: era un'area di testo.
				'label_block' => true,
				'placeholder' => class_exists( 'JText' ) ? (string) JText::_( 'VBOROOMREQINFOSENTOK' ) : '',
			)
		);

		// VBEB 7.666.0, su richiesta dell'utente: larghezza massima e posizione
		// del riquadro del messaggio. VikBooking lo stampa in cima alla pagina,
		// fuori dai contenitori di Elementor, largo il 100% dell'area del
		// contenuto (".notice", vikbooking_styles.css, riga 11542): con
		// "Elementor larghezza piena" e' largo quanto la finestra.
		$avviso_riquadro = '.notice:has(> p > .vbeb-reqinfo-msg)';

		$this->add_responsive_control(
			'notice_max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 100, 'max' => 2000 ),
					'%'  => array( 'min' => 10, 'max' => 100 ),
					'vw' => array( 'min' => 10, 'max' => 100 ),
				),
				'separator'  => 'before',
				'selectors'  => array(
					$avviso_riquadro => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'notice_position',
			array(
				'label'                => esc_html__( 'Position', 'vbeb' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'   => array( 'title' => esc_html__( 'Left', 'vbeb' ), 'icon' => 'eicon-h-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'vbeb' ), 'icon' => 'eicon-h-align-center' ),
					'right'  => array( 'title' => esc_html__( 'Right', 'vbeb' ), 'icon' => 'eicon-h-align-right' ),
				),
				'selectors_dictionary' => array(
					'left'   => 'margin-left: 5px; margin-right: auto;',
					'center' => 'margin-left: auto; margin-right: auto;',
					'right'  => 'margin-left: auto; margin-right: 5px;',
				),
				'selectors'            => array(
					$avviso_riquadro => '{{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - TITOLO ================= */
		$this->start_controls_section(
			'section_style_title',
			array(
				'label'     => esc_html__( 'Heading', 'vbeb' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.263.0, su richiesta dell'utente con uno screenshot: le
		// impostazioni del Titolo di Elementor (come "VBEB Camera - Nome");
		// tolto "Spazio sotto".
		$titolo = $w . ' .vbeb-reqinfoform-title';

		$this->add_responsive_control(
			'title_align',
			array(
				'label'     => esc_html__( 'Alignment', 'vbeb' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array( 'title' => esc_html__( 'Left', 'vbeb' ), 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => esc_html__( 'Center', 'vbeb' ), 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => esc_html__( 'Right', 'vbeb' ), 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => esc_html__( 'Justified', 'vbeb' ), 'icon' => 'eicon-text-align-justify' ),
				),
				'selectors' => array( $titolo => 'text-align: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'title_typography',
				'selector'  => $titolo,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => 'title_text_stroke',
				'selector' => $titolo,
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'title_text_shadow',
				'selector' => $titolo,
			)
		);

		$this->add_control(
			'title_blend_mode',
			array(
				'label'     => esc_html__( 'Blend Mode', 'vbeb' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''            => esc_html__( 'Normal', 'vbeb' ),
					'multiply'    => esc_html__( 'Multiply', 'vbeb' ),
					'screen'      => esc_html__( 'Screen', 'vbeb' ),
					'overlay'     => esc_html__( 'Overlay', 'vbeb' ),
					'darken'      => esc_html__( 'Darken', 'vbeb' ),
					'lighten'     => esc_html__( 'Lighten', 'vbeb' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'vbeb' ),
					'saturation'  => esc_html__( 'Saturation', 'vbeb' ),
					'color'       => esc_html__( 'Color', 'vbeb' ),
					'difference'  => esc_html__( 'Difference', 'vbeb' ),
					'exclusion'   => esc_html__( 'Exclusion', 'vbeb' ),
					'hue'         => esc_html__( 'Hue', 'vbeb' ),
					'luminosity'  => esc_html__( 'Luminosity', 'vbeb' ), // VBEB 7.511.0: in fondo, come in Elementor.
				),
				'selectors' => array( $titolo => 'mix-blend-mode: {{VALUE}};' ),
			)
		);

		// VBEB 7.482.0, su richiesta dell'utente: tolte le schede
		// Normale/Hover (e il colore al passaggio del mouse); resta il
		// colore del testo, stesso identificativo. VBEB free, su richiesta
		// dell'utente: tolta la linea di separazione sopra.
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $titolo => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - MODULO =================
		 * VBEB 7.470.0, su richiesta dell'utente con uno screenshot: solo le
		 * impostazioni della sezione "Modulo" del widget Modulo di Elementor.
		 * Tolti Rientro, Colore di sfondo, bordo, raggio e ombra (Normale e
		 * Hover) del riquadro: fuori dal popup resta l'aspetto del riquadro
		 * di VikBooking. Spaziatura colonne e righe fra i campi
		 * (".vbdialog-reqinfo-formcont" flessibile, vedi render()), poi la
		 * sottosezione "Etichetta".
		 */
		$this->start_controls_section( 'section_style_form', array( 'label' => esc_html__( 'Form', 'vbeb' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		// Lo spazio fra le colonne passa con --vbeb-ri-gap, che usano anche
		// le larghezze dei campi ("Larghezza colonna").
		$this->add_responsive_control(
			'form_column_gap',
			array(
				'label'      => esc_html__( 'Columns Gap', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'selectors'  => array( $w => '--vbeb-ri-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		// VikBooking distanzia i campi con un margine sotto (10px, 15px la
		// casella dei termini, vikbooking_styles.css, righe 4255-4260): qui
		// diventa lo spazio fra le righe del contenitore.
		$this->add_responsive_control(
			'form_row_gap',
			array(
				'label'      => esc_html__( 'Rows Gap', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'selectors'  => array(
					$w . ' .vbdialog-reqinfo-formcont' => 'row-gap: {{SIZE}}{{UNIT}};',
					$w . ' .vbdialog-reqinfo-formcont > .vbdialog-reqinfo-formentry' => 'margin-bottom: 0;',
				),
			)
		);

		/* ---------- MODULO > ETICHETTA ---------- */
		$this->add_control(
			'labels_heading',
			array(
				'label'     => esc_html__( 'Label', 'vbeb' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$etichette = $w . ' .vbdialog-reqinfo-formentry:not(.vbdialog-reqinfo-formentry-ckbox) > label';

		// VBEB 7.265.0: un vero gap fra etichetta e campo (il contenitore
		// ".vbdialog-reqinfo-formentry" diventa una colonna flex, l'etichetta
		// perde i 3px di VikBooking, riga 4217). Solo per i campi con
		// etichetta sopra. Vuoto, resta l'impaginazione di VikBooking.
		$this->add_responsive_control(
			'labels_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array(
					$w . ' .vbdialog-reqinfo-formentry:not(.vbdialog-reqinfo-formentry-ckbox):not(.vbdialog-reqinfo-formentry-captcha):not(.vbdialog-reqinfo-formsubmit)' => 'display: flex; flex-direction: column; gap: {{SIZE}}{{UNIT}};',
					$etichette => 'margin-bottom: 0;',
				),
			)
		);

		$this->add_control(
			'labels_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.473.0, su richiesta dell'utente: anche "Testo
				// accettazione" (l'etichetta della casella dei termini) e
				// "Testo prima del link"; il link dei termini no (colore dei
				// link). La Tipografia li comprende gia'.
				'selectors' => array( $etichette . ', ' . $w . ' .vbdialog-reqinfo-formentry-ckbox label, ' . $w . ' .vbdialog-reqinfo-formentry-ckbox .vbeb-terms-before' => 'color: {{VALUE}};' ),
			)
		);

		// L'asterisco di "Marcatura richiesta" (pannello Contenuto).
		$this->add_control(
			'labels_mark_color',
			array(
				'label'     => esc_html__( 'Mark Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				// Stesso selettore della regola dell'asterisco (pesa uguale e
				// viene dopo, quindi vince sul rosso di base).
				'selectors' => array( $w . ' .vbdialog-reqinfo-formentry:not(.vbdialog-reqinfo-formentry-ckbox):not(.vbdialog-reqinfo-formentry-captcha):not(.vbdialog-reqinfo-formsubmit) > label::after' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'labels_typography',
				// VBEB 7.272.0: anche il testo di "Termini e condizioni"
				// (etichetta o link); VBEB 7.464.0: anche il "Testo prima del
				// link".
				// VBEB 7.474.0, su richiesta dell'utente: non il link dei
				// termini, che prende tipografia e colore dalle impostazioni
				// del sito di Elementor (Tipografia > Link).
				'selector' => $etichette . ', ' . $w . ' .vbdialog-reqinfo-formentry-ckbox label, ' . $w . ' .vbdialog-reqinfo-formentry-ckbox .vbeb-terms-before',
			)
		);

		$this->end_controls_section();

		/* ================= STILE - CAMPO =================
		 * VBEB 7.472.0, su richiesta dell'utente con uno screenshot: era
		 * "Campi"; solo le impostazioni della sezione "Campo" del widget
		 * Modulo di Elementor: Colore del testo, Tipografia, Colore di
		 * sfondo, Colore del bordo, Spessore bordi, Raggio del bordo. Tolte
		 * le schede Normale/Hover con bordo, raggio e ombra. Colore e
		 * spessore del bordo hanno gli identificativi dei campi del vecchio
		 * gruppo "Bordo" (fields_border_color, fields_border_width), il
		 * raggio quello di prima (fields_radius): i valori restano. Linea
		 * sempre continua. VikBooking da' ai campi un bordo di 1px
		 * (vikbooking_styles.css, righe 4221 e 4236): il pannello pesa di
		 * piu'.
		 */
		$this->start_controls_section( 'section_style_fields', array( 'label' => esc_html__( 'Field', 'vbeb' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$campi = $w . ' .vbdialog-reqinfo-formentry input[type="text"], ' . $w . ' .vbdialog-reqinfo-formentry input[type="email"], ' . $w . ' .vbdialog-reqinfo-formentry textarea';

		$this->add_control(
			'fields_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $campi => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'fields_typography',
				'selector' => $campi,
			)
		);

		$this->add_control(
			'fields_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( $campi => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'fields_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( $campi => 'border-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'fields_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', 'vw', 'custom' ),
				'selectors'  => array( $campi => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'fields_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array( $campi => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		/* ---------- CAMPO > SEGNAPOSTO ----------
		 * VBEB 7.476.0, su richiesta dell'utente: il testo dei segnaposto
		 * ("::placeholder") di Nome, e-Mail e Messaggio. Il widget Modulo di
		 * Elementor non lo prevede: senza, il segnaposto prende la
		 * tipografia del campo e il colore del browser o del tema.
		 */
		$segnaposto = $w . ' .vbdialog-reqinfo-formentry input[type="text"]::placeholder, ' . $w . ' .vbdialog-reqinfo-formentry input[type="email"]::placeholder, ' . $w . ' .vbdialog-reqinfo-formentry textarea::placeholder';

		$this->add_control(
			'fields_placeholder_heading',
			array(
				'label'     => esc_html__( 'Placeholder', 'vbeb' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'fields_placeholder_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $segnaposto => 'color: {{VALUE}}; opacity: 1;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'fields_placeholder_typography',
				'selector' => $segnaposto,
			)
		);

		$this->end_controls_section();

		// VBEB 7.272.0, su richiesta dell'utente: tolta la sezione "Termini e
		// condizioni". La tipografia arriva da "Etichette"; il colore, essendo
		// un link, dalle impostazioni globali di Elementor.

		/* ================= STILE - PULSANTE ================= */
		$this->start_controls_section( 'section_style_button', array( 'label' => esc_html__( '"Send" Button', 'vbeb' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		// VikBooking: ".vbdialog-reqinfo button.btn" e ".vbo-pref-color-btn"
		// (vikbooking_styles.css, righe 4247 e 145), senza "!important": il
		// selettore del pannello pesa di piu', anche con i colori globali.
		$pulsante = $w . ' .vbdialog-reqinfo-formsubmit button.btn';

		// VBEB 7.271.0, su richiesta dell'utente con uno screenshot: le
		// impostazioni del Pulsante di Elementor, senza "Posizione" (come
		// "VBEB Camera - Pulsante informazioni", 7.258.0). Restano i nomi di
		// prima dove il controllo e' lo stesso (tipografia, colori del
		// testo, colore del bordo in hover, bordo, raggio, ombra, rientro);
		// i vecchi colori di sfondo semplici non ci sono piu'.
		$pulsante_hover = $pulsante . ':hover, ' . $pulsante . ':focus';

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => $pulsante,
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'button_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'vbeb' ),
				'selector' => $pulsante,
			)
		);

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb' ) ) );

		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $pulsante => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'button_background',
				'types'          => array( 'classic', 'gradient' ),
				'exclude'        => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector'       => $pulsante,
				'fields_options' => array(
					'background' => array( 'label' => esc_html__( 'Background Type', 'vbeb' ) ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_shadow',
				'selector' => $pulsante,
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb' ) ) );

		$this->add_control(
			'button_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $pulsante_hover => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'button_background_hover',
				'types'          => array( 'classic', 'gradient' ),
				'exclude'        => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector'       => $pulsante_hover,
				'fields_options' => array(
					'background' => array( 'label' => esc_html__( 'Background Type', 'vbeb' ) ),
				),
			)
		);

		$this->add_control(
			'button_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'button_border_border!' => '' ),
				'selectors' => array( $pulsante_hover => 'border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'button_border',
				'selector'  => $pulsante,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'button_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( $pulsante => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'separator'  => 'before',
				'selectors'  => array( $pulsante => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - MESSAGGIO INVIATO =================
		 * VBEB 7.488.0, su richiesta dell'utente. Il riquadro e' quello dei
		 * messaggi di sistema di VikBooking (libraries/html/system/messages.php:
		 * div.notice.notice-success con dentro un <p>), riconosciuto dallo
		 * <span class="vbeb-reqinfo-msg"> attorno al testo. I selettori non
		 * hanno {{WRAPPER}}: il messaggio e' fuori dal widget, in cima alla
		 * pagina. Sfondo e colore del testo battono i "!important" di
		 * VikBooking (vikbooking_styles.css, riga 11553). Si vede solo sul
		 * sito, dopo un invio.
		 */
		$avviso_box  = '.notice:has(> p > .vbeb-reqinfo-msg)';
		$avviso_p    = '.notice > p:has(> .vbeb-reqinfo-msg)';
		$avviso_text = '.notice > p > .vbeb-reqinfo-msg';

		$this->start_controls_section( 'section_style_notice', array( 'label' => esc_html__( 'Message', 'vbeb' ), 'tab' => Controls_Manager::TAB_STYLE ) ); // VBEB 7.492.0: era "Messaggio inviato".

		$this->add_responsive_control(
			'notice_align',
			array(
				'label'     => esc_html__( 'Alignment', 'vbeb' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array( 'title' => esc_html__( 'Left', 'vbeb' ), 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => esc_html__( 'Center', 'vbeb' ), 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => esc_html__( 'Right', 'vbeb' ), 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => esc_html__( 'Justified', 'vbeb' ), 'icon' => 'eicon-text-align-justify' ),
				),
				'selectors' => array( $avviso_box => 'text-align: {{VALUE}};' ),
			)
		);

		// VBEB 7.493.0: ordine come da screenshot dell'utente (Tipografia
		// prima del Colore del testo).
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'notice_typography',
				'selector' => $avviso_p,
			)
		);

		$this->add_control(
			'notice_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $avviso_text => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'notice_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.495.0: una variabile, usata dalla regola fissa del
				// widget con "!important" (vedi render()). Con un colore
				// globale Elementor riscrive tutto il valore dopo i ":" con
				// "var(--e-global-color-...)" e toglie "!important"
				// (core/files/css/base.php, riga 368): lo sfondo forzato di
				// VikBooking vinceva.
				'selectors' => array( $avviso_box => '--vbeb-notice-bg: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'notice_border',
				'selector'       => $avviso_box,
				'fields_options' => array(
					'border' => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-style: {{VALUE}} !important;' ),
					),
					'width'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
					),
					'color'  => array(
						// VBEB 7.495.0: variabile, come lo sfondo (colori globali).
						'selectors' => array( '{{SELECTOR}}' => '--vbeb-notice-bc: {{VALUE}};' ),
					),
				),
			)
		);

		$this->add_responsive_control(
			'notice_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array( $avviso_box => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'notice_box_shadow',
				'selector' => $avviso_box,
			)
		);

		$this->add_responsive_control(
			'notice_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb' ), // VBEB 7.511.0: "Rientro" di Elementor (confermato dall'utente).
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'separator'  => 'before',
				'selectors'  => array( $avviso_box => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		// VBEB 7.493.0: tolto "Margine" (non nello screenshot dell'utente).
		$this->end_controls_section();
	}

	protected function render() {
		$in_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$html      = class_exists( 'VBEB_Room_Context' ) && VBEB_Room_Context::has_context() ? VBEB_Room_Context::$reqinfo_html : '';

		if ( empty( $html ) && $in_editor && function_exists( 'vbeb_html_camera_anteprima' ) ) {
			$html = vbeb_html_camera_anteprima( 'reqinfo' );
		}

		$parti = self::parti( $html );

		if ( '' === $parti['form'] ) {
			if ( $in_editor ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'Room - Information Form: the real form will appear in the popup of the information button on the room page.', 'vbeb' ) . '</div>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( $in_editor ) {
			if ( function_exists( 'vbeb_stili_vikbooking_editor' ) ) {
				echo vbeb_stili_vikbooking_editor(); // phpcs:ignore -- foglio di VikBooking e variabili dei suoi colori.
			}

			// Nell'editor l'invio porterebbe via dall'anteprima.
			$parti['form'] = preg_replace( '/<form\b/', '<form onsubmit="return false;"', $parti['form'], 1 );
		}

		// Fuori dal popup la classe del riquadro di VikBooking, senza la sua
		// posizione fissa, cosi' il modulo ha lo stesso aspetto. Nel popup il
		// riquadro c'e' gia'.
		$fuori = ! self::$nel_popup;

		static $css = false;
		if ( ! $css || vbeb_editor_ripete() ) {
			$css = true;
			echo '<style>'
				. '.vbeb-reqinfoform.vbeb-reqinfoform-fuori{position:static;transform:none;left:auto;top:auto;width:auto;max-width:none;min-height:0;overflow:visible;z-index:auto;}'
				// VBEB 7.477.0, su richiesta dell'utente: senza l'aspetto del
				// riquadro di VikBooking (".vbdialog-reqinfo",
				// vikbooking_styles.css, riga 4197: spaziatura interna 25px,
				// sfondo bianco, raggio 10px, ombra) e senza margini, ne' sul
				// widget ne' sotto i campi (".vbdialog-reqinfo-formcont",
				// margine sotto 20px, riga 4194). Nel popup il riquadro di
				// VikBooking resta attorno al template.
				. '.vbeb-reqinfoform.vbeb-reqinfoform-fuori{margin:0;padding:0;border:0;border-radius:0;box-shadow:none;background:transparent;}'
				. '.vbeb-reqinfoform .vbdialog-reqinfo-formcont{margin:0;}'
				. '.vbeb-reqinfoform .vbeb-reqinfoform-title{margin-top:0;}'
				// VBEB 7.457.0: campi affiancabili ("Larghezza colonna"). Gli
				// elementi senza larghezza propria (reCAPTCHA, pulsante)
				// restano a tutta riga.
				. '.vbeb-reqinfoform .vbdialog-reqinfo-formcont{display:flex;flex-wrap:wrap;column-gap:var(--vbeb-ri-gap,10px);}'
				. '.vbeb-reqinfoform .vbdialog-reqinfo-formcont > .vbdialog-reqinfo-formentry{flex:0 0 100%;max-width:100%;min-width:0;box-sizing:border-box;}'
				// VBEB 7.465.0: casella dei termini prima del testo.
				. '.vbeb-reqinfoform .vbdialog-reqinfo-formentry-ckbox input[type="checkbox"]{margin:0 4px 0 0;vertical-align:middle;}'
				// VBEB 7.495.0: sfondo e colore del bordo del messaggio dopo
				// l'invio (Stile > Messaggio), anche con i colori globali,
				// contro i "!important" di VikBooking (vikbooking_styles.css,
				// riga 11553). Senza impostazione: i colori di VikBooking.
				. '.notice:has(> p > .vbeb-reqinfo-msg){background-color:var(--vbeb-notice-bg,var(--vbo-white-bg-color)) !important;border-color:var(--vbeb-notice-bc,var(--vbo-lightblue-color)) !important;}'
				// VBEB 7.666.0: blocco a tutta riga invece di "inline-block" con
				// "width: 100%" piu' 5px di margine, che sbordava a destra; cosi'
				// anche la posizione con i margini automatici funziona.
				. '.notice:has(> p > .vbeb-reqinfo-msg){display:block;width:auto;box-sizing:border-box;}'
				. '</style>';
		}

		// VBEB 7.254.0, su richiesta dell'utente: nell'editor l'anteprima sta
		// dentro un contenitore con la classe della scheda camera di
		// VikBooking ("vbo-listing-details-wrap", default_listing.php, riga
		// 40). Sul sito quel contenitore avvolge gia' tutto il template, e le
		// regole di VikBooking che ne dipendono (vikbooking_styles.css, righe
		// 7960-8275) valgono; nell'editor mancava. Solo nell'editor, e per
		// ogni widget per conto suo.
		if ( $in_editor ) {
			echo '<div class="vbo-listing-details-wrap">';
		}

		echo '<div class="vbeb-reqinfoform' . ( $fuori ? ' vbdialog-reqinfo vbeb-reqinfoform-fuori' : '' ) . '">';

		// VBEB 7.266.0: nomi e segnaposto dei campi scelti nel pannello.
		$parti['form'] = self::campi( $parti['form'], $settings, (array) $this->get_data( 'settings' ) );

		// VBEB 7.478.0, su richiesta dell'utente: le etichette scelte vanno
		// anche nell'e-mail della richiesta. Campi nascosti nel modulo
		// ("vbeb_ri_lbl[nome|email|messaggio]"), letti al momento dell'invio
		// da vbeb_reqinfo_etichette_email() (vbeb.php).
		$parti['form'] = self::etichette_nascoste( $parti['form'], $settings, (array) $this->get_data( 'settings' ) );

		// VBEB 7.465.0, su richiesta dell'utente: la casella dei termini
		// prima del testo. VikBooking la mette dopo l'etichetta o il link
		// (default_reqinfo.php, righe 64 e 72): qui si sposta in testa al
		// suo contenitore.
		$parti['form'] = preg_replace_callback(
			'/(<div class="vbdialog-reqinfo-formentry vbdialog-reqinfo-formentry-ckbox[^"]*">)(.*?)(<input type="checkbox"[^>]*>)/s',
			function ( $m ) {
				return $m[1] . $m[3] . ' ' . $m[2];
			},
			$parti['form']
		);

		// VBEB 7.462.0, su richiesta dell'utente: il link dei termini apre la
		// pagina in una nuova scheda. VikBooking gli da' la classe
		// "vbomodalframe", a cui collega il suo popup (fancybox,
		// roomdetails/tmpl/default.php, riga 50), che pero' si apre dietro il
		// riquadro del modulo. Senza la classe resta il suo target="_blank".
		$parti['form'] = preg_replace_callback(
			'/<a\b[^>]*\bid="vbof\d*"[^>]*>/',
			function ( $m ) {
				$tag = preg_replace( '/\s*class="vbomodalframe"/', '', $m[0] );
				$tag = preg_replace( '/\s*rel="[^"]*"/', '', $tag );
				return preg_replace( '/^<a\b/', '<a rel="noopener"', $tag );
			},
			$parti['form']
		);

		// VBEB 7.268.0, su richiesta dell'utente: il messaggio alto 5 righe
		// di base (VikBooking non da' "rows", e il browser ne usa 2).
		// "Altezza del messaggio" nel pannello Stile, se impostata, vince.
		// VBEB 7.460.0: il numero di righe da "Campi modulo" > Messaggio >
		// "Righe" (5 se manca).
		$vbeb_righe = 5;
		if ( ! empty( $settings['form_fields'] ) && is_array( $settings['form_fields'] ) ) {
			foreach ( $settings['form_fields'] as $vbeb_voce ) {
				if ( isset( $vbeb_voce['field_key'] ) && 'messaggio' === $vbeb_voce['field_key'] && ! empty( $vbeb_voce['field_rows'] ) ) {
					$vbeb_righe = max( 1, (int) $vbeb_voce['field_rows'] );
				}
			}
		}
		$parti['form'] = preg_replace( '/<textarea\b(?![^>]*\brows=)([^>]*\bid="reqmess")/', '<textarea rows="' . $vbeb_righe . '"$1', $parti['form'], 1 );

		// VBEB 7.468.0, su richiesta dell'utente: il messaggio obbligatorio
		// anche nel modulo (VikBooking lo controlla solo dopo l'invio,
		// controller.php, riga 1737): il browser avvisa prima di inviare.
		$parti['form'] = preg_replace( '/<textarea\b(?![^>]*\brequired)([^>]*\bid="reqmess"[^>]*)>/', '<textarea$1 required>', $parti['form'], 1 );

		$titolo = self::titolo( $parti['titolo'], isset( $settings['title_text'] ) ? (string) $settings['title_text'] : '' );

		if ( '' !== $titolo ) {
			$tag = isset( $settings['title_tag'] ) && in_array( $settings['title_tag'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ), true ) ? $settings['title_tag'] : 'h3';

			echo '<' . $tag . ' class="vbeb-reqinfoform-title">' . $titolo . '</' . $tag . '>'; // phpcs:ignore -- nome della camera da VikBooking, testo del pannello con esc_html().
		}

		echo $parti['form']; // phpcs:ignore -- HTML reale gia' generato da VikBooking.
		echo '</div>';

		if ( $in_editor ) {
			echo '</div>';
		}
	}
}
