<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Camera di esempio nell'editor di Elementor.
 *
 * VBEB 7.30.0. Fino a qui, dentro l'editor i widget camera restavano
 * vuoti: il contesto lo riempie l'override di VikBooking, e l'override
 * gira solo quando qualcuno visita davvero quella vista. Nell'editor
 * l'anteprima e' una pagina di WordPress - nessun "roomid"
 * nell'indirizzo, nessuna ricerca in corso - quindi VikBooking non
 * viene interpellato e il contesto resta vuoto.
 *
 * E' lo stesso comportamento del widget "Titolo dell'articolo" di
 * Elementor, che senza un articolo corrente ne mostra comunque uno
 * vero.
 *
 * QUALE CAMERA. L'ultima creata, che in pratica vuol dire quella con
 * l'identificativo piu' alto. La tabella delle camere non ha nessuna
 * colonna di data: non c'e' un "created", non c'e' un "modified", non
 * c'e' niente che registri quando una camera sia stata inserita o
 * toccata. L'unico ordine disponibile e' quello dell'identificativo,
 * che cresce a ogni inserimento.
 *
 * Funziona bene ma non e' infallibile, e vale la pena saperlo: se una
 * camera viene cancellata e ricreata, il suo identificativo e' nuovo e
 * quindi risulta "l'ultima"; e modificare una camera vecchia non la fa
 * risalire, perche' l'identificativo non cambia. Per un'anteprima va
 * piu' che bene.
 *
 * SOLO NELL'EDITOR. Sul front-end questo file non fa assolutamente
 * nulla: se il contesto e' vuoto li', e' perche' deve esserlo, e un
 * ripiego mostrerebbe la camera sbagliata a un ospite.
 *
 * E SOLO SE IL CONTESTO E' VUOTO: quando l'override lo ha gia' riempito
 * non si tocca niente.
 */

/**
 * Riempie il contesto camera con l'ultima camera creata.
 *
 * @return bool Vero se il contesto e' stato riempito.
 */
function vbeb_carica_camera_anteprima() {
	global $wpdb;

	if ( ! isset( $wpdb ) || ! class_exists( 'VikBooking' ) || ! class_exists( 'VBEB_Room_Context' ) ) {
		return false;
	}

	// Una volta sola per richiesta: la stessa camera serve a tutti i
	// widget del template, e una query per widget sarebbe uno spreco.
	static $tentato = false;

	if ( $tentato ) {
		return ! empty( VBEB_Room_Context::$room );
	}
	$tentato = true;

	$tabella = apply_filters( 'vik_get_db_prefix', $wpdb->prefix ) . 'vikbooking_rooms'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro di VikBooking.
	$esiste  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tabella ) ); // phpcs:ignore WordPress.DB -- nome tabella di VikBooking.

	if ( ! $esiste ) {
		return false;
	}

	// Solo fra le camere attive: una camera disattivata non comparirebbe
	// mai sul front-end, e usarla come esempio sarebbe fuorviante.
	$camera = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM %i WHERE `avail`='1' ORDER BY `id` DESC LIMIT 1", $tabella ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- tabella di VikBooking, solo nell'editor.

	if ( empty( $camera ) ) {
		return false;
	}

	$vbo_tn = VikBooking::getTranslator();

	if ( $vbo_tn ) {
		$elenco = array( $camera );
		$vbo_tn->translateContents( $elenco, '#__vikbooking_rooms' );
		$camera = $elenco[0];
	}

	// Prezzo di partenza, con lo stesso calcolo della sorgente
	// "catalogo": prezzo personalizzato se c'e', altrimenti la tariffa
	// piu' bassa per una notte con le stagioni di oggi. Senza, il widget
	// Prezzo resterebbe l'unico vuoto.
	$camera['cost'] = 0;
	$custprice      = VikBooking::getRoomParam( 'custprice', $camera['params'] );

	if ( strlen( (string) $custprice ) > 0 && (float) $custprice > 0 ) {
		$camera['cost'] = (float) $custprice;
	} else {
		$costi   = apply_filters( 'vik_get_db_prefix', $wpdb->prefix ) . 'vikbooking_dispcost'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro di VikBooking.
		$tariffe = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE `idroom` = %d ORDER BY `days` ASC, `cost` ASC LIMIT 1', $costi, (int) $camera['id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- tabella di VikBooking, solo nell'editor.

		if ( ! empty( $tariffe ) ) {
			$oggi    = mktime( 0, 0, 0, (int) date( 'm' ), (int) date( 'd' ), (int) date( 'Y' ) ); // phpcs:ignore WordPress.DateTime -- stesso calcolo della sorgente catalogo.
			$tariffe = VikBooking::applySeasonsRoom( $tariffe, $oggi, $oggi + 86400 );
			$giorni  = ( isset( $tariffe[0]['days'] ) && $tariffe[0]['days'] > 1 ) ? $tariffe[0]['days'] : 1;

			$camera['cost'] = $tariffe[0]['cost'] / $giorni;
		}
	}

	VBEB_Room_Context::$room           = $camera;
	VBEB_Room_Context::$vbo_tn         = $vbo_tn;
	VBEB_Room_Context::$search_idroom  = (int) $camera['id'];
	VBEB_Room_Context::$search_indroom = null;

	// Restano vuoti: sono HTML che il Dettaglio Camera genera durante
	// una richiesta vera, e qui non esiste.
	VBEB_Room_Context::$avcalendars_html = null;
	VBEB_Room_Context::$bookingform_html = null;
	VBEB_Room_Context::$reqinfo_html     = null;

	return true;
}


