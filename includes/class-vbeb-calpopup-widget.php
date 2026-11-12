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
use Elementor\Group_Control_Background;

/**
 * VBEB 7.210.0: "VBEB Calendario popup".
 *
 * Richiesto dall'utente. Il widget si mette in un template "VBEB Popup" con
 * "Tipo di popup: Calendario popup" (versione free; prima Sezione) e
 * serve a dare un ASPETTO al calendario delle date di VikBooking: i widget
 * che hanno la sezione "Calendario popup" (nella versione free solo "VBEB
 * Camera - Modulo prenotazione")
 * scelgono il template, e il loro calendario prende lo stile disegnato qui.
 *
 * UN SOLO STRUMENTO, DUE MODI DI MOSTRARSI. Tutti i calendari delle date di
 * VikBooking sono il datepicker di jQuery UI con l'estensione
 * "vboDatesRangePicker" (site/resources/datesrangepicker.js), e l'HTML e'
 * lo stesso ovunque. Cambia dove compare:
 * - A COMPARSA, nel riquadro unico "#ui-datepicker-div" in fondo alla pagina
 *   (dettaglio camera, con i pulsanti "Cancella le date" e "Chiudi");
 * - IN LINEA, dentro il widget: e' il caso del modulo di ricerca con le date
 *   leggibili ("inlineContainer" in
 *   modules/mod_vikbooking_horizontalsearch/tmpl/default.php), dove
 *   VikBooking dice esplicitamente che i pulsanti non sono supportati.
 *
 * COME ARRIVA LO STILE. Ogni selettore di questo widget e' scritto due volte
 * (vbeb_calpop_sel()): con la classe "vbeb-calpop-{ID}" su un ANTENATO del
 * calendario (anteprima qui dentro, e calendario in linea del widget che
 * sceglie il template, a cui si aggiunge la classe) oppure SUL calendario
 * stesso (il riquadro a comparsa, a cui la classe va data all'apertura).
 * {{ID}} e' l'identificativo di questo elemento, che Elementor sostituisce
 * sia nel file CSS del template sia nell'anteprima dal vivo. Il widget che
 * sceglie il template ne stampa il CSS e aggiunge la classe.
 *
 * ":not(#vbeb-calpop)" aggiunge il peso di un identificativo senza
 * restringere nulla: il foglio di VikBooking usa molti "!important" e regole
 * fino a sei classi (vikbooking_styles.css, righe 5739-6051), e cosi' il
 * pannello le supera tutte; i valori che VikBooking marca "!important" sono
 * scritti qui con "!important" anch'essi.
 *
 * I COLORI GLOBALI DI ELEMENTOR PERDONO IL "!important" (7.211.0, segnalato
 * dall'utente). Con un colore globale Elementor riscrive ogni dichiarazione
 * del selettore con la sua variabile, sostituendo tutto cio' che sta fra ":"
 * e ";" (core/files/css/base.php, riga 369): "color: X !important" diventa
 * "color:var(--e-global-color-...)", e contro un "!important" di VikBooking
 * perde. Dove VikBooking usa "!important" con una SUA variabile, il pannello
 * imposta anche quella variabile, sullo stesso elemento: la regola di
 * VikBooking legge cosi' il colore scelto, globale o no. Lo sfondo di hover
 * delle frecce, che VikBooking azzera con un valore fisso, passa invece da
 * uno strato "::before" che nessun'altra regola tocca.
 *
 * L'ANTEPRIMA e' un calendario vero, aperto, di jQuery UI, con i nomi dei
 * mesi e dei giorni di VikBooking e un soggiorno di esempio (check-in oggi,
 * check-out fra tre giorni) disegnato con le stesse classi dell'estensione:
 * "checkin-date", "checkout-date", "checkin-checkout-inner". I pulsanti in
 * basso hanno il markup di VikBooking. Il riquadro riprende l'aspetto del
 * calendario a comparsa del dettaglio camera (riga 5909).
 *
 * IL PANNELLO CONTENUTO porta anche due comportamenti, applicati dal widget
 * che sceglie il template: i mesi visibili e i pulsanti "Cancella le date" /
 * "Chiudi". "Predefinito" lascia la scelta di VikBooking.
 */
