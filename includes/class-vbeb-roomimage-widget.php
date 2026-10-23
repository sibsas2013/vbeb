<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

/**
 * Widget Elementor "VBEB Camera - Immagine principale": la sola foto
 * principale, senza carosello.
 *
 * VBEB 7.25.0. Fino a qui l'unico modo di mostrare la foto di una
 * camera era il widget Galleria, che le mostra tutte - principale piu'
 * extra - dentro uno Swiper. Va benissimo dove serve sfogliarle, ma
 * quando ne serve una sola porta con se' una libreria, un carosello e
 * una trentina di controlli che non servono.
 *
 * IL WIDGET GALLERIA NON E' STATO TOCCATO: questo gli sta accanto, non
 * lo sostituisce, e i due possono convivere nello stesso template.
 *
 * DOVE SI USA: nei template ripetuti per camera - la scheda del ciclo
 * della Lista Camere, i risultati di ricerca, il Dettaglio Camera -
 * cioe' ovunque VBEB_Room_Context sia pieno.
 *
 * LE DIMENSIONI DISPONIBILI SONO DUE, e la ragione sta in come
 * VikBooking salva i file (admin/controller.php, caricamento della
 * camera). Per le foto EXTRA genera tre file: originale, "big_" e
 * "thumb_". Per la foto PRINCIPALE no: salva il file - eventualmente
 * gia' ridimensionato, e in quel caso il nome comincia per "r_" - e dal
 * 1.7.5 ne affianca una miniatura di 96 pixel con il prefisso "mini_".
 * Non esistono quindi un "thumb_" ne' un "big_" della principale, ed e'
 * il motivo per cui VikBooking la stampa sempre senza prefisso in tutte
 * le sue viste.
 */
