<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;

/**
 * Widget Elementor per i servizi/caratteristiche (amenities) della camera
 * VikBooking. Usa VikBooking::loadRoomAmenities() (stessa funzione reale
 * vista nel template originale default_listing.php) per ottenere i dati
 * strutturati (icona, nome, icona-testo). Pannello Contenuto e Stile
 * ricalcati su quelli reali del widget nativo "Lista Icone" di Elementor:
 * Layout (Predefinito verticale / Inline orizzontale), Icona (colore
 * Normale/Hover, dimensione, spaziatura, allineamento), Testo (colore
 * Normale/Hover, tipografia), Divisore (stile, spessore, larghezza,
 * colore).
 */
class VBEB_Amenities_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_amenities_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Amenities', 'vbeb-bridge-for-vikbooking-and-elementor' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'servizi', 'amenities', 'caratteristiche', 'camera', 'lista icone' );
	}

	/*
	 * VBEB 7.507.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i testi del suo widget "Elenco icone"
	 * (dominio "elementor", icon-list.php), cosi' la traduzione arriva dai
	 * file di lingua di Elementor. Restano in italiano, solo di VBEB: "Lista
	 * servizi" e "Mostra nome servizio".
	 */
	protected function register_controls() {

		/* ================= CONTENUTO - SERVIZI ================= */
		$this->start_controls_section(
			'section_amenities',
			array(
				'label' => esc_html__( 'Amenities List', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'traditional',
				'options' => array(
					'traditional' => array(
						'title' => esc_html__( 'Default', 'vbeb-bridge-for-vikbooking-and-elementor' ),
						'icon'  => 'eicon-editor-list-ul',
					),
					'inline'      => array(
						'title' => esc_html__( 'Inline', 'vbeb-bridge-for-vikbooking-and-elementor' ),
						'icon'  => 'eicon-ellipsis-h',
					),
				),
			)
		);

		// VBEB 7.393.0, su richiesta dell'utente: "Mostra nome servizio".
		// Spento, di ogni servizio resta solo l'icona; i servizi senza
		// icona (solo testo) non compaiono.
		$this->add_control(
			'show_name',
			array(
				'label'        => esc_html__( 'Show Amenity Name', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'No', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		/* ================= STILE - ELENCO =================
		 * VBEB 7.394.0, su richiesta dell'utente: al posto della sezione
		 * "Divisore", la sezione "Elenco" del widget Elenco icone di
		 * Elementor: Spazio intermedio (era "Spaziatura tra elementi" nel
		 * pannello Contenuto, stesso identificativo), Allineamento,
		 * Divisore, Stile, Spessore, Larghezza (Altezza con il layout in
		 * linea) e Colore. Vale per entrambi i layout: in verticale il
		 * divisore e' una linea orizzontale fra un servizio e l'altro, in
		 * linea una linea verticale; sta sempre a meta' dello spazio
		 * intermedio. I valori passano come variabili CSS sull'elenco
		 * (--vbeb-am-*), lette dalle regole fisse nel render().
		 */
		$elenco = '{{WRAPPER}} .vbeb-amenities-list';

		$this->start_controls_section(
			'section_style_divider',
			array(
				'label' => esc_html__( 'List', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.395.0: spostata da Contenuto > Servizi a Stile > Elenco.
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'min'            => 1,
				'max'            => 6,
				'condition'      => array( 'layout' => 'traditional' ),
				'selectors'      => array(
					'{{WRAPPER}} .vbeb-amenities-traditional' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
			)
		);

		$this->add_responsive_control(
			'space_between',
			array(
				'label'      => esc_html__( 'Space Between', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 60 ),
					'em' => array( 'min' => 0, 'max' => 5 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'selectors'  => array(
					$elenco => 'gap: {{SIZE}}{{UNIT}}; --vbeb-am-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'list_align',
			array(
				'label'                => esc_html__( 'Alignment', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'start'  => array( 'title' => esc_html__( 'Start', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-center' ),
					'end'    => array( 'title' => esc_html__( 'End', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-h-align-right' ),
				),
				// Allinea il contenuto di ogni servizio (in verticale) o i
				// servizi nella riga (in linea), e il divisore orizzontale.
				'selectors_dictionary' => array(
					'start'  => 'flex-start; --vbeb-am-dl: 0; --vbeb-am-dr: auto;',
					'center' => 'center; --vbeb-am-dl: 0; --vbeb-am-dr: 0;',
					'end'    => 'flex-end; --vbeb-am-dl: auto; --vbeb-am-dr: 0;',
				),
				'selectors'            => array(
					'{{WRAPPER}} .vbeb-amenities-traditional .vbeb-amenity-item, {{WRAPPER}} .vbeb-amenities-inline' => 'justify-content: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'divider',
			array(
				'label'        => esc_html__( 'Divider', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'label_off'    => esc_html__( 'Off', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'prefix_class' => 'vbeb-am-divider-',
			)
		);

		$this->add_control(
			'divider_style',
			array(
				'label'     => esc_html__( 'Style', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'solid'  => esc_html__( 'Solid', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'double' => esc_html__( 'Double', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'dotted' => esc_html__( 'Dotted', 'vbeb-bridge-for-vikbooking-and-elementor' ),
					'dashed' => esc_html__( 'Dashed', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				),
				'condition' => array( 'divider' => 'yes' ),
				'selectors' => array( $elenco => '--vbeb-am-ds: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'divider_weight',
			array(
				'label'      => esc_html__( 'Weight', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 20 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1 ),
				'condition'  => array( 'divider' => 'yes' ),
				'selectors'  => array( $elenco => '--vbeb-am-dwt: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'divider_width',
			array(
				'label'      => esc_html__( 'Width', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px', 'custom' ),
				'range'      => array( '%' => array( 'min' => 0, 'max' => 100 ) ),
				'default'    => array( 'unit' => '%' ),
				'condition'  => array(
					'divider' => 'yes',
					'layout'  => 'traditional',
				),
				'selectors'  => array( $elenco => '--vbeb-am-dw: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'divider_height',
			array(
				'label'      => esc_html__( 'Height', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px', 'custom' ),
				'range'      => array( '%' => array( 'min' => 0, 'max' => 100 ) ),
				'default'    => array( 'unit' => '%' ),
				'condition'  => array(
					'divider' => 'yes',
					'layout'  => 'inline',
				),
				'selectors'  => array( $elenco => '--vbeb-am-dh: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'divider_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ddd',
				'condition' => array( 'divider' => 'yes' ),
				'selectors' => array( $elenco => '--vbeb-am-dc: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - ICONA ================= */
		$this->start_controls_section(
			'section_style_icon',
			array(
				'label' => esc_html__( 'Icon', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		/*
		 * VBEB 7.399.0, su richiesta dell'utente: impostazioni come la
		 * sezione "Icona" del widget Elenco icone di Elementor, senza le
		 * schede Normale/Hover (il colore al passaggio del mouse e la durata
		 * della transizione sono tolti). Identificativi invariati, valori
		 * salvati conservati.
		 */
		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-amenity-icon' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Size', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'separator'  => 'before',
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 6, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 24 ),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-amenity-icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; object-fit: contain;',
					'{{WRAPPER}} .vbeb-amenity-icon'     => 'font-size: {{SIZE}}{{UNIT}}; --vbeb-am-is: {{SIZE}}{{UNIT}};',
					// VBEB 7.402.0: anche sull'icona vera e propria (<i> o <svg>), che
					// altrimenti tiene la dimensione impostata altrove.
					'{{WRAPPER}} .vbeb-amenity-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vbeb-amenity-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_spacing',
			array(
				'label'      => esc_html__( 'Gap', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 50 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 8 ),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-amenity-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// VBEB 7.628.0, su richiesta dell'utente: tolto "Allineamento
		// orizzontale" ("icon_align"): spostava l'icona solo dentro il suo
		// spazio largo quanto la Dimensione, quindi non cambiava nulla con le
		// immagini e di pochi pixel con le icone strette. Le icone restano
		// centrate nel loro spazio (regola fissa ".vbeb-amenity-icon").
		$this->add_responsive_control(
			'icon_vertical_align',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'separator' => 'before',
				'options'   => array(
					'flex-start' => array( 'title' => esc_html__( 'Start', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-top' ),
					'center'     => array( 'title' => esc_html__( 'Center', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-middle' ),
					'flex-end'   => array( 'title' => esc_html__( 'End', 'vbeb-bridge-for-vikbooking-and-elementor' ), 'icon' => 'eicon-v-align-bottom' ),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .vbeb-amenity-item' => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_vertical_offset',
			array(
				'label'      => esc_html__( 'Adjust Vertical Position', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -15, 'max' => 15 ),
					'em' => array( 'min' => -1, 'max' => 1, 'step' => 0.1 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 0 ),
				'selectors'  => array(
					'{{WRAPPER}} .vbeb-amenity-icon' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - TESTO ================= */
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => esc_html__( 'Text', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				// VBEB 7.393.0: solo con il nome visibile.
				'condition' => array( 'show_name' => 'yes' ),
			)
		);

		/*
		 * VBEB 7.401.0, su richiesta dell'utente: impostazioni come la
		 * sezione "Testo" del widget Elenco icone di Elementor, senza le
		 * schede Normale/Hover (il colore al passaggio del mouse e' tolto).
		 * Identificativi invariati, valori salvati conservati.
		 */
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .vbeb-amenity-name',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .vbeb-amenity-name',
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Color', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-amenity-name' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! VBEB_Room_Context::has_context() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'Room - Amenities: the real list will appear on the front end of a room page.', 'vbeb-bridge-for-vikbooking-and-elementor' ) . '</div>';
			}
			return;
		}

		if ( ! class_exists( 'VikBooking' ) || ! defined( 'VBO_SITE_URI' ) ) {
			return;
		}

		$settings = $this->get_settings_for_display();

		$idcarat = VBEB_Room_Context::get( 'idcarat' );
		$vbo_tn  = VBEB_Room_Context::$vbo_tn;

		// Stessa identica funzione usata nel template originale
		// default_listing.php per ottenere i servizi della camera.
		$amenities = VikBooking::loadRoomAmenities( $idcarat, $vbo_tn );

		if ( empty( $amenities ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'This room has no amenities assigned.', 'vbeb-bridge-for-vikbooking-and-elementor' ) . '</div>';
			}
			return;
		}

		$layout      = isset( $settings['layout'] ) ? $settings['layout'] : 'traditional';
		$layout_class = ( 'inline' === $layout ) ? 'vbeb-amenities-inline' : 'vbeb-amenities-traditional';

		echo '<ul class="vbeb-amenities-list ' . esc_attr( $layout_class ) . '">';

		// VBEB 7.393.0: "Mostra nome servizio".
		$mostra_nome = ! isset( $settings['show_name'] ) || 'yes' === $settings['show_name'];

		foreach ( $amenities as $a ) {
			$name    = isset( $a['name'] ) ? $a['name'] : '';
			$icon    = isset( $a['icon'] ) ? $a['icon'] : '';
			$textimg = isset( $a['textimg'] ) ? $a['textimg'] : '';

			// Stessa identica logica di priorita' vista nel template originale
			// di VikBooking: textimg+icon, poi textimg (font-icon/SVG o testo),
			// poi icon da sola, poi solo nome.
			$icona = '';
			$testo = '';
			if ( ! empty( $textimg ) ) {
				if ( ! empty( $icon ) ) {
					$icona = '<span class="vbeb-amenity-icon"><img src="' . esc_url( VBO_SITE_URI . 'resources/uploads/' . $icon ) . '" alt="' . esc_attr( $name ) . '" /></span>';
					$testo = $textimg;
				} elseif ( strpos( $textimg, '</i>' ) !== false || strpos( $textimg, '<svg' ) !== false ) {
					$icona = '<span class="vbeb-amenity-icon">' . $textimg . '</span>';
					$testo = $name;
				} else {
					$testo = $textimg;
				}
			} elseif ( ! empty( $icon ) ) {
				$icona = '<span class="vbeb-amenity-icon"><img src="' . esc_url( VBO_SITE_URI . 'resources/uploads/' . $icon ) . '" alt="' . esc_attr( $name ) . '" title="' . esc_attr( $name ) . '" /></span>';
				$testo = $name;
			} else {
				$testo = $name;
			}

			// Senza nome e senza icona non resta nulla da mostrare.
			if ( ! $mostra_nome && '' === $icona ) {
				continue;
			}

			echo '<li class="vbeb-amenity-item">';
			echo $icona; // phpcs:ignore -- stesso trattamento del codice originale VikBooking (icona di VikBooking o immagine con attributi protetti).
			if ( $mostra_nome ) {
				echo '<span class="vbeb-amenity-name">' . esc_html( $testo ) . '</span>';
			}
			echo '</li>';
		}

		echo '</ul>';
		?>
		<style>
			.vbeb-amenities-list { list-style: none; margin: 0; padding: 0; }
			.vbeb-amenity-item { display: flex; }
			/* VBEB 7.399.0: spazio dell'icona largo quanto la Dimensione, per l'allineamento orizzontale; spostamento verticale con "top". */
			.vbeb-amenity-icon { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; width: var(--vbeb-am-is, auto); position: relative; line-height: 1; }
			.vbeb-amenities-traditional { display: grid; grid-template-columns: repeat(1, 1fr); }
			.vbeb-amenities-inline { display: flex; flex-wrap: wrap; }
			/* VBEB 7.394.0: divisore (sezione di stile "Elenco"), a meta' dello spazio intermedio. */
			.vbeb-amenity-item { position: relative; }
			.vbeb-am-divider-yes .vbeb-amenities-traditional .vbeb-amenity-item:not(:last-child)::after { content: ""; position: absolute; bottom: calc(var(--vbeb-am-gap, 10px) / -2); left: var(--vbeb-am-dl, 0); right: var(--vbeb-am-dr, auto); margin: 0 auto; width: var(--vbeb-am-dw, 100%); border-top: var(--vbeb-am-dwt, 1px) var(--vbeb-am-ds, solid) var(--vbeb-am-dc, #ddd); transform: translateY(50%); }
			.vbeb-am-divider-yes .vbeb-amenities-inline .vbeb-amenity-item:not(:last-child)::after { content: ""; position: absolute; right: calc(var(--vbeb-am-gap, 10px) / -2); top: 50%; height: var(--vbeb-am-dh, 100%); border-left: var(--vbeb-am-dwt, 1px) var(--vbeb-am-ds, solid) var(--vbeb-am-dc, #ddd); transform: translate(50%, -50%); }
		</style>
		<?php
	}
}