class VBEB_Calpopup_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_calpopup_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Popup Calendar', 'vbeb-bridge-for-vikbooking-and-elementor' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return array( 'vbeb-search' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'calendario', 'popup', 'date', 'datepicker' );
	}

	/**
	 * Il datepicker di jQuery UI, per l'anteprima.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'jquery-ui-datepicker', 'vbeb-calpopup' );
	}

	/**
	 * VBEB free: foglio con le regole fisse dei widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'vbeb-widgets' );
	}

	/**
	 * Selettore doppio per una parte del calendario.
	 *
	 * @param string $parte Parte del calendario, con lo spazio iniziale se
	 *                      discendente (es. " td > a"), vuota per il riquadro.
	 * @return string
	 */
	private function sel( $parte ) {
		return vbeb_calpop_sel( $parte );
	}

	/**
	 * Sfondo, bordo e raggio del cerchio di uno stato dei giorni.
	 *
	 * VBEB 7.227.0, su richiesta dell'utente, per Oggi, Giorni futuri e
	 * Giorni non disponibili. Il bersaglio e' il cerchio con il numero (il
	 * link o lo span dentro la cella). VikBooking gli da' bordo (1,5px, con
	 * "!important" per i non disponibili: riga 5791) e raggio 50% con
	 * "!important" (riga 5784): qui tutto e' con "!important" e pesa di piu'.
	 * Tipo di bordo e raggio si nascondono con la griglia accesa.
	 *
	 * @param string       $nome         Prefisso dei controlli.
	 * @param string|array $parte        Parte o parti del calendario (i cerchi).
	 * @param string|array $parte_colore Parti per il colore del bordo, se diverse.
	 * @return void
	 */
	private function stile_cerchio( $nome, $parte, $parte_colore = '' ) {
		// VBEB 7.239.0: anche piu' parti (Giorni futuri).
		$parti      = (array) $parte;
		$sel        = vbeb_calpop_sel_multi( $parti );
		$sel_colore = vbeb_calpop_sel_multi( '' !== $parte_colore && array() !== $parte_colore ? (array) $parte_colore : $parti );
		$condizione = array( 'days_grid!' => 'yes' );

		// VBEB 7.230.0, su richiesta dell'utente: senza griglia lo sfondo va
		// al cerchio, con la griglia alla cella (e al cerchio, cosi' non si
		// vede un cerchio di altro colore dentro il quadretto). La griglia si
		// riconosce dalla classe "vbeb-calpop-grid" accanto a quella del
		// template (vedi vbeb_calpop_sel_griglia()).
		$celle = array();
		foreach ( $parti as $p ) {
			$celle[] = preg_replace( '/\s*>\s*(\*|a)$/', '', $p );
		}

		$this->add_control(
			$nome . '_bg',
			array(
				'label'       => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					vbeb_calpop_sel_griglia_multi( $parti, false )                   => 'background: {{VALUE}} !important;',
					vbeb_calpop_sel_griglia_multi( array_merge( $celle, $parti ), true ) => 'background: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => $nome . '_border',
				'selector'       => $sel,
				'condition'      => $condizione,
				'fields_options' => array(
					'border' => array(
						'label'     => esc_html__( 'Border Type', 'vbeb-bridge-for-vikbooking-and-elementor' ),
						'selectors' => array( '{{SELECTOR}}' => 'border-style: {{VALUE}} !important;' ),
					),
					'width'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
					),
					'color'  => array(
						'selectors' => array( $sel_colore => 'border-color: {{VALUE}} !important;' ),
					),
				),
			)
		);

		$this->add_responsive_control(
			$nome . '_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'condition'  => $condizione,
				'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
			)
		);
	}

	/**
	 * Come stile_cerchio(), per piu' parti del calendario insieme.
	 *
	 * VBEB 7.229.0, per "Giorno selezionato": sfondo, bordo e raggio; con la
	 * griglia si nascondono il bordo (7.234.0) e il raggio (7.235.0). Il colore del bordo imposta anche la variabile che la
	 * regola di VikBooking legge con "!important" (riga 6029); lo sfondo
	 * pure, e il bordo, se scelto, lo ridefinisce dopo.
	 *
	 * @param string $nome  Prefisso dei controlli.
	 * @param array  $parti Parti del calendario.
	 * @return void
	 */
	private function stile_cerchio_multi( $nome, array $parti ) {
		$sel = vbeb_calpop_sel_multi( $parti );

		$this->add_control(
			$nome . '_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.230.0: come in stile_cerchio(). Con la griglia anche
				// la variabile dello sfondo delle celle scelte, letta da
				// VikBooking con "!important" (riga 6001).
				'selectors' => array(
					vbeb_calpop_sel_griglia_multi( $parti, false ) => 'background: {{VALUE}} !important; --vbo-pref-bgcolor: {{VALUE}};',
					vbeb_calpop_sel_griglia_multi( array_merge( $parti, array_map( function ( $p ) { return preg_replace( '/\s*>\s*(\*|a)$/', '', $p ); }, $parti ) ), true ) => 'background: {{VALUE}} !important; --vbo-pref-bgcolor: {{VALUE}}; --vbo-light-grey-bg-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => $nome . '_border',
				// VBEB 7.234.0: con la griglia i cerchi non hanno bordo.
				'condition'      => array( 'days_grid!' => 'yes' ),
				'selector'       => $sel,
				'fields_options' => array(
					'border' => array(
						'label'     => esc_html__( 'Border Type', 'vbeb-bridge-for-vikbooking-and-elementor' ),
						'selectors' => array( '{{SELECTOR}}' => 'border-style: {{VALUE}} !important;' ),
					),
					'width'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
					),
					'color'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-color: {{VALUE}} !important;' ),
					),
				),
			)
		);

		$this->add_responsive_control(
			$nome . '_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				// VBEB 7.235.0: con la griglia i cerchi non hanno raggio.
				'condition'  => array( 'days_grid!' => 'yes' ),
				'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
			)
		);
	}

	/*
	 * VBEB 7.520.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i suoi testi (dominio "elementor"), cosi' la
	 * traduzione arriva dai file di lingua di Elementor. Le altre (sezioni
	 * del calendario, colori di frecce e linee, griglia, anteprima, ...) sono
	 * solo di VBEB e restano in italiano fino alla fase delle traduzioni.
	 */
	protected function register_controls() {

		/* ---------------------------------------------------------------
		 * CONTENUTO
		 * ------------------------------------------------------------- */
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'Calendar', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => Controls_Manager::TAB_CONTENT ) );

		$this->add_control(
			'months',
			array(
				'label'     => esc_html__( 'Visible Months', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''  => esc_html__( 'Default (VikBooking)', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'1' => '1',
					'2' => '2',
				),
			)
		);

		$this->add_control(
			'buttons',
			array(
				'label'       => esc_html__( '"Clear dates" and "Close" Buttons', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'label_block' => true,
				'options'     => array(
					''     => esc_html__( 'Default (VikBooking)', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'show' => esc_html__( 'Show', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'hide' => esc_html__( 'Hide', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
			)
		);

		// VBEB 7.223.0: lo stato mostrato dal calendario di prova, per
		// disegnare le due schede di "Intervallo date" (Giorni - numero).
		// Vale solo qui: sul sito il calendario e' quello vero.
		$this->add_control(
			'preview_state',
			array(
				'label'       => esc_html__( 'Editor Preview', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'label_block' => true,
				'separator'   => 'before',
				'options'     => array(
					''        => esc_html__( 'Check-in and Check-out Selected', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'checkin' => esc_html__( 'Check-in Only (Mouse over a Check-out)', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * CONTENUTO - DIVISORE
		 *
		 * VBEB 7.216.0, su richiesta dell'utente. La linea fra il mese e la
		 * riga dei giorni della settimana NON viene da VikBooking ne' da jQuery
		 * UI (che anzi toglie il bordo a quelle celle: ".ui-datepicker th
		 * { border: 0 }"), ma dal tema Hello Elementor: il suo reset.css da'
		 * un bordo superiore alle celle della prima riga dell'intestazione di
		 * ogni tabella ("table thead:first-child tr:first-child th
		 * { border-block-start: 1px solid hsla(0,0%,50%,.502) }"), con un
		 * selettore piu' forte di quello di jQuery UI. Ogni mese del
		 * calendario e' una tabella, e i bordi delle sette celle formano la
		 * linea.
		 * ------------------------------------------------------------- */
		$this->start_controls_section( 'section_content_divider', array( 'label' => esc_html__( 'Divider', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => Controls_Manager::TAB_CONTENT ) );

		$this->add_control(
			'divider_hide',
			array(
				'label'        => esc_html__( 'Hide Line', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'selectors'    => array( $this->sel( ' .ui-datepicker-calendar thead tr:first-child th' ) => 'border-top-width: 0 !important; border-block-start-width: 0 !important;' ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * CONTENUTO - PULSANTI "CANCELLA LE DATE" E "CHIUDI"
		 *
		 * VBEB free, su richiesta dell'utente: i testi dei due pulsanti, ognuno
		 * nella propria sezione. Vuoti, quelli di VikBooking (gia' tradotti in
		 * ogni lingua). Scritti qui, stanno nel template: per le altre lingue
		 * si traduce il template (Polylang o WPML, vedi vbeb_template_lingua()
		 * e wpml-config.xml). Le sezioni non compaiono con i pulsanti su
		 * "Nascondi".
		 * ------------------------------------------------------------- */
		$this->start_controls_section(
			'section_content_clear',
			array(
				'label'     => esc_html__( '"Clear dates" Button', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'buttons!' => 'hide' ),
			)
		);

		$this->add_control(
			'clear_text',
			array(
				'label'       => esc_html__( 'Text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => vbeb_calpop_testo_vikbooking( 'VBO_CLEAR_DATES' ),
				'label_block' => false,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_close',
			array(
				'label'     => esc_html__( '"Close" Button', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'buttons!' => 'hide' ),
			)
		);

		$this->add_control(
			'close_text',
			array(
				'label'       => esc_html__( 'Text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => vbeb_calpop_testo_vikbooking( 'VBO_CLOSE' ),
				'label_block' => false,
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * STILE - CONTENITORE
		 * ------------------------------------------------------------- */
		// VBEB free, su richiesta dell'utente: tolta la sezione "Contenitore".
		// Sfondo, bordo, raggio, ombra e rientro del riquadro del calendario si
		// impostano nel template "VBEB Popup" (Stile > Popup, Avanzato >
		// Rientro), vedi class-vbeb-popup-document.php.

		/* ---------------------------------------------------------------
		 * STILE - NAVIGAZIONE MESE
		 *
		 * VBEB 7.221.0, su richiesta dell'utente: una sola sezione con due
		 * sottosezioni, "Mese e anno" e "Frecce", che prima erano sezioni a
		 * se'. I nomi dei controlli sono gli stessi, quindi le impostazioni
		 * gia' salvate restano.
		 * ------------------------------------------------------------- */
		$this->start_controls_section( 'section_style_title', array( 'label' => esc_html__( 'Month Navigation', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$this->add_control(
			'nav_heading_title',
			array(
				'label' => esc_html__( 'Month and Year', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		// VikBooking: 14px, grassetto, "--vbo-text-color" (riga 5915).
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => $this->sel( ' .ui-datepicker-header .ui-datepicker-title' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .ui-datepicker-header .ui-datepicker-title' ) => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_control(
			'nav_heading_arrows',
			array(
				'label'     => esc_html__( 'Arrows', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Le frecce sono i link ".ui-datepicker-prev" e "-next": cerchi da
		// 34px con bordo (riga 5939) e l'icona disegnata da ":after" dello
		// span interno, con il carattere di Font Awesome (riga 5971).

		$this->start_controls_tabs( 'arrows_tabs' );

		$this->start_controls_tab( 'arrows_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'arrows_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( vbeb_calpop_sel_multi( array( ' .ui-datepicker-prev span:after', ' .ui-datepicker-next span:after' ) ) => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_control(
			'arrows_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( vbeb_calpop_sel_multi( array( ' .ui-datepicker-prev', ' .ui-datepicker-next' ) ) => 'background-color: {{VALUE}} !important;' ),
			)
		);

		$this->add_control(
			'arrows_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( vbeb_calpop_sel_multi( array( ' .ui-datepicker-prev', ' .ui-datepicker-next' ) ) => 'border-color: {{VALUE}} !important;' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'arrows_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'arrows_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( vbeb_calpop_sel_multi( array( ' .ui-datepicker-prev:hover span:after', ' .ui-datepicker-next:hover span:after' ) ) => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_control(
			'arrows_bg_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.211.0: lo sfondo di hover va a uno strato "::before"
				// della freccia (vbeb_calpop_css_base()), perche' VikBooking
				// lo azzera con "background: transparent !important" (riga
				// 5931) e con un colore globale di Elementor il "!important"
				// del pannello si perde (vedi il commento della classe).
				'selectors' => array( vbeb_calpop_sel_multi( array( ' .ui-datepicker-prev:hover::before', ' .ui-datepicker-next:hover::before' ) ) => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrows_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.211.0: anche la variabile che la regola di VikBooking
				// legge con "!important" (riga 6037: --vbo-pref-bgcolor).
				'selectors' => array( vbeb_calpop_sel_multi( array( ' .ui-datepicker-prev:hover', ' .ui-datepicker-next:hover' ) ) => 'border-color: {{VALUE}} !important; --vbo-pref-bgcolor: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * STILE - DIVISORE
		 *
		 * VBEB 7.216.0: colore e spessore della linea sotto il mese (vedi la
		 * sezione "Divisore" del Contenuto). Lo spessore imposta anche lo
		 * stile continuo, cosi' la linea compare pure con un tema che non la
		 * disegna. Nascosta dal pannello Contenuto, la sezione si nasconde.
		 * ------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_divider',
			array(
				'label'     => esc_html__( 'Divider', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'divider_hide!' => 'yes' ),
			)
		);

		$this->add_control(
			'divider_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .ui-datepicker-calendar thead tr:first-child th' ) => 'border-top-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'divider_width',
			array(
				'label'      => esc_html__( 'Weight', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'selectors'  => array( $this->sel( ' .ui-datepicker-calendar thead tr:first-child th' ) => 'border-top-width: {{SIZE}}{{UNIT}}; border-top-style: solid;' ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * STILE - GIORNI DELLA SETTIMANA
		 * ------------------------------------------------------------- */
		$this->start_controls_section( 'section_style_weekdays', array( 'label' => esc_html__( 'Days - Name', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		// VikBooking: 10px, maiuscolo, "--vbo-middle-text-color" (riga 5922).
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'weekdays_typography',
				'selector' => $this->sel( ' .ui-datepicker-calendar th' ),
			)
		);

		$this->add_control(
			'weekdays_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.211.0: anche la variabile che la regola di VikBooking
				// legge con "!important" (riga 5922: --vbo-middle-text-color).
				'selectors' => array( $this->sel( ' .ui-datepicker-calendar th' ) => 'color: {{VALUE}} !important; --vbo-middle-text-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * STILE - GIORNI - NUMERO
		 *
		 * VBEB 7.223.0, riorganizzata su richiesta dell'utente: tipografia e
		 * dimensione per tutti i giorni, poi una sottosezione per stato.
		 * Tolti il raggio del bordo, l'opacita' dei non disponibili e le
		 * schede Hover e Selezionato.
		 *
		 * GLI STATI, come li vede il calendario (una cella "td" per giorno,
		 * dentro un link o uno span, il cerchio):
		 * - OGGI: "ui-datepicker-today" (jQuery UI). Vince sugli altri stati,
		 *   tranne quando e' dentro l'intervallo scelto.
		 * - PASSATI: prima di oggi. Ne' jQuery UI ne' VikBooking hanno una
		 *   classe (la "date-past" di VikBooking vuol dire "prima della data
		 *   minima", che dopo il check-in e' il check-in stesso): la mette lo
		 *   script del plugin, "vbeb-past" (vbeb_calpop_script_popup()).
		 * - FUTURI: i giorni cliccabili, fuori dall'intervallo.
		 * - NON DISPONIBILI: "ui-datepicker-unselectable" (jQuery UI) che non
		 *   sono passati: occupati, chiusi, sotto il soggiorno minimo, oltre
		 *   la data massima, dopo un giorno occupato mentre si sceglie il
		 *   check-out.
		 * - INTERVALLO DATE, due schede:
		 *   Normale, check-in e check-out scelti: "checkin-date",
		 *   "checkout-date", "checkin-checkout-inner" (datesrangepicker.js,
		 *   righe 727-740); nei calendari a data singola il giorno scelto,
		 *   "ui-datepicker-current-day".
		 *   Hover, solo il check-in scelto: il check-in ha anche
		 *   "without-checkout-date"; con il mouse su un possibile check-out
		 *   VikBooking marca quel giorno "checkout-date date-will" e quelli in
		 *   mezzo "checkin-checkout-inner" (righe 821-834). ":has()" sul
		 *   calendario riconosce il momento, e pesa piu' della scheda Normale.
		 *   La striscia (lo sfondo delle celle) si disegna solo con il mouse
		 *   su un check-out: il check-in da solo ha il solo cerchio.
		 * Le variabili di VikBooking servono contro i suoi "!important" con i
		 * colori globali (vedi il commento della classe).
		 * ------------------------------------------------------------- */
		$this->start_controls_section( 'section_style_days', array( 'label' => esc_html__( 'Days - Number', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		// Ogni giorno e' un link (o uno span se non selezionabile) dentro la
		// cella: un cerchio da 40px con bordo di 1,5px (riga 5781).
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'days_typography',
				'selector' => $this->sel( ' .ui-datepicker-calendar td > *' ),
			)
		);

		$this->add_responsive_control(
			'days_size',
			array(
				'label'      => esc_html__( 'Size', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 70,
					),
				),
				'selectors'  => array(
					$this->sel( ' .ui-datepicker-calendar td > *' ) => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important; line-height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$intervallo = ':not(.checkin-date):not(.checkout-date):not(.checkin-checkout-inner):not(.ui-datepicker-current-day)';

		// VBEB 7.231.0: la cella di oggi, con il peso di un identificativo
		// in piu' (":not(#vbeb-calpop-oggi)" non restringe nulla). Oggi e'
		// anche un giorno futuro cliccabile, quindi lo prendono anche i
		// selettori di "Giorni futuri", che pesavano di piu' (una classe e
		// un elemento). Con i colori manuali vinceva Oggi per il suo
		// "!important"; con un colore globale Elementor lo toglie e vinceva
		// il colore dei giorni futuri (segnalato dall'utente: l'oro di Oggi
		// non si vedeva). Ora Oggi vince sempre, anche per sfondo e bordo.
		// VBEB 7.244.0: non quando oggi e' chiuso (dopo l'orario di check-in,
		// vedi vbeb_calpop_script_popup()): allora e' un giorno passato.
		$oggi = ' .ui-datepicker-calendar td.ui-datepicker-today:not(#vbeb-calpop-oggi):not(.vbeb-past)' . $intervallo;

		// VBEB 7.239.0, segnalato dall'utente con uno screenshot:
		// - le celle vuote degli altri mesi ("ui-datepicker-other-month")
		//   sono anch'esse non cliccabili, e con la griglia prendevano lo
		//   sfondo dei non disponibili;
		// - i giorni "date-past" (datesrangepicker.js, riga 700: prima della
		//   data minima, cioe' prima del check-in mentre si sceglie il
		//   check-out, o prima dell'anticipo minimo) non sono giorni pieni:
		//   su scelta dell'utente hanno l'aspetto dei giorni futuri, come in
		//   altri sistemi di prenotazione (vedi $futuro_min e
		//   vbeb_calpop_css_base()).
		// Restano non disponibili i giorni pieni, chiusi o con restrizioni.
		$nondisp = ' .ui-datepicker-calendar td.ui-datepicker-unselectable:not(.vbeb-past):not(.date-past):not(.ui-datepicker-other-month)';

		/* --- Griglia ---
		 * VBEB 7.225.0, su richiesta dell'utente: un bordo attorno alle celle
		 * di oggi, dei giorni futuri e dei non disponibili, cioe' di tutte
		 * tranne i giorni passati ("vbeb-past") e le celle vuote degli altri
		 * mesi. Ogni mese e' una tabella con i bordi uniti
		 * ("border-collapse: collapse", jQuery UI), quindi le linee fra due
		 * celle sono singole. Anche i giorni dell'intervallo hanno la
		 * griglia: la striscia colorata risulta divisa in quadretti (scelta
		 * dell'utente, per ora).
		 *
		 * L'interruttore basta da solo: colore e spessore passano da due
		 * variabili sul calendario, con valori predefiniti (1px, #dddddd).
		 *
		 * I NON DISPONIBILI: VikBooking li sbiadisce con opacita' 0,2 sulla
		 * cella intera (".ui-state-disabled", riga 5897), bordo compreso, e
		 * la griglia attorno a loro sparirebbe. Con la griglia accesa lo
		 * sbiadimento passa dalla cella al cerchio con il numero.
		 */
		$griglia_celle = ' .ui-datepicker-calendar td:not(.vbeb-past):not(.ui-datepicker-other-month)';

		$this->add_control( 'days_grid_heading', array( 'label' => esc_html__( 'Grid', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );

		$this->add_control(
			'days_grid',
			array(
				'label'        => esc_html__( 'Show Grid', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				// VBEB 7.230.0: il calendario di prova si ridisegna, per la
				// classe "vbeb-calpop-grid" (vedi render()).
				'render_type'  => 'template',
				'selectors'    => array(
					// VBEB 7.226.0: i bordi uniti. VikBooking li separa
					// ("border-collapse: initial", vikbooking_styles.css,
					// riga 5754) e ogni cella disegnava il suo: fra due
					// celle la linea era doppia (segnalato dall'utente con
					// uno screenshot). Uniti, il browser fonde le due linee
					// di confine in una, qualunque sia il mese; verso un
					// giorno passato, senza bordo, resta quella del giorno
					// accanto.
					$this->sel( ' table.ui-datepicker-calendar' ) => 'border-collapse: collapse !important;',
					$this->sel( $griglia_celle ) => 'border: var(--vbeb-calpop-grid-width, 1px) solid var(--vbeb-calpop-grid-color, #dddddd) !important;',
					$this->sel( ' .ui-datepicker-calendar td.ui-datepicker-unselectable:not(.vbeb-past)' ) => 'opacity: 1 !important;',
					// VBEB 7.233.0, su richiesta dell'utente (estende la
					// 7.231.0, che valeva solo per oggi): con la griglia
					// nessun cerchio ha il bordo - oggi, passati, futuri
					// (anche al passaggio del mouse, riga 6044), non
					// disponibili, giorni dell'intervallo e check-out indicato
					// dal mouse; dalla 7.234.0 anche "Giorno selezionato". Un
					// identificativo in piu' per vincere sulle regole del
					// pannello (scheda Hover dei giorni futuri, Giorno
					// selezionato) e su quelle "!important" di VikBooking.
					// VBEB 7.235.0, su richiesta dell'utente: anche il raggio,
					// per tutti i cerchi (VikBooking: 50% con "!important",
					// riga 5784).
					$this->sel( ' .ui-datepicker-calendar td:not(#vbeb-calpop-nobordo) > *' ) => 'border-color: transparent !important; border-radius: 0 !important;',
					// VBEB 7.245.0, su richiesta dell'utente (la questione
					// lasciata aperta nella 7.225.0): niente linee verticali
					// fra le celle dell'intervallo, cosi' la striscia resta
					// continua. Una cella dell'intervallo che segue un'altra
					// cella dell'intervallo nascosta la linea di sinistra: con
					// i bordi uniti "hidden" vince su ogni altro bordo, anche
					// su quello della cella accanto. Vale anche mentre si
					// sceglie il check-out. Le linee orizzontali, fra una riga
					// e l'altra, restano: il CSS non vede la cella di sopra.
					$this->sel( ' .ui-datepicker-calendar td:is(.checkin-date,.checkin-checkout-inner) + td:is(.checkin-checkout-inner,.checkout-date):not(#vbeb-calpop-striscia)' ) => 'border-left-style: hidden !important;',
					// VBEB 7.228.0, su richiesta dell'utente: niente pillola
					// con la griglia. VikBooking arrotonda al 50% gli angoli
					// di sinistra della cella del check-in e di destra di
					// quella del check-out, anche di quello indicato dal
					// mouse (vikbooking_styles.css, righe 5825-5832); con i
					// bordi uniti le curve scoprivano gli angoli della
					// griglia. Qui le celle tornano quadrate; il cerchio con
					// il numero dentro non cambia.
					$this->sel( ' .ui-datepicker-calendar td.checkin-date' ) . ', ' . $this->sel( ' .ui-datepicker-calendar td.checkout-date' ) => 'border-radius: 0 !important;',
					$this->sel( $nondisp . ' > *' ) => 'opacity: .2;',
				),
			)
		);

		$this->add_control(
			'days_grid_color',
			array(
				'label'     => esc_html__( 'Line Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'days_grid' => 'yes' ),
				'selectors' => array( $this->sel( '' ) => '--vbeb-calpop-grid-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'days_grid_width',
			array(
				'label'      => esc_html__( 'Line Thickness', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'condition'  => array( 'days_grid' => 'yes' ),
				'selectors'  => array( $this->sel( '' ) => '--vbeb-calpop-grid-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		/* --- Oggi --- */
		$this->add_control( 'days_today_heading', array( 'label' => esc_html__( 'Today', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );

		$this->add_control(
			'days_today_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.230.0: con un colore globale il numero di oggi non
				// prendeva il colore (segnalato dall'utente): Elementor toglie
				// il "!important" (vedi il commento della classe) e vince una
				// regola "!important" di VikBooking. Si impostano anche le
				// variabili che quelle regole leggono: "--vbo-pref-textcolor"
				// sulla cella (".ui-datepicker .ui-datepicker-today", riga
				// 5903, e il passaggio del mouse, riga 6044) e, sul numero,
				// quelle di ".ui-state-active" (riga 5882) e del giorno
				// scelto (righe 6029 e 6041).
				'selectors' => array(
					$this->sel( $oggi ) => '--vbo-pref-textcolor: {{VALUE}};',
					$this->sel( $oggi . ' > *' ) => 'color: {{VALUE}} !important; --vbo-pref-textcolor: {{VALUE}}; --vbo-light-dark-grey-bg-color: {{VALUE}}; --vbo-pref-fontcolor: {{VALUE}}; --vbo-contrast-text-color: {{VALUE}};',
				),
			)
		);

		/* --- Giorni passati --- */
		$this->stile_cerchio( 'days_today', $oggi . ' > *' );

		$this->add_control( 'days_past_heading', array( 'label' => esc_html__( 'Past Days', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );

		// VBEB 7.224.0, su richiesta dell'utente: l'opacita' al posto del
		// colore del testo. VikBooking da' 0,2 con "!important" a tutte le
		// celle non cliccabili (".ui-state-disabled", vikbooking_styles.css,
		// riga 5897): il selettore del pannello pesa di piu'. Vale per la
		// cella intera, cerchio e numero compresi.
		$this->add_control(
			'days_past_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'selectors' => array( $this->sel( ' .ui-datepicker-calendar td.vbeb-past' ) => 'opacity: {{SIZE}} !important;' ),
			)
		);

		/* --- Giorni futuri --- */
		$this->add_control( 'days_future_heading', array( 'label' => esc_html__( 'Available Days', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );

		$futuro = ' .ui-datepicker-calendar td:not(.ui-datepicker-unselectable):not(.vbeb-past)' . $intervallo;
		// VBEB 7.239.0: i giorni prima della data minima (vedi $nondisp), con
		// l'aspetto dei giorni futuri nella scheda Normale. Non sono
		// cliccabili: la scheda Hover non li riguarda.
		$futuro_min = ' .ui-datepicker-calendar td.date-past:not(.vbeb-past):not(.ui-datepicker-other-month)' . $intervallo;

		// VBEB 7.232.0, su richiesta dell'utente: schede Normale e Hover.
		$this->start_controls_tabs( 'days_future_tabs' );

		$this->start_controls_tab( 'days_future_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'days_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB 7.212.0: senza "!important", vedi la 7.212.0 nel readme.
				'selectors' => array( $this->sel( $futuro . ' > a' ) . ', ' . $this->sel( $futuro_min . ' > *' ) => 'color: {{VALUE}}; --vbo-light-dark-grey-bg-color: {{VALUE}};' ),
			)
		);

		// Il colore del bordo non vale al passaggio del mouse: li' decide la
		// scheda Hover o, se vuota, VikBooking (riga 6044).
		$this->stile_cerchio( 'days_future', array( $futuro . ' > a', $futuro_min . ' > *' ), array( $futuro . ' > a:not(:hover)', $futuro_min . ' > *' ) );

		$this->end_controls_tab();

		/*
		 * Hover: il mouse sopra un giorno futuro cliccabile. VikBooking gli da'
		 * bordo "--vbo-pref-bgcolor" e testo "--vbo-pref-textcolor", tutti e
		 * due con "!important" (vikbooking_styles.css, riga 6044), e sfondo
		 * trasparente (riga 5796). Qui con "!important" e con le stesse
		 * variabili, contro i colori globali. Lo sfondo segue la griglia come
		 * nella scheda Normale: cerchio senza, cella (e cerchio) con. Vale
		 * anche per oggi, che e' un giorno futuro cliccabile.
		 */
		$this->start_controls_tab( 'days_future_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		// VBEB 7.238.0, su richiesta dell'utente: anche i giorni in mezzo a
		// un intervallo gia' scelto, che sono cliccabili come gli altri (un
		// clic ricomincia la scelta). Mentre si sceglie il check-out il giorno
		// sotto il mouse e' "date-will", non "checkin-checkout-inner": quel
		// momento resta alla scheda Hover di "Intervallo date". Con ":hover"
		// questi selettori pesano piu' dello sfondo dell'intervallo.
		// "--vbo-white-bg-color" e' la variabile che VikBooking legge con
		// "!important" per lo sfondo di quei giorni al passaggio del mouse
		// (riga 5880), per i colori globali.
		$interno = ' .ui-datepicker-calendar td.checkin-checkout-inner';
		// VBEB 7.244.0, su richiesta dell'utente: anche oggi, finche' e'
		// prenotabile. Oggi e' gia' fra i giorni disponibili, ma le regole di
		// "Oggi" pesano di piu' (7.231.0) e vincevano anche sotto il mouse:
		// qui un identificativo in piu'.
		$oggi_h = $oggi . ':not(#vbeb-calpop-oggi-h)';

		$this->add_control(
			'days_future_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( vbeb_calpop_sel_multi( array( $futuro . ' > a:hover', $interno . ' > *:hover', $oggi_h . ' > a:hover' ) ) => 'color: {{VALUE}} !important; --vbo-pref-textcolor: {{VALUE}}; --vbo-light-dark-grey-bg-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'days_future_bg_hover',
			array(
				'label'       => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					vbeb_calpop_sel_griglia_multi( array( $futuro . ' > a:hover', $interno . ' > *:hover', $oggi_h . ' > a:hover' ), false ) => 'background: {{VALUE}} !important; --vbo-white-bg-color: {{VALUE}};',
					vbeb_calpop_sel_griglia_multi( array( $futuro . ':hover', $futuro . ':hover > a', $interno . ':hover', $interno . ':hover > *', $oggi_h . ':hover', $oggi_h . ':hover > a' ), true ) => 'background: {{VALUE}} !important; --vbo-light-grey-bg-color: {{VALUE}}; --vbo-white-bg-color: {{VALUE}};',
				),
			)
		);

		// VBEB 7.243.0, su richiesta dell'utente: al posto del solo colore del
		// bordo, tipo di bordo (gruppo di Elementor) e raggio, solo con la
		// griglia spenta (con la griglia i cerchi non hanno ne' bordo ne'
		// raggio). Il colore imposta anche "--vbo-pref-bgcolor", letta con
		// "!important" da VikBooking al passaggio del mouse (riga 6044), per i
		// colori globali.
		$hover_sel = vbeb_calpop_sel_multi( array( $futuro . ' > a:hover', $interno . ' > *:hover', $oggi_h . ' > a:hover' ) );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'days_future_border_hover',
				'selector'       => $hover_sel,
				'condition'      => array( 'days_grid!' => 'yes' ),
				'fields_options' => array(
					'border' => array(
						'label'     => esc_html__( 'Border Type', 'vbeb-bridge-for-vikbooking-and-elementor' ),
						'selectors' => array( '{{SELECTOR}}' => 'border-style: {{VALUE}} !important;' ),
					),
					'width'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
					),
					'color'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-color: {{VALUE}} !important; --vbo-pref-bgcolor: {{VALUE}};' ),
					),
				),
			)
		);

		$this->add_responsive_control(
			'days_future_radius_hover',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'condition'  => array( 'days_grid!' => 'yes' ),
				'selectors'  => array( $hover_sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		/* --- Giorni non disponibili --- */
		$this->add_control( 'days_disabled_heading', array( 'label' => esc_html__( 'Not Available Days', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );

		$this->add_control(
			'days_disabled_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				// VBEB free, segnalato dall'utente: con "Griglia" il numero dei
				// giorni non disponibili e' sbiadito (opacita' .2, vedi
				// days_grid); con un colore scelto si vede pieno.
				'selectors' => array( $this->sel( $nondisp . ' > *' ) => 'color: {{VALUE}} !important; opacity: 1 !important;' ),
			)
		);

		/* --- Intervallo date --- */
		$this->stile_cerchio( 'days_disabled', $nondisp . ' > *' );

		/* --- Giorno selezionato ---
		 * VBEB 7.229.0, su richiesta dell'utente (opzione 1): il cerchio del
		 * giorno scelto, in ogni fase. Check-in e check-out scelti nei
		 * calendari con intervallo ("checkin-date", "checkout-date" ma non
		 * "date-will", che e' il check-out indicato dal mouse e segue la
		 * scheda Hover di "Intervallo date"), e il giorno cliccato nei
		 * calendari a data singola ("ui-datepicker-current-day": Pacchetto -
		 * Modulo prenotazione, Operatore - Giorni di lavoro). VikBooking li
		 * colora con "!important" leggendo le sue variabili (riga 6029):
		 * il pannello imposta anche quelle, contro i colori globali.
		 */
		$selezionato = array(
			' .ui-datepicker-calendar td.checkin-date > *',
			' .ui-datepicker-calendar td.checkout-date:not(.date-will) > *',
			' .ui-datepicker-calendar td.ui-datepicker-current-day > *',
		);

		$this->add_control( 'days_selected_heading', array( 'label' => esc_html__( 'Selected Day', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );

		$this->add_control(
			'days_selected_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( vbeb_calpop_sel_multi( $selezionato ) => 'color: {{VALUE}} !important; --vbo-pref-fontcolor: {{VALUE}}; --vbo-contrast-text-color: {{VALUE}}; --vbo-light-dark-grey-bg-color: {{VALUE}};' ),
			)
		);

		$this->stile_cerchio_multi( 'days_selected', $selezionato );

		$this->add_control( 'days_range_heading', array( 'label' => esc_html__( 'Date Range', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );

		$this->start_controls_tabs( 'days_range_tabs' );

		$this->start_controls_tab( 'days_range_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		// VBEB 7.229.0: i cerchi di check-in, check-out e giorno scelto
		// sono passati a "Giorno selezionato"; qui la striscia e i cerchi
		// dei giorni in mezzo.
		$scelto_cerchi = array(
			' .ui-datepicker-calendar td.checkin-checkout-inner > *',
		);
		$scelto_celle  = array(
			' .ui-datepicker-calendar td.checkin-date:not(.without-checkout-date)',
			' .ui-datepicker-calendar td.checkout-date',
			' .ui-datepicker-calendar td.checkin-checkout-inner',
		);

		$this->add_control(
			'days_range_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( vbeb_calpop_sel_multi( $scelto_cerchi ) => 'color: {{VALUE}} !important; --vbo-pref-fontcolor: {{VALUE}}; --vbo-contrast-text-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'days_range_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					vbeb_calpop_sel_multi( $scelto_celle )  => 'background: {{VALUE}} !important; --vbo-light-grey-bg-color: {{VALUE}};',
					// VBEB 7.238.0: piu' "--vbo-white-bg-color", che VikBooking
					// legge con "!important" per lo sfondo di questi giorni al
					// passaggio del mouse (riga 5880): con un colore globale il
					// giorno diventava bianco (segnalato dall'utente). Ora resta
					// del colore dell'intervallo, se la scheda Hover di "Giorni
					// futuri" e' vuota.
					vbeb_calpop_sel_multi( $scelto_cerchi ) => 'background: {{VALUE}} !important; border-color: {{VALUE}} !important; --vbo-pref-bgcolor: {{VALUE}}; --vbo-light-grey-bg-color: {{VALUE}}; --vbo-white-bg-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'days_range_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		// VBEB 7.229.0: il cerchio del check-in segue "Giorno selezionato"
		// anche in questa fase (opzione 1 dell'utente).
		$anteprima_cerchi = array(
			':has(td.date-will) .ui-datepicker-calendar td.checkout-date.date-will > *',
			':has(td.date-will) .ui-datepicker-calendar td.checkin-checkout-inner > *',
		);
		$anteprima_celle  = array(
			':has(td.date-will) .ui-datepicker-calendar td.checkin-date.without-checkout-date',
			':has(td.date-will) .ui-datepicker-calendar td.checkout-date.date-will',
			':has(td.date-will) .ui-datepicker-calendar td.checkin-checkout-inner',
		);

		$this->add_control(
			'days_preview_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					vbeb_calpop_sel_multi( $anteprima_cerchi ) => 'color: {{VALUE}} !important; --vbo-pref-fontcolor: {{VALUE}}; --vbo-contrast-text-color: {{VALUE}}; --vbo-pref-textcolor: {{VALUE}};',
					// VBEB 7.241.0: per "Anche il check-in" (sotto).
					$this->sel( '' ) => '--vbeb-calpop-prev-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'days_preview_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					vbeb_calpop_sel_multi( $anteprima_celle )  => 'background: {{VALUE}} !important; --vbo-light-grey-bg-color: {{VALUE}};',
					vbeb_calpop_sel_multi( $anteprima_cerchi ) => 'background: {{VALUE}} !important; border-color: {{VALUE}} !important; --vbo-pref-bgcolor: {{VALUE}}; --vbo-light-grey-bg-color: {{VALUE}};',
					// VBEB 7.241.0: per "Anche il check-in" (sotto).
					$this->sel( '' ) => '--vbeb-calpop-prev-bg: {{VALUE}};',
				),
			)
		);

		/*
		 * VBEB 7.241.0, su richiesta dell'utente: il check-in prende anche lui
		 * i colori di questa scheda, invece di quelli di "Giorno selezionato",
		 * mentre il mouse indica un possibile check-out (":has(td.date-will)",
		 * come il resto della scheda). I due colori qui sopra impostano anche
		 * due variabili sul calendario, lette da questa regola; se un colore
		 * e' vuoto resta quello del giorno selezionato (le variabili di
		 * VikBooking, che "Giorno selezionato" imposta). Tre identificativi:
		 * vince su "Giorno selezionato" con e senza griglia. Con la griglia
		 * anche la cella.
		 */
		$checkin_anteprima = ':has(td.date-will) .ui-datepicker-calendar td.checkin-date.without-checkout-date:not(#vbeb-calpop-prev)';

		$this->add_control(
			'days_preview_checkin',
			array(
				'label'        => esc_html__( 'Check-in Too', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'selectors'    => array(
					$this->sel( $checkin_anteprima . ':not(#vbeb-calpop-prev2) > *' ) => 'color: var(--vbeb-calpop-prev-color, var(--vbo-pref-fontcolor)) !important; background: var(--vbeb-calpop-prev-bg, var(--vbo-pref-bgcolor)) !important;',
					vbeb_calpop_sel_griglia( $checkin_anteprima, true ) => 'background: var(--vbeb-calpop-prev-bg, var(--vbo-pref-bgcolor)) !important;',
				),
			)
		);


		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * STILE - PULSANTE "CANCELLA LE DATE" E PULSANTE "CHIUDI"
		 *
		 * VBEB 7.215.0: al posto della sezione unica "Pulsanti", due sezioni
		 * con le impostazioni del Pulsante di Elementor meno "Posizione", su
		 * richiesta dell'utente con uno screenshot. I nomi di tipografia e
		 * colori del testo restano quelli della 7.210.0 ("clear_..." e
		 * "close_..."), quindi quei valori restano; lo sfondo diventa il
		 * gruppo "Tipo di sfondo" (tinta unita o sfumatura) e i vecchi colori
		 * di sfondo semplici non ci sono piu'. Il gruppo e' lo stesso che
		 * funziona nella Finanza e nei Giorni di lavoro.
		 * ------------------------------------------------------------- */
		// "Cancella le date" e' un link sottolineato con il colore del testo secondario di VikBooking e, al passaggio del mouse, uno sfondo grigio chiaro (vikbooking_styles.css, righe 6011-6023).
		$this->start_controls_section( 'section_style_clear', array( 'label' => esc_html__( '"Clear dates" Button', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'clear_typography',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'clear_text_shadow',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ),
			)
		);

		$this->start_controls_tabs( 'clear_tabs' );

		$this->start_controls_tab( 'clear_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'clear_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ) => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'clear_background',
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'clear_shadow',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'clear_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'clear_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a:hover' ) => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'clear_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a:hover' ),
			)
		);

		$this->add_control(
			'clear_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a:hover' ) => 'border-color: {{VALUE}} !important;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'clear_shadow_hover',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a:hover' ),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'clear_border',
				'selector'  => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'clear_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					$this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'clear_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					$this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-clear a' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		// "Chiudi" e' un pulsante con il colore preferito di VikBooking (".vbo-pref-color-btn", riga 145), che al passaggio del mouse prende il colore preferito di hover.
		$this->start_controls_section( 'section_style_close', array( 'label' => esc_html__( '"Close" Button', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'close_typography',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'close_text_shadow',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ),
			)
		);

		$this->start_controls_tabs( 'close_tabs' );

		$this->start_controls_tab( 'close_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'close_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ) => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'close_background',
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'close_shadow',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'close_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'close_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button:hover' ) => 'color: {{VALUE}} !important;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'close_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- campi esclusi da un controllo di Elementor, non una query.
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button:hover' ),
			)
		);

		$this->add_control(
			'close_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button:hover' ) => 'border-color: {{VALUE}} !important;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'close_shadow_hover',
				'selector' => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button:hover' ),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'close_border',
				'selector'  => $this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'close_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					$this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'close_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					$this->sel( ' .vbo-drp-commands-bottom .vbo-drp-command-close button' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Nomi di mesi e giorni di VikBooking per il datepicker, come in
	 * VboApplication::loadDatePicker() (admin/helpers/jv_helper.php, riga
	 * 1633), piu' il primo giorno della settimana della struttura.
	 *
	 * @return array
	 */
	public function regionale() {
		if ( ! class_exists( 'JText' ) ) {
			return array();
		}

		$mesi   = array( 'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER' );
		$giorni = array( 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT' );

		$nomi_mesi = array();
		foreach ( $mesi as $m ) {
			$nomi_mesi[] = JText::_( $m );
		}

		$brevi = array();
		$min   = array();
		foreach ( $giorni as $g ) {
			$nome    = JText::_( $g );
			$brevi[] = $nome;
			$min[]   = function_exists( 'mb_substr' ) ? mb_substr( $nome, 0, 2 ) : substr( $nome, 0, 2 );
		}

		if ( count( array_unique( $min ) ) !== count( $min ) ) {
			$min = $brevi;
		}

		$regionale = array(
			'monthNames'  => $nomi_mesi,
			'dayNamesMin' => $min,
		);

		if ( class_exists( 'VikBooking' ) && method_exists( 'VikBooking', 'getFirstWeekDay' ) ) {
			$regionale['firstDay'] = (int) VikBooking::getFirstWeekDay();
		}

		return $regionale;
	}

	protected function render() {
		$impostazioni = $this->get_settings_for_display();
		$id           = $this->get_id();
		$mesi         = ( isset( $impostazioni['months'] ) && '1' === $impostazioni['months'] ) ? 1 : 2;
		$pulsanti     = ! ( isset( $impostazioni['buttons'] ) && 'hide' === $impostazioni['buttons'] );

		// Foglio di VikBooking, variabili dei colori, icone (le frecce usano
		// Font Awesome) e foglio di jQuery UI di VikBooking: sul sito e
		// nell'editor il template non passa da una pagina di VikBooking.
		// VBEB free (Plugin Check): il foglio di jQuery UI di VikBooking lo
		// carica l'anteprima dell'editor ("elementor/preview/enqueue_styles",
		// vbeb.php) invece di un "<link>" qui; sul sito il template del
		// calendario non viene stampato, se ne legge solo lo stile.

		// VBEB free: testi di VikBooking (pulsanti, mesi, giorni) nella lingua
		// del template aperto, sia all'apertura sia dopo una modifica.
		$widget    = $this;
		$traduzioni = function_exists( 'vbeb_in_lingua_template' )
			? vbeb_in_lingua_template(
				function () use ( $widget, $impostazioni ) {
					return array( vbeb_calpop_testi( $impostazioni ), $widget->regionale() );
				}
			)
			: array( vbeb_calpop_testi( $impostazioni ), $this->regionale() );
		$testi      = $traduzioni[0];

		$dati = array(
			'months'   => $mesi,
			'buttons'  => $pulsanti,
			'texts'    => $testi,
			'regional' => $traduzioni[1],
			'preview'  => ( isset( $impostazioni['preview_state'] ) && 'checkin' === $impostazioni['preview_state'] ) ? 'checkin' : '',
		);

		// VBEB free (revisione di wordpress.org): le regole del riquadro di
		// prova sono in assets/css/vbeb-widgets.css e il calendario lo avvia
		// assets/js/vbeb-calpopup.js; qui solo i dati di oggi.
		vbeb_calpop_dati_oggi();

		$griglia = ( isset( $impostazioni['days_grid'] ) && 'yes' === $impostazioni['days_grid'] ) ? ' vbeb-calpop-grid' : '';

		echo '<div class="vbeb-calpop-preview vbeb-calpop-' . esc_attr( $id ) . esc_attr( $griglia ) . '" data-vbeb-calpop="' . esc_attr( wp_json_encode( $dati ) ) . '"><div class="vbeb-calpop-dp"></div></div>';

	}
}

/* =====================================================================
 * Funzioni comuni del calendario popup, usate dal widget qui sopra e dai
 * widget che scelgono un template (per ora "VBEB Modulo Ricerca").
 * =================================================================== */

/**
 * Selettore doppio di una parte del calendario (vedi il commento della
 * classe): classe su un antenato, oppure sul calendario stesso.
 *
 * @param string $parte Parte del calendario, vuota per il riquadro.
 * @return string
 */
function vbeb_calpop_sel( $parte ) {
	return '.vbeb-calpop-{{ID}}:not(#vbeb-calpop) .ui-datepicker' . $parte
		. ', .vbeb-calpop-{{ID}}.ui-datepicker:not(#vbeb-calpop)' . $parte;
}

/**
 * Selettore doppio, solo con la griglia accesa o solo con la griglia spenta.
 *
 * VBEB 7.230.0. Con "Mostra la griglia" attiva il calendario ha anche la
 * classe "vbeb-calpop-grid", accanto a "vbeb-calpop-{ID}": la mette il
 * widget che sceglie il template (contenitore del calendario in linea,
 * vbeb_calpop_dati() per quello a comparsa) e l'anteprima. Con la griglia
 * il selettore pesa un identificativo in piu', per vincere sulle regole di
 * "Intervallo date" (anche quelle con ":has()").
 *
 * @param string $parte   Parte del calendario.
 * @param bool   $griglia True con la griglia, false senza.
 * @return string
 */
function vbeb_calpop_sel_griglia( $parte, $griglia ) {
	$classe = $griglia ? '.vbeb-calpop-grid:not(#vbeb-calpop):not(#vbeb-calpop-grid)' : ':not(.vbeb-calpop-grid):not(#vbeb-calpop)';

	return '.vbeb-calpop-{{ID}}' . $classe . ' .ui-datepicker' . $parte
		. ', .vbeb-calpop-{{ID}}.ui-datepicker' . $classe . $parte;
}

/**
 * Come vbeb_calpop_sel_griglia(), per piu' parti insieme.
 *
 * @param array $parti   Parti del calendario.
 * @param bool  $griglia True con la griglia, false senza.
 * @return string
 */
function vbeb_calpop_sel_griglia_multi( array $parti, $griglia ) {
	$out = array();

	foreach ( $parti as $parte ) {
		$out[] = vbeb_calpop_sel_griglia( $parte, $griglia );
	}

	return implode( ', ', $out );
}

/**
 * Selettore doppio per piu' parti insieme.
 *
 * @param array $parti Parti del calendario.
 * @return string
 */
function vbeb_calpop_sel_multi( array $parti ) {
	$out = array();

	foreach ( $parti as $parte ) {
		$out[] = vbeb_calpop_sel( $parte );
	}

	return implode( ', ', $out );
}

/**
 * Cerca il widget "VBEB Calendario popup" dentro gli elementi di un template.
 *
 * @param array $elementi Elementi di Elementor.
 * @return array|null Identificativo e impostazioni del primo trovato.
 */
function vbeb_calpop_cerca( array $elementi ) {
	foreach ( $elementi as $el ) {
		if ( ! is_array( $el ) ) {
			continue;
		}

		if ( isset( $el['widgetType'] ) && 'vbeb_calpopup_widget' === $el['widgetType'] && ! empty( $el['id'] ) ) {
			return array(
				'id'       => (string) $el['id'],
				'settings' => isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array(),
			);
		}

		if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
			$trovato = vbeb_calpop_cerca( $el['elements'] );

			if ( $trovato ) {
				return $trovato;
			}
		}
	}

	return null;
}

/**
 * Il widget "VBEB Calendario popup" di un template, se c'e'.
 *
 * @param int $tpl_id Template.
 * @return array|null
 */
function vbeb_calpop_da_template( $tpl_id ) {
	static $cache = array();

	$tpl_id = (int) $tpl_id;

	if ( $tpl_id <= 0 || ! class_exists( '\Elementor\Plugin' ) ) {
		return null;
	}

	if ( array_key_exists( $tpl_id, $cache ) ) {
		return $cache[ $tpl_id ];
	}

	$cache[ $tpl_id ] = null;

	try {
		$documento = \Elementor\Plugin::$instance->documents->get( $tpl_id );

		if ( $documento ) {
			$cache[ $tpl_id ] = vbeb_calpop_cerca( (array) $documento->get_elements_data() );
		}
	} catch ( \Throwable $e ) {
		$cache[ $tpl_id ] = null;
	}

	return $cache[ $tpl_id ];
}

/**
 * Il CSS del template, una volta per pagina.
 *
 * Il widget che sceglie il template non lo contiene, quindi Elementor non
 * ne caricherebbe il foglio: lo si prende dal file che Elementor stesso
 * genera per il template (vedi vbeb_calpop_html_popup()).
 *
 * @param int $tpl_id Template.
 * @return string CSS, senza tag.
 */
function vbeb_calpop_css_template( $tpl_id ) {
	static $fatti = array();

	$tpl_id = (int) $tpl_id;

	if ( $tpl_id <= 0 || isset( $fatti[ $tpl_id ] ) || ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
		return '';
	}

	$fatti[ $tpl_id ] = true;

	try {
		return (string) \Elementor\Core\Files\CSS\Post::create( $tpl_id )->get_content();
	} catch ( \Throwable $e ) {
		return '';
	}
}

/**
 * Elenco dei template "VBEB Popup" di tipo "Calendario popup", per i menu.
 *
 * @return array
 */
function vbeb_calpop_elenco_template() {
	$elenco = array( '' => esc_html__( '— Original VikBooking Calendar —', 'vbeb-bridge-for-vikbooking-and-elementor' ) );

	// VBEB free, su richiesta dell'utente: il calendario sta in un template
	// "VBEB Popup" con "Tipo di popup: Calendario popup" (come il popup del
	// Modulo informazioni), non piu' in un template di tipo Sezione.
	if ( function_exists( 'vbeb_popup_elenco' ) ) {
		$elenco += vbeb_popup_elenco( 'calpopup' );
	}

	return $elenco;
}

/**
 * I dati del calendario popup scelto da un widget, pronti per l'uso.
 *
 * VBEB 7.217.0. Comune ai widget che scelgono un template: legge il widget
 * "VBEB Calendario popup" del template e ne ricava classe, mesi, pulsanti e
 * testi.
 *
 * @param int $tpl_id Template scelto.
 * @return array|null Null se il template non c'e' o non contiene il widget.
 */
function vbeb_calpop_dati( $tpl_id ) {
	$calpop = vbeb_calpop_da_template( $tpl_id );

	if ( ! $calpop ) {
		return null;
	}

	$mesi     = isset( $calpop['settings']['months'] ) ? (string) $calpop['settings']['months'] : '';
	$pulsanti = isset( $calpop['settings']['buttons'] ) ? (string) $calpop['settings']['buttons'] : '';

	return array(
		'class'   => 'vbeb-calpop-' . sanitize_html_class( $calpop['id'] ),
		// VBEB free: le classi del template "VBEB Popup", cosi' sul sito il
		// riquadro del calendario ("#ui-datepicker-div") prende lo stile
		// "Popup" e il "Rientro" del template (class-vbeb-popup-document.php).
		'popup'   => 'vbeb-popup vbeb-popup-' . (int) $tpl_id,
		'months'  => in_array( $mesi, array( '1', '2' ), true ) ? (int) $mesi : 0,
		'buttons' => 'show' === $pulsanti,
		'hide'    => 'hide' === $pulsanti,
		'grid'    => isset( $calpop['settings']['days_grid'] ) && 'yes' === $calpop['settings']['days_grid'],
		'texts'   => vbeb_calpop_testi( $calpop['settings'] ),
		// VBEB free: testi scelti nel template, da mettere anche sui
		// pulsanti che VikBooking crea da se' (vedi vbeb_calpop_script_popup()).
		'custom'  => '' !== trim( (string) ( isset( $calpop['settings']['clear_text'] ) ? $calpop['settings']['clear_text'] : '' ) )
			|| '' !== trim( (string) ( isset( $calpop['settings']['close_text'] ) ? $calpop['settings']['close_text'] : '' ) ),
	);
}

/**
 * I testi dei pulsanti "Cancella le date" e "Chiudi".
 *
 * VBEB free: quelli scritti nel widget "VBEB Calendario popup" o, se vuoti,
 * quelli di VikBooking (VBO_CLEAR_DATES e VBO_CLOSE, gia' tradotti).
 *
 * @param array $settings Impostazioni del widget "VBEB Calendario popup".
 * @return array
 */
function vbeb_calpop_testi( $settings ) {
	$clear = isset( $settings['clear_text'] ) ? trim( (string) $settings['clear_text'] ) : '';
	$close = isset( $settings['close_text'] ) ? trim( (string) $settings['close_text'] ) : '';

	if ( '' === $clear ) {
		$clear = vbeb_calpop_testo_vikbooking( 'VBO_CLEAR_DATES' );
	}
	if ( '' === $close ) {
		$close = vbeb_calpop_testo_vikbooking( 'VBO_CLOSE' );
	}

	return array(
		'clear' => '' !== $clear ? $clear : 'Clear dates',
		'close' => '' !== $close ? $close : 'Close',
	);
}

/**
 * Un testo di VikBooking, vuoto se la traduzione non e' disponibile
 * (VikBooking restituisce la chiave stessa quando non la trova).
 *
 * @param string $chiave Chiave di lingua di VikBooking.
 * @return string
 */
function vbeb_calpop_testo_vikbooking( $chiave ) {
	if ( ! class_exists( 'JText' ) ) {
		return '';
	}
	$testo = (string) JText::_( $chiave );
	return $testo === $chiave ? '' : $testo;
}

/**
 * I dati di oggi per lo script del calendario a comparsa
 * (assets/js/vbeb-calpopup.js), una volta per pagina.
 *
 * VBEB free (revisione di wordpress.org): lo script del calendario a
 * comparsa e' nel file assets/js/vbeb-calpopup.js (lo descrive il suo
 * commento): qui si carica e gli si passano, con wp_add_inline_script(), i
 * dati di oggi.
 *
 * @return void
 */
function vbeb_calpop_dati_oggi() {
	static $gia = false;

	wp_enqueue_script( 'vbeb-calpopup' );

	if ( $gia ) {
		return;
	}
	$gia = true;

	// VBEB 7.244.0, su richiesta dell'utente: OGGI DOPO L'ORARIO DI CHECK-IN.
	// Con "Prenotazioni per oggi a qualsiasi ora" (todaybookings) su No,
	// VikBooking accetta oggi solo fino all'orario di check-in: la ricerca
	// confronta il check-in di oggi a quell'ora con l'ora attuale, e da
	// un secondo dopo risponde "Check-in per oggi non piu' disponibile"
	// (site/views/search/view.html.php, righe 185-199; stesso controllo in
	// site/controller.php, riga 882). Il calendario non lo sa: qui si
	// calcola il momento con le stesse funzioni di VikBooking e lo script,
	// passato quel momento, tratta oggi come un giorno passato (classe
	// "vbeb-past", quindi l'opacita' di "Giorni passati") e non cliccabile.
	// Con l'impostazione su Si oggi resta prenotabile fino a mezzanotte, e
	// non serve nulla. Vale solo per i calendari delle prenotazioni (dato
	// "today" del contenitore), non per i Giorni di lavoro degli operatori.
	$chiude = 0;
	$data   = '';

	try {
		if ( class_exists( 'VikBooking' ) && method_exists( 'VikBooking', 'todayBookings' ) && ! VikBooking::todayBookings() ) {
			$orari = VikBooking::getTimeOpenStore();

			if ( is_array( $orari ) ) {
				$hm     = VikBooking::getHoursMinutes( $orari[0] );
				$data   = date( 'Y-m-d' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- stesso calcolo di VikBooking (date() nel suo codice), con il fuso orario che VikBooking usa per getDateTimestamp().
				$chiude = (int) VikBooking::getDateTimestamp( $data, $hm[0], $hm[1] );
			}
		}
	} catch ( \Throwable $e ) {
		$chiude = 0;
	}

	wp_add_inline_script(
		'vbeb-calpopup',
		'window.vbebCalpopOggi=' . wp_json_encode(
			array(
				'chiude' => (int) $chiude,
				'data'   => $chiude ? $data : '',
			)
		) . ';',
		'before'
	);
}

/**
 * La sezione "Calendario popup" di un widget con un calendario A COMPARSA.
 *
 * VBEB 7.220.0, su richiesta dell'utente, per i widget "VBEB Camera - Modulo
 * prenotazione", "VBEB Prenotazione - Modulo ricerca", "VBEB Prenotazione -
 * Modulo ricerca AJAX" e "VBEB Pacchetto - Modulo prenotazione". Stessa
 * sezione di "Giorni di lavoro": il widget mette i dati sul proprio
 * contenitore (vbeb_calpop_attributo()) e stampa CSS e script
 * (vbeb_calpop_html_popup()); lo script da' al riquadro "#ui-datepicker-div"
 * l'aspetto del template quando lo apre un campo del widget.
 *
 * @param \Elementor\Widget_Base $widget      Il widget.
 * @param string                 $descrizione Testo sotto il menu.
 * @return void
 */
function vbeb_calpop_sezione( $widget, $descrizione ) {
	$widget->start_controls_section( 'section_calpopup', array( 'label' => esc_html__( 'Popup Calendar', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );

	$vbeb_ctrl = array(
		'label'       => esc_html__( 'Calendar Template', 'vbeb-bridge-for-vikbooking-and-elementor' ),
		'type'        => \Elementor\Controls_Manager::SELECT,
		'default'     => '',
		'label_block' => true,
		'options'     => vbeb_calpop_elenco_template(),
	);
	// VBEB 7.444.0: senza testo, nessuna nota sotto il menu.
	if ( '' !== $descrizione ) {
		$vbeb_ctrl['description'] = $descrizione;
	}
	$widget->add_control( 'calpopup_template', $vbeb_ctrl );

	$widget->end_controls_section();
}

/**
 * Il template del calendario scelto da un widget, nella lingua della pagina.
 *
 * VBEB free, su richiesta dell'utente: con i testi dei pulsanti il template
 * del calendario si puo' tradurre. Con Polylang o WPML si usa la sua copia
 * nella lingua della pagina, se esiste ed e' pubblicata; altrimenti
 * l'originale (vbeb_template_lingua(), vbeb.php).
 *
 * @param array $settings Impostazioni del widget che sceglie il template.
 * @return int
 */
function vbeb_calpop_template_scelto( $settings ) {
	$tpl = isset( $settings['calpopup_template'] ) ? (int) $settings['calpopup_template'] : 0;

	if ( $tpl > 0 && function_exists( 'vbeb_template_lingua' ) ) {
		$tpl = vbeb_template_lingua( $tpl );
	}

	return $tpl;
}

/**
 * I dati del template scelto, come attributo del contenitore del widget.
 *
 * VBEB 7.220.0.
 *
 * @param array $settings Impostazioni del widget.
 * @return string Vuoto se non c'e' un template valido.
 */
function vbeb_calpop_attributo( $settings ) {
	$tpl  = vbeb_calpop_template_scelto( $settings );
	$dati = $tpl > 0 ? vbeb_calpop_dati( $tpl ) : null;

	if ( $dati ) {
		// VBEB 7.244.0: calendario delle prenotazioni (vedi
		// vbeb_calpop_script_popup(), oggi dopo l'orario di check-in).
		$dati['today'] = true;
	}

	return $dati ? wp_json_encode( $dati ) : '';
}

/**
 * CSS del template e script del calendario a comparsa.
 *
 * VBEB 7.220.0. Nell'editor avvisa se il template non contiene il widget.
 *
 * @param array $settings Impostazioni del widget.
 * @return string HTML da stampare (avviso o segnaposto), anche vuoto.
 */
function vbeb_calpop_html_popup( $settings ) {
	$tpl = vbeb_calpop_template_scelto( $settings );

	if ( $tpl <= 0 ) {
		return '';
	}

	if ( ! vbeb_calpop_dati( $tpl ) ) {
		// VBEB free: l'avviso solo se il template scelto esiste ed e' davvero
		// un popup "Calendario popup" senza il widget. Un valore rimasto da un
		// template eliminato o di un altro tipo non mostra nulla: resta il
		// calendario originale di VikBooking.
		$in_editor = function_exists( 'vbeb_editor_ripete' ) && vbeb_editor_ripete();
		if ( ! $in_editor || 'publish' !== get_post_status( $tpl ) || 'vbeb-popup' !== get_post_meta( $tpl, '_elementor_template_type', true ) ) {
			return '';
		}
		$impostazioni = get_post_meta( $tpl, '_elementor_page_settings', true );
		if ( ! is_array( $impostazioni ) || empty( $impostazioni['vbeb_popup_tipo'] ) || 'calpopup' !== $impostazioni['vbeb_popup_tipo'] ) {
			return '';
		}

		return '<div style="padding:10px;margin-bottom:10px;background:#fff3cd;border:1px solid #ffc107;color:#856404;">' . esc_html__( 'Popup Calendar: the selected template does not contain the "VBEB Popup Calendar" widget.', 'vbeb-bridge-for-vikbooking-and-elementor' ) . '</div>';
	}

	// VBEB free (revisione di wordpress.org): lo script del calendario a
	// comparsa e i suoi dati (vbeb_calpop_dati_oggi()); il CSS del template
	// sul sito con wp_add_inline_style() (vbeb_aggiungi_stile()), nell'editor
	// con lo script dell'editor (segnaposto nascosto, vedi
	// assets/js/vbeb-editor.js).
	vbeb_calpop_dati_oggi();

	$css = vbeb_calpop_css_template( $tpl );
	if ( '' === $css ) {
		return '';
	}

	if ( function_exists( 'vbeb_editor_ripete' ) && vbeb_editor_ripete() ) {
		return '<span class="vbeb-css-globali" hidden data-vbeb-css="' . esc_attr( $css ) . '"></span>';
	}

	vbeb_aggiungi_stile( $css );
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->frontend->print_fonts_links();
	}

	return '';
}
