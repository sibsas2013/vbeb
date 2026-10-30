<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Widget Elementor "VBEB Camera - Carosello d'immagini" (fino alla 7.619.0 "VBEB Camera - Galleria").
 * Pannello Contenuto e Stile ricalcati su quelli reali del widget nativo
 * "Carosello Immagini" di Elementor (stessa terminologia, stessi
 * raggruppamenti), applicati pero' alle foto reali della camera VikBooking
 * (immagine principale + foto aggiuntive) invece che alla Libreria Media.
 * Motore: Swiper.js, gia' incluso in Elementor (dichiarato come
 * dipendenza, non ricaricato una seconda volta). Il click su una slide
 * (Link: File multimediale) apre la lightbox di Elementor (VBEB 7.615.0,
 * su richiesta dell'utente; prima quella di VikBooking, vikFxGallery),
 * con l'aspetto deciso in Impostazioni del sito > Lightbox.
 */
class VBEB_Gallery_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_image_carousel_widget'; // VBEB 7.620.0: era "vbeb_room_gallery_widget".
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Image Carousel', 'vbeb-bridge-for-vikbooking-and-elementor' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'galleria', 'gallery', 'foto', 'camera', 'slideshow', 'carousel', 'slider' );
	}

	public function get_script_depends() {
		return array( 'swiper' );
	}

	/**
	 * VBEB: senza questo metodo, dalla versione 3.24 di Elementor il CSS
	 * di Swiper non viene piu' caricato automaticamente su tutte le
	 * pagine (ottimizzazione delle performance) - va dichiarato
	 * esplicitamente qui, altrimenti lo slideshow perde ogni
	 * impaginazione e le slide collassano una sotto l'altra.
	 */
	public function get_style_depends() {
		return array( 'swiper' );
	}

	/*
	 * VBEB 7.509.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i testi del suo widget "Carosello d'immagini"
	 * (dominio "elementor", image-carousel.php), cosi' la traduzione arriva
	 * dai file di lingua di Elementor. Restano in italiano, solo di VBEB:
	 * Includi immagine principale, il segnaposto del link, le Miniature e le
	 * loro impostazioni, Opacita' al passaggio del mouse.
	 */
	protected function register_controls() {

		/* ================= CONTENUTO - CAROSELLO IMMAGINI ================= */
		$this->start_controls_section(
			'section_carousel',
			array(
				'label' => esc_html__( 'Image Carousel', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_main_image',
			array(
				'label'   => esc_html__( 'Include Main Image', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		// VBEB 7.630.0, su richiesta dell'utente: "Risoluzione immagine" come
		// nel Carosello d'immagini di Elementor, con le sole voci "Pieno" (le
		// foto di VikBooking cosi' come sono) e "Personalizzato" (copia
		// ridimensionata, vedi vbeb_carousel_resized()). Le misure di
		// WordPress non ci sono: le foto non stanno nella Libreria media.
		// VBEB 7.631.0: due impostazioni normali al posto del gruppo
		// "Image Size" di Elementor (stessi nomi, "thumbnail_size" e
		// "thumbnail_custom_dimension"): nel gruppo "include" non filtra le
		// misure ma i campi del gruppo stesso, e il menu spariva.
		$this->add_control(
			'thumbnail_size',
			array(
				'label'   => esc_html__( 'Image Resolution', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'full',
				'options' => array(
					'full'   => esc_html__( 'Full', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'custom' => esc_html__( 'Custom', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
			)
		);

		$this->add_control(
			'thumbnail_custom_dimension',
			array(
				'label'       => esc_html__( 'Image Dimension', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::IMAGE_DIMENSIONS,
				'description' => esc_html__( 'You can crop the original image size to any custom size. You can also set a single value for height or width in order to keep the original size ratio.', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'condition'   => array( 'thumbnail_size' => 'custom' ),
			)
		);

		$this->add_responsive_control(
			'slides_per_view',
			array(
				'label'           => esc_html__( 'Slides to Show', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'            => Controls_Manager::NUMBER,
				'default'         => 1,
				'tablet_default'  => 1,
				'mobile_default'  => 1,
				'min'             => 1,
				'max'             => 5,
			)
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			array(
				'label'           => esc_html__( 'Slides to Scroll', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'            => Controls_Manager::NUMBER,
				'default'         => 1,
				'tablet_default'  => 1,
				'mobile_default'  => 1,
				'min'             => 1,
				'max'             => 5,
				// VBEB 7.612.0, su richiesta dell'utente: nascosta con "Slide da
				// visualizzare" a 1, come il Carosello d'immagini di Elementor
				// (per ogni dispositivo: Elementor confronta il valore dello
				// stesso dispositivo). Numero o testo: Elementor confronta in
				// modo stretto, quindi tutti e due.
				'condition'       => array( 'slides_per_view!' => array( 1, '1' ) ),
			)
		);

		// VBEB 7.630.0: "Allunga immagine" come Elementor: con "Si'" le foto
		// piu' piccole della slide la riempiono in larghezza.
		$this->add_control(
			'image_stretch',
			array(
				'label'   => esc_html__( 'Image Stretch', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => array(
					'no'  => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'yes' => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
			)
		);

		$this->add_control(
			'navigation',
			array(
				'label'   => esc_html__( 'Navigation', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'both',
				'options' => array(
					'both'   => esc_html__( 'Arrows and Dots', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'arrows' => esc_html__( 'Arrows', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'dots'   => esc_html__( 'Dots', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'none'   => esc_html__( 'None', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
			)
		);

		// VBEB 7.606.0, su richiesta dell'utente: icone delle frecce, come il
		// widget Carosello d'immagini di Elementor (image-carousel.php).
		// "Predefinito" (vuoto) lascia la freccia di Swiper di prima.
		$this->add_control(
			'navigation_previous_icon',
			array(
				'label'            => esc_html__( 'Previous Arrow Icon', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin'             => 'inline',
				'label_block'      => false,
				'skin_settings'    => array(
					'inline' => array(
						'none' => array(
							'label' => 'Default',
							'icon'  => 'eicon-chevron-left',
						),
						'icon' => array(
							'icon' => 'eicon-star',
						),
					),
				),
				'recommended'      => array(
					'fa-regular' => array( 'arrow-alt-circle-left', 'caret-square-left' ),
					'fa-solid'   => array( 'angle-double-left', 'angle-left', 'arrow-alt-circle-left', 'arrow-circle-left', 'arrow-left', 'caret-left', 'caret-square-left', 'chevron-circle-left', 'chevron-left', 'long-arrow-alt-left' ),
				),
				'condition'        => array( 'navigation' => array( 'arrows', 'both' ) ),
			)
		);

		$this->add_control(
			'navigation_next_icon',
			array(
				'label'            => esc_html__( 'Next Arrow Icon', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin'             => 'inline',
				'label_block'      => false,
				'skin_settings'    => array(
					'inline' => array(
						'none' => array(
							'label' => 'Default',
							'icon'  => 'eicon-chevron-right',
						),
						'icon' => array(
							'icon' => 'eicon-star',
						),
					),
				),
				'recommended'      => array(
					'fa-regular' => array( 'arrow-alt-circle-right', 'caret-square-right' ),
					'fa-solid'   => array( 'angle-double-right', 'angle-right', 'arrow-alt-circle-right', 'arrow-circle-right', 'arrow-right', 'caret-right', 'caret-square-right', 'chevron-circle-right', 'chevron-right', 'long-arrow-alt-right' ),
				),
				'condition'        => array( 'navigation' => array( 'arrows', 'both' ) ),
			)
		);

		// VBEB 7.614.0, su richiesta dell'utente: "Link" in fondo alla sezione.
		$this->add_control(
			'link_to',
			array(
				'label'   => esc_html__( 'Link', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'file',
				'options' => array(
					'none'   => esc_html__( 'None', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'file'   => esc_html__( 'Media File', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					// VBEB 7.613.0, su richiesta dell'utente: tolto "URL
					// personalizzato" (e il campo del collegamento).
				),
			)
		);


		// VBEB 7.616.0, su richiesta dell'utente: come il Carosello
		// d'immagini di Elementor (image-carousel.php, "open_lightbox"):
		// Predefinito segue "Lightbox immagini" delle Impostazioni del sito,
		// Si' la apre sempre, No mai (la foto si apre come collegamento).
		$this->add_control(
			'open_lightbox',
			array(
				'label'       => esc_html__( 'Lightbox', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => sprintf(
					/* translators: 1: Link open tag, 2: Link close tag. */
					esc_html__( 'Manage your site’s lightbox settings in the %1$sLightbox panel%2$s.', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'<a href="javascript: $e.run( \'panel/global/open\' ).then( () => $e.route( \'panel/global/settings-lightbox\' ) )">',
					'</a>'
				),
				'default'     => 'default',
				'options'     => array(
					'default' => esc_html__( 'Default', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'yes'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'no'      => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'condition'   => array( 'link_to' => 'file' ),
			)
		);

		$this->end_controls_section();

		/* ================= CONTENUTO - MINIATURE ================= */
		// VBEB 7.609.0, su richiesta dell'utente: prima di "Opzioni aggiuntive".
		$this->start_controls_section(
			'section_thumbs',
			array(
				'label' => esc_html__( 'Thumbnails', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'show_thumbs',
			array(
				'label'              => esc_html__( 'Show Thumbnails', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'tablet_default'     => '',
				'mobile_default'     => '',
				// VBEB 7.619.0: con "selectors" Elementor considera l'impostazione
				// solo di stile e nell'editor aggiorna il CSS senza ridisegnare il
				// widget; le miniature pero' vengono scritte dal PHP solo se
				// attive, quindi accendendola non comparivano. "template" forza
				// il ridisegno.
				'render_type'        => 'template',
				'selectors_dictionary' => array(
					'yes' => 'flex',
					''    => 'none',
				),
				'selectors'          => array(
					'{{WRAPPER}} .vbeb-gallery-thumbs' => 'display: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumbs_per_view',
			array(
				'label'          => esc_html__( 'Visible Thumbnails', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 4,
				'tablet_default' => 3,
				'mobile_default' => 3,
				'min'            => 1,
				'max'            => 10,
				'condition'      => array( 'show_thumbs' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'thumbs_space_between',
			array(
				'label'      => esc_html__( 'Spacing', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.637.0: era "Spaziatura tra miniature".
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 40 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'condition'  => array( 'show_thumbs' => 'yes' ),
			)
		);

		// VBEB 7.636.0, su richiesta dell'utente: "Proporzioni" al posto di
		// "Dimensione miniatura" ("thumbs_size", altezza in px), come nel widget
		// "VBEB Camera - Galleria". Le miniature sono larghe quanto la loro
		// colonna (Miniature visibili) e alte di conseguenza.
		$this->add_control(
			'thumbs_aspect_ratio',
			array(
				'label'                => esc_html__( 'Aspect Ratio', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => '3:2',
				'options'              => array(
					'1:1'  => '1:1',
					'3:2'  => '3:2',
					'4:3'  => '4:3',
					'9:16' => '9:16',
					'16:9' => '16:9',
					'21:9' => '21:9',
				),
				'selectors_dictionary' => array(
					'1:1'  => '1 / 1',
					'3:2'  => '3 / 2',
					'4:3'  => '4 / 3',
					'9:16' => '9 / 16',
					'16:9' => '16 / 9',
					'21:9' => '21 / 9',
				),
				'selectors'            => array(
					'{{WRAPPER}} .vbeb-gallery-thumbs .swiper-slide img' => 'aspect-ratio: {{VALUE}};',
				),
				'condition'            => array( 'show_thumbs' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ================= CONTENUTO - OPZIONI AGGIUNTIVE =================
		 * VBEB 7.607.0, su richiesta dell'utente: Effetto, Autoplay, Pausa al
		 * passaggio del mouse, Velocita' dell'autoplay, Ciclo infinito e
		 * Velocita' dell'animazione spostati qui dalla sezione "Carosello
		 * d'immagini" (stessi identificativi: i valori gia' scelti restano).
		 */
		$this->start_controls_section(
			'section_additional_settings',
			array(
				'label' => esc_html__( 'Additional Options', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.608.0: come il Carosello d'immagini di Elementor (era "Additional Settings").
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// VBEB 7.610.0, su richiesta dell'utente: tolto "Effetto" (sempre
		// "Slide", come il Carosello d'immagini di Elementor); al suo posto,
		// in fondo alla sezione, "Direzione".

		// VBEB 7.611.0, su richiesta dell'utente: come il Carosello
		// d'immagini di Elementor (lazy di Swiper: si caricano le foto
		// visibili e quelle accanto, le altre quando si arriva).
		$this->add_control(
			'lazyload',
			array(
				'label'        => esc_html__( 'Lazyload', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'   => esc_html__( 'Autoplay', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'     => esc_html__( 'Pause on Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		// VBEB 7.611.0, su richiesta dell'utente: come Elementor, l'autoplay
		// si ferma quando il visitatore usa frecce, punti o trascina le foto
		// (prima ripartiva sempre).
		$this->add_control(
			'pause_on_interaction',
			array(
				'label'        => esc_html__( 'Pause on Interaction', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'     => esc_html__( 'Autoplay Speed', 'vbeb-bridge-for-vikbooking-and-elementor' ) . ' (ms)',
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5000,
				'min'       => 1000,
				'max'       => 20000,
				'step'      => 500,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'   => esc_html__( 'Infinite Loop', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'   => esc_html__( 'Animation Speed', 'vbeb-bridge-for-vikbooking-and-elementor' ) . ' (ms)',
				'type'    => Controls_Manager::NUMBER,
				'default' => 500,
				'min'     => 0,
				'max'     => 3000,
				'step'    => 100,
			)
		);

		// VBEB 7.610.0, su richiesta dell'utente: come il Carosello
		// d'immagini di Elementor (attributo "dir" sul carosello, che Swiper
		// legge per scorrere verso destra).
		$this->add_control(
			'direction',
			array(
				'label'   => esc_html__( 'Direction', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ltr',
				'options' => array(
					'ltr' => esc_html__( 'Left', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'rtl' => esc_html__( 'Right', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - NAVIGAZIONE =================
		 * VBEB 7.410.0, su richiesta dell'utente: al posto delle sezioni
		 * "Frecce" e "Puntini", la sezione "Navigazione" del widget Carosello
		 * immagini di Elementor. Frecce: Posizione, Dimensione, Colore (senza
		 * schede Normale/Hover). Paginazione: Posizione, Spazio tra i punti,
		 * Dimensione, Colore, Colore attivo. Identificativi di prima dove il
		 * controllo e' lo stesso: i valori salvati restano.
		 */
		$frecce = '{{WRAPPER}} .vbeb-gallery-swiper .swiper-button-next, {{WRAPPER}} .vbeb-gallery-swiper .swiper-button-prev';

		$this->start_controls_section(
			'section_style_navigation',
			array(
				'label'     => esc_html__( 'Navigation', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'navigation!' => 'none' ),
			)
		);

		$this->add_control(
			'heading_style_arrows',
			array(
				'label'     => esc_html__( 'Arrows', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array( 'navigation' => array( 'arrows', 'both' ) ),
			)
		);

		$this->add_control(
			'arrows_position',
			array(
				'label'        => esc_html__( 'Position', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'inside',
				'options'      => array(
					'inside'  => esc_html__( 'Inside', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'outside' => esc_html__( 'Outside', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'prefix_class' => 'vbeb-arrows-position-',
				'condition'    => array( 'navigation' => array( 'arrows', 'both' ) ),
			)
		);

		$this->add_responsive_control(
			'arrows_size',
			array(
				'label'      => esc_html__( 'Size', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 10, 'max' => 80 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 30 ),
				'condition'  => array( 'navigation' => array( 'arrows', 'both' ) ),
				// VBEB 7.604.0, segnalato dall'utente: la misura non cambiava.
				// Le frecce di Swiper (swiper.min.css) sono il "::after" del
				// pulsante, con "font-size: var(--swiper-navigation-size)", e
				// il pulsante stesso usa quella variabile per larghezza,
				// altezza e centratura: va impostata la variabile, non il
				// font-size del pulsante.
				'selectors'  => array(
					$frecce => '--swiper-navigation-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'arrows_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'navigation' => array( 'arrows', 'both' ) ),
				'selectors' => array(
					$frecce => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'heading_style_dots',
			array(
				'label'     => esc_html__( 'Pagination', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'navigation' => array( 'dots', 'both' ) ),
			)
		);

		// Interno: i punti sopra le immagini (com'era finora). Esterno:
		// sotto le immagini, con lo spazio riservato in basso.
		$this->add_control(
			'dots_position',
			array(
				'label'        => esc_html__( 'Position', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'inside',
				'options'      => array(
					'outside' => esc_html__( 'Outside', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'inside'  => esc_html__( 'Inside', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'prefix_class' => 'vbeb-dots-position-',
				'condition'    => array( 'navigation' => array( 'dots', 'both' ) ),
			)
		);

		$this->add_responsive_control(
			'dots_gap',
			array(
				'label'      => esc_html__( 'Space Between Dots', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 50 ),
				),
				'condition'  => array( 'navigation' => array( 'dots', 'both' ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-gallery-swiper' => '--swiper-pagination-bullet-horizontal-gap: calc({{SIZE}}{{UNIT}} / 2);',
				),
			)
		);

		$this->add_responsive_control(
			'dots_size',
			array(
				'label'      => esc_html__( 'Size', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 4, 'max' => 30 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 8 ),
				'condition'  => array( 'navigation' => array( 'dots', 'both' ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-gallery-swiper .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vbeb-gallery-swiper' => '--vbeb-gal-ds: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Come in Elementor: un colore scelto per i punti si vede pieno
		// (Swiper li rende semitrasparenti di suo).
		$this->add_control(
			'dots_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'navigation' => array( 'dots', 'both' ) ),
				'selectors' => array(
					'{{WRAPPER}} .vbeb-gallery-swiper .swiper-pagination-bullet:not(.swiper-pagination-bullet-active)' => 'background: {{VALUE}}; opacity: 1;',
				),
			)
		);

		$this->add_control(
			'dots_color_active',
			array(
				'label'     => esc_html__( 'Active Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'navigation' => array( 'dots', 'both' ) ),
				'selectors' => array(
					'{{WRAPPER}} .vbeb-gallery-swiper .swiper-pagination-bullet-active' => 'background: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - IMMAGINI =================
		// VBEB 7.634.0, su richiesta dell'utente: dopo "Navigazione". */
		$this->start_controls_section(
			'section_style_images',
			array(
				'label' => esc_html__( 'Image', 'vbeb-bridge-for-vikbooking-and-elementor' ), // VBEB 7.635.0: era "Images".
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.632.0, su richiesta dell'utente: come la sezione "Immagine"
		// del Carosello d'immagini di Elementor: Allineamento verticale,
		// Spaziatura (Predefinito / Personalizzato) con Spaziatura immagine,
		// Tipo di bordo e Raggio del bordo. Allineamento e Spaziatura solo con
		// almeno 2 slide visibili.
		$this->add_responsive_control(
			'gallery_vertical_align',
			array(
				'label'     => esc_html__( 'Vertical Align', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => esc_html__( 'Start', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-top' ),
					'center'     => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-middle' ),
					'flex-end'   => array( 'title' => esc_html__( 'End', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-bottom' ),
				),
				'condition' => array( 'slides_per_view!' => array( 1, '1' ) ),
				'selectors' => array(
					'{{WRAPPER}} .vbeb-gallery-swiper .swiper-wrapper' => 'display: flex; align-items: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'image_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''       => esc_html__( 'Default', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'custom' => esc_html__( 'Custom', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'default'   => '',
				'condition' => array( 'slides_per_view!' => array( 1, '1' ) ),
			)
		);

		// VBEB 7.617.0: nascosta con una sola slide visibile. VBEB 7.618.0:
		// spostata qui da Contenuto > Carosello d'immagini. VBEB 7.632.0: solo
		// con "Spaziatura" su "Personalizzato" (con "Predefinito" 10px).
		$this->add_responsive_control(
			'space_between',
			array(
				'label'      => esc_html__( 'Image Spacing', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'condition'  => array(
					'image_spacing'    => 'custom',
					'slides_per_view!' => array( 1, '1' ),
				),
				'separator'  => 'after',
			)
		);

		// VBEB 7.630.0, su richiesta dell'utente: tolta "Altezza"
		// ("slide_height"): come nel Carosello d'immagini di Elementor le foto
		// si vedono con le loro proporzioni; per dare a tutte la stessa forma
		// c'e' "Risoluzione immagine" > "Personalizzato".

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .vbeb-gallery-swiper .swiper-slide img',
			)
		);

		// VBEB 7.632.0: "Raggio del bordo" per ogni angolo, come Elementor
		// (prima un solo valore, "img_radius").
		$this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-gallery-swiper .swiper-slide img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// VBEB 7.633.0, su richiesta dell'utente: tolta "Opacita' al passaggio
		// del mouse" ("img_opacity_hover").

		$this->end_controls_section();

		/* ================= STILE - MINIATURE ================= */
		$this->start_controls_section(
			'section_style_thumbs',
			array(
				'label'     => esc_html__( 'Thumbnails', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_thumbs' => 'yes' ),
			)
		);

		// VBEB 7.639.0, su richiesta dell'utente: schede Normale / Hover con
		// Colore del bordo, Spessore bordi, Raggio del bordo e Filtri CSS, come
		// la sezione "Immagine" della Galleria di Elementor Pro (Hover senza
		// Spessore, come la' ). "Raggio del bordo" resta "thumbs_radius": i
		// valori salvati restano.
		$th_img = '{{WRAPPER}} .vbeb-gallery-thumbs .swiper-slide img';

		$this->start_controls_tabs( 'thumbs_tabs' );

		$this->start_controls_tab(
			'thumbs_normal',
			array(
				'label' => esc_html__( 'Normal', 'vbeb-bridge-for-vikbooking-and-elementor' ),
			)
		);

		$this->add_control(
			'thumbs_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-gallery-thumbs' => '--vbeb-th-bc: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'thumbs_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'range'      => array(
					'px' => array( 'max' => 20 ),
					'em' => array( 'max' => 2 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-gallery-thumbs' => '--vbeb-th-bw: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'thumbs_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors' => array(
					$th_img => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'thumbs_css_filters',
				'selector' => $th_img,
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'thumbs_hover',
			array(
				'label' => esc_html__( 'Hover', 'vbeb-bridge-for-vikbooking-and-elementor' ),
			)
		);

		$this->add_control(
			'thumbs_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-gallery-thumbs .swiper-slide:hover img' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'thumbs_radius_hover',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-gallery-thumbs .swiper-slide:hover img' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'thumbs_css_filters_hover',
				'selector' => '{{WRAPPER}} .vbeb-gallery-thumbs .swiper-slide:hover img',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// VBEB 7.638.0, su richiesta dell'utente: tolta "Opacita' miniatura
		// inattiva" ("thumbs_opacity").

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'thumbs_active_border',
				'label'    => esc_html__( 'Active Thumbnail Border', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'separator' => 'before',
				'selector' => '{{WRAPPER}} .vbeb-gallery-thumbs .swiper-slide-thumb-active img',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! VBEB_Room_Context::has_context() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'Room - Image Carousel: the real photos will appear on the front end of a room page.', 'vbeb-bridge-for-vikbooking-and-elementor' ) . '</div>';
			}
			return;
		}

		if ( ! defined( 'VBO_SITE_URI' ) ) {
			return;
		}

		$settings   = $this->get_settings_for_display();
		$widget_id  = $this->get_id();

		// VBEB 5.66.2: l'identificativo deve essere unico A OGNI
		// DISEGNO, non per widget.
		//
		// get_id() identifica il widget DENTRO IL TEMPLATE, e nel ciclo
		// di Elementor quel widget e' uno solo, disegnato una volta per
		// camera: tutte le gallerie ricevevano lo stesso identificativo.
		// Il codice che le avvia cerca l'elemento per identificativo e ne
		// trovava sempre uno solo, quindi Swiper veniva inizializzato
		// piu' volte sullo stesso elemento e le miniature si agganciavano
		// alla galleria sbagliata.
		//
		// Prima che la lista camere fosse generata dal ciclo il problema
		// non emergeva, perche' ogni scheda nasceva da un'iniezione
		// separata del template.
		//
		// Si usa l'identificativo della camera quando c'e' - cosi'
		// l'identificativo resta stabile tra un disegno e l'altro, utile
		// se un domani servisse ritrovare la galleria di una camera
		// precisa - con un contatore come riserva.
		static $vbeb_seq = 0;
		$vbeb_seq++;

		$vbeb_room_id = 0;
		if ( method_exists( 'VBEB_Room_Context', 'current_room_id' ) ) {
			$vbeb_room_id = (int) VBEB_Room_Context::current_room_id();
		}

		// VBEB 5.72.0: nel gruppo va incluso anche l'indice del gruppo di
		// ricerca. Con piu' camere cercate la stessa tipologia compare in
		// piu' gruppi, e il solo identificativo della camera tornerebbe a
		// collidere - lo stesso problema risolto nella 5.66.2.
		$vbeb_grp = ( class_exists( 'VBEB_Room_Context' ) && null !== VBEB_Room_Context::$search_indroom )
			? 'g' . (int) VBEB_Room_Context::$search_indroom
			: '';

		$gallery_id = 'vbeb-gallery-' . $widget_id . '-' . ( $vbeb_room_id > 0 ? $vbeb_grp . 'r' . $vbeb_room_id : 'n' . $vbeb_seq );

		$name     = VBEB_Room_Context::get( 'name', '' );
		$main_img = VBEB_Room_Context::get( 'img', '' );
		$moreimgs = VBEB_Room_Context::get( 'moreimgs', '' );

		$items = array();

		if ( 'yes' === $settings['show_main_image'] && ! empty( $main_img ) ) {
			$items[] = array(
				'big'   => VBO_SITE_URI . 'resources/uploads/' . $main_img,
				'thumb' => VBO_SITE_URI . 'resources/uploads/' . $main_img,
				'file'  => $main_img,
				'alt'   => $name,
			);
		}

		if ( ! empty( $moreimgs ) ) {
			$moreimages  = explode( ';;', $moreimgs );
			$imgcaptions = json_decode( VBEB_Room_Context::get( 'imgcaptions', '' ), true );
			$usecaptions = is_array( $imgcaptions );

			foreach ( $moreimages as $iind => $mimg ) {
				if ( empty( $mimg ) ) {
					continue;
				}
				$alt = ( $usecaptions && ! empty( $imgcaptions[ $iind ] ) )
					? $imgcaptions[ $iind ]
					: substr( $mimg, 0, strpos( $mimg, '.' ) );

				$items[] = array(
					'big'   => VBO_SITE_URI . 'resources/uploads/big_' . $mimg,
					'thumb' => VBO_SITE_URI . 'resources/uploads/thumb_' . $mimg,
					'file'  => 'big_' . $mimg,
					'alt'   => $alt,
				);
			}
		}

		if ( empty( $items ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'This room has no photos.', 'vbeb-bridge-for-vikbooking-and-elementor' ) . '</div>';
			}
			return;
		}

		// VBEB 7.630.0: "Risoluzione immagine". La slide mostra "src"; il
		// collegamento della lightbox resta la foto intera ("big"), come in
		// Elementor.
		$res_custom = isset( $settings['thumbnail_size'] ) && 'custom' === $settings['thumbnail_size'];
		$res_w      = $res_custom && ! empty( $settings['thumbnail_custom_dimension']['width'] ) ? (int) $settings['thumbnail_custom_dimension']['width'] : 0;
		$res_h      = $res_custom && ! empty( $settings['thumbnail_custom_dimension']['height'] ) ? (int) $settings['thumbnail_custom_dimension']['height'] : 0;
		foreach ( $items as $k => $item ) {
			$items[ $k ]['src'] = $item['big'];
			if ( $res_w > 0 || $res_h > 0 ) {
				$resized = vbeb_carousel_resized( $item['file'], $res_w, $res_h );
				if ( '' !== $resized ) {
					$items[ $k ]['src'] = $resized;
				}
			}
		}

		// VBEB: usiamo la lightbox nativa di Elementor (non piu' quella di
		// VikBooking), quindi non serve piu' caricare vikfxgallery.js/css.

		$navigation = isset( $settings['navigation'] ) ? $settings['navigation'] : 'both';
		$show_arrows = in_array( $navigation, array( 'arrows', 'both' ), true );
		$show_dots   = in_array( $navigation, array( 'dots', 'both' ), true );

		$link_to = isset( $settings['link_to'] ) ? $settings['link_to'] : 'file';
		// VBEB 7.613.0: "URL personalizzato" non c'e' piu': i widget che lo
		// avevano scelto restano senza collegamento.
		if ( 'custom' === $link_to ) {
			$link_to = 'none';
		}

		// VBEB 7.615.0: lightbox di Elementor, niente piu' vikfxgallery.js/css.
		// VBEB 7.616.0: "Lightbox" (Predefinito / Si' / No), stessa regola di
		// Elementor: con "No" e "Lightbox immagini" del sito accesa si mette
		// "no"; con "Predefinito" la lightbox c'e' solo se quella del sito e'
		// accesa; con "Si'" sempre.
		$attr_lightbox = '';
		if ( 'file' === $link_to ) {
			$scelta_lb = isset( $settings['open_lightbox'] ) ? $settings['open_lightbox'] : 'default';
			$kit       = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
			$lb_sito   = $kit && 'yes' === $kit->get_settings( 'global_image_lightbox' );
			if ( 'no' === $scelta_lb ) {
				$attr_lightbox = $lb_sito ? 'no' : '';
			} elseif ( 'yes' === $scelta_lb || $lb_sito ) {
				$attr_lightbox = 'yes';
			}
		}

		$show_thumbs = ( 'yes' === $settings['show_thumbs'] )
			|| ( ! empty( $settings['show_thumbs_tablet'] ) && 'yes' === $settings['show_thumbs_tablet'] )
			|| ( ! empty( $settings['show_thumbs_mobile'] ) && 'yes' === $settings['show_thumbs_mobile'] );
		// VBEB: posizione fissa "sotto" - le varianti destra/sinistra sono
		// state rimosse temporaneamente perche' il layout non si conteneva
		// correttamente all'interno del widget.
		$thumbs_position = 'bottom';
		$thumbs_id       = $gallery_id . '-thumbs';

		echo '<div class="vbeb-gallery-outer">';

		echo '<div class="vbeb-gallery-swiper" id="' . esc_attr( $gallery_id ) . '">';
		// VBEB 7.610.0: "Direzione" (come Elementor, "dir" sul carosello).
		$direzione = ( isset( $settings['direction'] ) && 'rtl' === $settings['direction'] ) ? 'rtl' : 'ltr';
		// VBEB 7.611.0: "Lazyload" (immagini "swiper-lazy" con "data-src", come Elementor).
		$lazy = isset( $settings['lazyload'] ) && 'yes' === $settings['lazyload'];
		$stretch = isset( $settings['image_stretch'] ) && 'yes' === $settings['image_stretch'];
		echo '<div class="swiper' . ( $stretch ? ' vbeb-image-stretch' : '' ) . '" dir="' . esc_attr( $direzione ) . '">';
		echo '<div class="swiper-wrapper">';
		$item_index = 0;
		foreach ( $items as $item ) {
			$item_index++;
			echo '<div class="swiper-slide">';

			if ( 'none' === $link_to ) {
				// Nessun link: solo l'immagine, senza tag <a>.
				echo $lazy ? '<img class="swiper-lazy" data-src="' . esc_url( $item['src'] ) . '" alt="' . esc_attr( $item['alt'] ) . '" /><div class="swiper-lazy-preloader"></div>' : '<img src="' . esc_url( $item['src'] ) . '" alt="' . esc_attr( $item['alt'] ) . '" />';
			} else {
				// 'file': VBEB 7.615.0, su richiesta dell'utente, la lightbox di
				// Elementor (prima quella di VikBooking). Come nel suo Carosello
				// d'immagini: tutte le foto con lo stesso
				// "data-elementor-lightbox-slideshow" formano una presentazione
				// con avanti/indietro; l'ordine e' "data-elementor-lightbox-index"
				// e le copie create da Swiper per il ciclo infinito
				// (".swiper-slide-duplicate") Elementor le salta da solo.
				// VBEB 7.616.0: "Lightbox" come Elementor (add_lightbox_data_attributes()).
				// VBEB free: $attr_lightbox e' il valore ("yes", "no" o vuoto),
				// stampato con esc_attr().
				echo '<a href="' . esc_url( $item['big'] ) . '"' . ( '' !== $attr_lightbox ? ' data-elementor-open-lightbox="' . esc_attr( $attr_lightbox ) . '"' : '' ) . ( 'yes' === $attr_lightbox ? ' data-elementor-lightbox-slideshow="' . esc_attr( $gallery_id ) . '" data-elementor-lightbox-index="' . (int) ( $item_index - 1 ) . '"' : '' ) . '>';
				echo $lazy ? '<img class="swiper-lazy" data-src="' . esc_url( $item['src'] ) . '" alt="' . esc_attr( $item['alt'] ) . '" /><div class="swiper-lazy-preloader"></div>' : '<img src="' . esc_url( $item['src'] ) . '" alt="' . esc_attr( $item['alt'] ) . '" />';
				echo '</a>';
			}

			echo '</div>';
		}
		echo '</div>'; // .swiper-wrapper

		if ( $show_dots ) {
			echo '<div class="swiper-pagination"></div>';
		}
		if ( $show_arrows ) {
			// VBEB 7.606.0: icona scelta nel pannello dentro il pulsante; senza
			// icona resta la freccia di Swiper ("::after").
			foreach ( array( 'next', 'prev' ) as $verso ) {
				$icona = isset( $settings[ 'navigation_' . ( 'prev' === $verso ? 'previous' : 'next' ) . '_icon' ] ) ? $settings[ 'navigation_' . ( 'prev' === $verso ? 'previous' : 'next' ) . '_icon' ] : array();
				if ( ! empty( $icona['value'] ) ) {
					echo '<div class="swiper-button-' . esc_attr( $verso ) . ' vbeb-has-icon">';
					\Elementor\Icons_Manager::render_icon( $icona, array( 'aria-hidden' => 'true' ) );
					echo '</div>';
				} else {
					echo '<div class="swiper-button-' . esc_attr( $verso ) . '"></div>';
				}
			}
		}

		echo '</div>'; // .swiper
		echo '</div>'; // .vbeb-gallery-swiper

		if ( $show_thumbs ) {
			echo '<div class="vbeb-gallery-thumbs" id="' . esc_attr( $thumbs_id ) . '">';
			echo '<div class="swiper">';
			echo '<div class="swiper-wrapper">';
			foreach ( $items as $item ) {
				echo '<div class="swiper-slide">';
				echo '<img src="' . esc_url( $item['thumb'] ) . '" alt="' . esc_attr( $item['alt'] ) . '" />';
				echo '</div>';
			}
			echo '</div>'; // .swiper-wrapper
			echo '</div>'; // .swiper
			echo '</div>'; // .vbeb-gallery-thumbs
		}

		echo '</div>'; // .vbeb-gallery-outer

		$autoplay_js = 'false';
		if ( 'yes' === $settings['autoplay'] ) {
			$speed_ms       = isset( $settings['autoplay_speed'] ) ? (int) $settings['autoplay_speed'] : 5000;
			$pause_on_hover = ( 'yes' === $settings['pause_on_hover'] ) ? 'true' : 'false';
			// VBEB 7.611.0: "Pausa su interazione".
			$su_interazione = ( ! isset( $settings['pause_on_interaction'] ) || 'yes' === $settings['pause_on_interaction'] ) ? 'true' : 'false';
			$autoplay_js    = '{ delay: ' . $speed_ms . ', disableOnInteraction: ' . $su_interazione . ', pauseOnMouseEnter: ' . $pause_on_hover . ' }';
		}

		$loop_js         = ( 'yes' === $settings['loop'] ) ? 'true' : 'false';
		// VBEB 7.610.0: niente piu' "Effetto": sempre "slide".
		$effect_js       = 'slide';
		$slides_per_view = isset( $settings['slides_per_view'] ) ? (int) $settings['slides_per_view'] : 1;
		$slides_to_scroll = isset( $settings['slides_to_scroll'] ) ? (int) $settings['slides_to_scroll'] : 1;
		// VBEB 7.632.0: con "Spaziatura" su "Predefinito" 10px.
		$space_between   = ( isset( $settings['image_spacing'] ) && 'custom' === $settings['image_spacing'] && isset( $settings['space_between']['size'] ) ) ? (int) $settings['space_between']['size'] : 10;
		// VBEB 7.617.0: con una sola slide visibile la spaziatura e' nascosta, quindi nessuno spazio.
		if ( 1 === $slides_per_view ) {
			$space_between = 0;
		}
		$transition_speed = isset( $settings['speed'] ) ? (int) $settings['speed'] : 500;

		$thumbs_per_view = isset( $settings['thumbs_per_view'] ) ? (int) $settings['thumbs_per_view'] : 4;
		$thumbs_space    = isset( $settings['thumbs_space_between']['size'] ) ? (int) $settings['thumbs_space_between']['size'] : 10;
		$thumbs_direction_js = 'horizontal';

		?>
		<style>
			/* VBEB 7.630.0: foto con le loro proporzioni, centrate nella slide,
			 * come il Carosello d'immagini di Elementor; "Allunga immagine"
			 * le porta a tutta larghezza. */
			.vbeb-gallery-swiper .swiper-slide { text-align: center; }
			.vbeb-gallery-swiper .swiper-slide img { max-width: 100%; height: auto; display: inline-block; vertical-align: middle; }
			.vbeb-gallery-swiper .vbeb-image-stretch .swiper-slide img { width: 100%; }
			.vbeb-arrows-position-outside .vbeb-gallery-swiper { padding: 0 40px; }
			/* VBEB 7.606.0: icone delle frecce scelte nel pannello, con la
			 * misura di "Dimensione" e il colore di "Colore". */
			.vbeb-gallery-swiper .vbeb-has-icon::after { content: none; }
			.vbeb-gallery-swiper .vbeb-has-icon i { font-size: var(--swiper-navigation-size, 44px); line-height: 1; }
			.vbeb-gallery-swiper .vbeb-has-icon svg { width: var(--swiper-navigation-size, 44px); height: var(--swiper-navigation-size, 44px); fill: currentColor; }
			.vbeb-arrows-position-outside .vbeb-gallery-swiper .swiper-button-next { right: 0; }
			.vbeb-arrows-position-outside .vbeb-gallery-swiper .swiper-button-prev { left: 0; }
			/* VBEB 7.410.0: paginazione "Esterno", sotto le immagini. */
			.vbeb-dots-position-outside .vbeb-gallery-swiper .swiper { padding-bottom: calc(var(--vbeb-gal-ds, 8px) + 16px); box-sizing: border-box; --swiper-pagination-bottom: 4px; }

			/* VBEB 5.66.1: la galleria deve poter RESTRINGERSI.
			 *
			 * Swiper calcola le larghezze dal contenitore, e affianca le
			 * immagini su una riga molto più larga di quanto si veda.
			 * Dentro un contenitore flessibile - come una colonna di
			 * Elementor - un elemento ha larghezza minima pari a quella
			 * del proprio contenuto, quindi la galleria si rifiutava di
			 * rimpicciolirsi e schiacciava le colonne accanto.
			 *
			 * "min-width: 0" rimuove quel vincolo, "max-width: 100%" le
			 * impedisce di superare la colonna e "overflow: hidden"
			 * trattiene le immagini affiancate, che sono la parte che
			 * traboccava.
			 */
			.vbeb-gallery-outer {
				/* VBEB 5.66.3: "width: 0" unito a "min-width: 100%".
				 *
				 * Non è un refuso. Dentro una griglia o un contenitore
				 * flessibile - come le schede del ciclo di Elementor - la
				 * larghezza minima di un elemento è quella del suo
				 * contenuto, e la galleria affianca le immagini su una
				 * riga larghissima: quella misura risaliva la catena dei
				 * contenitori e gonfiava la pagina.
				 *
				 * Le regole della 5.66.1 agivano SULLA galleria, ma la
				 * misura da neutralizzare è il CONTRIBUTO che la
				 * galleria da' ai propri contenitori. Con "width: 0"
				 * quel contributo diventa nullo, e "min-width: 100%" le
				 * fa comunque occupare tutta la colonna disponibile.
				 *
				 * Nel template "Dettaglio Camera" il problema non si
				 * vedeva perché lì la galleria sta in un contenitore
				 * normale, la cui larghezza non dipende dal contenuto.
				 */
				width: 0;
				min-width: 100%;
				max-width: 100%;
				overflow: hidden;
			}
			.vbeb-gallery-outer .vbeb-gallery-swiper,
			.vbeb-gallery-outer .vbeb-gallery-thumbs,
			.vbeb-gallery-outer .swiper {
				width: 100%;
				min-width: 0;
				max-width: 100%;
				overflow: hidden;
			}
			/* Anche il contenitore del widget e il suo contenuto devono
			 * poter restringersi: senza questo il vincolo si sposta
			 * semplicemente di un livello più in alto. */
			.elementor-widget-vbeb_room_image_carousel_widget,
			.elementor-widget-vbeb_room_image_carousel_widget > .elementor-widget-container {
				min-width: 0;
				max-width: 100%;
			}
			.vbeb-gallery-outer { display: flex; flex-direction: column; }
			.vbeb-gallery-outer .vbeb-gallery-thumbs { margin-top: <?php echo (int) $thumbs_space; ?>px; }
			.vbeb-gallery-thumbs .swiper-slide { cursor: pointer; }
			/* VBEB 7.636.0: altezza dalle "Proporzioni" (predefinito 3:2). */
			.vbeb-gallery-thumbs .swiper-slide img { width: 100%; height: auto; aspect-ratio: 3 / 2; object-fit: cover; display: block; }
			/* VBEB 7.639.0: bordo delle miniature da "Colore del bordo" e
			 * "Spessore bordi" (scheda Normale); "Bordo miniatura attiva" lo
			 * sostituisce sulla miniatura scelta. */
			.vbeb-gallery-thumbs .swiper-slide img { border: solid var(--vbeb-th-bw, 0) var(--vbeb-th-bc, transparent); box-sizing: border-box; }
		</style>
		<script>
		(function() {
			function vbebInitSwiper() {
				if ( typeof Swiper === 'undefined' ) {
					return;
				}
				var mainEl = document.querySelector('#<?php echo esc_js( $gallery_id ); ?> .swiper');
				if ( ! mainEl || mainEl.swiper ) {
					return;
				}

				var thumbsSwiper = null;
				<?php if ( $show_thumbs ) : ?>
				var thumbsEl = document.querySelector('#<?php echo esc_js( $thumbs_id ); ?> .swiper');
				if ( thumbsEl && ! thumbsEl.swiper ) {
					thumbsSwiper = new Swiper(thumbsEl, {
						direction: '<?php echo esc_js( $thumbs_direction_js ); ?>',
						slidesPerView: <?php echo (int) $thumbs_per_view; ?>,
						spaceBetween: <?php echo (int) $thumbs_space; ?>,
						watchSlidesProgress: true
						// VBEB: niente loop qui apposta - un carosello miniature
						// con loop attivo riordina fisicamente i suoi elementi
						// per l'effetto infinito, facendo "saltare" l'ordine
						// delle foto visibili. Il loop resta solo sul carosello
						// principale qui sotto.
					});
				}
				<?php endif; ?>

				new Swiper(mainEl, {
					slidesPerView: <?php echo (int) $slides_per_view; ?>,
					slidesPerGroup: <?php echo (int) $slides_to_scroll; ?>,
					spaceBetween: <?php echo (int) $space_between; ?>,
					loop: <?php echo esc_js( $loop_js ); ?>,
					speed: <?php echo (int) $transition_speed; ?>,
					effect: '<?php echo esc_js( $effect_js ); ?>',
					autoplay: <?php echo esc_js( $autoplay_js ); ?>,
					<?php if ( isset( $settings['lazyload'] ) && 'yes' === $settings['lazyload'] ) : ?>
					lazy: { loadPrevNext: true, loadPrevNextAmount: 1 },
					preloadImages: false,
					<?php endif; ?>
					pagination: <?php echo $show_dots ? "{ el: '#" . esc_js( $gallery_id ) . " .swiper-pagination', clickable: true }" : 'false'; ?>,
					navigation: <?php echo $show_arrows ? "{ nextEl: '#" . esc_js( $gallery_id ) . " .swiper-button-next', prevEl: '#" . esc_js( $gallery_id ) . " .swiper-button-prev' }" : 'false'; ?>,
					thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined
				});

			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', vbebInitSwiper);
			} else {
				vbebInitSwiper();
			}
			window.addEventListener('load', vbebInitSwiper);
		})();
		</script>
		<?php
	}
}

/**
 * Copia ridimensionata di una foto di VikBooking, per "Risoluzione immagine"
 * > "Personalizzato" del widget "VBEB Camera - Carosello d'immagini".
 *
 * VBEB 7.630.0. Come Elementor (Group_Control_Image_Size, "custom"): con
 * larghezza e altezza la foto viene ritagliata al centro a quelle misure
 * esatte; con una sola delle due mantiene le proporzioni. La copia si crea
 * con l'editor di immagini di WordPress la prima volta e si riusa; il nome
 * contiene data e peso del file originale, quindi se in VikBooking la foto
 * cambia se ne crea una nuova.
 *
 * @param string $file Nome del file in resources/uploads di VikBooking.
 * @param int    $w    Larghezza (0 = libera).
 * @param int    $h    Altezza (0 = libera).
 * @return string Indirizzo della copia, vuoto se non si riesce.
 */
function vbeb_carousel_resized( $file, $w, $h ) {
	if ( ! defined( 'VBO_SITE_PATH' ) || '' === $file || ( $w < 1 && $h < 1 ) ) {
		return '';
	}
	$file = basename( $file );
	$src  = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $file;
	if ( ! is_file( $src ) ) {
		return '';
	}

	$up = wp_upload_dir();
	if ( ! empty( $up['error'] ) ) {
		return '';
	}
	$dir  = trailingslashit( $up['basedir'] ) . 'vbeb-carousel';
	$url  = trailingslashit( $up['baseurl'] ) . 'vbeb-carousel';
	$info = pathinfo( $file );
	$ext  = isset( $info['extension'] ) ? strtolower( $info['extension'] ) : 'jpg';
	$name = sanitize_file_name( $info['filename'] ) . '-' . (int) $w . 'x' . (int) $h . '-' . substr( md5( $file . '|' . filemtime( $src ) . '|' . filesize( $src ) ), 0, 8 ) . '.' . $ext;

	if ( is_file( $dir . '/' . $name ) ) {
		return $url . '/' . $name;
	}
	if ( ! wp_mkdir_p( $dir ) ) {
		return '';
	}

	$size = @getimagesize( $src );
	if ( ! $size || $size[0] < 1 || $size[1] < 1 ) {
		return '';
	}
	list( $ow, $oh ) = $size;

	// Misure finali e parte dell'originale da usare (ritaglio al centro).
	if ( $w > 0 && $h > 0 ) {
		$scale = max( $w / $ow, $h / $oh );
		$cw    = (int) round( $w / $scale );
		$ch    = (int) round( $h / $scale );
		$cx    = (int) floor( ( $ow - $cw ) / 2 );
		$cy    = (int) floor( ( $oh - $ch ) / 2 );
		$dw    = $w;
		$dh    = $h;
	} else {
		$cx = 0;
		$cy = 0;
		$cw = $ow;
		$ch = $oh;
		$dw = $w > 0 ? $w : (int) round( $ow * $h / $oh );
		$dh = $h > 0 ? $h : (int) round( $oh * $w / $ow );
	}

	$editor = wp_get_image_editor( $src );
	if ( is_wp_error( $editor ) ) {
		return '';
	}
	$ok = $editor->crop( $cx, $cy, $cw, $ch, max( 1, $dw ), max( 1, $dh ) );
	if ( is_wp_error( $ok ) ) {
		return '';
	}
	$saved = $editor->save( $dir . '/' . $name );
	if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
		return '';
	}
	return $url . '/' . basename( $saved['path'] );
}
