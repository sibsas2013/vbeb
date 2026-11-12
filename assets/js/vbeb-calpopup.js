/**
 * VBEB - Bridge for VikBooking and Elementor
 * Calendario popup: script dei calendari con un template "VBEB Popup" di
 * tipo "Calendario popup".
 *
 * VBEB free (revisione di wordpress.org): prima questo codice era stampato
 * dai widget in blocchi <script>; ora e' in questo file, caricato con
 * wp_enqueue_script(). I dati di oggi (orario di check-in, vedi
 * vbeb_calpop_dati_oggi() in includes/class-vbeb-calpopup-widget.php)
 * arrivano in window.vbebCalpopOggi con wp_add_inline_script().
 *
 * 1. window.vbebCalpopPulsanti(): i pulsanti "Cancella le date" e "Chiudi"
 *    dentro un calendario in linea (VikBooking li crea solo per quello a
 *    comparsa).
 * 2. Il calendario A COMPARSA di jQuery UI ("#ui-datepicker-div") prende
 *    l'aspetto del template quando lo apre un campo di un widget con
 *    "data-vbeb-calpop"; segna i giorni passati.
 * 3. Il calendario di prova del widget "VBEB Calendario popup" (editor).
 */

/* 1. Pulsanti. */
window.vbebCalpopPulsanti=window.vbebCalpopPulsanti||function(dpDiv,texts,onClear,onClose){var $=window.jQuery;if(!$||!dpDiv||!dpDiv.length){return;}var el=dpDiv[0];texts=texts||{};function metti(){if(dpDiv.children(".vbo-drp-commands-bottom").length){return;}var c=$("<div class=\"vbo-drp-commands-bottom vbeb-calpop-commands\"></div>");var a=$("<a href=\"javascript:void(0);\"></a>").text(texts.clear||"Clear dates").on("click",function(e){e.preventDefault();if(onClear){onClear();}});var b=$("<button type=\"button\" class=\"btn btn-small vbo-pref-color-btn\"></button>").text(texts.close||"Close").on("click",function(e){e.preventDefault();if(onClose){onClose();}});c.append($("<div class=\"vbo-drp-command vbo-drp-command-clear\"></div>").append(a)).append($("<div class=\"vbo-drp-command vbo-drp-command-close\"></div>").append(b));dpDiv.append(c);}metti();if(window.MutationObserver&&!el.vbebCalpopObs){el.vbebCalpopObs=new MutationObserver(function(){metti();});el.vbebCalpopObs.observe(el,{childList:true});}};

/* 2. Calendario a comparsa. */
(function(){var OG=window.vbebCalpopOggi||{},OGGI_CHIUDE=parseInt(OG.chiude||0,10),OGGI_DATA=OG.data||"";function ymd(d){var m=d.getMonth()+1,g=d.getDate();return d.getFullYear()+"-"+(m<10?"0":"")+m+"-"+(g<10?"0":"")+g;}function avvia(){var $=window.jQuery;if(!$||!$.datepicker||!$.datepicker._updateDatepicker){return false;}if($.datepicker._vbebCalpop){return true;}$.datepicker._vbebCalpop=1;function passati(inst){try{if(!inst||!inst.dpDiv){return;}var oggi=new Date();oggi.setHours(0,0,0,0);var chiuso=false;if(OGGI_CHIUDE&&OGGI_DATA&&Date.now()/1000>OGGI_CHIUDE){var box=$(inst.input||inst.dpDiv).closest("[data-vbeb-calpop]");chiuso=!!(box.length&&(box.data("vbeb-calpop")||{}).today);}inst.dpDiv.find(".ui-datepicker-calendar").each(function(g){var m=new Date(inst.drawYear,inst.drawMonth+g,1);$(this).find("td").each(function(){var td=$(this);td.removeClass("vbeb-past");if(td.hasClass("ui-datepicker-other-month")){return;}var n=parseInt($.trim(td.children().first().text()||td.text()),10);if(!n){return;}var x=new Date(m.getFullYear(),m.getMonth(),n);if(x<oggi){td.addClass("vbeb-past");}else if(chiuso&&ymd(x)===OGGI_DATA){td.addClass("vbeb-past ui-datepicker-unselectable").removeAttr("data-handler").off("click");}});});}catch(e){}}$(".hasDatepicker").not("input").each(function(){passati($.data(this,"datepicker"));});var orig=$.datepicker._updateDatepicker;$.datepicker._updateDatepicker=function(inst){var box=null,cfg=null;try{if(inst&&!inst.inline&&inst.input){box=inst.input.closest("[data-vbeb-calpop]");if(box.length){cfg=box.data("vbeb-calpop")||null;}}}catch(e){}if(cfg&&cfg.months){inst.settings.numberOfMonths=parseInt(cfg.months,10);}var r=orig.apply(this,arguments);passati(inst);try{if(inst&&!inst.inline){var d=inst.dpDiv;d.removeClass(function(i,c){return (c.match(/(^|\s)vbeb-(calpop-\S+|popup(-\S+)?)(?=\s|$)/g)||[]).join(" ");});d.children(".vbeb-calpop-commands").remove();if(cfg&&cfg.class){d.addClass(cfg.class);if(cfg.popup){d.addClass(cfg.popup);}if(cfg.hide){d.addClass("vbeb-calpop-nocmd");}if(cfg.grid){d.addClass("vbeb-calpop-grid");}if(cfg.custom&&cfg.texts){if(cfg.texts.clear){d.find(".vbo-drp-command-clear a").text(cfg.texts.clear);}if(cfg.texts.close){d.find(".vbo-drp-command-close button").text(cfg.texts.close);}}if(cfg.buttons&&!d.children(".vbo-drp-commands-bottom").length){var t=cfg.texts||{},c=$("<div class=\"vbo-drp-commands-bottom vbeb-calpop-commands\"></div>");var a=$("<a href=\"javascript:void(0);\"></a>").text(t.clear||"Clear dates").on("click",function(e){e.preventDefault();inst.input.val("").trigger("change");$.datepicker._hideDatepicker();});var b=$("<button type=\"button\" class=\"btn btn-small vbo-pref-color-btn\"></button>").text(t.close||"Close").on("click",function(e){e.preventDefault();$.datepicker._hideDatepicker();});c.append($("<div class=\"vbo-drp-command vbo-drp-command-clear\"></div>").append(a)).append($("<div class=\"vbo-drp-command vbo-drp-command-close\"></div>").append(b));d.append(c);}}}}catch(e){}return r;};return true;}if(!avvia()){var n=0,t=setInterval(function(){if(avvia()||++n>80){clearInterval(t);}},150);}})();

