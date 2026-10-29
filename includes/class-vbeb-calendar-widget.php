<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;

/**
 * Widget Elementor "VikBooking Calendario Disponibilita'". Non ricostruisce
 * il calendario: incapsula l'HTML reale generato da VikBooking stesso
 * (catturato dall'override file tramite $this->loadTemplate('avcalendars'),
 * l'unico punto con accesso sicuro alle prenotazioni esistenti), e aggiunge
 * controlli di stile Elementor mirati sulle classi CSS reali del calendario
 * (verificate nel codice sorgente di VikBooking, non indovinate).
 */
class VBEB_Calendar_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_calendar_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Calendar', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'calendario', 'calendar', 'disponibilita', 'availability' );
	}

	/*
	 * VBEB 7.510.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i suoi testi (dominio "elementor"), cosi' la
	 * traduzione arriva dai file di lingua di Elementor. "Spaziatura interna"
	 * e "Padding" diventano "Padding" di Elementor ("Rientro"), come
	 * confermato dall'utente. Le altre (sezioni del calendario, colori della
	 * legenda e degli stati, frecce, ...) sono solo di VBEB e restano in
	 * italiano fino alla fase delle traduzioni.
	 */
	protected function register_controls() {

		/* ================= CONTENUTO - TITOLO ================= */
		$this->start_controls_section(
			'section_content_title',
			array(
				'label' => esc_html__( 'Heading', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// VBEB 7.347.0, su richiesta dell'utente: "Mostra titolo". Acceso
		// di default (come prima); spento, il titolo di VikBooking viene
		// tolto dal calendario.
		$this->add_control(
			'show_title',
			array(
				'label'        => esc_html__( 'Show Title', 'vbeb' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb' ),
				'label_off'    => esc_html__( 'No', 'vbeb' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'title_text',
			array(
				'label'       => esc_html__( 'Title', 'vbeb' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'placeholder' => esc_html__( 'Leave empty to use the original VikBooking text', 'vbeb' ),
				'default'     => '',
				'condition'   => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'vbeb' ),
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

		/* ================= STILE - TITOLO ================= */
		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => esc_html__( 'Heading', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.379.0, su richiesta dell'utente: come la sezione "Titolo"
		// del widget Titolo di Elementor, senza Normale/Hover. Stessi
		// identificativi di prima per colore e tipografia.
		$titolo = '{{WRAPPER}} .vbeb-calendar-title';

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
				// display:block perche' l'allineamento valga anche con i tag
				// in linea (span).
				'selectors' => array( $titolo => 'text-align: {{VALUE}}; display: block;' ),
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
					'luminosity'  => esc_html__( 'Luminosity', 'vbeb' ), // VBEB 7.510.0: in fondo, come in Elementor.
				),
				'selectors' => array( $titolo => 'mix-blend-mode: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $titolo => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - MENU MESI ================= */
		$this->start_controls_section(
			'section_style_month_select',
			array(
				'label' => esc_html__( 'Month Dropdown', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.653.0, su richiesta dell'utente: nota "month_select_note" tolta (nella lista "note widget").

		$this->add_control(
			'month_select_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} select.vbselectm' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'month_select_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} select.vbselectm' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'month_select_typography',
				'selector' => '{{WRAPPER}} select.vbselectm',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'month_select_border',
				'selector' => '{{WRAPPER}} select.vbselectm',
			)
		);

		$this->add_control(
			'month_select_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 50 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors' => array(
					'{{WRAPPER}} select.vbselectm' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'month_select_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} select.vbselectm' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - LEGGENDA STATO GIORNI ================= */
		$this->start_controls_section(
			'section_style_legend',
			array(
				'label' => esc_html__( 'Day Status Legend', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'legend_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ), // VBEB 7.652.0: era "Colore testo legenda".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vblegenda-lbl' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'legend_typography',
				'selector' => '{{WRAPPER}} .vblegenda-lbl',
			)
		);

		/*
		 * VBEB 7.448.0, su richiesta dell'utente: i colori degli stati. Ognuno
		 * colora il quadratino della legenda e le celle di quello stato.
		 * Non disponibile e Parzialmente non disponibile sostituiscono, solo
		 * dentro il widget, le variabili di VikBooking --vbo-red-color e
		 * --vbo-orange-color: le usano il quadratino, la cella intera (anche
		 * nel calendario con i prezzi, vedi render()) e i triangoli dei giorni
		 * di arrivo/partenza (cella e legenda). Disponibile: in VikBooking la
		 * cella non ha sfondo e il quadratino e' grigio chiaro; qui li colora
		 * entrambi. Vuoti, restano i colori di VikBooking.
		 */
		$this->add_control(
			'legend_color_free',
			array(
				'label'     => esc_html__( 'Available Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .vblegfree, {{WRAPPER}} td.vbtdfree' => 'background-color: {{VALUE}};',
				),
			)
		);

		// VBEB 7.649.0, su richiesta dell'utente: parzialmente non disponibile prima di non disponibile.
		$this->add_control(
			'legend_color_warning',
			array(
				'label'     => esc_html__( 'Partially Reserved Color', 'vbeb' ), // VBEB 7.650.0: era "Colore parzialmente non disponibile".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--vbo-orange-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'legend_color_busy',
			array(
				'label'     => esc_html__( 'Not Available Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--vbo-red-color: {{VALUE}};',
				),
			)
		);

		// VBEB 7.663.0, su richiesta dell'utente: "Tipo di bordo" dei quadratini
		// colorati della legenda (".vblegenda-status"); vuoto, restano i bordi
		// di VikBooking.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'legend_square_border',
				'selector' => '{{WRAPPER}} .vblegendediv .vblegenda-status',
			)
		);

		$this->end_controls_section();

		/* ================= STILE - CONTENITORE CALENDARI ================= */
		$this->start_controls_section(
			'section_style_calendars_container',
			array(
				'label' => esc_html__( 'Calendars Container', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.653.0, su richiesta dell'utente: nota "calendars_container_note" tolta (nella lista "note widget").

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'calendars_container_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .vbcalsblock',
			)
		);

		$this->add_responsive_control(
			'calendars_container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vbcalsblock' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'calendars_container_border',
				'selector' => '{{WRAPPER}} .vbcalsblock',
			)
		);

		$this->add_control(
			'calendars_container_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vbcalsblock' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'calendars_container_box_shadow',
				'selector' => '{{WRAPPER}} .vbcalsblock',
			)
		);

		$this->add_responsive_control(
			'calendars_container_gap',
			array(
				'label'      => esc_html__( 'Spacing Between Calendars', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 60 ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vbcalsblock' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - INTESTAZIONE MESE ================= */
		$this->start_controls_section(
			'section_style_month_header',
			array(
				'label' => esc_html__( 'Month Header', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.654.0, su richiesta dell'utente: sottosezione "Mese e anno"
		// con Colore di sfondo, Colore testo e Tipografia.
		$this->add_control(
			'month_year_heading',
			array(
				'label' => esc_html__( 'Month and Year', 'vbeb' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'month_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} tr.vbcaltrmonth td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'month_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ), // VBEB 7.655.0: era "Colore testo (mese e anno)".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbcaltrmonth-month, {{WRAPPER}} .vbcaltrmonth-year' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'month_typography',
				'selector' => '{{WRAPPER}} .vbcaltrmonth-month, {{WRAPPER}} .vbcaltrmonth-year',
			)
		);

		// VBEB 7.661.0: il colore passa da una variabile ("--vbeb-month-bc") e
		// la regola con !important sta nel tipo di bordo. Con un colore globale
		// Elementor riscrive la regola del colore come "border-color: var(...)"
		// perdendo il "!important" (core/files/css/base.php), e vinceva il
		// colore di VikBooking.
		// VBEB 7.660.0, su richiesta dell'utente: "Tipo di bordo" (con larghezza
		// e colore) della riga del mese. Con !important: VikBooking impone il
		// colore del bordo con la classe "vbo-pref-bordercolor"
		// (vikbooking_styles.css, riga 168, "border-color ... !important").
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'month_border',
				'selector'       => '{{WRAPPER}} tr.vbcaltrmonth td',
				'fields_options' => array(
					'border' => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-style: {{VALUE}} !important; border-color: var(--vbeb-month-bc, var(--vbo-pref-bgcolor)) !important;' ),
					),
					'width'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
					),
					'color'  => array(
						'selectors' => array( '{{SELECTOR}}' => '--vbeb-month-bc: {{VALUE}};' ),
					),
				),
			)
		);

		// VBEB 7.656.0, su richiesta dell'utente: sottosezione "Navigazione"
		// con le impostazioni delle frecce.
		$this->add_control(
			'nav_arrows_heading',
			array(
				'label'     => esc_html__( 'Navigation', 'vbeb' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// VBEB 7.657.0, su richiesta dell'utente: schede Normale / Hover.
		$this->start_controls_tabs( 'nav_arrows_tabs' );

		$this->start_controls_tab( 'nav_arrows_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb' ) ) );

		$this->add_control(
			'nav_arrows_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb' ), // VBEB 7.658.0: era "Colore frecce navigazione".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbo-rdet-avcal-nav-prev, {{WRAPPER}} .vbo-rdet-avcal-nav-next' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'nav_arrows_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ), // VBEB 7.658.0: era "Colore sfondo frecce navigazione".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbo-rdet-avcal-nav' => 'background: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'nav_arrows_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb' ) ) );

		$this->add_control(
			'nav_arrows_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'vbeb' ), // VBEB 7.658.0: era "Colore frecce navigazione".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbo-rdet-avcal-nav-prev:hover, {{WRAPPER}} .vbo-rdet-avcal-nav-next:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'nav_arrows_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ), // VBEB 7.658.0: era "Colore sfondo frecce navigazione (hover)".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbo-rdet-avcal-nav:hover' => 'background: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'nav_arrows_size',
			array(
				'label'      => esc_html__( 'Size', 'vbeb' ), // VBEB 7.659.0: era "Dimensione frecce navigazione".
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array( 'min' => 10, 'max' => 60 ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vbo-rdet-avcal-nav-prev, {{WRAPPER}} .vbo-rdet-avcal-nav-next' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - NOMI GIORNI (Lun, Mar, ...) ================= */
		$this->start_controls_section(
			'section_style_weekdays',
			array(
				'label' => esc_html__( 'Day Names', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'weekday_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} tr.vbcaldays td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'weekday_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} tr.vbcaldays td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'weekday_typography',
				'selector' => '{{WRAPPER}} tr.vbcaldays td',
			)
		);

		$this->end_controls_section();

		/* ================= STILE - NUMERI GIORNI ================= */
		$this->start_controls_section(
			'section_style_daynums',
			array(
				'label' => esc_html__( 'Day Cells', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.446.0, su richiesta dell'utente: tolte le schede per stato
		// (Vuoto, Libero, Occupato, Parziale) con le loro impostazioni; verranno
		// ricreate in altro modo.

		/*
		 * VBEB 7.449.0, su richiesta dell'utente: sottosezione "Disponibile",
		 * il bordo delle celle dei giorni liberi (".vbtdfree"), Normale e
		 * Hover. VikBooking da' a tutte le celle un bordo di 1px grigio chiaro
		 * (vikbooking_styles.css, riga 2511) e, al passaggio del mouse sui
		 * giorni liberi non passati, il bordo verde (riga 11647, solo nel
		 * calendario senza prezzi). Hover qui vale per entrambi i calendari,
		 * solo per i giorni non passati (cliccabili); selettore piu' pesante
		 * di quello di VikBooking.
		 */
		$this->add_control(
			'daynum_free_heading',
			array(
				'label' => esc_html__( 'Available', 'vbeb' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		// VBEB 7.450.0, su richiesta dell'utente: Tipografia (fuori dalle
		// schede) e Colore del testo (Normale e Hover) del numero del giorno:
		// lo span nella cella (calendario senza prezzi) o nel riquadro
		// ".vbcalpricedaynum" (calendario con i prezzi).
		$num_libero       = '{{WRAPPER}} tr.vbcalnumdays td.vbtdfree span, {{WRAPPER}} tr.vbcalnumdaysprice td.vbtdfree .vbcalpricedaynum span';
		$num_libero_hover = '{{WRAPPER}} tr.vbcalnumdays td.vbtdfree:not(.vbtdpast):hover span, {{WRAPPER}} tr.vbcalnumdaysprice td.vbtdfree:not(.vbtdpast):hover .vbcalpricedaynum span';

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'daynum_free_cell_typography',
				'selector' => $num_libero,
			)
		);

		$this->start_controls_tabs( 'daynum_free_tabs' );

		$this->start_controls_tab( 'daynum_free_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb' ) ) );

		$this->add_control(
			'daynum_free_cell_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $num_libero => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'daynum_free_cell_border',
				'selector' => '{{WRAPPER}} table tr td.vbtdfree',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'daynum_free_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb' ) ) );

		$this->add_control(
			'daynum_free_cell_text_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $num_libero_hover => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'daynum_free_cell_border_hover',
				'selector' => '{{WRAPPER}} table tr td.vbtdfree:not(.vbtdpast):hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// VBEB 7.651.0, su richiesta dell'utente: ordine Disponibile, Parzialmente
		// prenotato, Non disponibile, Prezzi.
		/*
		 * VBEB 7.452.0, su richiesta dell'utente: sottosezione "Parzialmente
		 * disponibile" (".vbtdwarning": alcune unita' occupate, altre
		 * libere), in entrambi i calendari. Selettori del numero piu' pesanti
		 * della regola fissa della 7.447.0.
		 */
		$num_parz = '{{WRAPPER}} tr.vbcalnumdays td.vbtdwarning span, {{WRAPPER}} tr.vbcalnumdaysprice td.vbtdwarning .vbcalpricedaynum span';

		$this->add_control(
			'daynum_warning_heading',
			array(
				'label'     => esc_html__( 'Partially Reserved', 'vbeb' ), // VBEB 7.650.0: era "Parzialmente disponibile".
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'daynum_warning_cell_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $num_parz => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'daynum_warning_cell_typography',
				'selector' => $num_parz,
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'daynum_warning_cell_border',
				'selector' => '{{WRAPPER}} table tr td.vbtdwarning',
			)
		);

		/*
		 * VBEB 7.451.0, su richiesta dell'utente: sottosezione "Non
		 * disponibile" (".vbtdbusy"), senza Normale/Hover (giorni non
		 * cliccabili). Colore del testo solo sui giorni interamente occupati:
		 * quelli di arrivo/partenza (".vbtdbusyforcheckin/-checkout") hanno
		 * la cella in gran parte chiara con il triangolo, e un testo pensato
		 * per lo sfondo pieno potrebbe sparire. Tipografia e bordo su tutti.
		 * Il bordo con !important: VikBooking lo impone cosi'
		 * (".vbcal .vbtdbusy", vikbooking_styles.css, riga 2514). Selettori
		 * del numero piu' pesanti della regola fissa della 7.447.0.
		 */
		$num_occ_tutti = '{{WRAPPER}} tr.vbcalnumdays td.vbtdbusy span, {{WRAPPER}} tr.vbcalnumdaysprice td.vbtdbusy .vbcalpricedaynum span';
		$num_occ_pieni = '{{WRAPPER}} tr.vbcalnumdays td.vbtdbusy:not(.vbtdbusyforcheckin):not(.vbtdbusyforcheckout) span, {{WRAPPER}} tr.vbcalnumdaysprice td.vbtdbusy:not(.vbtdbusyforcheckin):not(.vbtdbusyforcheckout) .vbcalpricedaynum span';

		$this->add_control(
			'daynum_busy_heading',
			array(
				'label'     => esc_html__( 'Not Available', 'vbeb' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'daynum_busy_cell_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $num_occ_pieni => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'daynum_busy_cell_typography',
				'selector' => $num_occ_tutti,
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'daynum_busy_cell_border',
				'selector'       => '{{WRAPPER}} table tr td.vbtdbusy',
				'fields_options' => array(
					'border' => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-style: {{VALUE}} !important; border-color: var(--vbeb-busy-bc, var(--vbo-light-border-color)) !important;' ),
					),
					'width'  => array(
						'selectors' => array( '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
					),
					'color'  => array(
						'selectors' => array( '{{SELECTOR}}' => '--vbeb-busy-bc: {{VALUE}};' ),
					),
				),
			)
		);

		// VBEB 7.383.0, su richiesta dell'utente: sottosezione "Prezzi", il
		// riquadro del prezzo nei giorni liberi (calendario con i prezzi:
		// ".vbcalpricedaycost > div", in VikBooking bordo 1px nel colore del
		// testo scelto in VikBooking e raggio 4px, vikbooking_styles.css,
		// riga 3410). Tipo di bordo e raggio, senza Normale/Hover e ombra.
		$prezzo = '{{WRAPPER}} .vbcalpricedaycost > div';

		$this->add_control(
			'daynum_price_heading',
			array(
				'label'     => esc_html__( 'Pricing', 'vbeb' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// VBEB 7.384.0, su richiesta dell'utente: spaziatura interna, prima
		// del bordo.
		$this->add_responsive_control(
			'daynum_price_padding',
			array(
				'label'      => esc_html__( 'Padding', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					$prezzo => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'daynum_price_border',
				'selector' => $prezzo,
			)
		);

		$this->add_responsive_control(
			'daynum_price_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					$prezzo => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// VBEB 7.384.0, su richiesta dell'utente: colore del testo (anche
		// degli span di valuta e numero) e colore di sfondo, dopo il raggio.
		$this->add_control(
			'daynum_price_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $prezzo . ', ' . $prezzo . ' span' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'daynum_price_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $prezzo => 'background-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$in_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$content   = VBEB_Room_Context::has_context() ? VBEB_Room_Context::$avcalendars_html : '';

		// VBEB 7.246.0, su richiesta dell'utente: nell'editor il calendario
		// vero della camera di anteprima, disegnato da VikBooking stesso
		// (vbeb_html_camera_anteprima()), con il suo foglio di stile, le
		// variabili dei colori e le icone.
		if ( empty( $content ) && $in_editor && function_exists( 'vbeb_html_camera_anteprima' ) ) {
			$content = vbeb_html_camera_anteprima( 'avcalendars' );

			if ( '' !== $content ) {
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
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">Calendario Disponibilità: il calendario reale comparirà sul front-end di una pagina camera.</div>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		// VBEB: sostituiamo il tag <h4> originale del titolo (generato da
		// VikBooking) con il tag HTML scelto e/o un testo personalizzato,
		// stessa identica logica usata nel widget Modulo Prenotazione.
		$tag = isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h4';
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'h4';
		}

		// VBEB 7.347.0: "Mostra titolo" spento, il titolo si toglie.
		$mostra_titolo = ! isset( $settings['show_title'] ) || 'yes' === $settings['show_title'];

		if ( ! $mostra_titolo ) {
			$content = preg_replace( '/<h4>.*?<\/h4>/is', '', $content, 1 );
		} elseif ( preg_match( '/<h4>(.*?)<\/h4>/is', $content, $m ) ) {
			$original_text = $m[1];
			$label = ( '' !== $settings['title_text'] ) ? $settings['title_text'] : $original_text;
			$replacement = '<' . $tag . ' class="vbeb-calendar-title">' . wp_kses_post( $label ) . '</' . $tag . '>';
			$content = preg_replace( '/<h4>.*?<\/h4>/is', $replacement, $content, 1 );
		}

		// VBEB 7.250.0, su richiesta dell'utente: niente margine sopra il
		// titolo (il sotto resta). Viene dal tema: Hello Elementor da' ai
		// titoli "margin-block-start: .5rem" (reset.css). Una classe sola:
		// le regole del pannello, piu' specifiche, continuano a valere.
		static $vbeb_margine = false;
		if ( ! $vbeb_margine || vbeb_editor_ripete() ) {
			$vbeb_margine = true;
			echo '<style>.vbeb-calendar-title{margin-top:0;margin-block-start:0;}'
				// VBEB 7.447.0, su richiesta dell'utente: nel calendario con i
				// prezzi (tabella ".vbcalprice") il colore di VikBooking dello
				// stato copre tutta la cella, come nel calendario senza prezzi
				// (".vbcal .vbtdbusy/.vbtdwarning", vikbooking_styles.css, riga
				// 2514). VikBooking li colorava solo nel riquadro del numero
				// (".vbcalprice .vbtdbusy .vbcalpricedaynum span", riga 3486):
				// il riquadro diventa trasparente e prende il colore del testo
				// della cella. Disponibile: nessun riempimento, come senza
				// prezzi (il riquadro bianco del numero sparisce). I giorni di
				// arrivo/partenza (".vbtdbusyforcheckin/-checkout", mezzi
				// colorati con un triangolo) restano come li disegna VikBooking.
				. '.vbeb-calendar-wrap .vbcalprice td.vbtdbusy:not(.vbtdbusyforcheckin):not(.vbtdbusyforcheckout){background:var(--vbo-red-color);color:var(--vbo-contrast-text-color);}'
				. '.vbeb-calendar-wrap .vbcalprice td.vbtdwarning{background:var(--vbo-orange-color);color:var(--vbo-contrast-text-color);}'
				. '.vbeb-calendar-wrap .vbcalprice td.vbtdbusy .vbcalpricedaynum span,.vbeb-calendar-wrap .vbcalprice td.vbtdwarning .vbcalpricedaynum span{background:transparent;border-color:transparent;color:inherit;}'
				. '.vbeb-calendar-wrap .vbcalprice td.vbtdfree .vbcalpricedaynum span{background:transparent;border-color:transparent;}'
				// VBEB 7.453.0, su richiesta dell'utente: giorni passati (di
				// qualsiasi stato) con opacita' 50% anche nel calendario con i
				// prezzi, lo stesso valore che VikBooking da' in quello senza
				// ("tr.vbcalnumdays td.vbtdpast", vikbooking_styles.css, riga
				// 11641).
				. '.vbeb-calendar-wrap tr.vbcalnumdaysprice td.vbtdpast{opacity:.5;}'
				// VBEB 7.626.0, su richiesta dell'utente: due calendari per
				// riga fino a 860px di schermo, poi uno, come la scheda camera
				// di VikBooking (vikbooking_styles.css, righe 8128 e 10368).
				// Quelle regole dipendono dal contenitore
				// "vbo-roomdet-calscontainer-inner", tolto dall'override:
				// restava la regola generica (mesi larghi almeno 320px), che
				// passava a un calendario in base alla larghezza del widget.
				// Si riprende solo la disposizione: il resto dell'aspetto
				// resta quello del pannello Stile.
				. '.vbeb-calendar-wrap .vbcalsblock{grid-template-columns:repeat(2,minmax(0,1fr));}'
				. '@media only screen and (max-width:860px){.vbeb-calendar-wrap .vbcalsblock{grid-template-columns:repeat(1,minmax(0,1fr));}}'
				// VBEB 7.627.0, su richiesta dell'utente: ogni calendario
				// riempie la sua colonna. VikBooking fra 681 e 980px di schermo
				// lo stringe a meta' ("width: calc(50% - 3px)", riga 10141);
				// nella sua scheda camera lo rimette al 100% (riga 8136).
				. '.vbeb-calendar-wrap .vbcalsblock .vbcaldivcont{width:100%;}'
				// VBEB 7.627.0: se due mesi affiancati non ci stanno (la
				// tabella e' piu' larga della sua colonna e finirebbe sopra
				// l'altra), uno sotto l'altro. La classe la mette lo script qui
				// sotto, che misura i calendari.
				. '.vbeb-calendar-wrap .vbcalsblock.vbeb-cal-una-colonna{grid-template-columns:minmax(0,1fr);}'
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
		echo '<div class="vbeb-calendar-wrap">';
		echo $content; // phpcs:ignore -- HTML reale gia' generato e sicuro da VikBooking stesso, solo tag/testo del titolo gestiti qui sopra con escaping.
		echo '</div>';
		// VBEB 7.627.0: con due colonne, se una tabella non entra nella sua
		// colonna i mesi vanno uno sotto l'altro. Si ricontrolla quando il
		// riquadro cambia larghezza e quando VikBooking cambia i mesi.
		?>
		<script>
		(function (s) {
			var wrap = s && s.previousElementSibling;
			if (!wrap || !wrap.classList.contains('vbeb-calendar-wrap')) { return; }
			var block = wrap.querySelector('.vbcalsblock');
			if (!block) { return; }
			var cls = 'vbeb-cal-una-colonna', last = -1;
			function check(force) {
				var w = block.clientWidth;
				if (!force && w === last) { return; }
				last = w;
				block.classList.remove(cls);
				if (window.getComputedStyle(block).gridTemplateColumns.split(' ').length < 2) { return; }
				var over = false;
				block.querySelectorAll('.vbcaldivcont').forEach(function (c) {
					var t = c.querySelector('table');
					if (t && t.offsetWidth > c.clientWidth + 1) { over = true; }
				});
				if (over) { block.classList.add(cls); }
			}
			check(true);
			if (window.ResizeObserver) { new ResizeObserver(function () { check(false); }).observe(block); }
			else { window.addEventListener('resize', function () { check(false); }); }
			if (window.MutationObserver) { new MutationObserver(function () { check(true); }).observe(block, { childList: true }); }
			window.addEventListener('load', function () { check(true); });
		})(document.currentScript);
		</script>
		<?php
		if ( $in_editor ) {
			echo '</div>';
		}
	}
}
