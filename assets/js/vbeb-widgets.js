/**
 * VBEB - Bridge for VikBooking and Elementor
 * Script dei widget della camera, sul sito e nell'editor.
 *
 * VBEB free (revisione di wordpress.org): prima questo codice era stampato
 * dai widget in blocchi <script>; ora e' in questo file, caricato con
 * wp_enqueue_script(). Le impostazioni di ogni widget sono in attributi
 * data- del suo HTML.
 *
 * - VBEB Camera - Calendario: con due colonne, se una tabella non entra
 *   nella sua colonna i mesi vanno uno sotto l'altro.
 * - VBEB Camera - Carosello d'immagini: avvia Swiper (data-vbeb-carosello).
 * - VBEB Camera - Galleria: avvia la galleria di Elementor (data-vbeb-galleria).
 */
( function () {
	'use strict';

	/* ---------- Calendario ---------- */

	function calendario( wrap ) {
		var block = wrap.querySelector( '.vbcalsblock' );
		if ( ! block || block.vbebCal ) {
			return;
		}
		block.vbebCal = true;

		var cls = 'vbeb-cal-una-colonna';
		var last = -1;

		function check( force ) {
			var w = block.clientWidth;
			if ( ! force && w === last ) {
				return;
			}
			last = w;
			block.classList.remove( cls );
			if ( window.getComputedStyle( block ).gridTemplateColumns.split( ' ' ).length < 2 ) {
				return;
			}
			var over = false;
			block.querySelectorAll( '.vbcaldivcont' ).forEach( function ( c ) {
				var t = c.querySelector( 'table' );
				if ( t && t.offsetWidth > c.clientWidth + 1 ) {
					over = true;
				}
			} );
			if ( over ) {
				block.classList.add( cls );
			}
		}

		check( true );
		if ( window.ResizeObserver ) {
			new ResizeObserver( function () {
				check( false );
			} ).observe( block );
		} else {
			window.addEventListener( 'resize', function () {
				check( false );
			} );
		}
		if ( window.MutationObserver ) {
			new MutationObserver( function () {
				check( true );
			} ).observe( block, { childList: true } );
		}
		window.addEventListener( 'load', function () {
			check( true );
		} );
	}

	/* ---------- Carosello d'immagini ---------- */

	function carosello( datiEl ) {
		if ( typeof window.Swiper === 'undefined' ) {
			return false;
		}

		var d;
		try {
			d = JSON.parse( datiEl.getAttribute( 'data-vbeb-carosello' ) || '' );
		} catch ( e ) {
			return true;
		}

		var gal = document.getElementById( d.id );
		var mainEl = gal ? gal.querySelector( '.swiper' ) : null;
		if ( ! mainEl || mainEl.swiper ) {
			return true;
		}

		var thumbsSwiper = null;
		if ( d.thumbs ) {
			var th = document.getElementById( d.thumbs );
			var thumbsEl = th ? th.querySelector( '.swiper' ) : null;
			// Niente loop sulle miniature: riordinerebbe le foto visibili.
			if ( thumbsEl && ! thumbsEl.swiper ) {
				thumbsSwiper = new window.Swiper( thumbsEl, d.thumbsOptions );
			}
		}

		var m = d.main;
		var opz = {
			slidesPerView: m.slidesPerView,
			slidesPerGroup: m.slidesPerGroup,
			spaceBetween: m.spaceBetween,
			loop: m.loop,
			speed: m.speed,
			effect: m.effect,
			autoplay: m.autoplay,
			pagination: m.pagination ? { el: gal.querySelector( '.swiper-pagination' ), clickable: true } : false,
			navigation: m.navigation ? { nextEl: gal.querySelector( '.swiper-button-next' ), prevEl: gal.querySelector( '.swiper-button-prev' ) } : false
		};
		if ( m.lazy ) {
			opz.lazy = { loadPrevNext: true, loadPrevNextAmount: 1 };
			opz.preloadImages = false;
		}
		if ( thumbsSwiper ) {
			opz.thumbs = { swiper: thumbsSwiper };
		}

		new window.Swiper( mainEl, opz );
		return true;
	}

	/* ---------- Galleria ---------- */

	function galleria( el ) {
		if ( typeof window.EGallery === 'undefined' || typeof window.jQuery === 'undefined' ) {
			return false;
		}
		if ( el.vbebGallery ) {
			return true;
		}
		var config;
		try {
			config = JSON.parse( el.getAttribute( 'data-vbeb-galleria' ) || '' );
		} catch ( e ) {
			return true;
		}
		config.container = window.jQuery( el );
		el.vbebGallery = new window.EGallery( config );
		return true;
	}

	/* ---------- Avvio ---------- */

	function avvia( radice ) {
		radice = radice || document;
		var attesa = false;

		radice.querySelectorAll( '.vbeb-calendar-wrap' ).forEach( calendario );
		radice.querySelectorAll( '[data-vbeb-carosello]' ).forEach( function ( el ) {
			if ( ! carosello( el ) ) {
				attesa = true;
			}
		} );
		radice.querySelectorAll( '[data-vbeb-galleria]' ).forEach( function ( el ) {
			if ( ! galleria( el ) ) {
				attesa = true;
			}
		} );

		return attesa;
	}

	function avviaPagina() {
		// Swiper ed EGallery possono arrivare dopo questo script: si riprova
		// al caricamento completo della pagina.
		if ( avvia( document ) ) {
			window.addEventListener( 'load', function () {
				avvia( document );
			} );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', avviaPagina );
	} else {
		avviaPagina();
	}

	// Nell'editor Elementor ridisegna il widget a ogni modifica.
	function collega() {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			[ 'vbeb_room_calendar_widget', 'vbeb_room_image_carousel_widget', 'vbeb_room_photo_gallery_widget' ].forEach( function ( nome ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/' + nome + '.default', function ( $scope ) {
					if ( $scope && $scope[ 0 ] ) {
						avvia( $scope[ 0 ] );
					}
				} );
			} );
			return true;
		}
		return false;
	}

	if ( ! collega() ) {
		window.addEventListener( 'elementor/frontend/init', collega );
	}
} )();
