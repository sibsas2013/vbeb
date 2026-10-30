<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;

/**
 * Widget Elementor "VikBooking Descrizione Camera". Stessa fonte dati
 * (do_shortcode(wpautop($room['info']))), con gli stessi controlli del
 * widget nativo "Editor di Testo" di Elementor: colonne/spaziatura
 * colonne nella scheda Contenuto, colore/tipografia/allineamento/ombra
 * testo nella scheda Stile.
 */
class VBEB_Description_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_description_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Description', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-text';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'descrizione', 'description', 'testo', 'camera' );
	}

	/*
	 * VBEB 7.505.0, su richiesta dell'utente: i nomi delle impostazioni sono
	 * i testi del widget "Editor di testo" di Elementor (dominio
	 * "elementor", text-editor.php), cosi' la traduzione arriva dai file di
	 * lingua di Elementor.
	 */
	protected function register_controls() {

		/* ================= CONTENUTO - EDITOR DI TESTO =================
		 * VBEB 7.387.0, su richiesta dell'utente: al posto della sezione
		 * "Layout", come la sezione "Editor di testo" del widget Editor di
		 * testo di Elementor: Capolettera, Colonne, Spaziatura colonne.
		 * "Colonne" e "Spaziatura colonne" tengono gli identificativi di
		 * prima ("columns", "column_gap"). La capolettera e' la prima
		 * lettera del testo ("::first-letter"), grande e a sinistra come in
		 * Elementor (50px, interlinea 1, 10px di distanza dal testo).
		 */
		$this->start_controls_section(
			'section_content_layout',
			array(
				'label' => esc_html__( 'Text Editor', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'drop_cap',
			array(
				'label'        => esc_html__( 'Drop Cap', 'vbeb' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'vbeb' ),
				'label_off'    => esc_html__( 'Off', 'vbeb' ),
				'return_value' => 'yes',
				'default'      => '',
				// VBEB 7.388.0: classe sul widget ("vbeb-dropcap-yes") al
				// posto del CSS diretto, cosi' anche la sezione di stile
				// "Capolettera" vale solo con l'interruttore acceso.
				'prefix_class' => 'vbeb-dropcap-',
			)
		);

		$colonne = array( '' => esc_html__( 'Default', 'vbeb' ) );
		for ( $n = 1; $n <= 10; $n++ ) {
			$colonne[ (string) $n ] = (string) $n;
		}

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => esc_html__( 'Columns', 'vbeb' ),
				'type'      => Controls_Manager::SELECT,
				'separator' => 'before',
				'default'   => '',
				'options'   => $colonne,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-description' => 'column-count: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => esc_html__( 'Columns Gap', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
					'em' => array( 'min' => 0, 'max' => 10 ),
					'%'  => array( 'min' => 0, 'max' => 10 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-description' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - EDITOR DI TESTO =================
		 * VBEB 7.385.0, su richiesta dell'utente: come la sezione "Editor di
		 * testo" del widget Editor di testo di Elementor, nello stesso
		 * ordine: Allineamento, Tipografia, Ombra del testo, Spaziatura
		 * paragrafo, Normale/Hover con Colore del testo e Colore del link.
		 * Stessi identificativi di prima: i valori gia' scelti restano.
		 */
		$testo = '{{WRAPPER}} .vbeb-description';

		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => esc_html__( 'Text Editor', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'vbeb' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array( 'title' => esc_html__( 'Left', 'vbeb' ), 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => esc_html__( 'Center', 'vbeb' ), 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => esc_html__( 'Right', 'vbeb' ), 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => esc_html__( 'Justified', 'vbeb' ), 'icon' => 'eicon-text-align-justify' ),
				),
				'selectors' => array( $testo => 'text-align: {{VALUE}};' ),
				// VBEB 7.386.0: la linea va qui, dopo l'allineamento: il
				// "separator" sul gruppo Tipografia non viene disegnato.
				'separator' => 'after',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'typography',
				'selector'  => $testo,
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'selector' => $testo,
			)
		);

		$this->add_responsive_control(
			'paragraph_spacing',
			array(
				'label'      => esc_html__( 'Paragraph Spacing', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'vh', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
					'em' => array( 'min' => 0, 'max' => 10 ),
				),
				'selectors'  => array( $testo . ' p' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->start_controls_tabs( 'text_color_tabs', array( 'separator' => 'before' ) );

		$this->start_controls_tab( 'text_color_tab_normal', array( 'label' => esc_html__( 'Normal', 'vbeb' ) ) );

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $testo => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'link_color',
			array(
				'label'     => esc_html__( 'Link Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $testo . ' a' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'text_color_tab_hover', array( 'label' => esc_html__( 'Hover', 'vbeb' ) ) );

		// VBEB 7.582.0, su richiesta dell'utente: tolto "Colore del testo"
		// dalla scheda Hover (text_color_hover); resta solo in Normale.
		$this->add_control(
			'link_color_hover',
			array(
				'label'     => esc_html__( 'Link Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $testo . ' a:hover' => 'color: {{VALUE}};' ),
			)
		);

		// VBEB 7.582.0, su richiesta dell'utente (come "VBEB Camera -
		// Descrizione breve", 7.497.0, e il widget "Editor di testo" di
		// Elementor): solo nella scheda Hover; la durata vale per i link.
		$this->add_control(
			'link_hover_color_transition_duration',
			array(
				'label'      => esc_html__( 'Transition Duration', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's', 'ms', 'custom' ),
				'default'    => array( 'unit' => 's' ),
				'selectors'  => array( $testo . ' a' => 'transition-duration: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/* ================= STILE - CAPOLETTERA =================
		 * VBEB 7.388.0, su richiesta dell'utente: come il widget Editor di
		 * testo di Elementor, la sezione compare solo con "Capolettera"
		 * acceso. Stesse impostazioni e stessi tre aspetti ("Visualizza":
		 * Predefinito, Impilato, Con cornice, classe "vbeb-dropcap-view-*"
		 * sul widget) con gli stessi valori di partenza di Elementor
		 * (impilato: sfondo #69727d, lettera bianca, 10px di spazio
		 * interno; con cornice: bordo 3px #69727d). La capolettera e' la
		 * prima lettera del testo ("::first-letter").
		 */
		$lettera = '{{WRAPPER}} .vbeb-description::first-letter';
		$vista   = '{{WRAPPER}}.vbeb-dropcap-view-';

		$this->start_controls_section(
			'section_style_drop_cap',
			array(
				'label'     => esc_html__( 'Drop Cap', 'vbeb' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'drop_cap' => 'yes' ),
			)
		);

		$this->add_control(
			'drop_cap_view',
			array(
				'label'        => esc_html__( 'View', 'vbeb' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'default',
				'options'      => array(
					'default' => esc_html__( 'Default', 'vbeb' ),
					'stacked' => esc_html__( 'Stacked', 'vbeb' ),
					'framed'  => esc_html__( 'Framed', 'vbeb' ),
				),
				'prefix_class' => 'vbeb-dropcap-view-',
			)
		);

		$this->add_control(
			'drop_cap_primary_color',
			array(
				'label'     => esc_html__( 'Primary Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$vista . 'stacked .vbeb-description::first-letter' => 'background-color: {{VALUE}};',
					$vista . 'framed .vbeb-description::first-letter, ' . $vista . 'default .vbeb-description::first-letter' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'drop_cap_secondary_color',
			array(
				'label'     => esc_html__( 'Secondary Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'drop_cap_view!' => 'default' ),
				'selectors' => array(
					$vista . 'stacked .vbeb-description::first-letter' => 'color: {{VALUE}};',
					$vista . 'framed .vbeb-description::first-letter' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'drop_cap_shadow',
				'selector' => $lettera,
			)
		);

		$this->add_control(
			'drop_cap_size',
			array(
				'label'      => esc_html__( 'Size', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'default'    => array( 'size' => 5, 'unit' => 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'condition'  => array( 'drop_cap_view!' => 'default' ),
				'selectors'  => array( $lettera => 'padding: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'drop_cap_space',
			array(
				'label'      => esc_html__( 'Space', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'default'    => array( 'size' => 10, 'unit' => 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
				'selectors'  => array( $lettera => 'margin-inline-end: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'drop_cap_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'default'    => array( 'unit' => '%' ),
				'range'      => array( '%' => array( 'min' => 0, 'max' => 50 ) ),
				'condition'  => array( 'drop_cap_view!' => 'default' ),
				'selectors'  => array( $lettera => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'drop_cap_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'vbeb' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'condition'  => array( 'drop_cap_view' => 'framed' ),
				'selectors'  => array( $lettera => 'border-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'drop_cap_typography',
				'selector' => $lettera,
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! VBEB_Room_Context::has_context() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'Room - Description: the real text will appear on the front end of a room page.', 'vbeb' ) . '</div>';
			}
			return;
		}

		$info = VBEB_Room_Context::get( 'info', '' );

		if ( '' === trim( wp_strip_all_tags( $info ) ) ) {
			return;
		}

		// Stessa identica logica del template originale VikBooking per il campo descrizione.
		$html = do_shortcode( wpautop( $info ) );

		// VBEB 7.248.0, su richiesta dell'utente: niente margine attorno al
		// testo. Non viene da una "spaziatura paragrafo" di Elementor (il
		// widget non e' l'Editor di testo; la sua "Spaziatura paragrafo" si
		// applica solo se impostata), ma dal tema: Hello Elementor da' a ogni
		// paragrafo "margin-block-end: .9rem" (reset.css), altri temi anche un
		// margine sopra. Si azzerano il margine sopra del primo elemento e
		// quello sotto dell'ultimo: la spaziatura fra un paragrafo e l'altro
		// resta, e la "Spaziatura paragrafo" del pannello continua a valere
		// fra i paragrafi.
		static $vbeb_margini = false;
		if ( ! $vbeb_margini || vbeb_editor_ripete() ) {
			$vbeb_margini = true;
			echo '<style>.vbeb-description>:first-child{margin-top:0 !important;margin-block-start:0 !important;}.vbeb-description>:last-child{margin-bottom:0 !important;margin-block-end:0 !important;}'
				// VBEB 7.388.0: capolettera (solo con "Capolettera" acceso),
				// con i valori di partenza del widget Editor di testo di
				// Elementor per i tre aspetti.
				. '.vbeb-dropcap-yes .vbeb-description::first-letter{float:left;text-align:center;font-size:50px;line-height:1;margin-inline-end:10px;}'
				. '.vbeb-dropcap-yes.vbeb-dropcap-view-stacked .vbeb-description::first-letter{padding:10px;background-color:#69727d;color:#fff;}'
				. '.vbeb-dropcap-yes.vbeb-dropcap-view-framed .vbeb-description::first-letter{padding:10px;color:#69727d;border:3px solid;background-color:transparent;}'
				. '</style>';
		}

		echo '<div class="vbeb-description">' . $html . '</div>'; // phpcs:ignore -- HTML gia' gestito da VikBooking/WordPress (do_shortcode+wpautop), stessa logica del tag dinamico Descrizione.
	}
}
