$(document).ready(function() {
	ATK14.init();
});

/*
 * ATK14 namespace
 *
 */
var ATK14 = {
	VERSION: '',
	LANG: ''
};

/*
 * First things first
 *
 */
ATK14.init = function() {
	// set a language for gettext
	ATK14.LANG = $("meta[name='x-lang']").attr("content");
	// convert obfuscated email addresses to normal mailto links
	$('.atk14_no_spam').unobfuscate();
	// attach live events on remote elements
	$('a.remote_link').live('click', ATK14.Remote.handle_link);
	$('form.remote_form').livequery('submit', ATK14.Remote.handle_form);
	// global ajaxStart/ajaxStop events
	$('body')
		.ajaxStart(function() {
			$(this).addClass('loading');
		})
		.ajaxStop(function() {
			$(this).removeClass('loading');
		});
}

/*
 * First approach to gettext implementation.
 * Language code is determined from the specified meta tag:
 *   <meta name="x-lang" content="cs" />
 */
ATK14.gettext = function(msg) {
	if (ATK14.LANG == 'cs') {
		switch(msg) {
			case 'Are you sure?':
				return 'Jste si jistý(á)?';
				break;
		}
	}
	return msg;
}

/*
 * Module for handling remote elements.
 * We have 2 basic remote elements: remote links and remote forms.
 * Remote links default to GET method and remote forms default to POST method.
 * Remote link with class="post" will be handled with POST method though.
 *
 */
ATK14.Remote = (function() {

	// private scope
	function randomizeUrlForAjax(url) {
		if (url.indexOf('?') > 0) {
			url = url + '&';
		} else{
			url = url + '?';
		}
		url = url + '_=' + (Math.random());
		return url;
	}

	// an object with public methods is assigned to ATK14.Remote
	return {
		evaluateSourceForRemoteLink: function(source, $link) {
			eval(source);
		},
		evaluateSourceForRemoteForm: function(source, $form) {
			eval(source);
		},

		handle_link: function() {
			var $a = $(this);

			if ($a.hasClass("confirm") && !confirm(ATK14.gettext("Are you sure?"))) {
				return false;
			}

			// is there any additional callback for remote link?
			if (typeof before_remote_link != "undefined") {
				var ret = before_remote_link($a);
				if (!ret) {
					return ret;
				}
			}

			var params = {
				$link: $a,
				cache: false,
				type: 'GET',
				dataType: 'text',
				data: null,
				url: $a.attr('href'),
				success: function(source) {
					ATK14.Remote.evaluateSourceForRemoteLink(source,this.$link);
					if (typeof after_remote_link != "undefined") {
						after_remote_link(this.$link);
					}
				},
				complete: function() {
					$('.atk14_no_spam').unobfuscate();
				}
			}
			// when dealing with .post link, we have to modify default params
			if ($a.hasClass('post')) {
				params = $.extend({}, params, { type: 'POST', data: '' });
			}
			/*
			if ($a.hasClass('http_method_delete')) {
				params = $.extend({}, params, { type: 'POST', data: '_method=delete' });
			}*/

			$.ajax(params);

			return false;
		},

		handle_form: function() {
			var $f = $(this);
			$.ajax({
				$form: $f,
				cache: false,
				type: 'POST',
				url: randomizeUrlForAjax($f.attr('action')),
				dataType: 'text',
				data: $f.serialize(),
				complete: function(){
					$('.atk14_no_spam').unobfuscate();
				},
				success: function(source) {
					ATK14.Remote.evaluateSourceForRemoteForm(source,this.$form);
				}
			});
			return false;
		}
	};
})();
