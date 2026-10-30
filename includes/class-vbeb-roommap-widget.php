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
 * Widget Elementor "VBEB Camera - Mappa".
 *
 * VBEB 7.592.0, su richiesta dell'utente. Ristampa la mappa ("Dove sarai")
 * della scheda camera, cioe' l'HTML che VikBooking genera con
 * $this->loadTemplate('geomap') (site/views/roomdetails/tmpl/
 * default_geomap.php), catturato dall'override del dettaglio camera
 * (override-templates/default_listing.php) in VBEB_Room_Context. Niente di
 * ricostruito: tutte le impostazioni della mappa (geocoding acceso o spento,
 * indirizzo, coordinate, zoom, tipo di mappa, altezza, segnaposto, immagine
 * sovrapposta) restano in VikBooking, camera per camera, compresi gli
 * script di Google Maps. Su richiesta dell'utente niente impostazione per
 * l'altezza: la decide VikBooking.
 *
 * SUL SITO compare solo nelle camere con "Abilita Geocoding" attivo (e con
 * la chiave di Google Maps impostata): e' VikBooking a decidere.
 *
 * NELL'EDITOR (VBEB 7.594.0) la mappa vera della camera di anteprima, se
 * ha "Abilita Geocoding" attivo (vedi html_editor()); altrimenti un
 * riquadro grigio con l'altezza scelta in VikBooking e la scritta "La mappa
 * comparira' sul front-end", dentro lo stesso contenitore, cosi' bordo,
 * raggio e ombra si vedono.
 *
 * Il titolo ("<h4>" di VikBooking, "Dove sarai") si puo' nascondere,
 * riscrivere e cambiare di tag, come in "VBEB Camera - Prezzi stagionali".
 */
