/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood{at}iinet.com.au),
			  Stéphane Nahmani (sholby@sholby.net),
			  Stéphane Raimbault <stephane.raimbault@gmail.com> */
(function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define([ "../jquery.ui.datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}(function( datepicker ) {
	datepicker.regional['it'] = {
		closeText: 'vicino',
		prevText: 'precedente',
		nextText: 'seguente',
		currentText: 'oggi',
		monthNames: ['gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno',
			'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'],
		monthNamesShort: ['gen.', 'feb.', 'mar.', 'apr.', 'mag.', 'giu.',
			'lug.', 'agost.', 'sett.', 'ott.', 'nov.', 'dic.'],
		dayNames: ['domenica', 'lunedi', 'martedì', 'mercoledì', 'giovedi', 'venerdì', 'sabato'],
		dayNamesShort: ['dom', 'lun', 'mar', 'mer', 'gio', 'ven', 'sab'],
		dayNamesMin: ['D','L','M','M','G','V','S'],
		weekHeader: 'Set.',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	datepicker.setDefaults(datepicker.regional['it']);

	return datepicker.regional['it'];

}));
