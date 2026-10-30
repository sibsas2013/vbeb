<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Text_Shadow;

/**
 * Widget Elementor "Titolo VikBooking": mostra il nome della camera
 * (stesso dato del nome camera) con la
 * possibilita' di scegliere il tag HTML con cui viene stampato
 * (h1-h6, div, span, p), come il widget "Titolo" nativo di Elementor.
 */
class VBEB_Title_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_title_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Name', 'vbeb-bridge-for-vikbooking-and-elementor' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'titolo', 'title', 'nome', 'camera' );
	}

	/*
	 * VBEB 7.498.0, su richiesta dell'utente: i nomi delle impostazioni sono
	 * i testi di Elementor (stesso testo inglese e dominio "elementor"), cosi'
	 * la traduzione arriva dai file di lingua di Elementor (Colore del testo,
	 * Modalita' di fusione, ...). H1-H6, div, span e p restano come sono.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_title',
			array(
				'label' => esc_html__( 'Heading', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.580.0: "Heading" come il widget Titolo di Elementor (in spagnolo "Encabezado").
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'html_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
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

		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => esc_html__( 'Heading', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.363.0: era "Stile".
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_force_block',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'selectors' => array(
					// VBEB: forziamo display:block indipendentemente dal tag HTML
					// scelto (anche span, che e' inline per natura), altrimenti
					// text-align non avrebbe alcun effetto visivo - stesso
					// accorgimento usato dal widget "Titolo" nativo di Elementor.
					'{{WRAPPER}} .vbeb-room-title' => 'display: block;',
				),
			)
		);

		$this->add_responsive_control(
			'title_align',
			array(
				'label'   => esc_html__( 'Alignment', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array( 'title' => esc_html__( 'Left', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => esc_html__( 'Right', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-right' ),
					'justify'=> array( 'title' => esc_html__( 'Justified', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-justify' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vbeb-room-title' => 'text-align: {{VALUE}};',
				),
				// VBEB 7.499.0, su richiesta dell'utente: linea di separazione
				// tra Allineamento e Tipografia, come il widget "Titolo" di
				// Elementor (heading.php, riga 295).
				'separator' => 'after',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .vbeb-room-title',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => 'title_text_stroke',
				'selector' => '{{WRAPPER}} .vbeb-room-title',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .vbeb-room-title',
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
					// VBEB 7.499.0: in fondo, come nel widget "Titolo" di Elementor.
					'luminosity'  => esc_html__( 'Luminosity', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vbeb-room-title' => 'mix-blend-mode: {{VALUE}};',
				),
			)
		);

		// VBEB 7.363.0, su richiesta dell'utente: tolte le schede
		// Normale/Hover (e il colore al passaggio); resta "Colore del testo",
		// con lo stesso identificativo di prima.
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-room-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$tag = isset( $settings['html_tag'] ) ? $settings['html_tag'] : 'h2';
		// Whitelist di sicurezza: solo i tag ammessi nei controlli sopra.
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'h2';
		}

		if ( VBEB_Room_Context::has_context() ) {
			$name = VBEB_Room_Context::get( 'name', '' );
		} else {
			$name = \Elementor\Plugin::$instance->editor->is_edit_mode() ? __( '[Room Name]', 'vbeb-bridge-for-vikbooking-and-elementor' ) : '';
		}

		if ( '' === $name ) {
			return;
		}

		// VBEB 7.249.0, su richiesta dell'utente: niente margine, come il
		// Titolo di Elementor (".elementor-heading-title { margin: 0 }"). Il
		// margine viene dal tema: Hello Elementor da' ai titoli h1-h6
		// "margin-block-start: .5rem; margin-block-end: 1rem" (reset.css).
		// Una classe sola, come quello di Elementor: le regole del pannello,
		// piu' specifiche, continuano a valere.
		static $vbeb_margine = false;
		if ( ! $vbeb_margine ) {
			$vbeb_margine = true;
			echo '<style>.vbeb-room-title{margin:0;}</style>';
		}

		echo '<' . tag_escape( $tag ) . ' class="vbeb-room-title">' . esc_html( $name ) . '</' . tag_escape( $tag ) . '>';
	}
}