/**
 * Aggancia nell'editor le traduzioni del front-end di VikBooking.
 *
 * VBEB 7.33.0. Segnalato dall'utente: nell'anteprima comparivano
 * "VBOPKGMOREDETAILS" e "VBOPKGVALIDATES" al posto dei testi. Non sono
 * classi CSS ne' segnaposto: sono CHIAVI DI LINGUA, e quando una chiave
 * non e' definita il traduttore di VikBooking restituisce la chiave
 * stessa.
 *
 * PERCHE' NON ERANO DEFINITE. VikBooking sceglie quali file di lingua
 * agganciare in base al contesto
 * (libraries/system/builder.php::setupLanguage): sempre "system.php", e
 * poi "adminsys.php" piu' "admin.php" se siamo
 * nell'amministrazione, oppure "site.php" se siamo sul front-end. Non
 * entrambi.
 *
 * L'editor di Elementor gira dentro wp-admin. Per VikBooking siamo
 * quindi nell'amministrazione, e "site.php" - dove stanno le etichette
 * delle pagine pubbliche - non viene mai agganciato. Il testo del
 * pulsante, sul front-end, esisteva eccome: mancava solo li'.
 *
 * SI AGGANCIA IN CODA, non in testa, e la differenza conta:
 * findTranslation() interroga i gestori in ordine e si ferma al primo
 * che risponde. Mettendolo per ultimo, le definizioni
 * dell'amministrazione mantengono la precedenza su eventuali chiavi
 * presenti in entrambi i file, e si aggiungono soltanto quelle che
 * mancavano. Nessuna etichetta dell'amministrazione cambia.
 *
 * L'operazione e' ripetibile senza danno - attachHandler tiene i
 * gestori per firma e ignora i doppioni - ma la si fa comunque una
 * volta sola.
 *
 * @return void
 */
function vbeb_lingua_sito_in_editor() {
	static $fatto = false;

	if ( $fatto ) {
		return;
	}
	$fatto = true;

	if ( ! class_exists( 'JFactory' ) || ! defined( 'VIKBOOKING_LIBRARIES' ) || ! defined( 'VIKBOOKING_LANG' ) ) {
		return;
	}

	$file = VIKBOOKING_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'site.php';

	if ( ! file_exists( $file ) ) {
		return;
	}

	$lang = JFactory::getLanguage();

	if ( ! is_object( $lang ) || ! method_exists( $lang, 'attachHandler' ) ) {
		return;
	}

	$lang->attachHandler( $file, 'vikbooking' );
	$lang->load( 'vikbooking', VIKBOOKING_LANG );
}


