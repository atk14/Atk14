var ATK14 = ( function() {
	"use strict";

	$( document ).on( "click", "a[data-remote], a[data-method]", function( e ) {
		var $link = $( this );

		if ( !allowAction( $link ) ) {
			return false;
		}

		if ( $link.data( "remote" ) ) {
			ATK14.handleRemote( this );
			e.preventDefault();
		} else if ( $link.data( "method" ) ) {
			handleMethod( $link );
			e.preventDefault();
		}
	} );

	// Here is onClick on a button with a "name" attribute.
	// Values of attributes "name" and "value" have to be put together into an extra parameter and passed to handleRemote.
	$( document ).on( "click", "form[data-remote] button[type=\"submit\"][name]", function( e ) {
		var $button = $( this ), $form = $button.closest( "form" );
		e.preventDefault();
		ATK14.handleRemote( $form[ 0 ], [ {
			name: $button.attr( "name" ),
			value: $button.attr( "value" )
		} ] );
		return false;
	} );

	$( document ).on( "submit", "form[data-remote]", function( e ) {
		ATK14.handleRemote( this );
		e.preventDefault();
	} );

	$( document )
		.ajaxStart( function() {
			$( document.body ).addClass( "loading" );
		} )
		.ajaxStop( function() {
			$( document.body ).removeClass( "loading" );
		} );

	$.ajaxSetup( {
		converters: {
			"text conscript": true
		},
		dataType: "conscript"
	} );

	function fire( obj, name, data ) {
		var event = new $.Event( name );
		obj.trigger( event, data );
		return event.result !== false;
	}

	function allowAction( $element ) {
		var message = $element.data( "confirm" );
		return !message || ( fire( $element, "confirm" ) && window.confirm( message ) );
	}

	function handleMethod( $link ) {
		var href = $link.attr( "href" ),
			method = $link.data( "method" ),
			$form = $( "<form method='post' action='" + href + "'></form>" ),
			metadataInput = "<input name='_method' value='" + method + "' type='hidden' />";

		$form.hide().append( metadataInput ).appendTo( "body" );
		$form.submit();
		$form.remove();
	}

	return {

		action: $( "meta[name='x-action']" ).attr( "content" ),

		handleRemote: function( element, extraParams ) {
			var method, url, data, formData, $link, $form,
				$element = $( element ),
				settings,
				dataType = $element.data( "type" ) || $.ajaxSettings.dataType;

			if ( element instanceof jQuery ) {
				element = element[ 0 ];
			}

			if ( extraParams === undefined ) { // [ { name: "name1", value: "value1" }, { name: "name2", value: "value2" }, ... ]
				extraParams = [];
			}

			method = $element.is( "form" ) ? $element.attr( "method" ) : $element.data( "method" );
			method = method || "GET"; // By default the method is GET
			method = method.toUpperCase();

			if ( $element.is( "form" ) ) {
				$form = $element; // Remove later
				url = $element.attr( "action" );
				if ( method == "POST" && ( "FormData" in window ) ) {
					formData = new FormData( element );
					for ( var i in extraParams ) {
						formData.append( extraParams[ i ].name, extraParams[ i ].value );
					}
				} else{
					data = $element.serializeArray();
					data = data.concat( extraParams );
				}
			} else {
				$link = $element; // Remove later
				url = $element.attr( "href" );
				data = null;
			}

			if ( method == "GET" ) {
				url += url.indexOf("?")>=0 ? "&" : "?";
				url += "__xhr_request=1";
			}

			settings = {
				url: url,
				type: method,
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
			}

			if ( formData ) {
				// https://www.mattlunn.me.uk/blog/2012/05/sending-formdata-with-jquery-ajax/
				settings.data = formData;
				settings.contentType = false;
				settings.processData = false;
			} else {
				settings.data = data;
			}

			$.ajax( settings );
		}
	};

} )();
