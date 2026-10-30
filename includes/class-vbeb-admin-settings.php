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
 *
 * VBEB free, su richiesta dell'utente: solo i template di tipo "Pagina"
 * ("_elementor_template_type" = "page") e, con Polylang o WPML, solo quelli
 * nella lingua predefinita del sito.
 */
function vbeb_get_elementor_templates() {
	if ( ! post_type_exists( 'elementor_library' ) ) {
		return array();
	}

	$args = array(
		'post_type'      => 'elementor_library',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'meta_key'       => '_elementor_template_type', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- elenco dei template, letto solo in questa pagina.
		'meta_value'     => 'page', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- come sopra.
	);

	// VBEB free, segnalato dall'utente: la scelta e' una sola per tutte le
	// lingue (sulle pagine nelle altre lingue VBEB usa da solo la copia
	// tradotta, vbeb_template_lingua()). Con Polylang o WPML si elencano
	// quindi solo i template nella lingua predefinita del sito, qualunque
	// lingua sia scelta nella barra in alto: prima, con "English" scelto,
	// Polylang nascondeva il template italiano gia' salvato e il menu
	// mostrava "Usa il template originale di VikBooking".
	$predefinita = '';
	if ( function_exists( 'pll_default_language' ) ) {
		$predefinita = (string) pll_default_language( 'slug' );
		if ( '' !== $predefinita ) {
			$args['lang'] = $predefinita;
		}
	}

	$templates = get_posts( $args );

	if ( ! function_exists( 'pll_default_language' ) && has_filter( 'wpml_default_language' ) ) {
		$predefinita = (string) apply_filters( 'wpml_default_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro di WPML.
		if ( '' !== $predefinita ) {
			$templates = array_values(
				array_filter(
					$templates,
					function ( $tpl ) use ( $predefinita ) {
						$dettagli = apply_filters( 'wpml_post_language_details', null, $tpl->ID ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro di WPML.
						return ! is_array( $dettagli ) || empty( $dettagli['language_code'] ) || $dettagli['language_code'] === $predefinita;
					}
				)
			);
		}
	}

	return $templates;
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


		<form method="post" action="options.php">
			<?php settings_fields( 'vbeb_template_settings_group' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="vbeb_template_listing"><?php esc_html_e( 'Room Details', 'vbeb' ); ?></label></th>
					<td>
						<select name="vbeb_template_listing" id="vbeb_template_listing">
							<option value="0"><?php esc_html_e( '-- Use the original VikBooking template --', 'vbeb' ); ?></option>
							<?php
							foreach ( $templates as $tpl ) :
								// VBEB free, segnalato dall'utente: il valore resta il
								// template nella lingua predefinita (la scelta vale per
								// tutte le lingue); nome e ID mostrati sono quelli della
								// copia nella lingua scelta nella barra in alto, se c'e'.
								$mostrato = function_exists( 'vbeb_template_lingua' ) ? get_post( vbeb_template_lingua( $tpl->ID ) ) : null;
								if ( ! $mostrato ) {
									$mostrato = $tpl;
								}
								?>
								<option value="<?php echo esc_attr( $tpl->ID ); ?>" <?php selected( $current_listing, $tpl->ID ); ?>>
									<?php echo esc_html( $mostrato->post_title ); ?> (ID <?php echo (int) $mostrato->ID; ?>)
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
