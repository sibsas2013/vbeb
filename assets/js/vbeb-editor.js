/**
 * VBEB - Bridge for VikBooking and Elementor
 * Script dell'anteprima dell'editor di Elementor (non si carica sul sito).
 *
 * VBEB free (revisione di wordpress.org): prima questo codice era stampato
 * dai widget in blocchi <script> e <style>; ora e' in questo file, caricato
 * con wp_enqueue_script() solo nell'anteprima dell'editor.
 *
 * 1. Widget "VBEB Camera - Mappa": la mappa vera della camera di anteprima,
 *    con i dati dell'attributo data-vbeb-map del riquadro.
 * 2. Colori globali nelle regole forzate dei widget VBEB (vedi
 *    vbeb_regole_globali_forzate() in vbeb.php): sul sito sono nel foglio
 *    CSS di Elementor; nell'editor il widget le porta nell'attributo
 *    data-vbeb-css di un segnaposto nascosto e qui diventano un foglio
 *    nell'intestazione dell'anteprima, aggiornato a ogni modifica. Allo
 *    stesso modo arriva il CSS del template del calendario popup scelto nel
 *    widget "VBEB Camera - Modulo prenotazione".
 */
( function () {
	'use strict';

	function avviaMappa( el ) {
		if ( ! el || el.getAttribute( 'data-vbeb-ok' ) ) {
			return;
		}

		var d;
		try {
			d = JSON.parse( el.getAttribute( 'data-vbeb-map' ) || '' );
		} catch ( e ) {
			return;
		}
		if ( ! d || ! d.key ) {
			return;
		}

		function init() {
			if ( el.getAttribute( 'data-vbeb-ok' ) ) {
				return;
			}
			el.setAttribute( 'data-vbeb-ok', '1' );

			var m = new google.maps.Map( el, {
				center: { lat: d.lat, lng: d.lng },
				zoom: d.zoom,
				mapId: 'vbo_map',
				mapTypeId: d.tipo,
				mapTypeControl: false
			} );

			( d.punti || [] ).forEach( function ( p ) {
				if ( google.maps.marker && google.maps.marker.AdvancedMarkerElement ) {
					new google.maps.marker.AdvancedMarkerElement( { map: m, position: p, title: d.nome } );
				} else {
					new google.maps.Marker( { map: m, position: p, title: d.nome } );
				}
			} );

			if ( d.overlay ) {
				new google.maps.GroundOverlay(
					d.overlay.url,
					new google.maps.LatLngBounds(
						{ lat: d.overlay.south, lng: d.overlay.west },
						{ lat: d.overlay.north, lng: d.overlay.east }
					)
				).setMap( m );
			}
		}

		if ( window.google && google.maps && google.maps.Map ) {
			init();
			return;
		}

		window.vbebGmCoda = window.vbebGmCoda || [];
		window.vbebGmCoda.push( init );

		if ( window.vbebGmCarica ) {
			return;
		}
		window.vbebGmCarica = 1;

		window.vbebGmPronto = function () {
			var coda = window.vbebGmCoda || [];
			window.vbebGmCoda = [];
			coda.forEach( function ( f ) {
				f();
			} );
		};

		var s = document.createElement( 'script' );
		s.async = true;
		s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent( d.key ) + '&libraries=marker&loading=async&callback=vbebGmPronto';
		document.head.appendChild( s );
	}

	function cerca( radice ) {
		( radice || document ).querySelectorAll( '[data-vbeb-map]' ).forEach( avviaMappa );
	}

	function regoleGlobali( el ) {
		var id = el.getAttribute( 'data-id' );
		if ( ! id ) {
			return;
		}

		var css = '';
		el.querySelectorAll( '.vbeb-css-globali' ).forEach( function ( s ) {
			if ( s.closest( '.elementor-element' ) === el ) {
				css += s.getAttribute( 'data-vbeb-css' ) || '';
			}
		} );

		var foglio = document.getElementById( 'vbeb-css-globali-' + id );
		if ( '' === css ) {
			if ( foglio ) {
				foglio.parentNode.removeChild( foglio );
			}
			return;
		}
		if ( ! foglio ) {
			foglio = document.createElement( 'style' );
			foglio.id = 'vbeb-css-globali-' + id;
			document.head.appendChild( foglio );
		}
		foglio.textContent = css;
	}

	function collega() {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/vbeb_room_map_widget.default', function ( $scope ) {
				cerca( $scope && $scope[ 0 ] ? $scope[ 0 ] : null );
			} );
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
				if ( $scope && $scope[ 0 ] ) {
					regoleGlobali( $scope[ 0 ] );
				}
			} );
			return true;
		}
		return false;
	}

	if ( ! collega() ) {
		window.addEventListener( 'elementor/frontend/init', collega );
	}
} )();
