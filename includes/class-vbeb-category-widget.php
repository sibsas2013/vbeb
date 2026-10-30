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
 * Widget Elementor "VikBooking Categoria Camera": mostra la categoria
 * della camera (stessa funzione VikBooking::sayCategory() usata nei
 * template originali) con la possibilita' di scegliere il tag HTML con
 * cui viene stampata (h1-h6, div, span, p), come il widget "Titolo"
 * nativo di Elementor.
 */
class VBEB_Category_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_category_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Category', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'categoria', 'category', 'camera' );
	}

	/*
	 * VBEB 7.500.0, su richiesta dell'utente: i nomi delle impostazioni sono
	 * i testi di Elementor (widget "Titolo", dominio "elementor"), cosi' la
	 * traduzione arriva dai file di lingua di Elementor. Come "Nome" (7.498.0
	 * e 7.499.0).
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_category',
			array(
				'label' => esc_html__( 'Heading', 'vbeb' ), // VBEB 7.366.0: era "Categoria".
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'html_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'vbeb' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'span',
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
			'section_style_category',
			array(
				'label' => esc_html__( 'Heading', 'vbeb' ), // VBEB 7.366.0: era "Stile".
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'category_force_block',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'selectors' => array(
					// VBEB: forziamo display:block indipendentemente dal tag HTML
					// scelto (anche span, che e' inline per natura), altrimenti
					// text-align non avrebbe alcun effetto visivo - stesso
					// accorgimento usato dal widget "Titolo" nativo di Elementor.
					'{{WRAPPER}} .vbeb-room-category' => 'display: block;',
				),
			)
		);

		$this->add_responsive_control(
			'category_align',
			array(
				'label'   => esc_html__( 'Alignment', 'vbeb' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array( 'title' => esc_html__( 'Left', 'vbeb' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'vbeb' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => esc_html__( 'Right', 'vbeb' ), 'icon' => 'eicon-text-align-right' ),
					'justify'=> array( 'title' => esc_html__( 'Justified', 'vbeb' ), 'icon' => 'eicon-text-align-justify' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vbeb-room-category' => 'text-align: {{VALUE}};',
				),
				// VBEB 7.500.0: linea tra Allineamento e Tipografia, come il
				// widget "Titolo" di Elementor (heading.php, riga 295).
				'separator' => 'after',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'category_typography',
				'selector' => '{{WRAPPER}} .vbeb-room-category',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => 'category_text_stroke',
				'selector' => '{{WRAPPER}} .vbeb-room-category',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'category_text_shadow',
				'selector' => '{{WRAPPER}} .vbeb-room-category',
			)
		);

		$this->add_control(
			'category_blend_mode',
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
					'luminosity'  => esc_html__( 'Luminosity', 'vbeb' ), // VBEB 7.500.0: in fondo, come in Elementor.
				),
				'selectors' => array(
					'{{WRAPPER}} .vbeb-room-category' => 'mix-blend-mode: {{VALUE}};',
				),
			)
		);

		// VBEB 7.403.0, su richiesta dell'utente: tolte le schede
		// Normale/Hover (e il colore al passaggio del mouse); resta il
		// colore del testo, stesso identificativo.
		$this->add_control(
			'category_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-room-category' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$tag = isset( $settings['html_tag'] ) ? $settings['html_tag'] : 'span';
		// Whitelist di sicurezza: solo i tag ammessi nei controlli sopra.
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'span';
		}

		if ( VBEB_Room_Context::has_context() && class_exists( 'VikBooking' ) ) {
			$idcat    = VBEB_Room_Context::get( 'idcat' );
			$vbo_tn   = VBEB_Room_Context::$vbo_tn;
			// Stessa identica funzione usata nei template originali di VikBooking.
			$category = VikBooking::sayCategory( $idcat, $vbo_tn );
		} else {
			$category = \Elementor\Plugin::$instance->editor->is_edit_mode() ? __( '[Category]', 'vbeb' ) : '';
		}

		if ( '' === $category ) {
			return;
		}

		echo '<' . tag_escape( $tag ) . ' class="vbeb-room-category">' . esc_html( $category ) . '</' . tag_escape( $tag ) . '>';
	}
}
