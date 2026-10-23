<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Modules\Library\Documents\Library_Document;

/**
 * VBEB 7.481.0, su richiesta dell'utente: tipo di template "VBEB Popup".
 *
 * Un template da mostrare dentro un popup di VikBooking. Apertura,
 * chiusura e invio restano quelli di VikBooking; il template ne riempie il
 * riquadro (per il "Modulo informazioni": VBEB_Reqinfo_Widget::con_template()).
 *
 * Impostazioni: "Tipo di popup" (per ora solo "Modulo informazioni").
 * Stile: "Popup" e "Sovrapponi". Avanzato: "Avanzate" (Margine, Rientro)
 * e "CSS personalizzato". Le impostazioni vuote lasciano l'aspetto
 * di VikBooking per quel popup; compilate, valgono:
 * - nell'editor, sul contenitore del template (anteprima);
 * - sul sito, sul riquadro e sullo sfondo scuro di VikBooking.
 * Il selettore e' lo stesso nei due casi (".vbeb-popup.vbeb-popup-ID"):
 * nell'editor la classe va sul contenitore del template
 * (get_container_attributes()), sul sito sul riquadro di VikBooking.
 *
 * L'aspetto di VikBooking nell'anteprima vale solo nell'editor
 * (":where(body.elementor-editor-active)"); sul sito c'e' gia' quello vero.
 * ":where()" non pesa, quindi le impostazioni del pannello, che vengono
 * dopo, lo sostituiscono.
 */
class VBEB_Popup_Document extends Library_Document {

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_kit']    = true;
		$properties['show_in_finder'] = true;

