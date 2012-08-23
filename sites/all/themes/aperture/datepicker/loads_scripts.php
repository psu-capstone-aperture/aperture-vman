<script src="../sites/all/themes/aperture/datepicker/js/mootools-core.js" type="text/javascript"></script>
	<script src="../sites/all/themes/aperture/datepicker/js/mootools-more.js" type="text/javascript"></script>
	<script src="../sites/all/themes/aperture/datepicker/scripts/Locale.en-US.DatePicker.js" type="text/javascript"></script>
	<script src="../sites/all/themes/aperture/datepicker/scripts/Picker.js" type="text/javascript"></script>
	<script src="../sites/all/themes/aperture/datepicker/scripts/Picker.Attach.js" type="text/javascript"></script>
	<script src="../sites/all/themes/aperture/datepicker/scripts/Picker.Date.js" type="text/javascript"></script>
	<link href="../sites/all/themes/aperture/datepicker/scripts/datepicker_dashboard/datepicker_dashboard.css" rel="stylesheet">

	<script>
	window.addEvent('domready', function(){
		new Picker.Date($$('input'), {
			timePicker: false,
			positionOffset: {x: 5, y: 0},
			pickerClass: 'datepicker_dashboard',
			useFadeInOut: !Browser.ie
		});
	});
	</script>