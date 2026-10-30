<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Widget Elementor "VikBooking Prezzo Camera". Riproduce la stessa
 * struttura reale vista nei template originali di VikBooking
 * (default_listing.php): valuta, numero prezzo,
 * etichetta personalizzata ed eventuale sottotesto - con in piu' un
 * campo di testo libero prima del prezzo al posto della stringa
 * "a partire da" gestita dalle traduzioni di VikBooking, con controlli
 * di stile propri (colore, tipografia). VBEB 7.351.0: tolto il testo
 * libero dopo il prezzo, dove compare gia' l'etichetta personalizzata.
 */
class VBEB_Price_Widget extends Widget_Base {

	public function get_name() {
		return 'vbeb_room_price_widget';
	}

	public function get_title() {
		return esc_html__( 'VBEB Room - Price', 'vbeb' );
	}

	public function get_icon() {
		return 'eicon-price-list';
	}

	public function get_categories() {
		return array( 'vikbooking-bridge' );
	}

	public function get_keywords() {
		return array( 'vikbooking', 'prezzo', 'price', 'camera' );
	}

	/*
	 * VBEB 7.501.0, su richiesta dell'utente: le impostazioni che esistono
	 * anche in Elementor usano i suoi testi (dominio "elementor": Text Color,
	 * Color, Yes, No), cosi' la traduzione arriva dai file di lingua di
	 * Elementor. Le altre (Testi, Mostra etichetta..., Prezzo, ...) sono solo
	 * di VBEB e restano in italiano fino alla fase delle traduzioni.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Texts', 'vbeb' ), // VBEB 7.353.0: era "Contenuto".
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_text_before',
			array(
				'label'   => esc_html__( 'Show Label Before the Price', 'vbeb' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'text_before',
			array(
				'label'       => esc_html__( 'Text Before the Price', 'vbeb' ),
				'type'        => Controls_Manager::TEXT,
				// VBEB 7.353.0: campo sotto l'etichetta, largo come il pannello.
				'label_block' => true,
				'default'   => 'a partire da',
				'condition' => array( 'show_text_before' => 'yes' ),
			)
		);

		// VBEB 7.351.0, su richiesta dell'utente: tolti "Mostra testo dopo
		// il prezzo" e il suo testo. Dopo il prezzo compare gia', da solo,
		// l'"Etichetta prezzo personalizzato" della camera in VikBooking.

		// VBEB 7.354.0, su richiesta dell'utente: "per notte" (VBLISTPERNIGHT)
		// dopo il prezzo solo se lo si chiede, non piu' sempre come nella
		// 7.352.0: il widget si usa anche nella Sezione 2. L'etichetta
		// personalizzata della camera, se c'e', ha sempre la precedenza.
		$this->add_control(
			'show_default_after',
			array(
				'label'        => esc_html__( 'Show Default Label After the Price', 'vbeb' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'vbeb' ),
				'label_off'    => esc_html__( 'No', 'vbeb' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->end_controls_section();

		/* ================= STILE - PREZZO =================
		 * VBEB 7.358.0, su richiesta dell'utente: al posto delle sezioni
		 * "Simbolo valuta" e "Numero prezzo", una sola sezione "Prezzo" che
		 * agisce insieme su numero e valuta (".vbeb-price-number" e
		 * ".vbeb-price-currency", i due span di formatCurrencyNumber()).
		 * VBEB 7.359.0: prima sezione del pannello stile.
		 */
		$prezzo = '{{WRAPPER}} .vbeb-price-number, {{WRAPPER}} .vbeb-price-currency';

