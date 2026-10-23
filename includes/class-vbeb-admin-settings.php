<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trova la posizione reale del menu di VikBooking nella bacheca (cercando
 * tra tutte le voci registrate quella il cui slug contiene "vikbooking"),
 * cosi' possiamo posizionarci subito sotto senza dover indovinare un
 * numero fisso che potrebbe non essere corretto o cambiare in futuro.
 */
function vbeb_find_position_after_vikbooking() {
	global $menu;
	$fallback_position = 59;
	if ( ! is_array( $menu ) ) {
		return $fallback_position;
	}
	foreach ( $menu as $position => $item ) {
		$slug = isset( $item[2] ) ? $item[2] : '';
		if ( stripos( $slug, 'vikbooking' ) !== false ) {
			return $position + 1;
		}
	}
	return $fallback_position;
}

function vbeb_add_admin_menu() {
	// VBEB free, su richiesta dell'utente: niente pagina "Impostazioni"; la
	// pagina principale del menu e' "Template" (slug "vbeb-template").
	add_menu_page(
		'VBEB - Bridge for VikBooking and Elementor',
		'VBEB',
		'manage_options',
		'vbeb-template',
		'vbeb_render_template_page',
		'dashicons-layout',
		vbeb_find_position_after_vikbooking()
	);
	// WordPress crea da solo, per ogni menu principale con sottomenu, una
	// prima voce con lo stesso nome del menu ("VBEB"): un sottomenu con lo
	// STESSO slug della pagina principale ne cambia l'etichetta in
	// "Template".
	add_submenu_page(
		'vbeb-template',
		esc_html__( 'Template', 'vbeb' ) . ' - VBEB',
		esc_html__( 'Template', 'vbeb' ),
		'manage_options',
		'vbeb-template',
		'vbeb_render_template_page'
	);
}
// Priorita' 20: eseguito dopo la registrazione del menu di VikBooking
// (che normalmente avviene a priorita' predefinita 10), cosi' quando
// cerchiamo la sua posizione nell'array $menu la troviamo gia' presente.
add_action( 'admin_menu', 'vbeb_add_admin_menu', 20 );

function vbeb_register_settings() {
	register_setting( 'vbeb_template_settings_group', 'vbeb_template_listing', array( 'sanitize_callback' => 'absint' ) );
}
add_action( 'admin_init', 'vbeb_register_settings' );

/**
 * Ritorna l'elenco dei template salvati nella Libreria Template di Elementor,
 * cosi' l'utente puo' sceglierne uno da usare come override.
 */
function vbeb_get_elementor_templates() {
	if ( ! post_type_exists( 'elementor_library' ) ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
}

/**
 * Avvisi sull'installazione dell'override.
 *
 * VBEB 7.45.0. Il processo di copia e' automatico e salva il proprio esito
 * in tre opzioni; qui si leggono per mostrare eventuali problemi senza
 * bisogno di alcuna azione manuale.
 *
 * @return void
 */
function vbeb_avvisi_override() {
	$errore  = get_option( 'vbeb_override_install_error', '' );
	$falliti = get_option( 'vbeb_override_install_failed', array() );
	$saltati = get_option( 'vbeb_override_install_skipped', array() );

	if ( ! empty( $errore ) ) {
		echo '<div class="notice notice-error inline" style="margin:0 0 20px 0;"><p>' . esc_html__( 'Unable to write to the override folder: check the folder permissions via FTP.', 'vbeb' ) . '</p></div>';
	} elseif ( ! empty( $falliti ) ) {
		/* translators: %s: elenco dei file. */
		echo '<div class="notice notice-error inline" style="margin:0 0 20px 0;"><p>' . sprintf( esc_html__( 'Copy failed for: %s (probably a permission problem on that file).', 'vbeb' ), esc_html( implode( ', ', (array) $falliti ) ) ) . '</p></div>';
	} elseif ( ! empty( $saltati ) ) {
		/* translators: %s: elenco dei file. */
		echo '<div class="notice notice-warning inline" style="margin:0 0 20px 0;"><p>' . sprintf( esc_html__( 'Files already present with content that is not ours, not overwritten for safety: %s. See the "-vbeb-suggested.php" copy saved next to them.', 'vbeb' ), esc_html( implode( ', ', (array) $saltati ) ) ) . '</p></div>';
	}
}

/**
 * Pagina "Template": scelta del template Elementor per il Dettaglio Camera.
 */
function vbeb_render_template_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$templates       = vbeb_get_elementor_templates();
	$current_listing = (int) get_option( 'vbeb_template_listing', 0 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Template', 'vbeb' ); ?> - VBEB</h1>

		<?php if ( ! did_action( 'elementor/loaded' ) ) : ?>
			<div class="notice notice-error inline" style="margin:0 0 20px 0;"><p><?php esc_html_e( 'Elementor is not active. Activate it to use this feature.', 'vbeb' ); ?></p></div>
		<?php endif; ?>

		<?php vbeb_avvisi_override(); ?>

		<?php if ( empty( $templates ) ) : ?>
			<div class="notice notice-warning inline" style="margin:0 0 20px 0;">
				<p><?php esc_html_e( 'There are no templates in the Elementor Template Library yet. First create a template of type "Section" (Templates > Add New) with the VBEB widgets you want to use (category "VBEB - Room Details" in the widget panel), then come back here to select it.', 'vbeb' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'vbeb_template_settings_group' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="vbeb_template_listing"><?php esc_html_e( 'Room Details', 'vbeb' ); ?></label></th>
					<td>
						<select name="vbeb_template_listing" id="vbeb_template_listing">
							<option value="0"><?php esc_html_e( '-- Use the original VikBooking template --', 'vbeb' ); ?></option>
							<?php foreach ( $templates as $tpl ) : ?>
								<option value="<?php echo esc_attr( $tpl->ID ); ?>" <?php selected( $current_listing, $tpl->ID ); ?>>
									<?php echo esc_html( $tpl->post_title ); ?> (ID <?php echo (int) $tpl->ID; ?>)
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
