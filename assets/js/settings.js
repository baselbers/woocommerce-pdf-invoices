/* global settings */
( function( $ ) {
	// Select all/none
	$( '.wpi' ).on( 'click', '.select_all', function() {
		$( this ).closest( 'td' ).find( 'select option' ).attr( 'selected', 'selected' );
		$( this ).closest( 'td' ).find( 'select' ).trigger( 'change' );
		return false;
	});

	$( '.wpi' ).on( 'click', '.select_none', function() {
		$( this ).closest( 'td' ).find( 'select option' ).removeAttr( 'selected' );
		$( this ).closest( 'td' ).find( 'select' ).trigger( 'change' );
		return false;
	});

	$( window ).load( function () {
		$( '.wpi .bewpi-columns' ).find( 'ul.select2-choices' ).sortable();
		$( '.wpi .bewpi-columns' ).find( 'ul.select2-choices' ).disableSelection();
		$( '.wpi .bewpi-totals' ).find( 'ul.select2-choices' ).sortable();
		$( '.wpi .bewpi-totals' ).find( 'ul.select2-choices' ).disableSelection();
		$( '.wpi ul.select2-choices' ).sortable({
			stop: function( event, ui ) {

				var tr = $(event.target.closest('tr'));

				var columns = tr.find('li.select2-search-choice div').map(function () {
					return $(this).text();
				}).get();

				var options = tr.find('select option');
				var selected = [];

				columns.forEach(function (column) {
					options.each(function (index, option) {
						if (option.innerHTML === column) {
							selected.push(option);
						}
					});
				});

				selected.forEach(function (option) {
					tr.find('select')
						.append(option);
				});
			}
		});
	});

})( jQuery );