class VBEB_Roomimage_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_roomimage_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Main Image', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-image';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'camera', 'immagine', 'foto', 'principale' );
	}

	/*
	 * VBEB 7.503.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i testi del suo widget "Immagine" (dominio
	 * "elementor", image.php), cosi' la traduzione arriva dai file di lingua
	 * di Elementor. Restano in italiano, solo di VBEB: "Nome camera",
	 * "Dettaglio camera".
	 */
	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'Image', 'elementor' ), 'tab' => Controls_Manager::TAB_CONTENT ) );

		// VBEB 7.408.0, su richiesta dell'utente: tolta "Risoluzione
		// immagine" (con la sua nota); si usa sempre il file salvato da
		// VikBooking.
		$this->add_control(
			'caption',
			array(
				'label'       => esc_html__( 'Caption', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'none',
				'options'     => array(
					'none' => esc_html__( 'None', 'elementor' ),
					'name' => esc_html__( 'Room Name', 'vbeb' ),
				),
			)
		);

		$this->end_controls_section();

		/* ===================== STILE ===================== */
		$this->start_controls_section( 'section_style', array( 'label' => esc_html__( 'Image', 'elementor' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array( 'title' => esc_html__( 'Left', 'elementor' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'elementor' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => esc_html__( 'Right', 'elementor' ), 'icon' => 'eicon-text-align-right' ),
				),
				'selectors' => array( '{{WRAPPER}} .vbeb-room-image' => 'text-align: {{VALUE}};' ),
			)
		);

		// Larghezza e Larghezza massima agiscono entrambe su "max-width",
		// come nel widget nativo: la prima e' quella che si usa di
		// solito, la seconda serve a mettere un tetto quando la prima e'
		// espressa in percentuale.
		$this->add_responsive_control(
			'width',
			array(
				'label'          => esc_html__( 'Width', 'elementor' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( '%', 'px', 'em', 'rem', 'vw' ),
				'default'        => array( 'unit' => '%' ),
				'tablet_default' => array( 'unit' => '%' ),
				'mobile_default' => array( 'unit' => '%' ),
				'selectors'      => array( '{{WRAPPER}} .vbeb-room-image img' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'          => esc_html__( 'Max Width', 'elementor' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( '%', 'px', 'em', 'rem', 'vw' ),
				'default'        => array( 'unit' => '%' ),
				'tablet_default' => array( 'unit' => '%' ),
				'mobile_default' => array( 'unit' => '%' ),
				'selectors'      => array( '{{WRAPPER}} .vbeb-room-image img' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'      => esc_html__( 'Height', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vh' ),
				'range'      => array(
					'px' => array( 'min' => 1, 'max' => 800 ),
					'vh' => array( 'min' => 1, 'max' => 100 ),
				),
				'selectors'  => array( '{{WRAPPER}} .vbeb-room-image img' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);

		// VBEB 7.504.0, su richiesta dell'utente: responsive (come nel widget
		// "Immagine" di Elementor), e senza la nota sotto (salvata dall'utente
		// nella lista "note widget").
		$this->add_responsive_control(
			'object_fit',
			array(
				'label'       => esc_html__( 'Object Fit', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				// VBEB 7.587.0, su richiesta dell'utente: tutte le voci e
				// l'ordine del widget Immagine di Elementor.
				// VBEB 7.588.0, su richiesta dell'utente: valore di partenza
				// "Predefinito", come Elementor (prima "cover").
				'options'     => array(
					''           => esc_html__( 'Default', 'elementor' ),
					'fill'       => esc_html__( 'Fill', 'elementor' ),
					'cover'      => esc_html__( 'Cover', 'elementor' ),
					'contain'    => esc_html__( 'Contain', 'elementor' ),
					'scale-down' => esc_html__( 'Scale Down', 'elementor' ),
				),
				'selectors'   => array( '{{WRAPPER}} .vbeb-room-image img' => 'object-fit: {{VALUE}};' ),
				'condition'   => array( 'height[size]!' => '' ),
			)
		);

		// VBEB 7.504.0: responsive, su richiesta dell'utente.
		$this->add_responsive_control(
			'object_position',
			array(
				'label'     => esc_html__( 'Object Position', 'elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center center',
				'options'   => array(
					'center center' => esc_html__( 'Center Center', 'elementor' ),
					'center top'    => esc_html__( 'Top Center', 'elementor' ),
					'center bottom' => esc_html__( 'Bottom Center', 'elementor' ),
					'left center'   => esc_html__( 'Center Left', 'elementor' ),
					'right center'  => esc_html__( 'Center Right', 'elementor' ),
				),
				'selectors' => array( '{{WRAPPER}} .vbeb-room-image img' => 'object-position: {{VALUE}};' ),
				// VBEB 7.589.0, su richiesta dell'utente: come Elementor, anche
				// con "Contenitore" e "Riduci".
				'condition' => array( 'height[size]!' => '', 'object_fit' => array( 'cover', 'contain', 'scale-down' ) ),
			)
		);

		$this->add_control(
			'divisore_effetti',
			array(
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			)
		);

		// Le due schede Normale/Hover, con lo stesso impianto del widget
		// Immagine nativo: ogni effetto esiste due volte, una per stato.
		$this->start_controls_tabs( 'effetti_immagine' );

		$this->start_controls_tab( 'effetti_normale', array( 'label' => esc_html__( 'Normal', 'elementor' ) ) );

		$this->add_control(
			'opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0.10, 'max' => 1, 'step' => 0.01 ) ),
				'selectors' => array( '{{WRAPPER}} .vbeb-room-image img' => 'opacity: {{SIZE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .vbeb-room-image img',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'effetti_hover', array( 'label' => esc_html__( 'Hover', 'elementor' ) ) );

		$this->add_control(
			'opacity_hover',
			array(
				'label'     => esc_html__( 'Opacity', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0.10, 'max' => 1, 'step' => 0.01 ) ),
				'selectors' => array( '{{WRAPPER}} .vbeb-room-image img:hover' => 'opacity: {{SIZE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .vbeb-room-image img:hover',
			)
		);

		$this->add_control(
			'hover_transition',
			array(
				'label'     => esc_html__( 'Transition Duration', 'elementor' ) . ' (s)',
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.1 ) ),
				'selectors' => array( '{{WRAPPER}} .vbeb-room-image img' => 'transition-duration: {{SIZE}}s;' ),
			)
		);

		/**
		 * VBEB 7.54.1: animazione al passaggio del mouse.
		 *
		 * Il tipo di controllo lo fornisce Elementor - l'elenco delle
		 * animazioni e' suo, e cresce con le sue versioni, quindi non va
		 * ricopiato.
		 *
		 * NON PRODUCE CSS DA SOLO, a differenza di tutti gli altri
		 * controlli di questa scheda: non ha "selectors" perche' il suo
		 * valore e' il nome di una classe. E' il render a doverla
		 * attaccare all'immagine, come fa il widget nativo.
		 */
		$this->add_control(
			'hover_animation',
			array(
				'label' => esc_html__( 'Hover Animation', 'elementor' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .vbeb-room-image img',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .vbeb-room-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_box_shadow',
				'exclude'  => array( 'box_shadow_position' ),
				'selector' => '{{WRAPPER}} .vbeb-room-image img',
			)
		);

		$this->add_control(
			'heading_caption',
			array(
				'label'     => esc_html__( 'Caption', 'elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'caption!' => 'none' ),
			)
		);

		$this->add_control(
			'caption_color',
			array(
				'label'     => esc_html__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vbeb-room-image-caption' => 'color: {{VALUE}};' ),
				'condition' => array( 'caption!' => 'none' ),
			)
		);

		$this->add_control(
			'caption_space',
			array(
				'label'      => esc_html__( 'Spacing', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .vbeb-room-image-caption' => 'margin-top: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'caption!' => 'none' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$in_editor = class_exists( '\\Elementor\\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( ! class_exists( 'VBEB_Room_Context' ) || ! defined( 'VBO_SITE_URI' ) ) {
			return;
		}

		$img = VBEB_Room_Context::get( 'img', '' );

		if ( empty( $img ) ) {
			if ( $in_editor ) {
				echo '<div style="padding:15px;border:1px dashed #c5c7c9;border-radius:4px;text-align:center;color:#6d7882;font-size:13px;line-height:1.5;"><strong>VBEB Camera - Immagine principale</strong><br>La foto della camera comparir&agrave; sul front-end.</div>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		// VBEB 7.408.0: sempre il file come lo salva VikBooking.
		$src = VBO_SITE_URI . 'resources/uploads/' . $img;

		$nome = VBEB_Room_Context::get( 'name', '' );

		// VBEB 7.54.0: il testo alternativo non ha piu' un controllo
		// proprio - il pannello Contenuto e' stato ridotto a tre voci -
		// ma NON e' stato tolto dall'immagine: resta il nome della
		// camera, che era gia' il valore predefinito ed e' l'unica cosa
		// sensata da mettere. Toglierlo avrebbe peggiorato
		// l'accessibilita' senza che nessuno se ne accorgesse.
		// La classe dell'animazione si aggiunge qui: il controllo
		// restituisce solo il nome, ed e' il foglio di stile di
		// Elementor a definire "elementor-animation-<nome>".
		$classi = 'vbeb-room-image-img';

		if ( ! empty( $settings['hover_animation'] ) ) {
			$classi .= ' elementor-animation-' . $settings['hover_animation'];
		}

		// VBEB 7.407.0: come il widget Immagine di Elementor, l'immagine sta
		// al centro della riga e non sulla linea del testo: niente spazio
		// bianco sotto (quello riservato alle lettere che scendono).
		$tag = '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $nome ) . '" class="' . esc_attr( $classi ) . '" style="display:inline-block;vertical-align:middle;"/>';

		echo '<span class="vbeb-room-image" style="display:block;">';

		echo $tag; // phpcs:ignore -- markup composto qui sopra.

		if ( isset( $settings['caption'] ) && 'name' === $settings['caption'] && '' !== $nome ) {
			echo '<span class="vbeb-room-image-caption" style="display:block;">' . esc_html( $nome ) . '</span>';
		}

		echo '</span>';
	}
}
