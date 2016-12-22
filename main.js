/* 
 * version: 1.4
 * date: 2016-05-23
 * developer: Ph. Huwyler
 
 * URL PARAMS:
 * - dynFilterList: ex. dynFilterList=%5B"TagDescription%20LIKE%20%27%25UK3%25%27"%5D (like SQL filter but use url encoder. You can use an online url encoder like http://www.url-encode-decode.com/)
 * - startDate and endDate: ex. startDate=2015-06-01 or endDate=-7 in absolute or in relative format
 * - autoStart: ex. autoStart=true
 * - pageLen: ex. pageLen=50
 * - colSort: ex. colSort=-5 or colSort=-0. sort by col index
 * - top: ec. top=25. the top x items
 */
 
 $(document).ready(function() {
	
	// helper function to get url params
	$.urlParam = function(name) {
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if (results==null) {
		   return null;
		} else {
		   return decodeURIComponent(results[1]) || 0;
		}
	}

	// fist hide table and restore it after the query
	$( "#tbl" ).hide();
	
	// init from - to field as datepicker
	$("#from").datepicker({
      defaultDate: "-1w",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function( selectedDate ) {
        $( "#to" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#to" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function( selectedDate ) {
        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
	// format datepicker
	$( "#from, #to" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
	$( "#from, #to" ).datepicker( "option", $.datepicker.regional[ "de" ] );
	
	// init datepicker with date of today or from url params startDate and endDate (in absolute or relative format)
	$( "#to" ).datepicker('setDate', new Date());
	if($.urlParam('endDate') != null) {
		if((new RegExp(/^\-{1}\d+$/)).test($.urlParam('endDate')) == true) {
			var d = new Date();
			d.setDate(d.getDate()-Math.abs(parseInt($.urlParam('endDate'))));
			$( "#to" ).datepicker('setDate', d);
			h = true;
		} else if((new RegExp(/\b\d{4}[-]\d{1,2}[-]\d{1,2}\b/)).test($.urlParam('endDate')) == true) {
			$("#to").datepicker('setDate', new Date($.urlParam('endDate')));
		}
	}
	
	$( "#from" ).datepicker('setDate', new Date());
	if($.urlParam('startDate') != null) {
		if((new RegExp(/^\-{1}\d+$/)).test($.urlParam('startDate')) == true) {
			var d = new Date();
			d.setDate(d.getDate()-Math.abs(parseInt($.urlParam('startDate'))));
			$( "#from" ).datepicker('setDate', d);
			h = true;
		} else if((new RegExp(/\b\d{4}[-]\d{1,2}[-]\d{1,2}\b/)).test($.urlParam('startDate')) == true) {
			$("#from").datepicker('setDate', new Date($.urlParam('startDate')));
		}
	} else if($.urlParam('top') != null) {	// case startDate not defined and top is defined, set startDate to first possible date (otherwise startDate would be today or given params)
		$( "#from" ).datepicker('setDate', new Date('1970-01-01'));
	}
	
	// get autoStart param from url to check if we need to run the query on site loading
	var autoStart = false;
	if($.urlParam('autoStart') != null) {
		try {
			if($.parseJSON($.urlParam('autoStart')) === true) {
				autoStart = true;
			}
		} catch (err) {}
	}
	
	// query and display table on click
	var table;
	$( "button" )
      .button()
      .click(function( event ) {
		// show table
		if ( typeof table !== 'undefined' ) {
			table.clear();
			
			table.destroy();
			$('#tbl').empty(); // empty in case the columns change
			
			$('#tbl tr').remove();
			$('#tbl').hide();
			$('#tbl_wrapper').hide();
		}

		$('#tbl').show();
		//$('#tbl').find("caption").text("...");
		$('#tblCaption').text(">  ...");
		//var f = $.datepicker.formatDate( "dd/mm/yy", new Date( $( "#from" ).datepicker( "getDate" ) ) );	//	IT SQL VERSION
		var f = $.datepicker.formatDate( "yy-mm-dd", new Date( $( "#from" ).datepicker( "getDate" ) ) );	//	EN SQL VERSION
		//var f = $.datepicker.formatDate( "dd.mm.yy", new Date( $( "#from" ).datepicker( "getDate" ) ) );	//	DE SQL VERSION
		//var t = $.datepicker.formatDate( "dd/mm/yy", new Date( $( "#to" ).datepicker( "getDate" ) ) );		//	IT SQL VERSION
		var t = $.datepicker.formatDate( "yy-mm-dd", new Date( $( "#to" ).datepicker( "getDate" ) ) );	//	EN SQL VERSION
		//var t = $.datepicker.formatDate( "dd.mm.yy", new Date( $( "#to" ).datepicker( "getDate" ) ) );	//	DE SQL VERSION

		var req = './getData.php?';
		if($( "#from" ).val()) {
			req = req + 'startDate='+f;
		}
		if($( "#to" ).val()) {
			req = req + '&endDate='+t;
		}
		
		if($.urlParam('dynFilterList') != null) {
			req = req + "&dynFilterList=" + $.urlParam('dynFilterList');
		}
		
		if($.urlParam('top') != null) {
			req = req + "&top=" + $.urlParam('top');
		}
		
		$.getJSON( "./config.json.txt", function( config ) {
			var ATtblColumnNames4DataTable = [];
			$.ajax({
				type: "POST",
				dataType: "json",
				url: req,
				timeout: config.ajax.timeout,
				error: function(xhr, ajaxOptions, thrownError){
					// will fire when timeout is reached
					$('#tblCaption').text("> "+thrownError+" ("+xhr.status+")");
				},
				success: function(json) {
					
					// set table caption
					var caption = $.datepicker.formatDate( "D. dd. MM yy", new Date( json.period.from.year, json.period.from.month-1, json.period.from.day ))
									+ " - "
									+ $.datepicker.formatDate( "D. dd. MM yy", new Date( json.period.to.year, json.period.to.month-1, json.period.to.day ))
					$('#tbl').find("caption")
					.text(caption)
					.hide();
					$('#tblCaption').text(">  "+caption);
					
					// get header
					$("#tbl thead").remove();
					var thead = $('<thead></thead>').appendTo('#tbl');
					var tr = $('<tr></tr>').appendTo(thead);
					
					if(json.data.length > 0) {
						// generate columns param for DataTable object as array
						$.each(json.data[0], function(a, b) {
							ATtblColumnNames4DataTable.push($.parseJSON('{"data": "'+a+'"}'));
							$('<th>'+a+'</th>').appendTo(tr);
						});

						// generate table with new DataTable http://datatables.net/
						table = $('#tbl')
						.DataTable({
							"data": json.data,
							"columns": ATtblColumnNames4DataTable,
							"language": {
								"url": "./inc/localisation/de_DE.json.txt"
							},
							"destroy": true,
							"responsive": true,
							"autoWidth": true,
							"scrollX": true,
							"stateSave": true,
							"lengthMenu": [ [10, 20, 50, 100, -1], [10, 20, 50, 100, "ALL"] ],
							"paging": true,
							"pagingType": "full_numbers",
							"dom": 'RTC<"clear">lfrtip',
							"colVis": {
								"order": 'alpha'
							},
							"colReorder": {
								order: config.table.colReorder
							},
							"columnDefs": [
								{
									className: "dt-head-left",
									"targets": [ 0,1,2,3 ]
								}
							],
							"tableTools": {
								"sSwfPath": "./inc/swf/copy_csv_xls_pdf.swf",
								"aButtons": [
									{
										"sExtends": "copy",
										"sButtonText": "COPY"
									},
									{
										"sExtends": "csv",
										"sTitle": "AuditTrail "+f+" - "+t,
										"sFieldSeperator": config.csv.delimiter
									},
									{
										"sExtends": "xls",
										"sTitle": "AuditTrail "+f+" - "+t
									},
									{
										"sExtends": "pdf",
										"sTitle": "AuditTrail "+f+" - "+t,
										"orientation": "landscape"
									},
									{
										"sExtends": "print",
										"sButtonText": "PRINT"
									}
								]
							}
						});

						// init table depending on url params
						table.on('init.dt', function () {
							
							// col visibility
							$.each(config.table.colHidden, function(a, b) {
								// Toggle the visibility
								table.column(b).visible(false);
								//table.column('#column-'+b).visible(false); unavailable... use index instead of id
							});
							
							// page len
							if($.urlParam('pageLen') != null) {
								try {
									table.page.len(parseInt($.urlParam('pageLen'))).draw();
								} catch (err){}
							}
							
							// col sort
							if($.urlParam('colSort') != null) {
								try {
									table.order([Math.abs(parseInt($.urlParam('colSort'))), $.urlParam('colSort').indexOf('-') > -1 ? 'desc' : 'asc']).draw();
								} catch (err){}
							}
						});
					} else {
						// no data 
						$('#tblCaption').text("> no data");
					}
				}
			});
		});
        event.preventDefault();
      });
	  
	  // auto start query if autoStart param is true
	  if(autoStart) {
		  $( "button" ).click();
	  }
} );
