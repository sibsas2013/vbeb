<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;

/**
 * Widget Elementor "VBEB Camera - Descrizione breve": mostra la
 * descrizione breve della camera (campo reale "smalldesc", diverso dal
 * campo "info" usato da "VBEB Descrizione Camera" - verificato nel vero
 * template "roomslist" di VikBooking: do_shortcode($r['smalldesc']),
 * senza wpautop, dato che e' testo breve non paragrafato).
 *
 * Pensato soprattutto per essere usato dentro il template Elementor
 * scelto in VBEB -> Template -> "Template per la Lista Camere", ma
 * funziona ovunque VBEB_Room_Context sia popolato.
 */
class VBEB_Smalldescription_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_smalldescription_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Short Description', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-text';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'descrizione', 'breve', 'small', 'description', 'camera', 'lista' );
	}

	/*
	 * VBEB 7.506.0, su richiesta dell'utente: i nomi delle impostazioni sono
	 * i testi del widget "Editor di testo" di Elementor (dominio
	 * "elementor", text-editor.php), cosi' la traduzione arriva dai file di
	 * lingua di Elementor.
	 */
	protected function register_controls() {

		/* ================= CONTENUTO - EDITOR DI TESTO =================
		 * VBEB 7.390.0, su richiesta dell'utente: pannello Contenuto con la
		 * sezione "Editor di testo", come in "VBEB Camera - Descrizione"
		 * (7.387.0): Capolettera, Colonne, Spaziatura colonne. La
		 * capolettera (classe "vbeb-dropcap-yes" sul widget) e' la prima
		 * lettera del testo, grande e a sinistra come in Elementor.
		 */
		$this->start_controls_section(
			'section_content_text',
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
					'{{WRAPPER}} .vbeb-smalldescription' => 'column-count: {{VALUE}};',
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
					'{{WRAPPER}} .vbeb-smalldescription' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - EDITOR DI TESTO =================
		 * VBEB 7.391.0, su richiesta dell'utente: come in "VBEB Camera -
		 * Descrizione" (7.385.0-7.386.0) e nel widget Editor di testo di
		 * Elementor: Allineamento (linea dopo), Tipografia, Ombra del testo,
		 * Spaziatura paragrafo, Normale/Hover con Colore del testo e Colore
		 * del link. Stessi identificativi di prima: i valori restano.
		 */
		$testo = '{{WRAPPER}} .vbeb-smalldescription';

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

		// VBEB 7.496.0, su richiesta dell'utente: tolto "Colore del testo"
		// dalla scheda Hover (text_color_hover); resta solo in Normale.
		$this->add_control(
			'link_color_hover',
			array(
				'label'     => esc_html__( 'Link Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $testo . ' a:hover' => 'color: {{VALUE}};' ),
			)
		);

		// VBEB 7.497.0, su richiesta dell'utente: come il widget "Editor di
		// testo" di Elementor (text-editor.php, riga 392), solo nella scheda
		// Hover; la durata vale per i link.
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
		 * VBEB 7.392.0, su richiesta dell'utente (come "VBEB Camera -
		 * Descrizione", 7.388.0): come il widget Editor di
		 * testo di Elementor, la sezione compare solo con "Capolettera"
		 * acceso. Stesse impostazioni e stessi tre aspetti ("Visualizza":
		 * Predefinito, Impilato, Con cornice, classe "vbeb-dropcap-view-*"
		 * sul widget) con gli stessi valori di partenza di Elementor
		 * (impilato: sfondo #69727d, lettera bianca, 10px di spazio
		 * interno; con cornice: bordo 3px #69727d). La capolettera e' la
		 * prima lettera del testo ("::first-letter").
		 */
		$lettera = '{{WRAPPER}} .vbeb-smalldescription::first-letter';
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
					$vista . 'stacked .vbeb-smalldescription::first-letter' => 'background-color: {{VALUE}};',
					$vista . 'framed .vbeb-smalldescription::first-letter, ' . $vista . 'default .vbeb-smalldescription::first-letter' => 'color: {{VALUE}}; border-color: {{VALUE}};',
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
					$vista . 'stacked .vbeb-smalldescription::first-letter' => 'color: {{VALUE}};',
					$vista . 'framed .vbeb-smalldescription::first-letter' => 'background-color: {{VALUE}};',
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
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'Room - Short Description: the real text will appear on the front end of a room page or room list.', 'vbeb' ) . '</div>';
			}
			return;
		}

		$smalldesc = VBEB_Room_Context::get( 'smalldesc', '' );

		if ( '' === trim( wp_strip_all_tags( $smalldesc ) ) ) {
			return;
		}

		// Stessa identica logica del vero template "roomslist" di VikBooking per questo campo.
		$html = do_shortcode( $smalldesc );

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
			echo '<style>.vbeb-smalldescription>:first-child{margin-top:0 !important;margin-block-start:0 !important;}.vbeb-smalldescription>:last-child{margin-bottom:0 !important;margin-block-end:0 !important;}'
				// VBEB 7.390.0: capolettera, solo con "Capolettera" acceso.
				. '.vbeb-dropcap-yes .vbeb-smalldescription::first-letter{float:left;text-align:center;font-size:50px;line-height:1;margin-inline-end:10px;}'
				// VBEB 7.392.0: aspetti "Impilato" e "Con cornice".
				. '.vbeb-dropcap-yes.vbeb-dropcap-view-stacked .vbeb-smalldescription::first-letter{padding:10px;background-color:#69727d;color:#fff;}'
				. '.vbeb-dropcap-yes.vbeb-dropcap-view-framed .vbeb-smalldescription::first-letter{padding:10px;color:#69727d;border:3px solid;background-color:transparent;}'
				. '</style>';
		}

		echo '<div class="vbeb-smalldescription">' . $html . '</div>'; // phpcs:ignore -- HTML gia' gestito da VikBooking/WordPress (do_shortcode), stessa logica del template originale.
	}
}
