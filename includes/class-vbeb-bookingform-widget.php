<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;

/**
 * Widget Elementor "VikBooking Form Prenotazione". Non ricostruisce il
 * form: incapsula l'HTML reale generato da VikBooking stesso (catturato
 * dall'override file tramite $this->loadTemplate('bookingform'), l'unico
 * punto con accesso sicuro a validazione/invio della prenotazione), e
 * aggiunge controlli di stile Elementor mirati sulle classi CSS reali
 * del form (verificate nel codice sorgente di VikBooking, non indovinate).
 */
class VBEB_Bookingform_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_bookingform_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Booking Form', 'vbeb-bridge-for-vikbooking-and-elementor' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'prenotazione', 'booking', 'form', 'camera' );
	}

	/*
	 * VBEB 7.512.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i suoi testi (dominio "elementor"), cosi' la
	 * traduzione arriva dai file di lingua di Elementor. Le altre (Ospiti,
	 * Altezza campi, Mostra sfondo, Colore icone modulo, Pulsante Prenota,
	 * ...) sono solo di VBEB e restano in italiano fino alla fase delle
	 * traduzioni.
	 */
	protected function register_controls() {

		/* ================= CONTENUTO - TITOLO ================= */
		$this->start_controls_section(
			'section_content_title',
			array(
				'label' => esc_html__( 'Heading', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// VBEB 7.362.0, su richiesta dell'utente: "Mostra titolo", come nel
		// widget Calendario (7.347.0). Acceso di default (come prima);
		// spento, il titolo di VikBooking viene tolto dal modulo.
		$this->add_control(
			'show_title',
			array(
				'label'        => esc_html__( 'Show Title', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				// VBEB 7.605.0, su richiesta dell'utente: con "Prenotazione
				// abilitata" spenta in VikBooking il titolo ("Seleziona una
				// data di check-in e check-out") parte nascosto, perche' il
				// modulo con le date non c'e'. Si puo' comunque riaccendere.
				'default'      => ( function_exists( 'vbeb_vikbooking_booking_enabled' ) && ! vbeb_vikbooking_booking_enabled() ) ? '' : 'yes',
			)
		);

		$this->add_control(
			'title_text',
			array(
				'label'       => esc_html__( 'Title', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'placeholder' => esc_html__( 'Leave empty to use the original VikBooking text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'default'     => '',
				'condition'   => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h4',
				'condition' => array( 'show_title' => 'yes' ),
				'options' => array(
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

		/* ================= CONTENUTO - OSPITI =================
		 * VBEB 7.428.0, su richiesta dell'utente: "Layout blocco" spostato
		 * qui dalla sezione di stile "Ospiti" (sottosezione "Icona"); stesso
		 * identificativo, valore invariato.
		 */
		$this->start_controls_section(
			'section_content_guests',
			array(
				'label' => esc_html__( 'Guests', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'capacity_block_layout',
			array(
				'label'   => esc_html__( 'Layout', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'row',
				'options' => array(
					'row'    => array( 'title' => esc_html__( 'Horizontal', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-ellipsis-h' ),
					'column' => array( 'title' => esc_html__( 'Vertical', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-editor-list-ul' ),
				),
				// VBEB 7.430.0: oltre alla direzione, la posizione del
				// divisore (sezione di stile "Ospiti", "Elenco").
				'selectors_dictionary' => array(
					'row'    => 'flex-direction: row; --vbeb-cap-a-top: 50%; --vbeb-cap-a-bottom: auto; --vbeb-cap-a-left: auto; --vbeb-cap-a-right: calc(var(--vbeb-cap-gap, 10px) / -2); --vbeb-cap-a-w: 0; --vbeb-cap-a-h: 100%; --vbeb-cap-a-bl: var(--vbeb-cap-dwt, 1px) var(--vbeb-cap-ds, solid) var(--vbeb-cap-dc, #ddd); --vbeb-cap-a-bt: 0; --vbeb-cap-a-tr: translate(50%, -50%);',
					'column' => 'flex-direction: column; --vbeb-cap-a-top: auto; --vbeb-cap-a-bottom: calc(var(--vbeb-cap-gap, 10px) / -2); --vbeb-cap-a-left: 0; --vbeb-cap-a-right: 0; --vbeb-cap-a-w: var(--vbeb-cap-dw, 100%); --vbeb-cap-a-h: 0; --vbeb-cap-a-bl: 0; --vbeb-cap-a-bt: var(--vbeb-cap-dwt, 1px) var(--vbeb-cap-ds, solid) var(--vbeb-cap-dc, #ddd); --vbeb-cap-a-tr: translateY(50%);',
				),
				'selectors' => array(
					'{{WRAPPER}} .vbmaxminpeopleroom' => '{{VALUE}}',
				),
			)
		);

		// VBEB 7.644.0, su richiesta dell'utente: "Visualizza" e "Forma" delle
		// icone degli ospiti, come il widget "Riquadro icona" di Elementor.
		// Predefinito: solo l'icona, senza sfondo (prende il posto di "Mostra
		// sfondo"); Impilato: forma piena (l'aspetto di VikBooking, di
		// partenza); Incorniciato: solo il bordo, del colore dell'icona.
		$this->add_control(
			'capacity_icon_view',
			array(
				'label'                => esc_html__( 'View', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'separator'            => 'before',
				'default'              => 'stacked',
				'options'              => array(
					'default' => esc_html__( 'Default', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'stacked' => esc_html__( 'Stacked', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'framed'  => esc_html__( 'Framed', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				// VBEB 7.645.0: colori da "Colore principale" (--vbeb-cap-p) e
				// "Colore secondario" (--vbeb-cap-s) di Stile > Ospiti, con lo
				// stesso ruolo che hanno in Elementor; vuoti, i colori di
				// VikBooking (cerchio "--vbo-pref-textcolor", icona
				// "--vbo-contrast-text-color").
				'selectors_dictionary' => array(
					'default' => 'background: none; border: 0; color: var(--vbeb-cap-p, var(--vbo-pref-textcolor)); fill: var(--vbeb-cap-p, var(--vbo-pref-textcolor));',
					'stacked' => 'border: 0; background: var(--vbeb-cap-p, var(--vbo-pref-textcolor)); color: var(--vbeb-cap-s, var(--vbo-contrast-text-color)); fill: var(--vbeb-cap-s, var(--vbo-contrast-text-color));',
					'framed'  => 'background: var(--vbeb-cap-s, transparent); color: var(--vbeb-cap-p, var(--vbo-pref-textcolor)); fill: var(--vbeb-cap-p, var(--vbo-pref-textcolor)); border: 2px solid currentColor; box-sizing: border-box; display: inline-flex; align-items: center; justify-content: center;',
				),
				'selectors'            => array(
					'{{WRAPPER}} .vbmaxadultsdet .vbmaximgdet i, {{WRAPPER}} .vbmaxchildrendet .vbmaximgdet i, {{WRAPPER}} .vbmaxtotdet .vbmaximgdet i' => '{{VALUE}}',
				),
				// Con "Predefinito" l'icona perde anche larghezza e altezza fisse
				// (vedi render()): serve ridisegnare il widget.
				'render_type'          => 'template',
			)
		);

		$this->add_control(
			'capacity_icon_shape',
			array(
				'label'                => esc_html__( 'Shape', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'circle',
				'options'              => array(
					'square'  => esc_html__( 'Square', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'rounded' => esc_html__( 'Rounded', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'circle'  => esc_html__( 'Circle', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				// Come Elementor: arrotondato = 10%.
				'selectors_dictionary' => array(
					'square'  => '0',
					'rounded' => '10%',
					'circle'  => '50%',
				),
				'selectors'            => array(
					'{{WRAPPER}} .vbmaxadultsdet .vbmaximgdet i, {{WRAPPER}} .vbmaxchildrendet .vbmaximgdet i, {{WRAPPER}} .vbmaxtotdet .vbmaximgdet i' => 'border-radius: {{VALUE}};',
				),
				'condition'            => array( 'capacity_icon_view!' => 'default' ),
			)
		);

		$this->end_controls_section();

		/* ================= CONTENUTO - CAMPO MODULO =================
		 * VBEB 7.436.0, su richiesta dell'utente: larghezza (responsive) di
		 * ogni campo del modulo. Check-in e check-out sono figli diretti di
		 * ".vb-search-inner" (flessibile, a capo); Camere e il contenitore
		 * di Adulti/Bambini stanno in ".vbo-search-num-racblock", che con un
		 * valore impostato diventa flessibile anch'esso. "Adulti e bambini"
		 * agisce sul div che raccoglie le righe ospiti
		 * (".vbo-search-num-aduchild-block": una riga per camera, ognuna con
		 * i suoi Adulti e Bambini). I selettori pesano piu' di quelli del
		 * layout a colonna di VikBooking (".vbo-listing-details-wrap
		 * .vbo-listing-details-divsearch ...", flex-basis 100%). Con
		 * flex-shrink i campi affiancati si restringono quanto serve per lo
		 * spazio fra le colonne. Vuoti, nulla cambia.
		 */
		$this->start_controls_section(
			'section_content_fields_width',
			array(
				'label' => esc_html__( 'Form Fields', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.455.0: era "Campo modulo".
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$vbeb_inner = '{{WRAPPER}} .vbdivsearch .vb-search-inner';
		$vbeb_rac   = $vbeb_inner . ' > .vbo-search-num-racblock';
		// VBEB 7.438.0: come le colonne del widget Modulo di Elementor,
		// due campi al 50% stanno sulla stessa riga qualunque sia la
		// "Spaziatura colonne". Un contenitore flessibile va a capo in base
		// alla larghezza piena dei campi, quindi 50% + 50% + spazio non ci
		// sta: ogni campo toglie dalla sua larghezza la sua parte di spazio,
		// "spazio x (100 - larghezza) / 100" (la somma dei campi di una
		// riga piu' gli spazi fa esattamente 100%). Lo spazio arriva da
		// "Spaziatura colonne" con la variabile --vbeb-col-gap (0 se vuota).
		// Solo in percentuale, come in Elementor. Margini laterali di
		// VikBooking azzerati.
		$vbeb_larg  = 'flex: 0 0 calc({{SIZE}}% - var(--vbeb-col-gap, 0px) * (100 - {{SIZE}}) / 100); width: calc({{SIZE}}% - var(--vbeb-col-gap, 0px) * (100 - {{SIZE}}) / 100); max-width: 100%; min-width: 0; float: none; margin-left: 0; margin-right: 0; box-sizing: border-box;';

		$vbeb_nome_ospiti = esc_html__( 'Adults and Children', 'vbeb-bridge-for-vikbooking-and-elementor' );
		if ( class_exists( 'VikBooking' ) && method_exists( 'VikBooking', 'showChildrenFront' ) && ! VikBooking::showChildrenFront() ) {
			$vbeb_etichetta   = class_exists( 'VBOFactory' ) ? VBOFactory::getConfig()->get( 'guests_label', 'adults' ) : 'adults';
			$vbeb_nome_ospiti = ! strcasecmp( (string) $vbeb_etichetta, 'guests' ) ? esc_html__( 'Guests', 'vbeb-bridge-for-vikbooking-and-elementor' ) : esc_html__( 'Adults', 'vbeb-bridge-for-vikbooking-and-elementor' );
		}

		$vbeb_campi = array(
			'field_width_checkin'  => array( esc_html__( 'Check-in date', 'vbeb-bridge-for-vikbooking-and-elementor' ), array( $vbeb_inner . ' > .vbo-search-inpblock-checkin' => $vbeb_larg ) ),
			'field_width_checkout' => array( esc_html__( 'Check-out date', 'vbeb-bridge-for-vikbooking-and-elementor' ), array( $vbeb_inner . ' > .vbo-search-inpblock-checkout' => $vbeb_larg ) ),
			// Compare solo se nella camera e' attiva l'opzione "prenota piu'
			// unita'" e la camera ha piu' di un'unita'.
			'field_width_rooms'    => array( esc_html__( 'Rooms', 'vbeb-bridge-for-vikbooking-and-elementor' ), array(
				$vbeb_rac                                   => 'display: flex; flex-wrap: wrap;',
				$vbeb_rac . ' > .vbo-search-num-rooms'      => $vbeb_larg,
			) ),
			// VBEB 7.664.0, su richiesta dell'utente: il nome segue VikBooking
			// (Globale > Configurazione): con "Mostra numero di bambini" attivo
			// "Adulti e bambini"; spento, il modulo ha il solo campo Adulti, e
			// l'impostazione si chiama come la sua "Etichetta ospiti" ("Adulti"
			// o "Ospiti"). Stesso identificativo: il valore salvato resta.
			'field_width_guests'   => array( $vbeb_nome_ospiti, array(
				$vbeb_rac                                   => 'display: flex; flex-wrap: wrap;',
				$vbeb_rac . ' > .vbo-search-num-aduchild-block' => $vbeb_larg,
			) ),
		);

		foreach ( $vbeb_campi as $vbeb_id => $vbeb_campo ) {
			$this->add_responsive_control(
				$vbeb_id,
				array(
					// VBEB 7.440.0, su richiesta dell'utente: unita' nel nome,
					// "(%)", perche' con una sola unita' Elementor non la mostra.
					'label'      => $vbeb_campo[0] . ' (%)',
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( '%' ),
					'range'      => array(
						'%' => array( 'min' => 0, 'max' => 100 ),
					),
					'selectors'  => $vbeb_campo[1],
				)
			);
		}

		// VBEB 7.441.0, su richiesta dell'utente: "Altezza campi" come
		// "Dimensione input" del widget Modulo di Elementor, con le sue
		// misure (".elementor-field-textual.elementor-size-sm/md/lg":
		// altezza minima 40, 47, 59px). Qui e' l'altezza di tutti i campi,
		// bordo compreso, passata con la variabile --vbeb-field-h alle
		// regole fisse del render() (prima fissa a 54px).
		$this->add_responsive_control(
			'fields_height',
			array(
				'label'                => esc_html__( 'Field Height', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'md',
				'separator'            => 'before',
				'options'              => array(
					'sm' => esc_html__( 'Small', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'md' => esc_html__( 'Medium', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'lg' => esc_html__( 'Large', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'selectors_dictionary' => array(
					'sm' => '40px',
					'md' => '47px',
					'lg' => '59px',
				),
				'selectors'            => array(
					'{{WRAPPER}}' => '--vbeb-field-h: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - TITOLO =================
		 * VBEB 7.413.0, su richiesta dell'utente: era "Titolo Form"; visibile
		 * solo con "Mostra titolo" acceso.
		 */
		$this->start_controls_section(
			'section_style_title',
			array(
				'label'     => esc_html__( 'Heading', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		// VBEB 7.414.0, su richiesta dell'utente: tolte le impostazioni di
		// prima (Colore titolo, Tipografia, Padding, Margine, Bordo, Raggio
		// del bordo) e messe quelle della sezione "Titolo" del widget
		// Titolo di Elementor, senza Normale/Hover. Stessi identificativi
		// per tipografia e colore: quei valori restano.
		$titolo = '{{WRAPPER}} .vbeb-form-title';

		$this->add_responsive_control(
			'title_align',
			array(
				'label'     => esc_html__( 'Alignment', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'separator' => 'after',
				'options'   => array(
					'left'    => array( 'title' => esc_html__( 'Left', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => esc_html__( 'Right', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => esc_html__( 'Justified', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-justify' ),
				),
				// display:block perche' l'allineamento valga anche con i tag
				// in linea (span).
				'selectors' => array( $titolo => 'text-align: {{VALUE}}; display: block;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => $titolo,
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
				'label'     => esc_html__( 'Blend Mode', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''            => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'multiply'    => esc_html__( 'Multiply', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'screen'      => esc_html__( 'Screen', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'overlay'     => esc_html__( 'Overlay', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'darken'      => esc_html__( 'Darken', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'lighten'     => esc_html__( 'Lighten', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'saturation'  => esc_html__( 'Saturation', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'color'       => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'difference'  => esc_html__( 'Difference', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'exclusion'   => esc_html__( 'Exclusion', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'hue'         => esc_html__( 'Hue', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'luminosity'  => esc_html__( 'Luminosity', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.512.0: in fondo, come in Elementor.
				),
				'selectors' => array( $titolo => 'mix-blend-mode: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $titolo => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - CAPIENZA ================= */
		$this->start_controls_section(
			'section_style_capacity',
			array(
				'label' => esc_html__( 'Guests', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.425.0: era "Capienza".
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		/*
		 * VBEB 7.430.0, su richiesta dell'utente: sottosezioni "Elenco" e
		 * "Icona" come le sezioni omonime del widget Elenco icone di
		 * Elementor (senza "Colonne"). Le impostazioni che c'erano sono
		 * spostate e rinominate, stessi identificativi: Spaziatura icone ->
		 * Spazio intermedio, Allineamento orizzontale blocco ->
		 * Allineamento, Colore icone capienza -> Colore, Dimensione icone ->
		 * Dimensione, Allineamento verticale (icona-testo) -> Allineamento
		 * verticale. Nuove: Divisore (Stile, Spessore, Larghezza, Colore),
		 * Spaziatura, Allineamento orizzontale, Regola la posizione
		 * verticale. Restano Sfondo icone (cerchio) e il suo colore.
		 */
		$this->add_control(
			'heading_capacity_list',
			array(
				'label' => esc_html__( 'List', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'capacity_block_fix',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'selectors' => array(
					// VBEB: il contenitore delle tre voci (Adulti/Bambini/
					// Totale persone) e' di default inline-block con i figli
					// float:left - serve renderlo flex perche' i controlli
					// qui sotto (Layout blocco, Allineamento orizzontale
					// blocco, Spaziatura blocco) abbiano effetto.
					'{{WRAPPER}} .vbmaxminpeopleroom' => 'display: flex; flex-wrap: wrap;',
					// VBEB: azzeriamo il margine fisso di 5px che VikBooking
					// applica di suo tra le voci, sostituito dal controllo
					// di spaziatura qui sotto (altrimenti si sommerebbero).
					// VBEB 7.435.0, su richiesta dell'utente: anche senza il padding
					// di 10px di VikBooking sulle voci e senza il suo margine di
					// 10px fra icona e testo, cosi' "Spazio intermedio" e
					// "Spaziatura" (Icona) partono da 0. Questa regola viene
					// prima di "Spaziatura", che quindi la sostituisce.
					'{{WRAPPER}} .vbmaxminpeopleroom > div' => 'margin: 0; padding: 0; position: relative;',
					'{{WRAPPER}} .vbmaxminpeopleroom .vbmaximgdet i' => 'margin: 0;',
				),
			)
		);

		$this->add_responsive_control(
			'capacity_block_gap',
			array(
				'label'      => esc_html__( 'Space Between', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 80 ),
					'em' => array( 'min' => 0, 'max' => 6 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'selectors'  => array(
					'{{WRAPPER}} .vbmaxminpeopleroom' => 'gap: {{SIZE}}{{UNIT}}; --vbeb-cap-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'capacity_block_align',
			array(
				'label'   => esc_html__( 'Alignment', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'flex-start' => array( 'title' => esc_html__( 'Left', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-left' ),
					'center'     => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-center' ),
					'flex-end'   => array( 'title' => esc_html__( 'Right', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-right' ),
				),
				'selectors' => array(
					// VBEB 7.430.0: anche align-items, per il layout in verticale.
					'{{WRAPPER}} .vbmaxminpeopleroom' => 'justify-content: {{VALUE}}; align-items: {{VALUE}};',
				),
			)
		);

		// Divisore: una linea a meta' dello spazio intermedio, verticale con
		// il layout orizzontale e orizzontale con quello verticale (lo
		// decide "Layout blocco", nel pannello Contenuto, con le variabili
		// --vbeb-cap-a-*). Valori come variabili CSS sul blocco.
		$this->add_control(
			'capacity_divider',
			array(
				'label'        => esc_html__( 'Divider', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'Off', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'prefix_class' => 'vbeb-cap-divider-',
			)
		);

		$this->add_control(
			'capacity_divider_style',
			array(
				'label'     => esc_html__( 'Style', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'solid'  => esc_html__( 'Solid', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'double' => esc_html__( 'Double', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'dotted' => esc_html__( 'Dotted', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'dashed' => esc_html__( 'Dashed', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'condition' => array( 'capacity_divider' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .vbmaxminpeopleroom' => '--vbeb-cap-ds: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'capacity_divider_weight',
			array(
				'label'      => esc_html__( 'Weight', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 20 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1 ),
				'condition'  => array( 'capacity_divider' => 'yes' ),
				'selectors'  => array( '{{WRAPPER}} .vbmaxminpeopleroom' => '--vbeb-cap-dwt: {{SIZE}}{{UNIT}};' ),
			)
		);

		// Larghezza: vale con il layout verticale (linea orizzontale); con
		// quello orizzontale la linea e' alta quanto le voci.
		$this->add_responsive_control(
			'capacity_divider_width',
			array(
				'label'      => esc_html__( 'Width', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px', 'custom' ),
				'range'      => array( '%' => array( 'min' => 0, 'max' => 100 ) ),
				'default'    => array( 'unit' => '%' ),
				'condition'  => array( 'capacity_divider' => 'yes' ),
				'selectors'  => array( '{{WRAPPER}} .vbmaxminpeopleroom' => '--vbeb-cap-dw: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'capacity_divider_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ddd',
				'condition' => array( 'capacity_divider' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .vbmaxminpeopleroom' => '--vbeb-cap-dc: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'heading_capacity_icon',
			array(
				'label'     => esc_html__( 'Icon', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// VBEB 7.645.0, su richiesta dell'utente: al posto di "Colore" e
		// "Colore di sfondo" ("capacity_icon_color", "capacity_icon_bg_color"),
		// "Colore principale" e "Colore secondario" come il Riquadro icona di
		// Elementor. Principale: icona con Predefinito, forma con Impilato,
		// icona e bordo con Incorniciato. Secondario (non con Predefinito):
		// icona con Impilato, sfondo con Incorniciato.
		$this->add_control(
			'capacity_icon_primary_color',
			array(
				'label'     => esc_html__( 'Primary Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--vbeb-cap-p: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'capacity_icon_secondary_color',
			array(
				'label'     => esc_html__( 'Secondary Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'capacity_icon_view!' => 'default' ),
				'selectors' => array(
					'{{WRAPPER}}' => '--vbeb-cap-s: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'capacity_icon_size',
			array(
				'label'      => esc_html__( 'Size', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array( 'min' => 10, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 34 ),
				'selectors'  => array(
					// VBEB: VikBooking imposta di suo width/height/line-height
					// fissi a 34px/34px/32px sull'<i> (il cerchio) e font-size
					// a 16px (il simbolo dentro) - scaliamo tutto insieme
					// mantenendo la proporzione, cosi' il cerchio e il
					// simbolo crescono/rimpiccioliscono di pari passo.
					'{{WRAPPER}} .vbmaxadultsdet .vbmaximgdet i, {{WRAPPER}} .vbmaxchildrendet .vbmaximgdet i, {{WRAPPER}} .vbmaxtotdet .vbmaximgdet i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; line-height: calc({{SIZE}}{{UNIT}} - 2px); font-size: calc({{SIZE}}{{UNIT}} * 0.47);',
				),
			)
		);

		// VBEB 7.646.0, su richiesta dell'utente: "Rientro" (in Elementor
		// "Padding", Riquadro icona) fra Dimensione e Spaziatura, solo con
		// Impilato e Incorniciato: spazio fra il simbolo e il bordo della
		// forma, che cresce di conseguenza (la forma resta larga quanto la
		// Dimensione piu' il rientro sui due lati).
		$this->add_responsive_control(
			'capacity_icon_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'range'      => array(
					'px'  => array( 'max' => 50 ),
					'em'  => array( 'min' => 0, 'max' => 5 ),
					'rem' => array( 'min' => 0, 'max' => 5 ),
				),
				'condition'  => array( 'capacity_icon_view!' => 'default' ),
				'selectors'  => array(
					'{{WRAPPER}} .vbmaxadultsdet .vbmaximgdet i, {{WRAPPER}} .vbmaxchildrendet .vbmaximgdet i, {{WRAPPER}} .vbmaxtotdet .vbmaximgdet i' => 'padding: {{SIZE}}{{UNIT}}; box-sizing: content-box;',
				),
			)
		);

		// VikBooking: ".vbmaxminpeopleroom .vbmaximgdet i { margin: 0 10px 0
		// 0 }"; qui un selettore piu' pesante.
		$this->add_responsive_control(
			'capacity_icon_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vbmaxminpeopleroom .vbmaximgdet i' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// VBEB 7.648.0, su richiesta dell'utente: come il Riquadro icona di
		// Elementor, "Spessore bordi" solo con Incorniciato e "Raggio del
		// bordo" con Impilato e Incorniciato (prende il posto della Forma
		// quando e' impostato).
		$this->add_responsive_control(
			'capacity_icon_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', 'vw', 'custom' ),
				'condition'  => array( 'capacity_icon_view' => 'framed' ),
				'selectors'  => array(
					'{{WRAPPER}} .vbmaxadultsdet .vbmaximgdet i, {{WRAPPER}} .vbmaxchildrendet .vbmaximgdet i, {{WRAPPER}} .vbmaxtotdet .vbmaximgdet i' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'capacity_icon_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'condition'  => array( 'capacity_icon_view!' => 'default' ),
				'selectors'  => array(
					'{{WRAPPER}} .vbmaxadultsdet .vbmaximgdet i, {{WRAPPER}} .vbmaxchildrendet .vbmaximgdet i, {{WRAPPER}} .vbmaxtotdet .vbmaximgdet i' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'capacity_flex_fix',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'selectors' => array(
					// VBEB: il contenitore reale (.vbmaxadultsdet/-childrendet/
					// -totdet) e' di default inline-block, non flessibile -
					// serve renderlo inline-flex perche' i due controlli di
					// allineamento verticale qui sotto abbia effetto.
					'{{WRAPPER}} .vbmaxadultsdet, {{WRAPPER}} .vbmaxchildrendet, {{WRAPPER}} .vbmaxtotdet' => 'display: inline-flex;',
					// VBEB: VikBooking imposta di suo un margine superiore
					// fisso di 10px sul testo (margin: 10px 5px 0 0) - lo
					// azzeriamo, altrimenti "Alto" non risulta davvero in
					// alto ma spostato in basso di quei 10px.
					// VBEB 7.435.0: niente margine a destra del numero, che si
					// sommava allo spazio fra le voci.
					'{{WRAPPER}} .vbmaxlabeldet' => 'margin: 0 5px 0 0;',
					'{{WRAPPER}} .vbmaxnumberdet' => 'margin: 0;',
				),
			)
		);

		// VBEB 7.643.0, su richiesta dell'utente: tolto "Allineamento
		// orizzontale" ("capacity_icon_align") dalla sezione Stile > Ospiti.

		$this->add_responsive_control(
			'capacity_vertical_align',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'separator' => 'before',
				'options'   => array(
					'flex-start' => array( 'title' => esc_html__( 'Start', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-top' ),
					'center'     => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-middle' ),
					'flex-end'   => array( 'title' => esc_html__( 'End', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-bottom' ),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .vbmaxadultsdet, {{WRAPPER}} .vbmaxchildrendet, {{WRAPPER}} .vbmaxtotdet' => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'capacity_icon_offset',
			array(
				'label'      => esc_html__( 'Adjust Vertical Position', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -15, 'max' => 15 ),
					'em' => array( 'min' => -1, 'max' => 1, 'step' => 0.1 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 0 ),
				'selectors'  => array(
					'{{WRAPPER}} .vbmaxminpeopleroom .vbmaximgdet' => 'position: relative; top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// VBEB 7.432.0: ordine Elenco, Icona, Testo (su richiesta dell'utente).
		// VBEB 7.429.0, su richiesta dell'utente: sottosezione "Testo" con
		// Tipografia, Ombra del testo (nuova) e Colore, come nello
		// screenshot; tipografia e colore sono quelli di prima (stessi
		// identificativi), solo riordinati e rinominati.
		$this->add_control(
			'heading_capacity_text',
			array(
				'label'     => esc_html__( 'Text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'capacity_typography',
				'label'    => esc_html__( 'Typography', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'selector' => '{{WRAPPER}} .vbmaxlabeldet, {{WRAPPER}} .vbmaxnumberdet',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'capacity_text_shadow',
				'selector' => '{{WRAPPER}} .vbmaxlabeldet, {{WRAPPER}} .vbmaxnumberdet',
			)
		);

		$this->add_control(
			'capacity_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbmaxlabeldet, {{WRAPPER}} .vbmaxnumberdet' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - CONTENITORE FORM ================= */
		$this->start_controls_section(
			'section_style_container',
			array(
				'label' => esc_html__( 'Form', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.415.0: era "Contenitore Modulo".
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		/*
		 * VBEB 7.418.0, su richiesta dell'utente: "Spaziatura colonne" e
		 * "Spaziatura righe" come nel widget Modulo di Elementor. Il modulo
		 * di VikBooking distanzia i campi con margini fissi (vikbooking_
		 * styles.css: ".vbo-search-inpblock { margin: 0 10px 8px 0 }",
		 * ".vbo-search-num-adults-entry { margin: 0 20px 0 0 }", ecc.):
		 * impostando un valore, quei margini si azzerano e la distanza
		 * diventa il "gap" del contenitore flessibile ".vb-search-inner"
		 * (e della riga adulti/bambini). Vuoti, resta tutto come prima.
		 */
		$riga_inner = '{{WRAPPER}} .vbdivsearch .vb-search-inner';

		$this->add_responsive_control(
			'form_column_gap',
			array(
				'label'      => esc_html__( 'Columns Gap', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array(
					$riga_inner => 'column-gap: {{SIZE}}{{UNIT}}; --vbeb-col-gap: {{SIZE}}{{UNIT}};',
					$riga_inner . ' > .vbo-search-inpblock, ' . $riga_inner . ' > .vbo-search-num-racblock, ' . $riga_inner . ' > .vbo-search-submit, ' . $riga_inner . ' .vbo-search-num-rooms' => 'margin-left: 0; margin-right: 0;',
					$riga_inner . ' > .vbo-search-num-racblock, {{WRAPPER}} .vbo-search-num-aduchild-entry' => 'column-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vbo-search-num-aduchild-entry .vbo-search-num-adults-entry' => 'margin-right: 0;',
				),
			)
		);

		$this->add_responsive_control(
			'form_row_gap',
			array(
				'label'      => esc_html__( 'Rows Gap', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array(
					$riga_inner => 'row-gap: {{SIZE}}{{UNIT}};',
					$riga_inner . ' > .vbo-search-inpblock, ' . $riga_inner . ' > .vbo-search-num-racblock, ' . $riga_inner . ' > .vbo-search-submit' => 'margin-top: 0; margin-bottom: 0;',
					// Piu' camere: una riga adulti/bambini per camera.
					// Selettori piu' pesanti di quelli del layout a colonna di
					// VikBooking (".vbo-listing-details-wrap ... .vbo-search-num-
					// aduchild-block { margin-top: 10px }" e simili).
					$riga_inner . ' .vbo-search-num-racblock .vbo-search-num-aduchild-block' => 'display: flex; flex-direction: column; row-gap: {{SIZE}}{{UNIT}}; margin-top: 0;',
					$riga_inner . ' .vbo-search-num-racblock .vbo-search-num-aduchild-block .vbo-search-num-aduchild-entry' => 'margin-bottom: 0;',
					// Layout a colonna (dettaglio camera): fra il menu Camere,
					// se c'e', e adulti/bambini.
					$riga_inner . ' .vbo-search-num-racblock .vbo-search-num-rooms:has(select) + .vbo-search-num-aduchild-block' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// VBEB 7.423.0, su richiesta dell'utente: tolta la sottosezione
		// "Contenitore"; le sue impostazioni stanno qui, fra le spaziature
		// e "Etichetta".
		// VBEB 7.421.0, su richiesta dell'utente: tolti Sfondo, Padding e il
		// vecchio Raggio del bordo; al loro posto Tipo di bordo, Raggio del
		// bordo (quattro lati) e Ombra riquadro, come il contenitore di
		// Elementor (senza Normale/Hover).
		// VBEB 7.422.0, su richiesta dell'utente: di nuovo Colore di sfondo
		// e Padding. Il colore ha l'identificativo del colore del vecchio
		// gruppo Sfondo (container_background_color), il padding quello di
		// prima: i valori salvati tornano.
		$this->add_control(
			'container_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .vbdivsearch' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .vbdivsearch',
			)
		);

		$this->add_responsive_control(
			'container_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .vbdivsearch' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .vbdivsearch',
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%', 'vw', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .vbdivsearch' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		/* ================= STILE - MODULO > ETICHETTA =================
		 * VBEB 7.418.0, su richiesta dell'utente: come le etichette del
		 * widget Modulo di Elementor: Spaziatura (nuova), Colore del testo,
		 * Tipografia; tolto Allineamento (le etichette restano a sinistra,
		 * anche quelle di adulti e bambini che VikBooking centra). Stessi
		 * identificativi per colore e tipografia.
		 * VBEB 7.349.0: anche l'etichetta "Camere" (".vbo-search-num-rooms >
		 * label"), che VikBooking mostra solo se nella camera e' attiva
		 * l'opzione "prenota piu' unita'" e la camera ha piu' di un'unita'
		 * (default_bookingform.php, righe 132-143).
		 */
		// VBEB 7.437.0, su richiesta dell'utente: Colore del testo e
		// Tipografia anche per "Camera 1", "Camera 2"... (span
		// ".vbo-search-roomnum", solo con il campo Camere). Non per
		// "Spaziatura", che resta sulle sole etichette.
		$etichette = '{{WRAPPER}} .vbo-search-inpblock label, {{WRAPPER}} .vbo-search-num-adults-entry-label, {{WRAPPER}} .vbo-search-num-children-entry-label, {{WRAPPER}} .vbo-search-num-rooms > label, {{WRAPPER}} .vbo-search-roomnum';

		$this->add_control(
			'heading_labels',
			array(
				'label'     => esc_html__( 'Label', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Selettore piu' pesante delle regole di VikBooking sul margine
		// sotto le etichette (".vbdivsearch .vb-search-inner > div label").
		$this->add_responsive_control(
			'label_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vbdivsearch .vb-search-inner div > label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $etichette => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'selector' => $etichette,
			)
		);

		// VBEB 7.665.0, su richiesta dell'utente: sottosezione "Politica
		// ospiti ammessi", il testo che VikBooking stampa sotto il modulo
		// (".vbo-guests-allowed-policy", default_bookingform.php) quando
		// "Mostra numero di bambini" e' spento e il campo e' compilato.
		$this->add_control(
			'heading_guests_policy',
			array(
				'label'     => esc_html__( 'Guests allowed policy', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'guests_policy_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbo-guests-allowed-policy, {{WRAPPER}} .vbo-guests-allowed-policy *' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'guests_policy_typography',
				'selector' => '{{WRAPPER}} .vbo-guests-allowed-policy, {{WRAPPER}} .vbo-guests-allowed-policy *',
			)
		);

		$this->end_controls_section();

		/* ================= STILE - CAMPO =================
		 * VBEB 7.419.0, su richiesta dell'utente: era "Campi Date e Ospiti";
		 * impostazioni come la sezione "Campo" del widget Modulo di
		 * Elementor: Colore del testo, Tipografia, Colore di sfondo, Colore
		 * del bordo, Spessore bordi, Raggio del bordo. Restano la nota sui
		 * menu a tendina e "Colore icone modulo".
		 */
		$campi_testo = '{{WRAPPER}} #checkindate, {{WRAPPER}} #checkoutdate, {{WRAPPER}} select[name="adults[]"], {{WRAPPER}} select[name="children[]"], {{WRAPPER}} select[name="roomsnum"]';
		// VBEB 7.642.0: per i campi data, selettore piu' pesante di quello di
		// VikBooking (".vbo-listing-details-wrap .vbo-listing-details-divsearch
		// .vbo-search-inpblock .input-group:not([type=checkbox])",
		// vikbooking_styles.css, riga 8217: bordo grigio e sfondo bianco).
		// Sul sito vinceva l'ordine (il foglio di Elementor arriva dopo), ma
		// nell'editor il foglio di VikBooking e' stampato dentro il widget, quindi
		// dopo quello di Elementor: le modifiche a bordo e sfondo non si vedevano.
		$campi_box   = '{{WRAPPER}} .vbdivsearch .vbo-search-inpblock-checkin .input-group.input-group:not([type="checkbox"]), {{WRAPPER}} .vbdivsearch .vbo-search-inpblock-checkout .input-group.input-group:not([type="checkbox"]), {{WRAPPER}} select[name="adults[]"], {{WRAPPER}} select[name="children[]"], {{WRAPPER}} select[name="roomsnum"]';

		$this->start_controls_section(
			'section_style_fields',
			array(
				'label' => esc_html__( 'Field', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.444.0, su richiesta dell'utente: tolte le note della
		// sezione (trascritte nella lista "note widget").
		$this->add_control(
			'field_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $campi_testo => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'field_typography',
				'selector' => $campi_testo,
			)
		);

		$this->add_control(
			'field_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					// VBEB: per i campi data, il vero sfondo/bordo visibile
					// e' sul contenitore .input-group (che avvolge anche
					// l'icona del calendario), non sull'<input> stesso -
					// VikBooking blocca esplicitamente qualsiasi bordo
					// sull'input con "border: 0 !important". Per Adulti/
					// Bambini invece il <select> stesso e' l'elemento
					// visibile, quindi resta targettizzato direttamente.
					'{{WRAPPER}} .vbdivsearch .vbo-search-inpblock-checkin .input-group.input-group:not([type="checkbox"]), {{WRAPPER}} .vbdivsearch .vbo-search-inpblock-checkout .input-group.input-group:not([type="checkbox"]), {{WRAPPER}} select[name="adults[]"], {{WRAPPER}} select[name="children[]"], {{WRAPPER}} select[name="roomsnum"]' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Al posto del gruppo "Bordo": Colore del bordo e Spessore bordi
		// hanno gli stessi identificativi dei suoi campi (field_border_color,
		// field_border_width), quindi i valori salvati restano; lo stile e'
		// sempre a linea continua. Raggio del bordo ora a quattro lati.
		$this->add_control(
			'field_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( $campi_box => 'border-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'field_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', 'vw', 'custom' ),
				'selectors'  => array( $campi_box => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'field_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array( $campi_box => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'guests_field_appearance_fix',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'selectors' => array(
					// VBEB: rimuove l'aspetto nativo del browser (necessario
					// perche' bordo/sfondo/angoli siano davvero visibili su
					// un <select>). Applicato solo ai 2 <select> (Adulti/
					// Bambini): i campi data sono gia' <input> di testo,
					// non ne hanno bisogno. La freccia personalizzata (SVG)
					// viene generata a parte in render(), cosi' il suo
					// colore puo' essere controllato dal pannello.
					'{{WRAPPER}} select[name="adults[]"], {{WRAPPER}} select[name="children[]"], {{WRAPPER}} select[name="roomsnum"]' => '-webkit-appearance: none; -moz-appearance: none; appearance: none; background-repeat: no-repeat; background-position: right 8px center; background-size: 14px; padding-right: 28px;',
				),
			)
		);

		$this->add_control(
			'module_icons_color',
			array(
				'label'       => esc_html__( 'Form Icon Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'separator'   => 'before',
				'default'     => '#666666',
				'selectors'   => array(
					'{{WRAPPER}} .vbo-caltrigger' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - PULSANTE PRENOTA ================= */
		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => esc_html__( 'Book Button', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .vbdetbooksubmit',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'button_text_shadow',
				'selector' => '{{WRAPPER}} .vbdetbooksubmit',
			)
		);

		// VBEB 7.417.0, su richiesta dell'utente: le impostazioni del Pulsante
		// di Elementor (tranne "Posizione"). Normale: colore del testo, tipo
		// di sfondo (tinta unita o sfumatura), ombra; Hover: colore del
		// testo, tipo di sfondo, colore del bordo; poi bordo, raggio e
		// rientro per entrambi. Stessi identificativi dove il controllo e'
		// lo stesso (colori del testo, bordo, ombra, rientro); i vecchi
		// colori di sfondo semplici, il raggio a cursore e bordo, raggio e
		// ombra di hover non ci sono piu'.
		$pulsante       = '{{WRAPPER}} .vbdetbooksubmit';
		$pulsante_hover = '{{WRAPPER}} .vbdetbooksubmit:hover, {{WRAPPER}} .vbdetbooksubmit:focus';

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab( 'button_style_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $pulsante => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'           => 'button_background',
				'types'          => array( 'classic', 'gradient' ),
				'exclude'        => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector'       => $pulsante,
				'fields_options' => array(
					'background' => array( 'label' => esc_html__( 'Background Type', 'vbeb-bridge-for-vikbooking-and-elementor' ) ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => $pulsante,
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_style_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'button_text_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $pulsante_hover => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'           => 'button_background_hover',
				'types'          => array( 'classic', 'gradient' ),
				'exclude'        => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector'       => $pulsante_hover,
				'fields_options' => array(
					'background' => array( 'label' => esc_html__( 'Background Type', 'vbeb-bridge-for-vikbooking-and-elementor' ) ),
				),
			)
		);

		$this->add_control(
			'button_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
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
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array( $pulsante => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%', 'rem', 'vw', 'custom' ),
				'separator'  => 'before',
				'selectors'  => array( $pulsante => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// VBEB 7.220.0: la sezione "Calendario popup", su richiesta dell'utente
		// (vedi vbeb_calpop_sezione() in class-vbeb-calpopup-widget.php).
		if ( function_exists( 'vbeb_calpop_sezione' ) ) {
			// VBEB 7.444.0, su richiesta dell'utente: senza la nota sotto il
			// menu (trascritta nella lista "note widget").
			vbeb_calpop_sezione( $this, '' );
		}
	}

	protected function render() {
		$in_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$content   = VBEB_Room_Context::has_context() ? VBEB_Room_Context::$bookingform_html : '';

		// VBEB 7.246.0, su richiesta dell'utente: nell'editor il modulo vero
		// della camera di anteprima, disegnato da VikBooking stesso
		// (vbeb_html_camera_anteprima()), con il suo foglio di stile, le
		// variabili dei colori e le icone. L'invio e' disattivato: in
		// anteprima porterebbe via dall'editor.
		if ( empty( $content ) && $in_editor && function_exists( 'vbeb_html_camera_anteprima' ) ) {
			$content = vbeb_html_camera_anteprima( 'bookingform' );

			if ( '' !== $content ) {
				$content = str_replace( 'onsubmit="return vboValidateDates();"', 'onsubmit="return false;"', $content );

				if ( function_exists( 'vbeb_stili_vikbooking_editor' ) ) {
					echo vbeb_stili_vikbooking_editor(); // phpcs:ignore -- foglio di VikBooking e variabili dei suoi colori.
				}
				if ( function_exists( 'vbeb_icone_vikbooking_editor' ) ) {
					echo vbeb_icone_vikbooking_editor(); // phpcs:ignore -- fogli delle icone di VikBooking.
				}
			}
		}

		if ( empty( $content ) ) {
			if ( $in_editor ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'Room - Booking Form: the real form will appear on the front end of a room page.', 'vbeb-bridge-for-vikbooking-and-elementor' ) . '</div>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		// VBEB: sostituiamo il tag <h4> originale del titolo (generato da
		// VikBooking) con il tag HTML scelto e/o un testo personalizzato,
		// aggiungendo una classe dedicata "vbeb-form-title" cosi' i
		// controlli di stile funzionano sempre, indipendentemente dal tag.
		$tag = isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h4';
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'h4';
		}

		// VBEB 7.362.0: "Mostra titolo" spento, il titolo si toglie.
		$mostra_titolo = ! isset( $settings['show_title'] ) || 'yes' === $settings['show_title'];

		if ( ! $mostra_titolo ) {
			$content = preg_replace( '/<h4>.*?<\/h4>/is', '', $content, 1 );
		} elseif ( preg_match( '/<h4>(.*?)<\/h4>/is', $content, $m ) ) {
			$original_text = $m[1];
			$label = ( '' !== $settings['title_text'] ) ? $settings['title_text'] : $original_text;
			$replacement = '<' . $tag . ' class="vbeb-form-title">' . wp_kses_post( $label ) . '</' . $tag . '>';
			$content = preg_replace( '/<h4>.*?<\/h4>/is', $replacement, $content, 1 );
		}

		// VBEB 7.251.0, su richiesta dell'utente con uno screenshot: niente
		// margine sopra e sotto il modulo. VikBooking da'
		// ".vbo-seldates-cont { margin: 20px 0 }" (vikbooking_styles.css,
		// riga 2624); qui una classe in piu' pesa di piu'.
		static $vbeb_margine = false;
		if ( ! $vbeb_margine || vbeb_editor_ripete() ) {
			$vbeb_margine = true;
			// VBEB 7.252.0: anche il margine sotto il riquadro del modulo,
			// ".vbo-room-details-booking-wrapper .vbdivsearch { margin: 0 0
			// 10px }" (riga 2725), segnalato dall'utente.
			echo '<style>.vbeb-bookingform-wrap .vbo-seldates-cont{margin-top:0;margin-bottom:0;}.vbeb-bookingform-wrap .vbo-room-details-booking-wrapper .vbdivsearch{margin-bottom:0;}'
				// VBEB 7.411.0: niente margine sopra il titolo (Hello Elementor,
				// reset.css: "margin-block-start: .5rem" sui titoli).
				. ':where(.vbeb-bookingform-wrap) .vbeb-form-title{margin-top:0;}'
				// VBEB 7.445.0, su richiesta dell'utente: niente linea sotto il
				// titolo (".vbo-seldates-cont h4 { border-bottom: 1px solid }"
				// di VikBooking, vikbooking_styles.css riga 2649).
				. '.vbeb-bookingform-wrap .vbeb-form-title{border-bottom:0;}'
				// VBEB 7.412.0: niente spazio sotto il modulo. VikBooking da'
				// ".vbo-room-details-booking-wrapper { display: inline-block }"
				// (vikbooking_styles.css, riga 2149): sulla linea del testo,
				// sopra lo spazio delle lettere che scendono. Allineato in
				// alto lo spazio sparisce; resta inline-block, che contiene il
				// riquadro del modulo (float:left).
				. '.vbeb-bookingform-wrap .vbo-room-details-booking-wrapper{vertical-align:top;}'
				// VBEB 7.418.0: etichette a sinistra, come nel widget Modulo
				// di Elementor (VikBooking centra quelle di adulti e bambini).
				. '.vbeb-bookingform-wrap .vbo-search-num-adults-entry > label,.vbeb-bookingform-wrap .vbo-search-num-children-entry > label{text-align:left;}'
				// VBEB 7.436.0: Adulti e Bambini sempre a meta' della riga della
				// loro camera (VikBooking li fa crescere uguali solo nel layout a
				// colonna; nell'altro hanno la larghezza del contenuto). Se i
				// bambini non sono attivi, gli adulti prendono tutta la riga.
				. '.vbeb-bookingform-wrap .vbo-search-num-aduchild-entry > .vbo-search-num-adults-entry,.vbeb-bookingform-wrap .vbo-search-num-aduchild-entry > .vbo-search-num-children-entry{flex:1 1 0;min-width:0;}'
				// VBEB 7.439.0, su richiesta dell'utente: tutti i campi alti
				// uguali, 54px bordo compreso (la misura che VikBooking da' a
				// check-in e check-out nel layout del dettaglio camera: campo
				// di 42px + 4px sopra e sotto + bordo di 2px). Prima le date
				// crescevano con lo "Spessore bordi" e i menu a tendina
				// restavano a 50px ("height: 50px !important" del layout
				// a colonna). Date: altezza fissata sul riquadro
				// ".input-group", campo e icona si adattano dentro. Menu:
				// stessa altezza, con !important e selettore piu' pesante di
				// quello di VikBooking.
				// VBEB 7.441.0: altezza da "Altezza campi" (54px se manca).
				. '.vbeb-bookingform-wrap .vbdivsearch .vb-search-inner .vbo-search-inpblock .input-group{height:var(--vbeb-field-h,54px);box-sizing:border-box;}'
				. '.vbeb-bookingform-wrap .vbdivsearch .vb-search-inner .vbo-search-inpblock .input-group > input{height:100%;}'
				. '.vbeb-bookingform-wrap .vbdivsearch .vb-search-inner .vbo-search-inpblock .input-group > .vbo-caltrigger{height:auto;line-height:1;}'
				. '.vbeb-bookingform-wrap .vbo-seldates-cont .vbdivsearch .vb-search-inner div select{height:var(--vbeb-field-h,54px) !important;box-sizing:border-box;}'
				// VBEB 7.442.0, su richiesta dell'utente: "Camera 1, 2..." senza
				// il margine di VikBooking (".vbo-search-roomnum { margin: 24px
				// 10px 0 0 }") e centrato in verticale rispetto ad Adulti e
				// Bambini, che stanno nello stesso div (una riga per camera).
				// Lo spazio a destra ora e' la "Spaziatura colonne" (column-gap
				// della riga).
				// VBEB 7.443.0: centrato rispetto ai soli menu a tendina, non
				// all'etichetta sopra: sta in fondo alla riga, alto quanto un
				// campo ("Altezza campi"), con il testo al centro.
				. '.vbeb-bookingform-wrap .vbo-search-num-aduchild-entry > .vbo-search-roomnum{margin:0;align-self:flex-end;display:flex;align-items:center;height:var(--vbeb-field-h,54px);box-sizing:border-box;}'
				// VBEB 7.430.0: divisore fra le voci degli ospiti.
				. '.vbeb-cap-divider-yes .vbmaxminpeopleroom > div:not(:last-child)::after{content:"";position:absolute;top:var(--vbeb-cap-a-top);bottom:var(--vbeb-cap-a-bottom);left:var(--vbeb-cap-a-left);right:var(--vbeb-cap-a-right);width:var(--vbeb-cap-a-w);height:var(--vbeb-cap-a-h);margin:0 auto;border-left:var(--vbeb-cap-a-bl);border-top:var(--vbeb-cap-a-bt);transform:var(--vbeb-cap-a-tr);box-sizing:border-box;pointer-events:none;}</style>';
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

		// VBEB 7.220.0: il calendario popup scelto (vedi vbeb_calpop_sezione()).
		echo function_exists( 'vbeb_calpop_html_popup' ) ? vbeb_calpop_html_popup( $settings ) : ''; // phpcs:ignore -- CSS del template generato da Elementor e script del plugin.
		echo '<div class="vbeb-bookingform-wrap"' . ( function_exists( 'vbeb_calpop_attributo' ) ? vbeb_calpop_attributo( $settings ) : '' ) . '>'; // phpcs:ignore -- attributo gia' filtrato con esc_attr().
		echo $content; // phpcs:ignore -- HTML reale gia' generato e sicuro da VikBooking stesso, solo tag/testo del titolo gestiti qui sopra con escaping.
		echo '</div>';

		if ( $in_editor ) {
			echo '</div>';
		}

		// VBEB: ricostruiamo qui (lato server) l'SVG della freccia dei menu
		// a tendina Adulti/Bambini con il colore scelto nel pannello -
		// necessario farlo in PHP e non tramite i normali "selectors" di
		// Elementor, perche' il carattere "#" di un colore esadecimale
		// andrebbe codificato come "%23" per funzionare dentro un data-URI
		// SVG, cosa che la sostituzione automatica {{VALUE}} non fa da
		// sola (romperebbe l'immagine).
		$icon_color = isset( $settings['module_icons_color'] ) && '' !== $settings['module_icons_color']
			? $settings['module_icons_color']
			: '#666666';
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="' . $icon_color . '" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>';
		$svg_data_uri = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode( $svg );
		?>
		<style>
			.elementor-element-<?php echo esc_attr( $this->get_id() ); ?> select[name="adults[]"],
			.elementor-element-<?php echo esc_attr( $this->get_id() ); ?> select[name="children[]"],
			.elementor-element-<?php echo esc_attr( $this->get_id() ); ?> select[name="roomsnum"] {
				background-image: url("<?php echo esc_attr( $svg_data_uri ); ?>");
			}
		</style>
		<?php

		// VBEB 7.644.0: con Visualizza "Predefinito" (prima: "Mostra sfondo" spento).
		if ( isset( $settings['capacity_icon_view'] ) && 'default' === $settings['capacity_icon_view'] ) {
			// VBEB: con lo sfondo (cerchio) disattivato, non forziamo piu'
			// una larghezza/altezza fissa sull'icona - lasciamo che si
			// restringa alla dimensione naturale del simbolo, cosi' il
			// testo accanto risulta visivamente piu' vicino. Usiamo
			// !important perche' il controllo "Dimensione icone" genera
			// una regola con piu' specificita' della nostra (include le
			// classi automatiche di scoping di Elementor): senza
			// !important perderemmo sempre il confronto, indipendentemente
			// da dove viene stampata questa regola nella pagina.
			?>
			<style>
				.elementor-element-<?php echo esc_attr( $this->get_id() ); ?> .vbmaxadultsdet .vbmaximgdet i,
				.elementor-element-<?php echo esc_attr( $this->get_id() ); ?> .vbmaxchildrendet .vbmaximgdet i,
				.elementor-element-<?php echo esc_attr( $this->get_id() ); ?> .vbmaxtotdet .vbmaximgdet i {
					width: auto !important;
					height: auto !important;
					line-height: normal !important;
				}
			</style>
			<?php
		}
	}
}
