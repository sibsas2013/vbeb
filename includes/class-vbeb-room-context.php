<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contenitore statico temporaneo: l'override file di VikBooking imposta
 * $room (e $vbo_tn) subito prima di renderizzare il template Elementor,
 * cosi' i widget "VikBooking" possono leggerli durante quel singolo rendering.
 *
 * Non e' persistente: vive solo per la durata della richiesta HTTP corrente.
 */
class VBEB_Room_Context {

	/** @var array|null */
	public static $room = null;

	/** @var string|null */
	public static $vbo_tn = null;

	/**
	 * HTML gia' renderizzato del calendario disponibilita' reale di
	 * VikBooking ($this->loadTemplate('avcalendars')), catturato
	 * dall'override file (dove $this e' il vero oggetto View di
	 * VikBooking con accesso alle prenotazioni esistenti) e reso
	 * disponibile al widget "VikBooking Calendario Disponibilita'".
	 *
	 * @var string|null
	 */
	public static $avcalendars_html = null;

	/**
	 * HTML gia' renderizzato del form di prenotazione reale di VikBooking
	 * ($this->loadTemplate('bookingform')), catturato dall'override file
	 * per lo stesso motivo del calendario: contiene logica di
	 * validazione/invio reale che non va ricostruita.
	 *
	 * @var string|null
	 */
	public static $bookingform_html = null;

	/**
	 * HTML gia' renderizzato del bottone/modale "Richiedi Informazioni"
	 * reale di VikBooking ($this->loadTemplate('reqinfo')), catturato
	 * dall'override file. Contiene gia' internamente il controllo
	 * sull'opzione "Abilita richiesta informazioni" della singola camera:
	 * se disattivata, VikBooking stesso non genera alcun HTML.
	 *
	 * @var string|null
	 */
	public static $reqinfo_html = null;

	/**
	 * VBEB 7.592.0: HTML della mappa ("Dove sarai",
	 * $this->loadTemplate('geomap')), catturato dall'override del dettaglio
	 * camera per il widget "VBEB Camera - Mappa". VikBooking lo genera solo
	 * se nella camera e' attivo "Abilita Geocoding".
	 *
	 * @var string|null
	 */
	public static $geomap_html = null;

	/**
	 * Identificativi della camera nel contesto di una ricerca: "indroom"
	 * e' l'indice dello slot di ricerca, "idroom" l'id reale della camera.
	 * Nella versione free li imposta solo l'anteprima dell'editor; il
	 * widget Carosello d'immagini usa "indroom" (se presente) per rendere
	 * unico l'identificativo della galleria.
	 *
	 * @var int|string|null
	 */
	public static $search_indroom = null;
	public static $search_idroom = null;

	/**
	 * Ritorna un valore del record camera, oppure un fallback se non
	 * siamo dentro un rendering reale (es. stai modificando il template
	 * Elementor direttamente, fuori dal contesto di una pagina camera).
	 */
	public static function get( $key, $fallback = '' ) {
		if ( is_array( self::$room ) && array_key_exists( $key, self::$room ) ) {
			return self::$room[ $key ];
		}

		return $fallback;
	}

	public static function has_context() {
		return is_array( self::$room );
	}

	/**
	 * Imposta piu' valori del contesto in una volta.
	 *
	 * VBEB free (Plugin Check): l'override del Dettaglio Camera passa i dati
	 * da qui invece di assegnare le proprieta' una per una (Plugin Check le
	 * scambiava per variabili globali senza prefisso).
	 *
	 * @param array $valori Nome della proprieta' => valore.
	 * @return void
	 */
	public static function set_values( array $valori ) {
		foreach ( $valori as $nome => $valore ) {
			if ( property_exists( __CLASS__, $nome ) ) {
				self::$$nome = $valore;
			}
		}
	}

	/**
	 * Identificativo della camera corrente.
	 *
	 * @return int
	 */
	public static function current_room_id() {
		if ( is_array( self::$room ) && ! empty( self::$room['id'] ) ) {
			return (int) self::$room['id'];
		}
		return 0;
	}
}
