var ATK14 = (function( $ ) {

	$( "a[data-remote], a[data-method]" ).live( "click", function() {
		var $link = $( this );

		if ( !allowAction( $link ) ) return false;

		if ( $link.data( "remote" ) ) {
			ATK14.handleRemote( this );
			return false;
		} else if ( $link.data( "method" ) ) {
			handleMethod( $link );
			return false;
		}
	});

	$( "form[data-remote]" ).live( "submit", function() {
		ATK14.handleRemote( this );
		return false;
	});

	$( "body" )
		.ajaxStart(function() {
			$( this ).addClass( "loading" );
		})
		.ajaxStop(function() {
			$( this ).removeClass( "loading" );
		});

	$.ajaxSetup({
		converters: {
			"text conscript": true
		},
		dataType: "conscript"
	});

	function fire( obj, name, data ) {
		var event = new $.Event( name );
		obj.trigger( event, data );
		return event.result !== false;
	};

	function allowAction( $element ) {
		var message = $element.data( "confirm" );
		return !message || ( fire( $element, "confirm" ) && confirm( message ) );
	}

	function handleMethod( $link ) {
		var href = $link.attr( "href" ),
			method = $link.data( "method" ),
			$form = $( "<form method='post' action='" + href + "'></form>" ),
			metadata_input = "<input name='_method' value='" + method + "' type='hidden' />";

		$form.hide().append( metadata_input ).appendTo( "body" );
		$form.submit();
	}


	return {

		action: $( "meta[name='x-action']" ).attr( "content" ),

		handleRemote: function( element ) {
			var method, url, data, $link, $form,
				$element = $( element ),
				dataType = $element.data( "type" ) || $.ajaxSettings.dataType;

			if ( $element.is("form") ) {
				$form = $element; // remove later
				method = $element.attr( "method" );
				url = $element.attr( "action" );
				data = $element.serializeArray();
			} else {
				$link = $element; // remove later
				method = $element.data( "method" );
				url = $element.attr( "href" );
				data = null;
			}

			$.ajax({
				url: url,
				type: method || 'GET',
				data: data,
				dataType: dataType,
				beforeSend: function( xhr, settings ) {
					return fire( $element, "ajax:beforeSend", [ xhr, settings ] );
				},
				success: function( data, status, xhr ) {
					$element.trigger( "ajax:success", [ data, status, xhr ] );

					if ( dataType === "conscript" ) {
						eval( data );
					}
				},
				complete: function( xhr, status ) {
					$element.trigger( "ajax:complete", [ xhr, status ] );
				},
				error: function( xhr, status, error ) {
					$element.trigger( "ajax:error", [ xhr, status, error ] );
				}
			});
		}
	};

})( jQuery );
