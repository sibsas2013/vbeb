<?php
/**
 * Vista "Dettaglio camera" di VikBooking con il layout "listing" di VBEB.
 *
 * VBEB free, revisione di wordpress.org: VBEB non copia piu' nessun file
 * nella cartella degli override di VikBooking (uploads/vikbooking/overrides).
 * Prima di disegnare la pagina camera VikBooking lancia l'azione
 * "vikbooking_before_display_roomdetails" passando la vista per riferimento
 * (libraries/adapter/mvc/controller.php): VBEB la sostituisce con questa
 * sottoclasse, identica all'originale, che per il solo file
 * "default_listing" usa quello della cartella del plugin
 * (override-templates/roomdetails/default_listing.php). Tutti gli altri file
 * della vista (e gli override dell'utente) restano quelli di VikBooking.
 *
 * Questo file si carica solo dentro l'azione, quando la classe di VikBooking
 * esiste gia'.
 *
 * @package VBEB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VBEB_Roomdetails_View' ) && class_exists( 'VikbookingViewRoomdetails' ) ) {

	/**
	 * Vista Dettaglio camera con il layout "listing" preso da VBEB.
	 */
	class VBEB_Roomdetails_View extends VikbookingViewRoomdetails {

		/**
		 * Crea la vista VBEB con lo stesso stato della vista di VikBooking
		 * (cartella della vista, layout, modello, documento).
		 *
		 * @param VikbookingViewRoomdetails $originale Vista creata da VikBooking.
		 * @return VBEB_Roomdetails_View
		 */
		public static function da_vista( $originale ) {
			$vista = new self( '' );

			// Le proprieta' di JView sono protette: si copiano dall'interno
			// della classe JView.
			$copia = function ( $da ) {
				foreach ( get_object_vars( $da ) as $nome => $valore ) {
					$this->$nome = $valore;
				}
			};
			\Closure::bind( $copia, $vista, 'JView' )( $originale );

			return $vista;
		}

		/**
		 * La cartella del file di un layout: per "default_listing" quella di
		 * VBEB, per tutti gli altri la ricerca normale di VikBooking.
		 *
		 * @param string $base   Cartella della vista.
		 * @param string $layout Nome del file senza ".php".
		 * @return string|false
		 */
		protected function _getTemplateBasePath( $base, $layout ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- nome del metodo di VikBooking.
			$cartella = VBEB_PATH . 'override-templates/roomdetails';

			if ( 'default_listing' === $layout && is_file( $cartella . '/default_listing.php' ) ) {
				return $cartella;
			}

			return parent::_getTemplateBasePath( $base, $layout );
		}
	}
}