/**
 * L'HTML vero del calendario disponibilita' o del modulo di prenotazione
 * della camera di anteprima, per l'editor.
 *
 * VBEB 7.246.0, su richiesta dell'utente. Sul front-end i widget "VBEB
 * Camera - Calendario" e "VBEB Camera - Modulo prenotazione" ristampano
 * l'HTML che l'override della scheda camera cattura con
 * "$this->loadTemplate('avcalendars')" e "loadTemplate('bookingform')"
 * (override-templates/default_listing.php). Nell'editor quella vista non
 * gira e l'HTML mancava.
 *
 * NIENTE DI INVENTATO: si crea la stessa vista di VikBooking
 * (VikbookingViewRoomdetails, site/views/roomdetails/view.html.php), le si
 * danno gli stessi dati che le da' il suo display() - la camera attiva
 * con il suo prezzo di oggi, le occupazioni dal primo del mese in avanti,
 * nessuna promozione - e si chiede lo stesso sotto-template. Il risultato
 * e' quello che VikBooking stampa sulla scheda di quella camera. Le parti
 * del display() che servono solo alla pagina (titolo, metadati, calendario
 * delle stagioni, campi dei termini) non servono ai due sotto-template e
 * restano fuori.
 *
 * LA CAMERA e' quella dell'anteprima (vbeb_carica_camera_anteprima()).
 *
 * I CAMPI DATA restano senza calendario a comparsa: lo avvia lo script
 * della pagina camera (default.php), che nell'editor non c'e'.
 *
 * VBEB 7.253.0: anche "reqinfo", per "VBEB Camera - Modulo informazioni",
 * con i campi dei termini letti come nel display(). VikBooking stampa il
 * modulo solo per le camere con la richiesta informazioni attiva: per
 * l'anteprima l'opzione della camera viene accesa sulla sola copia data
 * alla vista, cosi' il modulo si vede qualunque sia la camera (come il
 * pulsante nella 7.90.0). Il resto e' tutto di VikBooking.
 *
 * @param string $parte "avcalendars", "bookingform" o "reqinfo".
 * @return string Vuoto se non si riesce.
 */
