<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Copia automaticamente i file override dalla cartella
 * "override-templates/" del plugin dentro le rispettive cartelle
 * wp-content/uploads/vikbooking/overrides/site/{vista}/, cosi' il
 * plugin funziona su un nuovo dominio senza dover ripetere il passaggio
 * manuale via FTP.
 *
 * Sicurezza: se in quella cartella esiste gia' un file con lo stesso nome
 * che NON e' una nostra versione precedente (cioe' non contiene la nostra
 * firma nel commento), non lo sovrascriviamo mai - potrebbe essere il
 * file originale di VikBooking non ancora sostituito, o una
 * personalizzazione manuale. In quel caso salviamo la nostra versione
 * accanto con un suffisso, cosi' l'utente puo' confrontarla e sostituirla
 * a mano se lo desidera, e mostriamo un avviso in bacheca.
 */

define( 'VBEB_OVERRIDE_MARKER', 'MODIFICATO da VikBooking Elementor Bridge' );

/**
 * Elenco dei file override gestiti da questo plugin: chiave = nome del
 * file cosi' come si trova dentro "override-templates/" (il nostro
 * sorgente), valore = dove va copiato realmente.
 * - "view": il nome della vista di VikBooking (site/{view}/...) - stesso
 *   nome usato internamente da VikBooking per costruire il percorso di
 *   ricerca dell'override (verificato nel suo codice sorgente,
 *   libraries/adapter/mvc/view.php).
 * - "dest_filename": il nome file che VikBooking si aspetta di trovare
 *   in quella cartella - non sempre uguale al nostro nome sorgente (es.
 *   "default_roomslist.php" diventerebbe "default.php" per VikBooking).
 * Nella versione free c'e' solo l'override del Dettaglio Camera.
 */
function vbeb_get_override_files_map() {
	return array(
		'default_listing.php' => array(
			'view'          => 'roomdetails',
			'dest_filename' => 'default_listing.php',
		),
	);
}

function vbeb_get_overrides_target_dir( $view ) {
	return trailingslashit( WP_CONTENT_DIR ) . 'uploads/vikbooking/overrides/site/' . $view . '/';
}

function vbeb_install_override_files() {
	$source_dir = VBEB_PATH . 'override-templates/';
	$files_map  = vbeb_get_override_files_map();
	$skipped    = array();
	$failed     = array();
	$dir_error  = false;

	foreach ( $files_map as $source_filename => $dest_info ) {
		$source     = $source_dir . $source_filename;
		$target_dir = vbeb_get_overrides_target_dir( $dest_info['view'] );
		$dest       = $target_dir . $dest_info['dest_filename'];

		if ( ! file_exists( $source ) ) {
			continue; // File sorgente del plugin mancante: non dovrebbe succedere.
		}

		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		if ( ! is_dir( $target_dir ) || ! wp_is_writable( $target_dir ) ) {
			$dir_error = true;
			$failed[]  = $dest_info['dest_filename'] . ' (' . $dest_info['view'] . ')';
			continue;
		}

		if ( file_exists( $dest ) ) {
			$dest_content = file_get_contents( $dest );
			if ( false === $dest_content || strpos( $dest_content, VBEB_OVERRIDE_MARKER ) === false ) {
				// File esistente non nostro: non lo tocchiamo. Salviamo la
				// nostra versione accanto per un confronto/sostituzione manuale.
				$alt_dest = $target_dir . str_replace( '.php', '-vbeb-suggested.php', $dest_info['dest_filename'] );
				if ( ! @copy( $source, $alt_dest ) ) {
					$failed[] = $dest_info['dest_filename'] . ' (' . $dest_info['view'] . ')';
				} else {
					$skipped[] = $dest_info['dest_filename'] . ' (' . $dest_info['view'] . ')';
				}
				continue;
			}
		}

		if ( ! @copy( $source, $dest ) ) {
			$failed[] = $dest_info['dest_filename'] . ' (' . $dest_info['view'] . ')';
		}
	}

	update_option( 'vbeb_override_install_skipped', $skipped );
	update_option( 'vbeb_override_install_failed', $failed );
	if ( $dir_error ) {
		update_option( 'vbeb_override_install_error', 'directory' );
	} else {
		delete_option( 'vbeb_override_install_error' );
	}

	if ( empty( $failed ) ) {
		// Segniamo la versione come "installata" SOLO se nessuna copia e'
		// fallita davvero (uno "skip" per file non-nostro e' comunque un
		// esito gestito correttamente, non un fallimento). In caso di
		// fallimento reale, la versione NON viene aggiornata: al prossimo
		// admin_init il tentativo verra' ripetuto automaticamente, invece
		// di restare bloccato per sempre pensando di aver gia' finito.
		update_option( 'vbeb_override_installed_version', VBEB_VERSION );
	}

	return array(
		'skipped' => $skipped,
		'failed'  => $failed,
	);
}
// Attivazione: gira DOPO il controllo dipendenze (registrato prima nel
// file principale), perche' se le dipendenze mancano quel controllo
// interrompe l'attivazione con wp_die() prima che questa funzione parta.
register_activation_hook( VBEB_MAIN_FILE, 'vbeb_install_override_files' );