		return $properties;
	}

	public static function get_type() {
		return 'vbeb-popup';
	}

	public function get_name() {
		return 'vbeb-popup';
	}

	public static function get_title() {
		return esc_html__( 'VBEB Popup', 'vbeb' );
	}

	public static function get_plural_title() {
		return 'VBEB Popup';
	}

	public function get_css_wrapper_selector() {
		return '.vbeb-popup.vbeb-popup-' . $this->get_main_id();
	}

	/**
	 * Nell'anteprima dell'editor le classi del popup vanno sul contenitore
	 * del template; sul sito no (vanno sul riquadro di VikBooking).
	 */
	public function get_container_attributes() {
		$attributes = parent::get_container_attributes();

		if ( \Elementor\Plugin::$instance->preview->is_preview_mode( $this->get_main_id() ) ) {
			$attributes['class'] .= ' vbeb-popup vbeb-popup-' . $this->get_main_id();
		}

		return $attributes;
	}

	/**
	 * VBEB 7.576.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i suoi testi (dominio "elementor"); le altre
	 * restano in italiano fino alla fase delle traduzioni.
	 */
	protected function register_controls() {

		/* ================= IMPOSTAZIONI - POPUP ================= */
		$this->start_controls_section(
			'vbeb_popup_section_tipo',
			array(
				'label' => esc_html__( 'Popup', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);

		$this->add_control(
			'vbeb_popup_tipo',
			array(
				'label'       => esc_html__( 'Popup Type', 'vbeb' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'reqinfo',
				'label_block' => true,
				'options'     => array(
					'reqinfo' => esc_html__( 'Information Form', 'vbeb' ),
				),
			)
		);

		// L'aspetto del popup "Richiedi informazioni" di VikBooking
		// (vikbooking_styles.css: "#vbdialog-overlay" riga 4124,
		// ".vbdialog-reqinfo" riga 4197), solo nell'anteprima dell'editor.
		// VBEB 7.485.0: la larghezza e' sempre quella di VikBooking per il
		// tipo di popup (nessuna impostazione).
		// VBEB 7.494.0, su richiesta dell'utente (come il popup di Elementor
		// Pro): nell'anteprima il template sta dentro ".vbeb-popup-modal"
		// (vbeb_popup_anteprima_modale()), fisso a tutto schermo, che lo
		// centra (flex) e porta lo sfondo scuro; il riquadro non ha margine
		// suo, quindi "Margine" a 0 lo lascia al centro. Sul sito il
		// riquadro di VikBooking e' centrato allo stesso modo (fisso, al 50%
		// con translate), e il margine lo sposta e basta.
		$this->add_control(
			'vbeb_popup_base_reqinfo',
			array(
				'type'      => Controls_Manager::HIDDEN,
				'default'   => 'yes',
				'condition' => array(
					'vbeb_popup_tipo' => 'reqinfo',
				),
				'selectors' => array(
					':where(body.elementor-editor-active) {{WRAPPER}}' => 'box-sizing: border-box; width: var(--vbeb-popup-w, 50vw); max-width: 560px; padding: 25px; background-color: var(--vbo-white-bg-color, #fff); border-radius: 10px; box-shadow: 0 0 5px 0 #333;',
					':where(body.elementor-editor-active) .vbeb-popup-modal:has(> {{WRAPPER}})' => 'background-color: rgba(0, 0, 0, .5);',
				),
			)
		);

		// VBEB 7.487.0, su richiesta dell'utente: il pulsante di chiusura
		// con l'immagine di VikBooking (vbeb_popup_css_chiusura()). Sul sito
		// lo aggiunge il "Pulsante informazioni" (con_template()); qui,
		// nell'anteprima dell'editor, e' un "::after" del contenitore del
		// template (solo da vedere).
		$this->add_control(
			'vbeb_popup_close',
			array(
				'label'        => esc_html__( 'Show Close Button', 'vbeb' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elementor' ),
				'label_off'    => esc_html__( 'No', 'elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'selectors'    => array(
					'body.elementor-editor-active {{WRAPPER}}::after' => 'content: "";',
				),
			)
		);

		$this->end_controls_section();

		parent::register_controls();

		/* ================= STILE - POPUP ================= */
		$this->start_controls_section(
			'vbeb_popup_section_style',
			array(
				'label' => esc_html__( 'Popup', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'vbeb_popup_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}}',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'vbeb_popup_border',
				'selector' => '{{WRAPPER}}',
			)
		);

		$this->add_responsive_control(
			'vbeb_popup_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'vbeb_popup_box_shadow',
				'selector' => '{{WRAPPER}}',
			)
		);

		$this->end_controls_section();

		/* ================= STILE - SOVRAPPONI ================= */
		$this->start_controls_section(
			'vbeb_popup_section_overlay',
			array(
				'label' => esc_html__( 'Overlay', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// VBEB 7.485.0, su richiesta dell'utente: sfondo completo (colore,
		// sfumatura, immagine) come in Elementor. Nell'editor: lo sfondo
		// scuro dell'anteprima (".vbeb-popup-modal", VBEB 7.494.0); sul sito: "#vbdialog-overlay" che contiene il
		// riquadro (stesso selettore dell'aspetto di base, che viene prima).
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'vbeb_popup_overlay_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => ':where(body.elementor-editor-active) .vbeb-popup-modal:has(> {{WRAPPER}}), #vbdialog-overlay:has({{WRAPPER}})',
			)
		);

		$this->end_controls_section();

		/* ================= AVANZATO - AVANZATE =================
		 * VBEB 7.484.0, su richiesta dell'utente: "Margine" e "Rientro" del
		 * riquadro del popup (il Rientro sostituisce la "Spaziatura interna"
		 * del pannello Stile, stesso identificativo "vbeb_popup_padding"),
		 * piu' "CSS personalizzato" di Elementor (con Elementor Pro: la
		 * parola "selector" indica il riquadro del popup).
		 */
		$this->start_controls_section(
			'vbeb_popup_section_advanced',
			array(
				'label' => esc_html__( 'Advanced', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$this->add_responsive_control(
			'vbeb_popup_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'vbeb_popup_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		\Elementor\Plugin::$instance->controls_manager->add_custom_css_controls( $this );
	}
}