function vbeb_html_camera_anteprima( $parte ) {
	static $cache = array();

	if ( ! in_array( $parte, array( 'avcalendars', 'bookingform', 'reqinfo' ), true ) ) {
		return '';
	}

	if ( ! class_exists( 'VBEB_Room_Context' ) || ! class_exists( 'VikBooking' ) || ! class_exists( 'JFactory' ) || ! defined( 'VBO_SITE_PATH' ) ) {
		return '';
	}

	$room_id = (int) VBEB_Room_Context::current_room_id();

	if ( $room_id < 1 ) {
		return '';
	}

	$chiave = $parte . '-' . $room_id;

	if ( isset( $cache[ $chiave ] ) ) {
		return $cache[ $chiave ];
	}
	$cache[ $chiave ] = '';

	try {
		$base = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'roomdetails';

		if ( ! class_exists( 'VikbookingViewRoomdetails' ) ) {
			$file = $base . DIRECTORY_SEPARATOR . 'view.html.php';

			if ( ! is_file( $file ) || ! function_exists( 'jimport' ) || ! class_exists( 'JViewVikBooking' ) ) {
				return '';
			}

			require_once $file;
		}

		$dbo    = JFactory::getDbo();
		$vbo_tn = VikBooking::getTranslator();

		// La camera, come la legge il display() della vista.
		$dbo->setQuery( 'SELECT * FROM `#__vikbooking_rooms` WHERE `id`=' . $room_id . " AND `avail`='1';" );
		$room = (array) $dbo->loadAssocList();

		if ( ! $room ) {
			return '';
		}

		if ( $vbo_tn ) {
			$vbo_tn->translateContents( $room, '#__vikbooking_rooms' );
		}

		// Il prezzo di oggi per una notte, con lo stesso calcolo.
		$vbo_df    = VikBooking::getDateFormat();
		$vbo_df    = '%d/%m/%Y' === $vbo_df ? 'd/m/Y' : ( '%m/%d/%Y' === $vbo_df ? 'm/d/Y' : 'Y/m/d' );
		$hcheckin  = 0;
		$mcheckin  = 0;
		$hcheckout = 0;
		$mcheckout = 0;
		$timeopst  = VikBooking::getTimeOpenStore();

		if ( is_array( $timeopst ) ) {
			$opent     = VikBooking::getHoursMinutes( $timeopst[0] );
			$closet    = VikBooking::getHoursMinutes( $timeopst[1] );
			$hcheckin  = $opent[0];
			$mcheckin  = $opent[1];
			$hcheckout = $closet[0];
			$mcheckout = $closet[1];
		}

		$checkin_today_ts     = VikBooking::getDateTimestamp( date( $vbo_df ), $hcheckin, $mcheckin ); // phpcs:ignore WordPress.DateTime -- stesso calcolo di VikBooking.
		$checkout_tomorrow_ts = VikBooking::getDateTimestamp( date( $vbo_df, strtotime( 'tomorrow' ) ), $hcheckout, $mcheckout ); // phpcs:ignore WordPress.DateTime -- stesso calcolo di VikBooking.

		$room[0]['base_cost'] = 0;
		$room[0]['cost']      = 0;

		$dbo->setQuery( 'SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`=' . (int) $room[0]['id'] . ' ORDER BY `#__vikbooking_dispcost`.`days` ASC, `#__vikbooking_dispcost`.`cost` ASC', 0, 1 );
		$tar = (array) $dbo->loadAssocList();

		if ( $tar ) {
			$room[0]['base_cost'] = $tar[0]['cost'] / ( $tar[0]['days'] > 1 ? $tar[0]['days'] : 1 );
			$tar                  = VikBooking::applySeasonsRoom( $tar, $checkin_today_ts, $checkout_tomorrow_ts );
			$room[0]['cost']      = $tar[0]['cost'] / ( $tar[0]['days'] > 1 ? $tar[0]['days'] : 1 );
		}

		// Le occupazioni, dal primo del mese in avanti.
		$actnow = mktime( 0, 0, 0, (int) date( 'm' ), 1, (int) date( 'Y' ) ); // phpcs:ignore WordPress.DateTime -- stesso calcolo di VikBooking.
		$dbo->setQuery( 'SELECT * FROM `#__vikbooking_busy` WHERE `idroom`=' . (int) $room[0]['id'] . ' AND (`checkin`>=' . $actnow . ' OR `checkout`>=' . $actnow . ');' );
		$busy = (array) $dbo->loadAssocList();

		// I campi dei termini, come nel display() (i primi due campi
		// casella obbligatori), per "reqinfo".
		$terms_fields = array();

		if ( 'reqinfo' === $parte ) {
			$dbo->setQuery( "SELECT * FROM `#__vikbooking_custfields` WHERE `type`='checkbox' AND `required`=1 ORDER BY `#__vikbooking_custfields`.`ordering` ASC", 0, 2 );
			$terms_fields = (array) $dbo->loadAssocList();

			if ( $terms_fields && $vbo_tn ) {
				$vbo_tn->translateContents( $terms_fields, '#__vikbooking_custfields' );
			}

			$parametri            = json_decode( (string) $room[0]['params'], true );
			$parametri            = is_array( $parametri ) ? $parametri : array();
			$parametri['reqinfo'] = 1;
			$room[0]['params']    = wp_json_encode( $parametri );
		}

		// Nessun calendario delle stagioni: il widget "Prezzi stagionali"
		// non fa parte della versione free.
		$seasons_cal = array();

		$view               = new VikbookingViewRoomdetails( $base );
		$view->room         = $room[0];
		$view->busy         = $busy;
		$view->seasons_cal  = $seasons_cal;
		$view->promo_season = array();
		$view->terms_fields = $terms_fields;
		$view->vbo_tn       = $vbo_tn;

		// VBEB free: le scritte fisse di VikBooking nella lingua del template
		// aperto (vedi vbeb_in_lingua_template()).
		$cache[ $chiave ] = (string) vbeb_in_lingua_template(
			function () use ( $view, $parte ) {
				return $view->loadTemplate( $parte );
			}
		);
	} catch ( \Throwable $e ) {
		$cache[ $chiave ] = '';
	}

	return $cache[ $chiave ];
}