/**
 * Ripete automaticamente la copia quando il plugin viene aggiornato a una
 * versione diversa (l'hook di attivazione da solo non basta: non si
 * riattiva sui semplici aggiornamenti del plugin via FTP/zip).
 */
function vbeb_maybe_reinstall_override_files() {
	if ( ! is_admin() ) {
		return;
	}
	$installed_version = get_option( 'vbeb_override_installed_version', '' );
	if ( $installed_version !== VBEB_VERSION ) {
		vbeb_install_override_files();
		return;
	}

	// VBEB: controllo aggiuntivo, piu' robusto - un aggiornamento di
	// VikBooking stesso (non del nostro plugin) puo' sovrascrivere
	// silenziosamente il file originale che modifichiamo, senza che la
	// versione del NOSTRO plugin cambi affatto (caso reale riscontrato e
	// segnalato dall'utente, passando da VikBooking Free a Pro).
	// Ripetiamo quindi la copia anche ogni volta che rileviamo un
	// cambio di versione di VikBooking stesso.
	$vikbooking_version = vbeb_get_vikbooking_version();
	if ( '' === $vikbooking_version ) {
		return; // VikBooking non attivo o versione non rilevabile, non c'e' nulla da verificare.
	}
	$installed_vikbooking_version = get_option( 'vbeb_vikbooking_installed_version', '' );
	if ( $installed_vikbooking_version !== $vikbooking_version ) {
		vbeb_install_override_files();
		update_option( 'vbeb_vikbooking_installed_version', $vikbooking_version );
	}
}
add_action( 'admin_init', 'vbeb_maybe_reinstall_override_files' );

/**
 * Ritorna la versione REALE di VikBooking attualmente installata, letta
 * dall'intestazione del suo vero file principale (VikBooking non
 * definisce alcuna costante di versione utilizzabile, verificato nel suo
 * codice sorgente - "VIKBOOKING_VERSION" non esiste da nessuna parte,
 * nonostante il controllo speculativo presente nel file principale di
 * questo stesso plugin). Stringa vuota se non rilevabile.
 */
function vbeb_get_vikbooking_version() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Percorso standard atteso (il nome cartella "vikbooking" e' quello
	// stesso usato ovunque in questo plugin, es. VBO_SITE_URI/VBO_MODULES_URI).
	$expected_path = WP_PLUGIN_DIR . '/vikbooking/vikbooking.php';
	if ( is_file( $expected_path ) ) {
		$data = get_plugin_data( $expected_path, false, false );
		if ( ! empty( $data['Version'] ) ) {
			return $data['Version'];
		}
	}

	// Fallback piu' robusto (ma piu' lento) se il percorso standard non
	// dovesse esistere per qualche motivo: cerchiamo tra tutti i plugin
	// installati quello il cui nome dichiarato contiene "VikBooking".
	$all_plugins = get_plugins();
	foreach ( $all_plugins as $plugin_file => $plugin_data ) {
		if ( false !== stripos( $plugin_data['Name'], 'VikBooking' ) && ! empty( $plugin_data['Version'] ) ) {
			return $plugin_data['Version'];
		}
	}

	return '';
}

/**
 * Avviso in bacheca se il file non e' stato sovrascritto automaticamente
 * perche' gia' presente con contenuto non nostro.
 */
function vbeb_admin_notice_override_status() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$error = get_option( 'vbeb_override_install_error', '' );
	if ( 'directory' === $error ) {
		?>
		<div class="notice notice-error">
			<p><strong>VBEB</strong>: <?php
			printf(
				/* translators: 1: cartella di destinazione, 2: cartella del plugin. */
				esc_html__( 'unable to create or write the override folder %1$s. Copy the file manually from %2$s via FTP, or fix the write permissions.', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				'<code>wp-content/uploads/vikbooking/overrides/site/roomdetails/</code>',
				'<code>wp-content/plugins/' . esc_html( basename( VBEB_PATH ) ) . '/override-templates/</code>'
			);
			?></p>
		</div>
		<?php
		return;
	}

	$skipped = get_option( 'vbeb_override_install_skipped', array() );
	if ( ! empty( $skipped ) ) {
		?>
		<div class="notice notice-warning is-dismissible">
			<p><strong>VBEB</strong>: <?php
			printf(
				/* translators: %s: elenco dei file. */
				esc_html__( 'the files %s already exist with content different from ours (probably not replaced yet, or customized by hand): they were not overwritten for safety. A copy with the suffix "-vbeb-suggested.php" was saved next to them: compare it and, if it is fine, rename or replace it manually via FTP.', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				esc_html( implode( ', ', $skipped ) )
			);
			?></p>
		</div>
		<?php
	}

	$failed = get_option( 'vbeb_override_install_failed', array() );
	if ( ! empty( $failed ) ) {
		?>
		<div class="notice notice-error">
			<p><strong>VBEB</strong>: <?php
			printf(
				/* translators: 1: elenco dei file, 2: cartella del plugin. */
				esc_html__( 'unable to write the files %1$s to their override folder (insufficient permissions?). Copy them manually via FTP from %2$s.', 'vbeb-bridge-for-vikbooking-and-elementor' ),
				esc_html( implode( ', ', $failed ) ),
				'<code>wp-content/plugins/' . esc_html( basename( VBEB_PATH ) ) . '/override-templates/</code>'
			);
			?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'vbeb_admin_notice_override_status' );
