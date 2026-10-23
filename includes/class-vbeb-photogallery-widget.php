<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Css_Filter;

/**
 * VBEB 7.621.0, su richiesta dell'utente: widget "VBEB Camera - Galleria".
 * Mostra tutte le "Immagini extra" caricate nella camera di VikBooking, con
 * l'opzione per includere anche l'immagine principale (come nel widget
 * "VBEB Camera - Carosello d'immagini").
 *
 * VBEB 7.622.0, su richiesta dell'utente: impostazioni del widget "Galleria"
 * di Elementor Pro (Lazy Load, Layout, Colonne, Altezza riga, Spaziatura,
 * Link, Lightbox), senza "Tipo" e "Ordina per". Il disegno usa la stessa
 * libreria della Galleria di Elementor (e-gallery), che e' gia' inclusa in
 * Elementor gratuito (script e stile "elementor-gallery"), quindi funziona
 * anche senza Elementor Pro. I testi presenti in Elementor gratuito usano il
 * dominio "elementor"; "Masonry" e "Altezza riga" ci sono solo in Pro e sono
 * testi di VBEB.
 */
class VBEB_Photogallery_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_photo_gallery_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Gallery', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'galleria', 'gallery', 'foto', 'camera', 'immagini', 'images', 'grid', 'masonry', 'justified' );
	}

	public function get_script_depends() {
		return array( 'elementor-gallery' );
	}

	public function get_style_depends() {
		return array( 'elementor-gallery' );
	}

	protected function register_controls() {

		/* ================= CONTENUTO - IMPOSTAZIONI ================= */
		$this->start_controls_section(
			'section_gallery',
			array(
				'label' => esc_html__( 'Settings', 'elementor' ), // VBEB 7.624.0: era "Galleria".
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_main_image',
			array(
				'label'   => esc_html__( 'Include Main Image', 'vbeb' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'lazyload',
			array(
				'label'        => esc_html__( 'Lazy Load', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'gallery_layout',
			array(
				'label'     => esc_html__( 'Layout', 'elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grid',
				'options'   => array(
					'grid'      => esc_html__( 'Grid', 'elementor' ),
					'justified' => esc_html__( 'Justified', 'elementor' ),
					'masonry'   => esc_html__( 'Masonry', 'vbeb' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns', 'elementor' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 4,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'min'            => 1,
				'max'            => 24,
				'condition'      => array( 'gallery_layout!' => 'justified' ),
			)
		);

		$this->add_responsive_control(
			'ideal_row_height',
			array(
				'label'          => esc_html__( 'Row Height', 'vbeb' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => array(
					'px' => array( 'min' => 50, 'max' => 500 ),
				),
				'default'        => array( 'size' => 200 ),
				'tablet_default' => array( 'size' => 150 ),
				'mobile_default' => array( 'size' => 150 ),
				'condition'      => array( 'gallery_layout' => 'justified' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'          => esc_html__( 'Spacing', 'elementor' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => array( 'size' => 10 ),
				'tablet_default' => array( 'size' => 10 ),
				'mobile_default' => array( 'size' => 10 ),
			)
		);

		// Come nel "Carosello d'immagini": senza "URL personalizzato".
		$this->add_control(
			'link_to',
			array(
				'label'   => esc_html__( 'Link', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'file',
				'options' => array(
					'none' => esc_html__( 'None', 'elementor' ),
					'file' => esc_html__( 'Media File', 'elementor' ),
				),
			)
		);

		$this->add_control(
			'open_lightbox',
			array(
				'label'       => esc_html__( 'Lightbox', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => sprintf(
					/* translators: 1: Link open tag, 2: Link close tag. */
					esc_html__( 'Manage your site’s lightbox settings in the %1$sLightbox panel%2$s.', 'elementor' ),
					'<a href="javascript: $e.run( \'panel/global/open\' ).then( () => $e.route( \'panel/global/settings-lightbox\' ) )">',
					'</a>'
				),
				'default'     => 'default',
				'options'     => array(
					'default' => esc_html__( 'Default', 'elementor' ),
					'yes'     => esc_html__( 'Yes', 'elementor' ),
					'no'      => esc_html__( 'No', 'elementor' ),
				),
				'condition'   => array( 'link_to' => 'file' ),
			)
		);

		// VBEB 7.623.0, su richiesta dell'utente: "Proporzioni" (solo Griglia).
		$this->add_control(
			'aspect_ratio',
			array(
				'label'     => esc_html__( 'Aspect Ratio', 'elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3:2',
				'options'   => array(
					'1:1'  => '1:1',
					'3:2'  => '3:2',
					'4:3'  => '4:3',
					'9:16' => '9:16',
					'16:9' => '16:9',
					'21:9' => '21:9',
				),
				'condition' => array( 'gallery_layout' => 'grid' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - IMMAGINE =================
		 * VBEB 7.625.0, su richiesta dell'utente: come la sezione "Immagine"
		 * della Galleria di Elementor Pro (stesse variabili CSS e classi).
		 */
		$this->start_controls_section(
			'image_style',
			array(
				'label' => esc_html__( 'Image', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'image_tabs' );

		$this->start_controls_tab(
			'image_normal',
			array(
				'label' => esc_html__( 'Normal', 'elementor' ),
			)
		);

		$this->add_control(
			'image_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--image-border-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'image_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'range'      => array(
					'px' => array( 'max' => 20 ),
					'em' => array( 'max' => 2 ),
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--image-border-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}}' => '--image-border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_css_filters',
				'selector' => '{{WRAPPER}} .e-gallery-image',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'image_hover',
			array(
				'label' => esc_html__( 'Hover', 'elementor' ),
			)
		);

		$this->add_control(
			'image_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-gallery-item:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'image_border_radius_hover',
			array(
				'label'      => esc_html__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-gallery-item:hover' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_css_filters_hover',
				'selector' => '{{WRAPPER}} .e-gallery-item:hover .e-gallery-image',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'image_hover_animation',
			array(
				'label'     => esc_html__( 'Hover Animation', 'elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''                      => esc_html__( 'None', 'elementor' ),
					'grow'                  => esc_html__( 'Zoom In', 'vbeb' ),
					'shrink-contained'      => esc_html__( 'Zoom Out', 'vbeb' ),
					'move-contained-left'   => esc_html__( 'Move Left', 'vbeb' ),
					'move-contained-right'  => esc_html__( 'Move Right', 'vbeb' ),
					'move-contained-top'    => esc_html__( 'Move Up', 'vbeb' ),
					'move-contained-bottom' => esc_html__( 'Move Down', 'vbeb' ),
				),
				'separator' => 'before',
				'default'   => '',
			)
		);

		$this->add_control(
			'image_animation_duration',
			array(
				'label'     => esc_html__( 'Animation Duration', 'elementor' ) . ' (ms)',
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 800 ),
				'range'     => array(
					'px' => array( 'min' => 0, 'max' => 3000, 'step' => 100 ),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--image-transition-duration: {{SIZE}}ms',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Valore di un'impostazione responsive per un dispositivo: se vuoto, si
	 * eredita dal dispositivo piu' grande (come fa Elementor).
	 */
	private function device_value( $settings, $key, $chain, $is_slider ) {
		foreach ( $chain as $device ) {
			$k = 'desktop' === $device ? $key : $key . '_' . $device;
			if ( ! isset( $settings[ $k ] ) ) {
				continue;
			}
			$v = $is_slider ? ( isset( $settings[ $k ]['size'] ) ? $settings[ $k ]['size'] : '' ) : $settings[ $k ];
			if ( '' !== $v && null !== $v ) {
				return (int) $v;
			}
		}
		return null;
	}

	/**
	 * Larghezza e altezza del file caricato in VikBooking (servono alla
	 * galleria per Justified e Masonry e per il caricamento lazy).
	 */
	private function image_size( $file ) {
		if ( defined( 'VBO_SITE_PATH' ) ) {
			$path = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $file;
			if ( is_file( $path ) ) {
				$size = @getimagesize( $path );
				if ( $size && $size[0] > 0 && $size[1] > 0 ) {
					return array( (int) $size[0], (int) $size[1] );
				}
			}
		}
		return array( 1500, 1000 );
	}

	/**
	 * VBEB 7.625.0: regole della Galleria di Elementor Pro
	 * (widget-gallery.css e transitions.css) che servono alla sezione Stile >
	 * Immagine, limitate a questo widget: senza Elementor Pro quei file non ci
	 * sono. Stampate una volta per pagina.
	 */
	private static function print_style() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;
		$g = '.vbeb-photogallery';
		echo '<style>'
			. $g . ' .elementor-gallery-item{border:solid var(--image-border-width) var(--image-border-color);border-radius:var(--image-border-radius);display:block;overflow:hidden;position:relative;text-decoration:none;transition-property:all;}'
			. $g . ' .elementor-gallery-item__image.e-gallery-image{transition-duration:var(--image-transition-duration);transition-property:filter,transform;}'
			. $g . ' .elementor-animated-content{--translate:0,0;}'
			. $g . ' .elementor-animated-content [class*=elementor-animated-item]{will-change:transform,opacity;}'
			. $g . ' .elementor-animated-content:hover .elementor-animated-item--grow,' . $g . ' .elementor-animated-content:focus .elementor-animated-item--grow{transform:scale(1.1);}'
			. $g . ' .elementor-animated-content .elementor-animated-item--shrink-contained{transform:scale(1.17);}'
			. $g . ' .elementor-animated-content:hover .elementor-animated-item--shrink-contained,' . $g . ' .elementor-animated-content:focus .elementor-animated-item--shrink-contained{transform:scale(1);}'
			. $g . ' .elementor-animated-content .elementor-animated-item--move-contained-left,' . $g . ' .elementor-animated-content .elementor-animated-item--move-contained-right,' . $g . ' .elementor-animated-content .elementor-animated-item--move-contained-top,' . $g . ' .elementor-animated-content .elementor-animated-item--move-contained-bottom{transform:scale(1.2) translate(var(--translate));}'
			. $g . ' .elementor-animated-content:hover .elementor-animated-item--move-contained-right,' . $g . ' .elementor-animated-content:focus .elementor-animated-item--move-contained-right{--translate:8%,0;}'
			. $g . ' .elementor-animated-content:hover .elementor-animated-item--move-contained-left,' . $g . ' .elementor-animated-content:focus .elementor-animated-item--move-contained-left{--translate:-8%,0;}'
			. $g . ' .elementor-animated-content:hover .elementor-animated-item--move-contained-top,' . $g . ' .elementor-animated-content:focus .elementor-animated-item--move-contained-top{--translate:0,-8%;}'
			. $g . ' .elementor-animated-content:hover .elementor-animated-item--move-contained-bottom,' . $g . ' .elementor-animated-content:focus .elementor-animated-item--move-contained-bottom{--translate:0,8%;}'
			// VBEB 7.640.0, su richiesta dell'utente: niente animazione
			// d'ingresso. Con il Lazy Load la libreria della galleria parte con
			// le foto rimpicciolite e trasparenti (e-gallery.css) e le fa
			// crescere quando arrivano: qui partono gia' nella loro forma, e
			// la foto compare senza movimento. Le animazioni al passaggio del
			// mouse che spostano la foto anche da ferma restano come sono.
			. $g . '.e-gallery--lazyload .e-gallery-image:not(.e-gallery-image-loaded){filter:none;transform:none;}'
			. $g . '.e-gallery--lazyload .elementor-animated-content .elementor-animated-item--shrink-contained:not(.e-gallery-image-loaded){transform:scale(1.17);}'
			. $g . '.e-gallery--lazyload .elementor-animated-content .elementor-animated-item--move-contained-left:not(.e-gallery-image-loaded),' . $g . '.e-gallery--lazyload .elementor-animated-content .elementor-animated-item--move-contained-right:not(.e-gallery-image-loaded),' . $g . '.e-gallery--lazyload .elementor-animated-content .elementor-animated-item--move-contained-top:not(.e-gallery-image-loaded),' . $g . '.e-gallery--lazyload .elementor-animated-content .elementor-animated-item--move-contained-bottom:not(.e-gallery-image-loaded){transform:scale(1.2) translate(var(--translate));}'
			. '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	protected function render() {
		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( ! VBEB_Room_Context::has_context() ) {
			if ( $is_editor ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">Galleria Camera: le foto reali compariranno sul front-end di una pagina camera.</div>';
			}
			return;
		}

		if ( ! defined( 'VBO_SITE_URI' ) ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$name     = VBEB_Room_Context::get( 'name', '' );
		$main_img = VBEB_Room_Context::get( 'img', '' );
		$moreimgs = VBEB_Room_Context::get( 'moreimgs', '' );

		$items = array();

		if ( isset( $settings['show_main_image'] ) && 'yes' === $settings['show_main_image'] && ! empty( $main_img ) ) {
			$items[] = array(
				'file' => $main_img,
				'alt'  => $name,
			);
		}

		// Immagini extra della camera, con le stesse didascalie usate da VikBooking.
		if ( ! empty( $moreimgs ) ) {
			$imgcaptions = json_decode( VBEB_Room_Context::get( 'imgcaptions', '' ), true );
			$usecaptions = is_array( $imgcaptions );

			foreach ( explode( ';;', $moreimgs ) as $iind => $mimg ) {
				if ( empty( $mimg ) ) {
					continue;
				}
				$items[] = array(
					'file' => 'big_' . $mimg,
					'alt'  => ( $usecaptions && ! empty( $imgcaptions[ $iind ] ) )
						? $imgcaptions[ $iind ]
						: substr( $mimg, 0, strpos( $mimg, '.' ) ),
				);
			}
		}

		if ( empty( $items ) ) {
			if ( $is_editor ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">Questa camera non ha foto caricate.</div>';
			}
			return;
		}

		// Identificativo unico a ogni disegno (stesso motivo del Carosello:
		// nel ciclo di Elementor lo stesso widget si disegna una volta per camera).
		static $vbeb_seq = 0;
		$vbeb_seq++;
		$gallery_id = 'vbeb-photogallery-' . $this->get_id() . '-' . $vbeb_seq;

		$layout = isset( $settings['gallery_layout'] ) && in_array( $settings['gallery_layout'], array( 'grid', 'justified', 'masonry' ), true )
			? $settings['gallery_layout']
			: 'grid';
		$lazy = isset( $settings['lazyload'] ) && 'yes' === $settings['lazyload'];

		// Impostazioni per dispositivo, come le passa la Galleria di Elementor Pro.
		$chain       = array( 'desktop' );
		$breakpoints = array();
		$bp_manager  = \Elementor\Plugin::$instance->breakpoints;
		if ( $bp_manager && method_exists( $bp_manager, 'get_active_breakpoints' ) ) {
			$active = $bp_manager->get_active_breakpoints();
			// Dal piu' grande al piu' piccolo, senza "widescreen" (come Elementor Pro).
			uasort( $active, function ( $a, $b ) {
				return $b->get_value() - $a->get_value();
			} );
			foreach ( $active as $bp_name => $bp ) {
				if ( 'widescreen' === $bp_name ) {
					continue;
				}
				$chain[] = $bp_name;
				$entry   = array(
					'columns'        => $this->device_value( $settings, 'columns', array_reverse( $chain ), false ),
					'horizontalGap'  => $this->device_value( $settings, 'gap', array_reverse( $chain ), true ),
					'idealRowHeight' => $this->device_value( $settings, 'ideal_row_height', array_reverse( $chain ), true ),
				);
				$breakpoints[ (int) $bp->get_value() ] = $entry;
			}
		}
		foreach ( $breakpoints as $px => $entry ) {
			$entry['verticalGap'] = $entry['horizontalGap'];
			// Senza i valori vuoti, come gli "undefined" di Elementor Pro.
			$breakpoints[ $px ] = array_filter( $entry, function ( $v ) {
				return null !== $v;
			} );
		}

		$desktop_gap = $this->device_value( $settings, 'gap', array( 'desktop' ), true );
		$config      = array(
			'type'           => $layout,
			'columns'        => $this->device_value( $settings, 'columns', array( 'desktop' ), false ),
			'idealRowHeight' => $this->device_value( $settings, 'ideal_row_height', array( 'desktop' ), true ),
			// VBEB 7.623.0: "Proporzioni" (predefinito 3:2, come Elementor Pro).
			'aspectRatio'    => ( isset( $settings['aspect_ratio'] ) && in_array( $settings['aspect_ratio'], array( '1:1', '3:2', '4:3', '9:16', '16:9', '21:9' ), true ) ) ? $settings['aspect_ratio'] : '3:2',
			'lastRow'        => 'normal',
			'horizontalGap'  => null === $desktop_gap ? 10 : $desktop_gap,
			'verticalGap'    => null === $desktop_gap ? 10 : $desktop_gap,
			'breakpoints'    => (object) $breakpoints,
			'rtl'            => is_rtl(),
			'lazyLoad'       => $lazy,
			// VBEB 7.640.0: niente movimento delle foto quando la galleria si dispone.
			'animationDuration' => 0,
		);
		$config = array_filter( $config, function ( $v ) {
			return null !== $v;
		} );

		// Lightbox: stessa regola del "Carosello d'immagini" (e di Elementor).
		$link_to       = isset( $settings['link_to'] ) ? $settings['link_to'] : 'file';
		$attr_lightbox = '';
		if ( 'file' === $link_to ) {
			$scelta_lb = isset( $settings['open_lightbox'] ) ? $settings['open_lightbox'] : 'default';
			$kit       = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
			$lb_sito   = $kit && 'yes' === $kit->get_settings( 'global_image_lightbox' );
			if ( 'no' === $scelta_lb ) {
				$attr_lightbox = $lb_sito ? ' data-elementor-open-lightbox="no"' : '';
			} elseif ( 'yes' === $scelta_lb || $lb_sito ) {
				$attr_lightbox = ' data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="' . esc_attr( $gallery_id ) . '"';
			}
		}
		$tag = 'file' === $link_to ? 'a' : 'div';

		// VBEB 7.625.0: "Animazione al passaggio del mouse", stesse classi
		// della Galleria di Elementor Pro.
		$hover_anim = isset( $settings['image_hover_animation'] ) && in_array( $settings['image_hover_animation'], array( 'grow', 'shrink-contained', 'move-contained-left', 'move-contained-right', 'move-contained-top', 'move-contained-bottom' ), true )
			? $settings['image_hover_animation']
			: '';

		self::print_style();

		echo '<div class="vbeb-photogallery elementor-gallery__container" id="' . esc_attr( $gallery_id ) . '">';
		foreach ( $items as $index => $item ) {
			$src  = VBO_SITE_URI . 'resources/uploads/' . $item['file'];
			$size = $this->image_size( $item['file'] );

			$open = '<' . $tag . ' class="e-gallery-item elementor-gallery-item' . ( $is_editor && 'a' === $tag ? ' elementor-clickable' : '' ) . ( '' !== $hover_anim ? ' elementor-animated-content' : '' ) . '"';
			if ( 'a' === $tag ) {
				$open .= ' href="' . esc_url( $src ) . '"' . $attr_lightbox;
				if ( false !== strpos( $attr_lightbox, 'slideshow' ) ) {
					$open .= ' data-elementor-lightbox-index="' . (int) $index . '"';
				}
			}
			echo $open . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="e-gallery-image elementor-gallery-item__image' . ( '' !== $hover_anim ? ' elementor-animated-item--' . esc_attr( $hover_anim ) : '' ) . '" data-thumbnail="' . esc_url( $src ) . '" data-width="' . (int) $size[0] . '" data-height="' . (int) $size[1] . '" aria-label="' . esc_attr( $item['alt'] ) . '" role="img"></div>';
			echo '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
		?>
		<script>
		(function() {
			function vbebInitPhotoGallery() {
				if ( typeof EGallery === 'undefined' || typeof jQuery === 'undefined' ) {
					return;
				}
				var el = document.getElementById('<?php echo esc_js( $gallery_id ); ?>');
				if ( ! el || el.vbebGallery ) {
					return;
				}
				var config = <?php echo wp_json_encode( $config ); ?>;
				config.container = jQuery(el);
				el.vbebGallery = new EGallery(config);
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', vbebInitPhotoGallery);
			} else {
				vbebInitPhotoGallery();
			}
			window.addEventListener('load', vbebInitPhotoGallery);
		})();
		</script>
		<?php
	}
}