		$this->start_controls_section(
			'section_style_price',
			array(
				'label' => esc_html__( 'Price', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_typography',
				'selector' => $prezzo,
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $prezzo => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - TESTO PRIMA DEL PREZZO ================= */
		$this->start_controls_section(
			'section_style_text_before',
			array(
				'label' => esc_html__( 'Label Before the Price', 'vbeb' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_text_before' => 'yes' ),
			)
		);

		// VBEB 7.585.0, su richiesta dell'utente: prima Tipografia, poi Colore.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_before_typography',
				'selector' => '{{WRAPPER}} .vbeb-price-text-before',
			)
		);

		$this->add_control(
			'text_before_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ), // VBEB 7.586.0: era "Color".
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vbeb-price-text-before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// VBEB 7.351.0: tolta anche la sezione di stile "Testo Dopo il
		// Prezzo", che valeva solo per quel testo.

		/* ================= STILE - ETICHETTA PREDEFINITA DOPO IL PREZZO =================
		 * VBEB 7.360.0, su richiesta dell'utente: il testo predefinito di
		 * VikBooking ("per notte", VBLISTPERNIGHT) mostrato dopo il prezzo
		 * con "Mostra etichetta predefinita dopo il prezzo" acceso e la
		 * camera senza etichetta personalizzata. Ha una classe propria,
		 * ".vbeb-price-default-label", separata da quella dell'etichetta
		 * personalizzata. Sezione visibile solo con l'interruttore acceso.
		 */
		$this->start_controls_section(
			'section_style_default_after',
			array(
				'label'     => esc_html__( 'Default Label After the Price', 'vbeb' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_default_after' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'default_after_typography',
				'selector' => '{{WRAPPER}} .vbeb-price-default-label',
			)
		);

		$this->add_control(
			'default_after_color',
			array(
				'label'     => esc_html__( 'Text Color', 'vbeb' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vbeb-price-default-label' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ================= STILE - ETICHETTA E SUB-TESTO PREZZO PERSONALIZZATO =================
		 * VBEB 7.361.0, su richiesta dell'utente: al posto della sezione
		 * "Etichetta/sottotesto personalizzati", due sezioni con i nomi dei
		 * campi della camera in VikBooking. Stessi identificativi di prima:
		 * i valori gia' scelti restano.
		 */
		foreach ( array(
			'section_style_custom'         => array( esc_html__( 'Custom Price Label', 'vbeb' ), 'custom_label', '.vbeb-price-custom-label' ),
			'section_style_custom_subtext' => array( esc_html__( 'Custom Price Sub-Text', 'vbeb' ), 'custom_subtext', '.vbeb-price-custom-subtext' ),
		) as $sezione => $dati ) {
			$this->start_controls_section(
				$sezione,
				array(
					'label' => $dati[0],
					'tab'   => Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $dati[1] . '_typography',
					'selector' => '{{WRAPPER}} ' . $dati[2],
				)
			);

			$this->add_control(
				$dati[1] . '_color',
				array(
					'label'     => esc_html__( 'Text Color', 'vbeb' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} ' . $dati[2] => 'color: {{VALUE}};' ),
				)
			);

			$this->end_controls_section();
		}
	}

	protected function render() {
		if ( ! VBEB_Room_Context::has_context() || ! class_exists( 'VikBooking' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div style="padding:20px;border:1px dashed #ccc;text-align:center;color:#888;">' . esc_html__( 'Room - Price: the real price will appear on the front end of a room page.', 'vbeb' ) . '</div>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		// Stessa identica logica/funzioni reali viste nei template originali
		// di VikBooking (default_listing.php).
		$rparams = json_decode( VBEB_Room_Context::get( 'params', '' ), true );
		$custprice       = VikBooking::getRoomParam( 'custprice', $rparams );
		$custpricetxt    = VikBooking::getRoomParam( 'custpricetxt', $rparams );
		// VBEB 7.352.0, su richiesta dell'utente: con "Etichetta prezzo
		// personalizzato" vuota compare "per notte", come promette la
		// descrizione di quel campo in VikBooking. VikBooking lo fa solo
		// nella lista camere (roomslist/tmpl/default.php, riga 176:
		// VBLISTPERNIGHT), non nel dettaglio camera; il widget lo fa ovunque.
		// VBEB 7.354.0: solo con "Mostra testo predefinito dopo il prezzo".
		$etichetta_classe = 'vbeb-price-custom-label';
		if ( ! empty( $custpricetxt ) ) {
			$custpricetxt = vbeb_vbo_testo( $custpricetxt );
		} elseif ( isset( $settings['show_default_after'] ) && 'yes' === $settings['show_default_after'] ) {
			$custpricetxt     = vbeb_vbo_testo( 'VBLISTPERNIGHT' );
			// VBEB 7.360.0: classe propria, per la sezione di stile
			// "Etichetta predefinita dopo il prezzo".
			$etichetta_classe = 'vbeb-price-default-label';
		} else {
			$custpricetxt = '';
		}
		$custpricesubtxt = VikBooking::getRoomParam( 'custpricesubtxt', $rparams );
		$cost            = VBEB_Room_Context::get( 'cost', 0 );

		if ( $cost <= 0 && empty( $custprice ) ) {
			return;
		}

		$currencysymb = VikBooking::getCurrencySymb();
		$use_cost     = $custprice ?: $cost;
		$formatted    = VikBooking::formatCurrencyNumber(
			VikBooking::numberFormat( $use_cost ),
			$currencysymb,
			array( '<span class="vbeb-price-currency">%s</span>', '<span class="vbeb-price-number">%s</span>' )
		);

		echo '<div class="vbeb-price-wrap">';

		if ( 'yes' === $settings['show_text_before'] && '' !== $settings['text_before'] ) {
			echo '<span class="vbeb-price-text-before">' . esc_html( $settings['text_before'] ) . '</span> ';
		}

		echo $formatted; // phpcs:ignore -- HTML gia' generato da VikBooking stesso (span di valuta/numero).

		if ( ! empty( $custpricetxt ) ) {
			// VBEB 7.352.0: uno spazio prima, come per il testo prima del
			// prezzo, cosi' non si attacca al simbolo della valuta.
			echo ' <span class="' . esc_attr( $etichetta_classe ) . '">' . esc_html( $custpricetxt ) . '</span>';
		}

		if ( ! empty( $custpricesubtxt ) ) {
			echo '<div class="vbeb-price-custom-subtext">' . esc_html( $custpricesubtxt ) . '</div>';
		}

		echo '</div>';
	}
}
