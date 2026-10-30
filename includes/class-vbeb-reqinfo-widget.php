<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

/**
 * Widget Elementor "VikBooking Richiedi Informazioni". Non ricostruisce
 * il comportamento reale (il click continua a chiamare la funzione JS
 * originale di VikBooking che apre la finestra modale): incapsula
 * l'HTML reale generato da VikBooking (catturato dall'override file
 * tramite $this->loadTemplate('reqinfo')), aggiungendo pero' un pannello
 * Contenuto e Stile equivalente a quello del widget "Pulsante" nativo di
 * Elementor (testo personalizzabile, icona, dimensione, allineamento,
 * animazione hover, tipografia, colori normale/hover, bordo, ombra,
 * padding).
 */
class VBEB_Reqinfo_Widget extends Widget_Base {

	public function get_name() {
		// VBEB 7.259.0, su richiesta dell'utente: nuovo nome interno (era
		// "vbeb_room_reqinfo_widget"); i widget gia' messi vanno reinseriti.
		return 'vbeb_room_reqinfobutton_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Information Button', 'vbeb-bridge-for-vikbooking-and-elementor' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'informazioni', 'reqinfo', 'contatto', 'camera', 'pulsante' );
	}

	/*
	 * VBEB 7.502.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i testi del suo widget "Pulsante" (dominio
	 * "elementor", button-trait.php), cosi' la traduzione arriva dai file di
	 * lingua di Elementor. Quelle solo di VBEB (Template del modulo, la sua
	 * descrizione, il segnaposto del Testo) restano in italiano fino alla
	 * fase delle traduzioni.
	 */
	protected function register_controls() {

		/* ================= CONTENUTO - PULSANTE ================= */
		$this->start_controls_section(
			'section_content_button',
			array(
				'label' => esc_html__( 'Button', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave empty to use the original VikBooking text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'default'     => '',
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'selected_icon',
			array(
				'label'       => esc_html__( 'Icon', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
			)
		);

		$this->add_control(
			'icon_align',
			array(
				// VBEB 7.404.0: come nel widget Pulsante di Elementor (scelta a
				// icone); stessi valori di prima.
				'label'     => esc_html__( 'Icon Position', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'right',
				'toggle'    => false,
				'options'   => array(
					'left'  => array( 'title' => esc_html__( 'Start', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-left' ),
					'right' => array( 'title' => esc_html__( 'End', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-right' ),
				),
				'condition' => array(
					'selected_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'icon_spacing',
			array(
				'label'      => esc_html__( 'Icon Spacing', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'vw', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 50 ),
					'em' => array( 'min' => 0, 'max' => 5 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 8 ),
				'condition'  => array(
					'selected_icon[value]!' => '',
				),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-reqinfo-btn-icon-wrap.vbeb-icon-left'  => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vbeb-reqinfo-btn-icon-wrap.vbeb-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= CONTENUTO - MODULO =================
		 * VBEB 7.253.0, su richiesta dell'utente: il template del popup. Un
		 * template di tipo Sezione con il widget "VBEB Camera - Modulo
		 * informazioni" (e cio' che si vuole attorno): il popup di VikBooking
		 * lo mostra al posto del suo titolo e del suo modulo. Vedi
		 * con_template().
		 */
		$this->start_controls_section(
			'section_content_form',
			array(
				'label' => esc_html__( 'Form', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// VBEB 7.486.0, su richiesta dell'utente: solo i template "VBEB
		// Popup" con "Tipo di popup" = "Modulo informazioni" (prima anche i
		// template Sezione e Contenitore).
		$elenco = array( '' => esc_html__( '— Original VikBooking Form —', 'vbeb-bridge-for-vikbooking-and-elementor' ) );
		if ( function_exists( 'vbeb_popup_elenco' ) ) {
			$elenco += vbeb_popup_elenco( 'reqinfo' );
		}

		$this->add_control(
			'form_template',
			array(
				'label'       => esc_html__( 'Form Template', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'label_block' => true,
				'options'     => $elenco,
				// VBEB 7.584.0, su richiesta dell'utente: tolta la nota
				// (spostata nella lista "note widget").
			)
		);

		$this->end_controls_section();

		/* ================= STILE - PULSANTE ================= */
		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => esc_html__( 'Button', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.406.0, su richiesta dell'utente: "Posizione" come nel
		// Pulsante di Elementor. Valori come variabili CSS sull'involucro,
		// lette dalle regole fisse nel render(): Estendi porta il pulsante a
		// tutta larghezza con il contenuto al centro.
		$this->add_responsive_control(
			'button_position',
			array(
				'label'                => esc_html__( 'Position', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'    => array( 'title' => esc_html__( 'Left', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-left' ),
					'center'  => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-center' ),
					'right'   => array( 'title' => esc_html__( 'Right', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-right' ),
					'justify' => array( 'title' => esc_html__( 'Stretch', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-stretch' ),
				),
				'selectors_dictionary' => array(
					'left'    => '--vbeb-ri-ta: left; --vbeb-ri-w: auto;',
					'center'  => '--vbeb-ri-ta: center; --vbeb-ri-w: auto;',
					'right'   => '--vbeb-ri-ta: right; --vbeb-ri-w: auto;',
					'justify' => '--vbeb-ri-ta: center; --vbeb-ri-w: 100%;',
				),
				'selectors'            => array(
					'{{WRAPPER}} .vbeb-reqinfo-wrap' => '{{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				// VBEB 7.406.0: anche sull'icona (dimensione dal font-size).
				'selector' => '{{WRAPPER}} .vbo-reqinfo-opener, {{WRAPPER}} .vbo-reqinfo-opener .vbeb-reqinfo-btn-icon-wrap i',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'button_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'selector' => '{{WRAPPER}} .vbo-reqinfo-opener',
			)
		);

		// VBEB 7.258.0, su richiesta dell'utente: le impostazioni del Pulsante
		// di Elementor (tranne "Posizione"). Normale: colore del testo, tipo
		// di sfondo (tinta unita o sfumatura), ombra; Hover: colore del
		// testo, tipo di sfondo, colore del bordo; poi bordo, raggio e rientro
		// per entrambi. Restano i nomi di prima dove il controllo e' lo
		// stesso (testo, ombra, bordo, raggio, rientro); i vecchi colori di
		// sfondo semplici e l'ombra di hover non ci sono piu'.
		$pulsante       = '{{WRAPPER}} .vbo-reqinfo-opener';
		$pulsante_hover = '{{WRAPPER}} .vbo-reqinfo-opener:hover, {{WRAPPER}} .vbo-reqinfo-opener:focus';

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab( 'button_style_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ) ) );

		$this->add_control(
			'button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$pulsante => 'color: {{VALUE}}; fill: {{VALUE}};',
					// VBEB 7.406.0: anche sull'icona.
					'{{WRAPPER}} .vbo-reqinfo-opener .vbeb-reqinfo-btn-icon-wrap i, {{WRAPPER}} .vbo-reqinfo-opener .vbeb-reqinfo-btn-icon-wrap svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
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
				'selectors' => array(
					$pulsante_hover => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .vbo-reqinfo-opener:hover .vbeb-reqinfo-btn-icon-wrap i, {{WRAPPER}} .vbo-reqinfo-opener:hover .vbeb-reqinfo-btn-icon-wrap svg, {{WRAPPER}} .vbo-reqinfo-opener:focus .vbeb-reqinfo-btn-icon-wrap i, {{WRAPPER}} .vbo-reqinfo-opener:focus .vbeb-reqinfo-btn-icon-wrap svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
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

		// VBEB 7.583.0, su richiesta dell'utente: come la scheda Hover del
		// widget Pulsante di Elementor (button-trait.php): ombra, durata
		// della transizione e animazione al passaggio del mouse (classe
		// "elementor-animation-*" sul pulsante, aggiunta nel render()).
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow_hover',
				'selector' => $pulsante_hover,
			)
		);

		$this->add_control(
			'button_hover_transition_duration',
			array(
				'label'      => esc_html__( 'Transition Duration', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's', 'ms', 'custom' ),
				'default'    => array( 'unit' => 's' ),
				'selectors'  => array( $pulsante => 'transition-duration: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'hover_animation',
			array(
				'label' => esc_html__( 'Hover Animation', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
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
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( $pulsante => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'separator'  => 'before',
				'selectors'  => array( $pulsante => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// VBEB 7.256.0, su richiesta dell'utente: tolte le sezioni "Stile -
		// Finestra Modale" e "Stile - Pulsante Invia". Il modulo nel popup ha
		// ora il suo widget, "VBEB Camera - Modulo informazioni", e prende
		// solo le impostazioni di quello; le vecchie sezioni lo modificavano
		// dal pulsante (i loro valori salvati non si applicano piu').
	}

	/**
	 * Dimensioni preset (padding/font-size) equivalenti a quelle del
	 * widget "Pulsante" nativo di Elementor, applicate alla nostra classe
	 * dedicata cosi' l'utente puo' comunque sovrascriverle con i controlli
	 * espliciti "Tipografia"/"Padding" qui sopra (che hanno selettori piu'
	 * specifici e vincono sempre in cascata CSS).
	 */
	private function get_size_css( $size ) {
		$presets = array(
			'xs' => array( 'padding' => '5px 10px', 'font' => '12px' ),
			'sm' => array( 'padding' => '9px 15px', 'font' => '13px' ),
			'md' => array( 'padding' => '12px 24px', 'font' => '15px' ),
			'lg' => array( 'padding' => '15px 30px', 'font' => '17px' ),
			'xl' => array( 'padding' => '20px 40px', 'font' => '19px' ),
		);
		return isset( $presets[ $size ] ) ? $presets[ $size ] : $presets['md'];
	}

	/**
	 * Il solo pulsante, per l'anteprima nell'editor.
	 *
	 * VBEB 7.90.0. Sul front-end il widget ristampa l'HTML che VikBooking
	 * genera per la scheda camera: il pulsante e il popup con il modulo
	 * (views/roomdetails/tmpl/default_reqinfo.php). Nell'editor quell'HTML
	 * non viene catturato.
	 *
	 * SOLO IL PULSANTE, per scelta dell'utente: per il modulo dentro il
	 * popup e' previsto un template dedicato, da fare in seguito.
	 *
	 * IL MARKUP E' QUELLO DELL'ORIGINALE, contenitore compreso, cosi' il
	 * resto del render lo tratta esattamente come sul front-end: testo,
	 * icone e dimensione scelti nel pannello vengono applicati dallo stesso
	 * codice, e cio' che si vede nell'editor e' cio' che si vedra' sul sito.
	 *
	 * IL PULSANTE E' INERTE: la funzione che apre il popup nell'editor non
	 * esiste, e un clic produrrebbe solo un errore nella console.
	 *
	 * @return string
	 */
	private function pulsante_anteprima() {
		// VBEB free: nell'editor nella lingua del template aperto.
		$testo = class_exists( 'JText' ) ? vbeb_vbo_testo( 'VBOROOMREQINFOBTN' ) : 'VBOROOMREQINFOBTN';

		return '<div class="vbo-reqinfo-cont"><span><a href="#" onclick="return false;" class="vbo-reqinfo-opener vbo-pref-color-btn">' . esc_html( $testo ) . '</a></span></div>';
	}

	/**
	 * Il popup con il template scelto al posto del titolo e del modulo.
	 *
	 * VBEB 7.253.0. Nell'HTML di VikBooking il popup e' "#vbdialog-overlay"
	 * con dentro il riquadro ".vbdialog-inner.vbdialog-reqinfo", che contiene
	 * il titolo (h3) e il modulo (default_reqinfo.php, righe 36-94). Si
	 * sostituisce il contenuto del riquadro con il template: il riquadro, il
	 * pulsante e lo script che apre e chiude il popup restano quelli di
	 * VikBooking. Il widget "Modulo informazioni" del template stampa il
	 * modulo dallo stesso HTML catturato, con lo stesso gettone di
	 * sicurezza, e non ci sono due moduli nella pagina.
	 *
	 * @param string $content HTML di VikBooking.
	 * @param int    $tpl     Template scelto.
	 * @return string
	 */
	private function con_template( $content, $tpl ) {
		static $in_corso = false;

		if ( $tpl <= 0 || $in_corso || ! class_exists( '\Elementor\Plugin' ) ) {
			return $content;
		}

		$post = get_post( $tpl );

		if ( ! $post || 'publish' !== $post->post_status || 'elementor_library' !== $post->post_type ) {
			return $content;
		}

		// VBEB free: con Polylang o WPML, la copia del popup nella lingua
		// della pagina (se esiste ed e' pubblicata).
		if ( function_exists( 'vbeb_template_lingua' ) ) {
			$tpl = vbeb_template_lingua( $tpl );
		}

		if ( ! preg_match( '/(<div class="vbdialog-inner vbdialog-reqinfo">)(.*?<\/form>\s*)(<\/div>)/s', $content ) ) {
			return $content;
		}

		$in_corso = true;

		if ( class_exists( 'VBEB_Reqinfoform_Widget' ) ) {
			VBEB_Reqinfoform_Widget::$nel_popup = true;
		}

		$html = (string) \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $tpl, true );

		if ( class_exists( 'VBEB_Reqinfoform_Widget' ) ) {
			VBEB_Reqinfoform_Widget::$nel_popup = false;
		}

		$in_corso = false;

		if ( '' === trim( $html ) ) {
			return $content;
		}

		// VBEB 7.481.0: con un template "VBEB Popup" il riquadro prende le
		// sue classi, cosi' le impostazioni di Stile del template (riquadro e
		// sfondo scuro) valgono sul popup vero.
		$apertura = '';
		if ( 'vbeb-popup' === get_post_meta( $tpl, '_elementor_template_type', true ) ) {
			$apertura = '<div class="vbdialog-inner vbdialog-reqinfo vbeb-popup vbeb-popup-' . (int) $tpl . '">';
			// VBEB 7.487.0: lo scorrimento passa a un contenitore interno,
			// cosi' il riquadro puo' lasciar uscire il pulsante di chiusura
			// (vedi vbeb_popup_css_sito()).
			$html = '<div class="vbeb-popup-scroll">' . $html . '</div>';
			// VBEB 7.487.0, su richiesta dell'utente: "Mostra pulsante di
			// chiusura" (Impostazioni del template VBEB Popup). Chiude con la
			// funzione di VikBooking (default_reqinfo.php, riga 98); titolo
			// "Close" come quello di fancybox (jquery.fancybox.js, riga 144).
			$imp = get_post_meta( $tpl, '_elementor_page_settings', true );
			if ( is_array( $imp ) && isset( $imp['vbeb_popup_close'] ) && 'yes' === $imp['vbeb_popup_close'] ) {
				$html = '<a class="vbeb-popup-close" href="javascript:;" title="Close" onclick="if(typeof vboHideRequestInfo===\'function\'){vboHideRequestInfo();}return false;"></a>' . $html;
			}
			if ( function_exists( 'vbeb_popup_css_sito' ) ) {
				$html .= vbeb_popup_css_sito();
			}
		}

		return preg_replace_callback(
			'/(<div class="vbdialog-inner vbdialog-reqinfo">)(.*?<\/form>\s*)(<\/div>)/s',
			function ( $m ) use ( $html, $apertura ) {
				return ( '' !== $apertura ? $apertura : $m[1] ) . $html . $m[3];
			},
			$content,
			1
		);
	}

	protected function render() {
		$in_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		// VBEB 7.90.0: nell'editor, se l'HTML vero manca, si usa il solo
		// pulsante. SENZA GUARDARE L'OPZIONE DELLA CAMERA: sul front-end il
		// pulsante compare solo se la camera ha la richiesta informazioni
		// attiva, ma nell'editor serve vederlo per impaginarlo, qualunque
		// sia la camera di anteprima.
		if ( $in_editor && ( ! VBEB_Room_Context::has_context() || empty( VBEB_Room_Context::$reqinfo_html ) ) ) {
			$content = $this->pulsante_anteprima();

			if ( function_exists( 'vbeb_stili_vikbooking_editor' ) ) {
				// Il pulsante usa il colore preferito di VikBooking.
				echo vbeb_stili_vikbooking_editor(); // phpcs:ignore -- foglio di VikBooking e variabili dei suoi colori.
			}
		} elseif ( ! VBEB_Room_Context::has_context() || empty( VBEB_Room_Context::$reqinfo_html ) ) {
			return;
		} else {
			$content = VBEB_Room_Context::$reqinfo_html;
		}

		$settings   = $this->get_settings_for_display();
		$widget_id  = $this->get_id();
		$size_class = 'vbeb-reqinfo-size-' . $widget_id;
		// VBEB 7.405.0: tolte "Dimensione", "Allineamento" e "Animazione al
		// passaggio del mouse" su richiesta dell'utente; resta la misura
		// media di prima (sovrascrivibile da Tipografia e Rientro).
		$size_css   = $this->get_size_css( 'md' );

		// Individua il vero pulsante generato da VikBooking (testo + attributi),
		// senza toccare l'onclick originale (che apre davvero la finestra modale).
		if ( preg_match( '/<a([^>]*class="[^"]*vbo-reqinfo-opener[^"]*"[^>]*)>(.*?)<\/a>/is', $content, $m ) ) {
			$attrs         = $m[1];
			$original_text = $m[2];

			$label = ( '' !== $settings['button_text'] ) ? $settings['button_text'] : $original_text;

			$icon_html = '';
			if ( ! empty( $settings['selected_icon']['value'] ) ) {
				ob_start();
				Icons_Manager::render_icon( $settings['selected_icon'], array( 'aria-hidden' => 'true' ) );
				$icon_html = ob_get_clean();
			}

			$icon_side = isset( $settings['icon_align'] ) ? $settings['icon_align'] : 'right';

			$new_inner = '';
			if ( $icon_html && 'left' === $icon_side ) {
				$new_inner .= '<span class="vbeb-reqinfo-btn-icon-wrap vbeb-icon-left">' . $icon_html . '</span>';
			}
			$new_inner .= '<span class="vbeb-reqinfo-btn-text">' . wp_kses_post( $label ) . '</span>';
			if ( $icon_html && 'right' === $icon_side ) {
				$new_inner .= '<span class="vbeb-reqinfo-btn-icon-wrap vbeb-icon-right">' . $icon_html . '</span>';
			}

			$extra_classes = 'vbeb-reqinfo-btn ' . esc_attr( $size_class );
			// VBEB 7.583.0: animazione al passaggio del mouse (scheda Hover).
			if ( ! empty( $settings['hover_animation'] ) ) {
				$extra_classes .= ' elementor-animation-' . sanitize_html_class( $settings['hover_animation'] );
			}

			$new_attrs   = preg_replace( '/class="([^"]*)"/', 'class="$1 ' . $extra_classes . '"', $attrs, 1 );
			$replacement = '<a' . $new_attrs . '>' . $new_inner . '</a>';

			$content = preg_replace( '/<a[^>]*class="[^"]*vbo-reqinfo-opener[^"]*"[^>]*>.*?<\/a>/is', $replacement, $content, 1 );
		}

		// VBEB 7.247.0, su richiesta dell'utente: niente margine sopra il
		// pulsante. VikBooking da' ".vbo-reqinfo-cont { margin-top: 10px }"
		// (vikbooking_styles.css); qui una classe in piu' pesa di piu'.
		// VBEB 7.255.0, su richiesta dell'utente: niente contorno di focus
		// sul pulsante (quello del browser e l'anello di accessibilita' di
		// Hello Elementor, reset.css: "a:focus-visible { box-shadow: 0 0 0
		// 4px #fff; outline: 2px solid #000 }").
		echo '<style>.vbeb-reqinfo-wrap .vbo-reqinfo-cont{margin-top:0;}'
			// VBEB 7.406.0: "Posizione" (variabili dal pannello Stile; senza
			// valore le regole non hanno effetto) e icona che segue
			// Tipografia e Colore del testo del pulsante.
			. '.vbeb-reqinfo-wrap .vbo-reqinfo-cont{text-align:var(--vbeb-ri-ta);}'
			. '.vbeb-reqinfo-wrap .vbo-reqinfo-opener.vbeb-reqinfo-btn{width:var(--vbeb-ri-w);justify-content:center;box-sizing:border-box;}'
			. '.vbeb-reqinfo-wrap .vbeb-reqinfo-btn-icon-wrap{display:inline-flex;align-items:center;font-size:inherit;color:inherit;line-height:1;}'
			. '.vbeb-reqinfo-wrap .vbeb-reqinfo-btn-icon-wrap i{font-size:inherit;color:inherit;}'
			. '.vbeb-reqinfo-wrap .vbeb-reqinfo-btn-icon-wrap svg{width:1em;height:1em;fill:currentColor;}'
			. '.vbeb-reqinfo-wrap .vbo-reqinfo-opener:focus,.vbeb-reqinfo-wrap .vbo-reqinfo-opener:focus-visible{outline:none !important;}'
			. '.vbeb-reqinfo-wrap .vbo-reqinfo-opener:focus-visible{box-shadow:none;}'
			// VBEB 7.273.0: altezza del popup. VikBooking (vikbooking_styles.css,
			// righe 4141 e 4197) limita .vbdialog-inner a max-height:50% e
			// impone min-height:50% con overflow-y:scroll, quindi il modulo
			// veniva tagliato. Qui l'altezza segue il contenuto fino al 90%
			// dello schermo e la barra di scorrimento compare solo se serve.
			. '.vbeb-reqinfo-wrap .vbdialog-inner.vbdialog-reqinfo{box-sizing:border-box;height:auto;min-height:0;max-height:90vh;max-height:90dvh;overflow-y:auto;overscroll-behavior:contain;}'
			. '</style>';
		// VBEB 7.253.0: il template del popup, se scelto (non nell'editor,
		// dove si vede il solo pulsante).
		if ( ! $in_editor ) {
			$content = $this->con_template( $content, isset( $settings['form_template'] ) ? (int) $settings['form_template'] : 0 );
		}

		echo '<div class="vbeb-reqinfo-wrap">';
		echo $content; // phpcs:ignore -- HTML reale gia' generato e sicuro da VikBooking stesso, solo testo/classi aggiuntive gestite qui sopra con escaping.
		echo '</div>';

		// Dimensione preset (padding/font-size), sovrascrivibile dai controlli
		// espliciti "Tipografia"/"Rientro" nel pannello Stile (piu' specifici).
		// VBEB 7.258.0: senza "!important" sul padding, che batteva anche il
		// "Rientro" del pannello; una classe in piu' basta a vincere su
		// ".vbo-reqinfo-opener" di VikBooking (vikbooking_styles.css, riga
		// 3514).
		?>
		<style>
			.vbeb-reqinfo-wrap .<?php echo esc_attr( $size_class ); ?> {
				padding: <?php echo esc_attr( $size_css['padding'] ); ?>;
				font-size: <?php echo esc_attr( $size_css['font'] ); ?>;
				display: inline-flex;
				align-items: center;
			}
		</style>
		<?php
	}
}