/**
 * La lingua del template aperto nell'editor, per i dati di VikBooking.
 *
 * VBEB free, segnalato dall'utente: con Polylang, nell'editor del template
 * inglese la camera di anteprima compariva in italiano. VikBooking traduce
 * nella lingua dell'utente (get_user_locale(), libraries/adapter/language/
 * language.php), che nell'amministrazione e' quella del profilo, non quella
 * del template. Qui si legge la lingua del template da Polylang
 * (pll_get_post_language()) o da WPML ("wpml_post_language_details") e la si
 * passa al traduttore di VikBooking con VikBookingTranslator::$force_tolang,
 * lo stesso modo che usa VikBooking per le e-mail nella lingua della
 * prenotazione. Solo nell'editor.
 *
 * @return string Etichetta di VikBooking (per esempio "en-US"), vuota se non
 *                c'e' un plugin multilingua o la lingua non e' nota.
 */
function vbeb_lingua_template_editor() {
	static $lingua = null;

	if ( null !== $lingua && '' !== $lingua ) {
		return $lingua;
	}
	$lingua = '';

	// VBEB free: nelle richieste ajax dell'editor (per esempio quella che
	// prepara i controlli dei widget) Elementor indica il template aperto con
	// editor_post_id; qui editor->get_post_id() e' ancora vuoto.
	$id = vbeb_id_editor_ajax();
	if ( ! $id && class_exists( '\\Elementor\\Plugin' ) ) {
		$el = \Elementor\Plugin::$instance;
		if ( isset( $el->editor ) && method_exists( $el->editor, 'get_post_id' ) ) {
			$id = (int) $el->editor->get_post_id();
		}
		if ( ! $id && isset( $el->preview ) && method_exists( $el->preview, 'get_post_id' ) ) {
			$id = (int) $el->preview->get_post_id();
		}
	}
	if ( ! $id ) {
		$id = (int) get_the_ID();
	}
	if ( ! $id ) {
		$lingua = null;
		return '';
	}

	$locale = '';
	if ( function_exists( 'pll_get_post_language' ) ) {
		$locale = (string) pll_get_post_language( $id, 'locale' );
	} elseif ( has_filter( 'wpml_post_language_details' ) ) {
		$dettagli = apply_filters( 'wpml_post_language_details', null, $id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro di WPML.
		if ( is_array( $dettagli ) && ! empty( $dettagli['locale'] ) ) {
			$locale = (string) $dettagli['locale'];
		}
	}

	$lingua = str_replace( '_', '-', $locale );
	$valore = $lingua;
	if ( '' === $lingua ) {
		$lingua = null;
	}

	return $valore;
}

/**
 * L'ID del template aperto nell'editor, nelle richieste ajax di Elementor.
 *
 * VBEB free. Solo lettura: la richiesta e' gia' verificata da Elementor.
 *
 * @return int 0 se non e' una richiesta ajax dell'editor.
 */
function vbeb_id_editor_ajax() {
	if ( ! wp_doing_ajax() || empty( $_REQUEST['editor_post_id'] ) || ! current_user_can( 'edit_posts' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- sola lettura, nonce verificato da Elementor.
		return 0;
	}

	$id = absint( wp_unslash( $_REQUEST['editor_post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- sola lettura, nonce verificato da Elementor.

	return current_user_can( 'edit_post', $id ) ? $id : 0;
}

/**
 * Esegue una funzione con le traduzioni di VikBooking nella lingua del
 * template aperto nell'editor.
 *
 * VBEB free, segnalato dall'utente: con Polylang o WPML le scritte fisse di
 * VikBooking (mesi, giorni, pulsanti, etichette dei moduli) devono comparire
 * nella lingua del template aperto, non in quella della richiesta (le
 * richieste dell'editor non sono tutte nella stessa lingua).
 *
 * Si cambiano SOLO le traduzioni dei domini "vikbooking" e "default" (i
 * nomi di mesi e giorni che VikBooking prende da WordPress), per la durata
 * della funzione, e poi si rimettono quelle di prima, identiche. Fino a qui
 * si cambiava la lingua di tutto WordPress (switch_to_locale()), e a volte
 * nomi e impostazioni dei widget nel pannello restavano nella lingua del
 * template invece che in quella della bacheca.
 *
 * File cercati, come fa VikBooking: wp-content/languages/loco/plugins,
 * wp-content/languages/plugins, poi la cartella "languages" di VikBooking
 * (.mo, oppure .l10n.php). Senza file per quella lingua: i testi originali
 * (inglese). Fuori dall'editor, o senza plugin multilingua, la funzione gira
 * e basta.
 *
 * @param callable $funzione Funzione da eseguire.
 * @return mixed Il suo risultato.
 */
function vbeb_in_lingua_template( callable $funzione ) {
	global $l10n;

	$locale = ( function_exists( 'vbeb_editor_ripete' ) && vbeb_editor_ripete() )
		? str_replace( '-', '_', vbeb_lingua_template_editor() )
		: '';

	if ( '' === $locale || ! class_exists( 'NOOP_Translations' ) ) {
		return $funzione();
	}

	if ( ! is_array( $l10n ) ) {
		$l10n = array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- elenco delle traduzioni caricate, vuoto.
	}

	// "default": i nomi di mesi e giorni, che VikBooking prende da WordPress
	// (libraries/language/system.php: "JANUARY" => __( 'January' )).
	$prima = array();
	foreach ( array( 'vikbooking', 'default' ) as $dominio ) {
		$prima[ $dominio ] = array_key_exists( $dominio, $l10n ) ? array( $l10n[ $dominio ] ) : null;
		$l10n[ $dominio ]  = vbeb_traduzioni_dominio( $dominio, $locale ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- solo per la durata della funzione, rimesse subito dopo.
	}

	try {
		return $funzione();
	} finally {
		foreach ( $prima as $dominio => $valore ) {
			if ( null === $valore ) {
				unset( $l10n[ $dominio ] );
			} else {
				$l10n[ $dominio ] = $valore[0]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- rimette le traduzioni di prima.
			}
		}
	}
}

/**
 * Un testo di VikBooking (JText) nella lingua del template aperto
 * nell'editor; fuori dall'editor come lo traduce VikBooking.
 *
 * VBEB free, segnalato dall'utente: nel template inglese "Richiedi
 * informazioni" e "Dove sarai" comparivano in italiano nell'editor.
 *
 * @param string $chiave Chiave di VikBooking.
 * @return string
 */
function vbeb_vbo_testo( $chiave ) {
	if ( ! class_exists( 'JText' ) ) {
		return (string) $chiave;
	}

	return (string) vbeb_in_lingua_template(
		function () use ( $chiave ) {
			return JText::translate( $chiave );
		}
	);
}

/**
 * Le traduzioni di un dominio per una lingua, lette dai file.
 *
 * - "vikbooking": wp-content/languages/loco/plugins, wp-content/languages/
 *   plugins, poi la cartella "languages" di VikBooking;
 * - "default" (WordPress): wp-content/languages.
 *
 * @param string $dominio "vikbooking" o "default".
 * @param string $locale  Lingua, per esempio "en_US".
 * @return Translations|NOOP_Translations
 */
function vbeb_traduzioni_dominio( $dominio, $locale ) {
	static $cache = array();

	$chiave = $dominio . '|' . $locale;
	if ( isset( $cache[ $chiave ] ) ) {
		return $cache[ $chiave ];
	}

	if ( 'default' === $dominio ) {
		$cartelle = array( trailingslashit( WP_LANG_DIR ) );
		$prefisso = '';
	} else {
		$cartelle = array(
			trailingslashit( WP_LANG_DIR ) . 'loco/plugins/',
			trailingslashit( WP_LANG_DIR ) . 'plugins/',
		);
		if ( defined( 'VIKBOOKING_LANG' ) ) {
			$cartelle[] = trailingslashit( WP_PLUGIN_DIR ) . trailingslashit( VIKBOOKING_LANG );
		}
		$prefisso = $dominio . '-';
	}

	$cache[ $chiave ] = new NOOP_Translations();

	foreach ( $cartelle as $cartella ) {
		$mo = $cartella . $prefisso . $locale . '.mo';
		if ( is_readable( $mo ) && class_exists( 'MO' ) ) {
			$file = new MO();
			if ( $file->import_from_file( $mo ) ) {
				$cache[ $chiave ] = $file;
				break;
			}
		}

		$php = $cartella . $prefisso . $locale . '.l10n.php';
		if ( is_readable( $php ) && class_exists( 'Translations' ) ) {
			$dati = include $php;
			if ( is_array( $dati ) && ! empty( $dati['messages'] ) && is_array( $dati['messages'] ) ) {
				$file = new Translations();
				foreach ( $dati['messages'] as $chiave => $valore ) {
					$contesto = null;
					if ( false !== strpos( (string) $chiave, "\4" ) ) {
						list( $contesto, $chiave ) = explode( "\4", (string) $chiave, 2 );
					}
					$parti = explode( "\0", (string) $chiave );
					$voce  = array(
						'singular'     => $parti[0],
						'translations' => explode( "\0", (string) $valore ),
					);
					if ( isset( $parti[1] ) ) {
						$voce['plural'] = $parti[1];
					}
					if ( null !== $contesto ) {
						$voce['context'] = $contesto;
					}
					$file->add_entry( new Translation_Entry( $voce ) );
				}
				$cache[ $chiave ] = $file;
				break;
			}
		}
	}

	return $cache[ $chiave ];
}

/**
 * La lingua di un contenuto secondo Polylang o WPML.
 *
 * VBEB free: per mostrare nei menu dei widget solo i template nella lingua
 * del template aperto nell'editor (vbeb_popup_elenco()).
 *
 * @param int $id Contenuto.
 * @return string Codice della lingua (per esempio "en_US"), vuoto se non c'e'
 *                un plugin multilingua o il contenuto non ha una lingua.
 */
function vbeb_lingua_contenuto( $id ) {
	$id = (int) $id;
	if ( $id < 1 ) {
		return '';
	}
	if ( function_exists( 'pll_get_post_language' ) ) {
		return (string) pll_get_post_language( $id, 'locale' );
	}
	if ( has_filter( 'wpml_post_language_details' ) ) {
		$dettagli = apply_filters( 'wpml_post_language_details', null, $id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro di WPML.
		if ( is_array( $dettagli ) && ! empty( $dettagli['locale'] ) ) {
			return (string) $dettagli['locale'];
		}
	}
	return '';
}

/**
 * Prima che un widget VBEB venga disegnato nell'editor, assicura che il
 * contesto camera abbia qualcosa da mostrare.
 *
 * Un solo punto di aggancio invece di una modifica in ogni widget:
 * l'hook di Elementor passa il widget, da cui si ricava il nome.
 *
 * @param \Elementor\Widget_Base $widget Widget in corso di rendering.
 * @return void
 */
function vbeb_anteprima_editor( $widget ) {
	if ( ! class_exists( '\\Elementor\\Plugin' ) || ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return;
	}

	if ( ! is_object( $widget ) || ! method_exists( $widget, 'get_name' ) ) {
		return;
	}

	if ( 0 !== strpos( $widget->get_name(), 'vbeb_' ) ) {
		return;
	}

	// VBEB 7.33.0: prima di tutto la lingua, altrimenti i widget
	// disegnerebbero le chiavi al posto dei testi.
	vbeb_lingua_sito_in_editor();

	// VBEB free: i dati di VikBooking nella lingua del template (Polylang o
	// WPML), non in quella del profilo dell'utente.
	$lingua = vbeb_lingua_template_editor();
	if ( '' !== $lingua && class_exists( 'VikBooking' ) ) {
		VikBooking::getTranslator(); // carica la classe VikBookingTranslator.
		if ( class_exists( 'VikBookingTranslator' ) ) {
			VikBookingTranslator::$force_tolang = $lingua;
		}
	}

	if ( class_exists( 'VBEB_Room_Context' ) && empty( VBEB_Room_Context::$room ) ) {
		vbeb_carica_camera_anteprima();
	}
}

add_action( 'elementor/widget/before_render_content', 'vbeb_anteprima_editor', 5 );