/* 3. Calendario di prova. */
		(function () {
	function avvia() {
		var $ = window.jQuery;
		if (!$ || !$.fn || !$.fn.datepicker) {
			return false;
		}
		$('.vbeb-calpop-preview').each(function () {
			var box = $(this), dp = box.find('.vbeb-calpop-dp');
			if (dp.hasClass('hasDatepicker')) {
				return;
			}
			var dati = box.data('vbeb-calpop') || {};
			var oggi = new Date(); oggi.setHours(0, 0, 0, 0);
			var fine = new Date(oggi.getTime()); fine.setDate(fine.getDate() + 3);
			var opzioni = $.extend({}, dati.regional || {}, {
				numberOfMonths: dati.months || 2,
				minDate: 0,
				showOtherMonths: false,
				beforeShowDay: function (d) {
					var t = d.getTime(), giorno = 86400000;
					// VBEB 7.223.0: due giorni non disponibili, fra 8 e 9 giorni.
					if (Math.round((t - oggi.getTime()) / giorno) === 8 || Math.round((t - oggi.getTime()) / giorno) === 9) { return [false, '']; }
					// VBEB 7.223.0: solo check-in, con il mouse sul
					// terzo giorno (le classi di VikBooking in quel momento).
					if (dati.preview === 'checkin') {
						if (t === oggi.getTime()) { return [true, 'checkin-date without-checkout-date']; }
						if (t === fine.getTime()) { return [true, 'checkout-date date-will']; }
					} else {
						if (t === oggi.getTime()) { return [true, 'checkin-date']; }
						if (t === fine.getTime()) { return [true, 'checkout-date']; }
					}
					if (t > oggi.getTime() && t < fine.getTime()) { return [true, 'checkin-checkout-inner']; }
					return [true, ''];
				}
			});
			dp.datepicker(opzioni);
			if (dati.buttons) {
				window.vbebCalpopPulsanti(dp.find('.ui-datepicker').first(), dati.texts, null, null);
			}
		});
		return true;
	}
	function avviaConAttesa() {
		if (!avvia()) {
			var tentativi = 0, t = setInterval(function () {
				if (avvia() || ++tentativi > 40) { clearInterval(t); }
			}, 150);
		}
	}
	avviaConAttesa();
	// Nell'editor Elementor ridisegna il widget a ogni modifica.
	function collega() {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/vbeb_calpopup_widget.default', avviaConAttesa);
			return true;
		}
		return false;
	}
	if (!collega()) {
		window.addEventListener('elementor/frontend/init', collega);
	}
})();