class VBEB_Roommap_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_map_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Map', 'vbeb-bridge-for-vikbooking-and-elementor' );
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'mappa', 'map', 'google', 'geocoding', 'dove', 'posizione' );
	}

	protected function register_controls() {

		/* ================= CONTENUTO - TITOLO ================= */
		$this->start_controls_section(
			'section_content_title',
			array(
				'label' => esc_html__( 'Heading', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_title',
			array(
				'label'        => esc_html__( 'Show Title', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'title_text',
			array(
				'label'       => esc_html__( 'Title', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'placeholder' => esc_html__( 'Leave empty to use the original VikBooking text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'default'     => '',
				'condition'   => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'     => esc_html__( 'HTML Tag', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h4',
				'condition' => array( 'show_title' => 'yes' ),
				'options'   => array(
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
				'label'     => esc_html__( 'Heading', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$titolo = '{{WRAPPER}} .vbeb-roommap-title';

		$this->add_responsive_control(
			'title_align',
			array(
				'label'     => esc_html__( 'Alignment', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array( 'title' => esc_html__( 'Left', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => esc_html__( 'Right', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => esc_html__( 'Justified', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-text-align-justify' ),
				),
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
					'luminosity'  => esc_html__( 'Luminosity', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'selectors' => array( $titolo => 'mix-blend-mode: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $titolo => 'color: {{VALUE}};' ),
			)
		);

		// VBEB 7.593.0, su richiesta dell'utente: tolta "Spaziatura"; fra
		// titolo e mappa resta lo spazio di VikBooking (15px).

		$this->end_controls_section();

		/* ================= STILE - MAPPA =================
		 * Bordo, raggio e ombra sul riquadro ".vbo-geo-wrapper" che
		 * VikBooking mette attorno alla mappa (raggio 20px, "overflow:
		 * hidden"; da telefono raggio 0). Il riquadro taglia gli angoli
		 * della mappa, cosi' il raggio si vede.
		 */
		$mappa = '{{WRAPPER}} .vbo-geo-wrapper';

		$this->start_controls_section(
			'section_style_map',
			array(
				'label' => esc_html__( 'Map', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.596.0, su richiesta dell'utente: tolti "Tipo di bordo" e
		// "Ombra riquadro"; resta solo "Raggio del bordo".
		$this->add_responsive_control(
			'map_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array( $mappa => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * VBEB 7.594.0: la mappa nell'editor di Elementor.
	 *
	 * Lo script di VikBooking (default_geomap.php) ha bisogno di VBOCore,
	 * Joomla.JText e VBOGMapsUtils, che nell'anteprima dell'editor non ci
	 * sono, e di un solo caricamento di Google Maps per pagina, mentre
	 * l'editor ridisegna il widget a ogni modifica del pannello. Qui, solo
	 * per l'anteprima, la stessa mappa con gli stessi dati della camera
	 * (centro, zoom, tipo, altezza, segnaposto della camera e delle unita',
	 * immagine sovrapposta) e Google Maps caricato una volta sola. Sul sito
	 * resta tutto di VikBooking.
	 *
	 * @return string
	 */
	protected function html_editor() {
		// VBEB free: nell'editor nella lingua del template aperto.
		$titolo  = class_exists( 'JText' ) ? vbeb_vbo_testo( 'VBO_LISTING_WHERE_YOULLBE' ) : '';
		$titolo  = ( '' === (string) $titolo || 'VBO_LISTING_WHERE_YOULLBE' === $titolo ) ? 'Dove sarai' : $titolo;
		$rparams = json_decode( (string) VBEB_Room_Context::get( 'params', '' ), true );
		$geo     = class_exists( 'VikBooking' ) && method_exists( 'VikBooking', 'getGeocodingInstance' ) ? VikBooking::getGeocodingInstance() : null;
		$gp      = ( $geo && $geo->isSupported() && is_array( $rparams ) ) ? $geo->getRoomGeoParams( $rparams ) : null;
		$attiva  = is_object( $gp ) && ! empty( $gp->enabled ) && isset( $gp->latitude, $gp->longitude );
		$altezza = ( is_object( $gp ) && ! empty( $gp->height ) ) ? (int) $gp->height : 300;
		$id      = 'vbeb-geo-map-' . $this->get_id();

		$html = '<div class="vbo-room-details-geo-wrapper"><h4>' . esc_html( $titolo ) . '</h4><div class="vbo-geo-wrapper">'
			. '<div id="' . esc_attr( $id ) . '" class="vbeb-geo-map-editor" style="width: 100%; height: ' . $altezza . 'px;">';

		if ( ! $attiva ) {
			return $html . '<span class="vbeb-roommap-placeholder">' . esc_html__( 'The map will appear on the front end.', 'vbeb-bridge-for-vikbooking-and-elementor' ) . '</span></div></div></div>';
		}

		$dati = array(
			'id'      => $id,
			'key'     => (string) $geo->getGoogleMapsKey(),
			'lat'     => (float) $gp->latitude,
			'lng'     => (float) $gp->longitude,
			'zoom'    => isset( $gp->zoom ) ? (int) $gp->zoom : 13,
			'tipo'    => ! empty( $gp->mtype ) ? (string) $gp->mtype : 'roadmap',
			'nome'    => (string) VBEB_Room_Context::get( 'name', '' ),
			'punti'   => array(),
			'overlay' => null,
		);

		if ( empty( $gp->marker_hide ) && ! empty( $gp->marker_lat ) && ! empty( $gp->marker_lng ) ) {
			$dati['punti'][] = array( 'lat' => (float) $gp->marker_lat, 'lng' => (float) $gp->marker_lng );
		}

		$unita = (int) VBEB_Room_Context::get( 'units', 1 );
		if ( ! empty( $gp->markers_multi ) && $unita > 0 && isset( $gp->units_pos ) && is_object( $gp->units_pos ) ) {
			foreach ( get_object_vars( $gp->units_pos ) as $n => $pos ) {
				if ( (int) $n <= $unita && is_object( $pos ) && isset( $pos->lat, $pos->lng ) ) {
					$dati['punti'][] = array( 'lat' => (float) $pos->lat, 'lng' => (float) $pos->lng );
				}
			}
		}

		if ( ! empty( $gp->goverlay ) && ! empty( $gp->overlay_img ) && ! empty( $gp->overlay_south ) ) {
			$dati['overlay'] = array(
				'url'   => (string) $gp->overlay_img,
				'south' => (float) $gp->overlay_south,
				'west'  => (float) $gp->overlay_west,
				'north' => (float) $gp->overlay_north,
				'east'  => (float) $gp->overlay_east,
			);
		}

		$js = '(function(d){'
			. 'function init(){var el=document.getElementById(d.id);if(!el||el.getAttribute("data-vbeb-ok"))return;el.setAttribute("data-vbeb-ok","1");'
			. 'var m=new google.maps.Map(el,{center:{lat:d.lat,lng:d.lng},zoom:d.zoom,mapId:"vbo_map",mapTypeId:d.tipo,mapTypeControl:false});'
			. 'd.punti.forEach(function(p){if(google.maps.marker&&google.maps.marker.AdvancedMarkerElement){new google.maps.marker.AdvancedMarkerElement({map:m,position:p,title:d.nome});}else{new google.maps.Marker({map:m,position:p,title:d.nome});}});'
			. 'if(d.overlay){new google.maps.GroundOverlay(d.overlay.url,new google.maps.LatLngBounds({lat:d.overlay.south,lng:d.overlay.west},{lat:d.overlay.north,lng:d.overlay.east})).setMap(m);}}'
			. 'if(window.google&&google.maps&&google.maps.Map){init();return;}'
			. 'window.vbebGmCoda=window.vbebGmCoda||[];window.vbebGmCoda.push(init);'
			. 'if(window.vbebGmCarica)return;window.vbebGmCarica=1;'
			. 'window.vbebGmPronto=function(){var c=window.vbebGmCoda||[];window.vbebGmCoda=[];c.forEach(function(f){f();});};'
			. 'var s=document.createElement("script");s.async=true;'
			. 's.src="https://maps.googleapis.com/maps/api/js?key="+encodeURIComponent(d.key)+"&libraries=marker&loading=async&callback=vbebGmPronto";'
			. 'document.head.appendChild(s);'
			. '})(' . wp_json_encode( $dati ) . ');';

		return $html . '</div></div></div><script>' . $js . '</script>';
	}

	protected function render() {
		$in_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$content   = VBEB_Room_Context::has_context() ? (string) VBEB_Room_Context::$geomap_html : '';

		// VBEB 7.594.0, su richiesta dell'utente: nell'editor la mappa vera
		// della camera di anteprima, se in VikBooking ha "Abilita Geocoding"
		// attivo (e c'e' la chiave di Google Maps); altrimenti il riquadro
		// grigio con "La mappa comparira' sul front-end".
		if ( $in_editor ) {
			$content = $this->html_editor();
		}

		if ( '' === trim( $content ) ) {
			return;
		}

		$settings = $this->get_settings_for_display();

		// Titolo: tolto, oppure con tag e testo scelti (come in "VBEB
		// Camera - Prezzi stagionali").
		$mostra_titolo = ! isset( $settings['show_title'] ) || 'yes' === $settings['show_title'];

		if ( ! $mostra_titolo ) {
			$content = preg_replace( '/<h4>.*?<\/h4>/is', '', $content, 1 );
		} elseif ( preg_match( '/<h4>(.*?)<\/h4>/is', $content, $m ) ) {
			$tag = isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h4';
			if ( ! in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ), true ) ) {
				$tag = 'h4';
			}
			$label   = ( isset( $settings['title_text'] ) && '' !== $settings['title_text'] ) ? $settings['title_text'] : $m[1];
			$content = preg_replace( '/<h4>.*?<\/h4>/is', '<' . $tag . ' class="vbeb-roommap-title">' . wp_kses_post( $label ) . '</' . $tag . '>', $content, 1 );
		}

		static $stile = false;
		if ( ! $stile ) {
			$stile = true;
			// Dentro il template VikBooking da' al blocco della mappa una riga
			// sopra, margini e spazio (".vbo-listing-details-wrap
			// .vbo-room-details-geo-wrapper", vikbooking_styles.css): nel
			// widget niente, l'impaginazione la decide il template. Titolo
			// senza margine sopra, 15px sotto come VikBooking (con qualunque
			// tag). Nell'editor il riquadro grigio al posto della mappa.
			// VBEB 7.595.0, su richiesta dell'utente: niente raggio di
			// VikBooking sul riquadro della mappa (20px, 0 da telefono); resta
			// "overflow: hidden". ":where()" non pesa: "Raggio del bordo" del
			// pannello lo sostituisce.
			echo '<style>.vbeb-roommap-wrap .vbo-room-details-geo-wrapper{margin:0;padding:0;border:0;}'
				. ':where(.vbeb-roommap-wrap) .vbo-geo-wrapper{border-radius:0;}'
				. '.vbeb-roommap-title{margin-top:0;margin-block-start:0;margin-bottom:15px;}'
				. ( $in_editor ? '.vbeb-roommap-wrap .vbeb-geo-map-editor{background:#e5e3df;display:flex;align-items:center;justify-content:center;}.vbeb-roommap-placeholder{color:#6d7882;font-size:13px;}' : '' )
				. '</style>';
		}

		// Nell'editor, come per gli altri widget della camera, il contenitore
		// della scheda camera di VikBooking, da cui dipendono alcune sue regole.
		if ( $in_editor ) {
			echo '<div class="vbo-listing-details-wrap">';
		}
		echo '<div class="vbeb-roommap-wrap">';
		echo $content; // phpcs:ignore -- HTML reale generato da VikBooking stesso, solo tag/testo del titolo gestiti qui sopra.
		echo '</div>';
		if ( $in_editor ) {
			echo '</div>';
		}
	}
}
