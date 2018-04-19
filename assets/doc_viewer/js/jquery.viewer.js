/**
 * Viewer 0.1.4 - Facebook-style dialog, with frills
 *
 * (c) 2008 Jason Frame
 * Licensed under the MIT License (LICENSE)
 */

/*
 * jQuery plugin
 *
 * Options:
 *   message: confirmation message for form submit hook (default: "Please confirm:")
 *
 * Any other options - e.g. 'clone' - will be passed onto the viewer constructor (or
 * Viewer.load for AJAX operations)
 */
jQuery.fn.viewer = function (options) {
	var i;
	options = options || {};

	return this.each(function () {
		var node = this.nodeName.toLowerCase(), self = this;
		if (node == 'a') {
			jQuery(this).click(function () {
				var active = Viewer.linkedTo(this),
					href = this.getAttribute('href'),
					localOptions = jQuery.extend({actuator: this, title: this.title}, options);

				if (active) {
					active.show();
				} else if (href.indexOf('#') >= 0) {
					var content = jQuery(href.substr(href.indexOf('#'))),
						newContent = content.clone(true);
					content.remove();
					localOptions.unloadOnHide = false;
					new Viewer(newContent, localOptions);
				} else { // fall back to AJAX; could do with a same-origin check
					if (!localOptions.cache) localOptions.unloadOnHide = true;
					Viewer.load(this.href, localOptions);
				}

				return false;
			});
		} else if (node == 'form') {
			jQuery(this).bind('submit.viewer', function () {
				Viewer.confirm(options.message || 'Please confirm:', function () {
					jQuery(self).unbind('submit.viewer').submit();
				});
				return false;
			});
		}
	});
};

//
// Viewer Class

function Viewer(element, options) {
	this.viewer = jQuery(Viewer.WRAPPER);
	jQuery.data(this.viewer[0], 'viewer', this);

	this.address = null;

	this.visible = false;
	this.options = jQuery.extend({}, Viewer.DEFAULTS, options || {});

	if (this.options.modal) {
		this.options = jQuery.extend(this.options, {center: true, draggable: true});
	}

	// options.actuator == DOM element that opened this viewer
	// association will be automatically deleted when this viewer is remove()d
	if (this.options.actuator) {
		jQuery.data(this.options.actuator, 'active.viewer', this);
	}

	this.setContent(element || "<div></div>");
	this._setupTitleBar();

	this.viewer.css('display', 'none').appendTo(document.body);
	this.toTop();

	if (this.options.fixed) {
		if (jQuery.browser.msie && jQuery.browser.version < 7) {
			this.options.fixed = false; // IE6 doesn't support fixed positioning
		} else {
			this.viewer.addClass('fixed');
		}
	}

	if (this.options.center && Viewer._u(this.options.x, this.options.y)) {
		this.center();
	} else {
		this.moveTo(
			Viewer._u(this.options.x) ? this.options.x : Viewer.DEFAULT_X,
			Viewer._u(this.options.y) ? this.options.y : Viewer.DEFAULT_Y
		);
	}

	if (this.options.show) this.show();

}

Viewer.EF = function () {
};
jQuery.extend(Viewer, {

	WRAPPER: "<table cellspacing='0' cellpadding='0' border='0' class='viewer-wrapper'>" +
	//"<tr><td class='top-left'></td><td class='top'></td><td class='top-right'></td></tr>" +
	"<tr><td class='leftt'></td><td class='viewer-inner'></td><td class='rightt'></td></tr>" +
	"<tr><td class='bottom-left'></td><td class='bottom'></td><td class='bottom-right'></td></tr>" +
	"</table>",

	DEFAULTS: {
		title: '&nbsp;',           // titlebar text. if null, titlebar will not be visible if not set.
		closeable: true,           // display close link in titlebar?
		draggable: true,           // can this dialog be dragged?
		clone: false,          // clone content prior to insertion into dialog?
		actuator: null,           // element which opened this dialog
		center: true,           // center dialog in viewport?
		show: true,           // show dialog immediately?
		modal: true,          // make dialog modal?
		fixed: true,           // use fixed positioning, if supported? absolute positioning used otherwise
		closeText: '<svg x="0px" y="0px" width="25px" height="25px" viewBox="0 0 10 10" focusable="false"><polygon fill="#ffffff" points="10,1.01 8.99,0 5,3.99 1.01,0 0,1.01 3.99,5 0,8.99 1.01,10 5,6.01 8.99,10 10,8.99 6.01,5 "></polygon></svg>',      // text to use for default close link
		unloadOnHide: true,          // should this dialog be removed from the DOM after being hidden?
		clickToFront: false,          // bring dialog to foreground on any click (not just titlebar)?
		behaviours: Viewer.EF,        // function used to apply behaviours to all content embedded in dialog.
		afterDrop: Viewer.EF,        // callback fired after dialog is dropped. executes in context of Viewer instance.
		afterShow: Viewer.EF,        // callback fired after dialog becomes visible. executes in context of Viewer instance.
		afterHide: Viewer.EF,        // callback fired after dialog is hidden. executed in context of Viewer instance.
		beforeUnload: Viewer.EF         // callback fired after dialog is unloaded. executed in context of Viewer instance.
	},

	DEFAULT_X: 50,
	DEFAULT_Y: 50,
	zIndex: 1337,
	dragConfigured: false, // only set up one drag handler for all boxys
	resizeConfigured: false,
	dragging: null,

	// load a URL and display in viewer
	// url - url to load
	// options keys (any not listed below are passed to viewer constructor)
	//   type: HTTP method, default: GET
	//   cache: cache retrieved content? default: false
	//   filter: jQuery selector used to filter remote content
	load: function (url, options) {
//        i = 0;
		options = options || {};

		this.address = url;

		var ajax = {
			url: url, type: 'GET', dataType: 'html', cache: false, success: function (html) {
				html = jQuery(html);
				if (options.filter) html = jQuery(options.filter, html);
				new Viewer(html, options);
			},
			error: function () {
				//$("#content_loader", parent.document.body).fadeOut('fast');
				Viewer.alert('Sorry, we couldn\'t load the resource. Verify you are connected and try again. If the problem persists, contact <a href="/">help</a>');
			},
			beforeSend: function (s) {
				//$("#content_loader", parent.document.body).fadeIn('fast');
			}
		};

		jQuery.each(['type', 'cache'], function () {
			if (this in options) {
				ajax[this] = options[this];
				delete options[this];
			}
		});
		jQuery.ajax(ajax);

	},

	// allows you to get a handle to the containing viewer instance of any element
	// e.g. <a href='#' onclick='alert(Viewer.get(this));'>inspect!</a>.
	// this returns the actual instance of the viewer 'class', not just a DOM element.
	// Viewer.get(this).hide() would be valid, for instance.
	get: function (ele) {
		var p = jQuery(ele).parents('.viewer-wrapper');
		return p.length ? jQuery.data(p[0], 'viewer') : null;
	},

	// returns the viewer instance which has been linked to a given element via the
	// 'actuator' constructor option.
	linkedTo: function (ele) {
		return jQuery.data(ele, 'active.viewer');
	},

	// displays an alert box with a given message, calling optional callback
	// after dismissal.
	alert: function (message, callback, options) {
		message = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAN1wAADdcBQiibeAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAP0SURBVFiFvVfNamVFEP6qz7k/CYybkfEnuPIR5q6dwUVEfIUBV26y9xFcidtAEAKiL+Aq4MYBV0I2PoALGWYmgkPQgST3/FS56Kru6nP6GiJiQXPvObe766uvfi+dPMTiQvBEAo6aBhvcIhTc0ncCQDiv22QccU6M47cJ37UXgifvvY/Txx8D9+7ffvi/kNevsHl6htNnvwKtBBw9OgSGX4DfX0x2EhAWAC0AanU1AJQBT4EwAAZkBGTQ1QPcx9+9LN8FHh0C357gqIVg88aDuXJqAFoCYakAFhkANaq88IEqH7NyaQE0gHTxvUn3AnjrQwCCTVujiBZRcVhFEPZsLPwjA2o9dxEANQAHfe7numYAaBEVhxUQ1jtY2AFAOFtPrSp1bDHmIAoA1GTLw9oBWCkwAxEitYULRgXQA9wq/QGQ4BQIwFy6IwOgrMyUJwBrx4K64Z1PS0t4AC6+AWQBUB+tB80BCANyUwEQzM+T1awBUgAexMx1DdDsKwOWKZEYEOsagTDqyykA87EpSjGwdjGxyvtqEvYy9ebzYC5iddMA0FAD0LpA2wEmuWQXgP2YckVscFZKg97tArEE0GYgwYPwcaGuqUmzjilXKB/jXTJExSmVDQDpASswHkTBirGxBmi1g4G16mZncZ+tLgoZYjYlAAhlmaVQYWQZlTd7dQC0isEWxpwNRd1wOgxAIPcAcpsb9+nYMSBVBqbluinv8joygAS/vogUcShdVGXANSsEPbtr6Ueo3vQ/SrBOKYzU1fwSy+FJq62JDFpmbX/lvtS49DGkCcb9KFY07NO1We411yvCfQYh4+QOu9eAqNFtAsDlIXGt1QYLamJtH2vaAcg2guNe5wE7z6UxMAY8AJlYKn1MJRsspMtFhoxeJ3/9rHs6HUA8iAkz1g2FXSVMaM3iFqAuNxaLXNE8//Mn5BBW9sw9fAPwNoOZMVLrBUlxF9NJWlU+6fs0RGbe/GRCvwB/fK93bDMAdmyk71UARrVOMmY1aVezAA1D2Uy88LVjoYt934NJbqk1I+6Rqp5osAnFgAs68dCING7VZLxSJhUAb3Ng2mJ1yQwAxAUa5Q3ku9rgql2NgasyVVMM3OS4kA7wY3phi4x5lEoAtKiQDRPaXKZZ8OwrFENp8vk0KCc5PCNT+uxzSFYcltrdNDB/+2Jyzhetwfm8K2NgKlVvSq/Tq6YcDc76u/wxcSxMLc8ACOeXL7FZHQDb5w7ECMh1tCoNFr7bWacEcs2v/TWrlO3VAXD5EgDhvCXG8Y9nOH38EfDggzpKHxN3Eqm/vrwAnp4BxDimk4dY/PAKXz6/xmevO+z/S1V3kntLXB3s4evD+/j8b5KoiyHTK51PAAAAAElFTkSuQmCCMTE0Mw==" align="left" style="padding-right: 20px;"/><div class="msg_body">' + message + '</div>';
		//add customization for icon alert
		return Viewer.ask(message, ['Close'], callback, options);
	},
	info: function (message, callback, options) {
		message = '<img width="50" src="/img/icons/ok.png" align="left" style="padding-right: 20px;"/><div class="msg_body">' + message + '</div>';
		return Viewer.ask(message, ['OK'], callback, options);
	},
	// displays an alert box with a given message, calling after callback iff
	// user selects OK.
	confirm: function (message, after, options) {
		message = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAFMklEQVR42rWXe1BUVRjAv3P3wd2FXRaVZZZHPmCULBLLyYqamESlUYupP0r6o9FxKgMLcfoHNVMwZ8Ry1FQIncYJyonMfBA6qPikmWIQAZGHPCrZXZ4Xdtm9u3vv3dO5vGKXZWHJ/Wb3/rHfud/3+x7nO2cRzEAq8pOlFOYXmlgcqqRlNp4XHvEC6JMyyrCvtpAvi+//sC5RqdJ8HByiTqbVIaoxhSAA09XTZTJZrxkN3YXthsHSDfsqnI8NoLZwzaKQ2SHHZut0r4EUAUJub+KhDwBxiTmA1qa/q38urUv/4lTdnf8NUP/j2vUR86IK5IF0IFBTvyCCYAHAYXJwuXlXt7Z0DBw7fd0waWm82qv5Pvmj6CcXHKXkFIV8KNaQNwLBmXm8I/dCxl/GwSPn/hjwCDGp2frCpNVPxMwtoWiJxBfnLiA8gMU4wH2w6/zrZyvZq9MGuFuQoIqar2tQzgoMn8w59mZgdA1Z5LQClF+6U7OnsH35n+28bVoAjUWJWZExur1I6kFJGs3JDad4yABZg2STk2AHgFVvhPVZN9ZeaXSWTAvAcG7lQ7VOFT0++qGIiWPBAsDbesHaayQgAgSoQ0AZFgWUghijPJeB67XBroOl+Rer8eaWXsBeAW7lPjU//rm5rZQCuWpJxMIgiYZpgZJzjU3Hb8IRvQmqgpUoaOu789NT345dRwV4SJiDBfZBJZy5Zf597wWc+E8/OLwCXMtelLw0LqqUkrk2LSapF2wOuFF2W//+KZzsdEKdhRuOJjNFu3B3elwjpXQ1h+2dIDANYDdycKsGNW8pwkv1ZrB4Bdj51pwlb8TD9QAZpse0eKShHBxkF5vzylvwNsYGY5Mud1OkZNObEVZZsEwuvoJJnZzWdjIMmKGZYDcC3K6H1i2FEN9hAvOUPRAkhzm8ExQT6kkgKArMLAf9438vzoqgVyVozZIAVuoUvXH/qcWGtesBLteg+m2n8fPdlikyMBOpO6zeGBUpnETIMmFECywB6AEouISuHCzHa/rYKXrAV7n3Ff1epNZ+QhqIafddIKafY0jjDgCknUD77rTj7QTA+y6YrlTvV9AKufOoVmvfQJFumeBcnBdi9N0AbQawphyFVzoHocrdzowAKr9UyoOD+N9CwxwrkBwAuZ+MeNi5g6TeQhKe+R06fLMNf+ae/hkD1B8I2B4eac9BAR6cC8PDykH60Eoa8NCv6OKpSryx3wbdnmzNCKD1G2nb7HB+nqea8ybinAyszh7gss+gk2XN+HOT3bPzGQH89KlCk/Qs2ydRk+DHj+qRtNtJ5LWNwKQVoQyjGRcT56w3ez4DnM9QxCQsYZulKjcFAeBI9IOk6985hDIbuvDhAfvokfUYAc5uVj79wgJrrcR9TBEAnsw4xgrOxIMottuCm6djz2eAtESZKlrLF88KxJHuAOJw7usHW04ZJJOO7/ELgCixOrncxoPr2Tey/VhOwBwnWBkWHt+teLz8snO5VBsWukpCywDGDTXRuWAX4G5de8cnebX3pmvPZ4CSPcs0iSsTGERPvH2Iu8BQfc2Wsrsm5n4n7vALwOWcWM3Lr8YzSAETDx4ygPrqq2DFjqbFD3vhgV8ASrNCNC89gxikwq7+xV1ADp3+Ryys2m/zH0BJhlTzYhzPUKqJGeDJEBroAlj9NSx+2OcngPxUiSZlqcAA7XoO4JESWMg1fPURWNzK+AkgdRmiNIH4gE4Nce46EYKzA/62Aj7stECbXwBE0dBAOQTxb6obAPk6yUOCgBu9sE4l/wI551s//xoC8QAAAABJRU5ErkJggg==" align="left" style="padding-right: 20px;padding-bottom:70px"/>' + message;
		return Viewer.ask(message, ['OK', 'Cancel'], function (response) {
			if (response == 'OK') after();
		}, options);
	},

	// asks a question with multiple responses presented as buttons
	// selected item is returned to a callback method.
	// answers may be either an array or a hash. if it's an array, the
	// the callback will received the selected value. if it's a hash,
	// you'll get the corresponding key.
	ask: function (question, answers, callback, options) {
		options = jQuery.extend({modal: true, closeable: false},
			options || {},
			{show: true, unloadOnHide: true});

		var body = jQuery('<div></div>').append(jQuery('<div class="question"></div>').html(question));

		// ick
		var map = {}, answerStrings = [];
		if (answers instanceof Array) {
			for (var i = 0; i < answers.length; i++) {
				if ($.isPlainObject(answers[i])) {
					map[answers[i].label] = answers[i];
				} else {
					map[answers[i]] = answers[i];
				}
				answerStrings.push(answers[i]);
			}
		} else {
			for (var k in answers) {
				map[answers[k]] = k;
				answerStrings.push(answers[k]);
			}
		}

		var buttons = jQuery('<form class="answers"></form>');
		buttons.html(jQuery.map(answerStrings, function (v) {
			if ($.isPlainObject(v)) {
				return "<input type='button' class='btn' title='" + v.title + "' value='" + v.label + "' " + v.state + " />";
			}
			return "<input type='button' class='btn' value='" + v + "' />";
		}).join(' '));

		jQuery('input[type=button]', buttons).click(function () {
			var clicked = this;
			Viewer.get(this).hide(function () {
				if (callback) callback(map[clicked.value]);
			});
		});

		body.append(buttons);

		new Viewer(body, options);

	},

	// returns true if a modal viewer is visible, false otherwise
	isModalVisible: function () {
		return jQuery('.viewer-modal-blackout').length > 0;
	},

	_u: function () {
		for (var i = 0; i < arguments.length; i++)
			if (typeof arguments[i] != 'undefined') return false;
		return true;
	},

	_handleResize: function (evt) {
		var d = jQuery(document);
		jQuery('.viewer-modal-blackout').css('display', 'none').css({
			width: d.width(), height: d.height()
		}).css('display', 'block');
	},

	_handleDrag: function (evt) {
		var d;
		if (d = Viewer.dragging) {
			d[0].viewer.css({left: evt.pageX - d[1], top: evt.pageY - d[2]});
		}
	},

	_nextZ: function () {
		return Boxy.zIndex++;
		// Viewer.zIndex++ will return a lower zindex if there is a boxy dialog open
	},

	_viewport: function () {
		var d = document.documentElement, b = document.body, w = window;
		return jQuery.extend(
			jQuery.browser.msie ?
				{left: b.scrollLeft || d.scrollLeft, top: b.scrollTop || d.scrollTop} :
				{left: w.pageXOffset, top: w.pageYOffset},
			!Viewer._u(w.innerWidth) ?
				{width: w.innerWidth, height: w.innerHeight} :
				(!Viewer._u(d) && !Viewer._u(d.clientWidth) && d.clientWidth != 0 ?
					{width: d.clientWidth, height: d.clientHeight} :
					{width: b.clientWidth, height: b.clientHeight}));
	}

});

Viewer.prototype = {

	// Returns the size of this viewer instance without displaying it.
	// Do not use this method if viewer is already visible, use getSize() instead.
	estimateSize: function () {
		this.viewer.css({visibility: 'hidden', display: 'block'});
		var dims = this.getSize();
		this.viewer.css('display', 'none').css('visibility', 'visible');
		return dims;
	},

	// Returns the dimensions of the entire viewer dialog as [width,height]
	getSize: function () {
		return [this.viewer.width(), this.viewer.height()];
	},

	// Returns the dimensions of the content region as [width,height]
	getContentSize: function () {
		var c = this.getContent();
		return [c.width(), c.height()];
	},

	// Returns the position of this dialog as [x,y]
	getPosition: function () {
		var b = this.viewer[0];
		return [b.offsetLeft, b.offsetTop];
	},

	// Returns the center point of this dialog as [x,y]
	getCenter: function () {
		var p = this.getPosition();
		var s = this.getSize();
		return [Math.floor(p[0] + s[0] / 2), Math.floor(p[1] + s[1] / 2)];
	},

	// Returns a jQuery object wrapping the inner viewer region.
	// Not much reason to use this, you're probably more interested in getContent()
	getInner: function () {
		return jQuery('.viewer-inner', this.viewer);
	},

	// Returns a jQuery object wrapping the viewer content region.
	// This is the user-editable content area (i.e. excludes titlebar)
	getContent: function () {
		return jQuery('.viewer-content', this.viewer);
	},

	// Replace dialog content
	setContent: function (newContent) {
		newContent = jQuery(newContent).css({display: 'block'}).addClass('viewer-content');
		if (this.options.clone) newContent = newContent.clone(true);
		this.getContent().remove();
		this.getInner().append(newContent);
		this._setupDefaultBehaviours(newContent);
		this.options.behaviours.call(this, newContent);
		return this;
	},

	// Move this dialog to some position, funnily enough
	moveTo: function (x, y) {
		this.moveToX(x).moveToY(y);
		return this;
	},

	// Move this dialog (x-coord only)
	moveToX: function (x) {
		if (typeof x == 'number') this.viewer.css({left: x});
		else this.centerX();
		return this;
	},

	// Move this dialog (y-coord only)
	moveToY: function (y) {
		if (typeof y == 'number') this.viewer.css({top: y});
		else this.centerY();
		return this;
	},

	// Move this dialog so that it is centered at (x,y)
	centerAt: function (x, y) {
		var s = this[this.visible ? 'getSize' : 'estimateSize']();
		if (typeof x == 'number') this.moveToX(x - s[0] / 2);
		if (typeof y == 'number') this.moveToY(y - s[1] / 2);
		return this;
	},

	centerAtX: function (x) {
		return this.centerAt(x, null);
	},

	centerAtY: function (y) {
		return this.centerAt(null, y);
	},

	// Center this dialog in the viewport
	// axis is optional, can be 'x', 'y'.
	center: function (axis) {
		var v = Viewer._viewport();
		var o = this.options.fixed ? [0, 0] : [v.left, v.top];
		if (!axis || axis == 'x') this.centerAt(o[0] + v.width / 2, null);
		if (!axis || axis == 'y') this.centerAt(null, o[1] + v.height / 2);
		return this;
	},

	// Center this dialog in the viewport (x-coord only)
	centerX: function () {
		return this.center('x');
	},

	// Center this dialog in the viewport (y-coord only)
	centerY: function () {
		return this.center('y');
	},

	// Resize the content region to a specific size
	resize: function (width, height, after) {
		if (!this.visible) return;
		var bounds = this._getBoundsForResize(width, height);
		this.viewer.css({left: bounds[0], top: bounds[1]});
		this.getContent().css({width: bounds[2], height: bounds[3]});
		if (after) after(this);
		return this;
	},

	// Tween the content region to a specific size
	tween: function (width, height, after) {
		if (!this.visible) return;
		var bounds = this._getBoundsForResize(width, height);
		var self = this;
		this.viewer.stop().animate({left: bounds[0], top: bounds[1]});
		this.getContent().stop().animate({width: bounds[2], height: bounds[3]}, function () {
			if (after) after(self);
		});
		return this;
	},

	// Returns true if this dialog is visible, false otherwise
	isVisible: function () {
		return this.visible;
	},

	// Make this viewer instance visible
	show: function () {
		if (this.visible) return;
		if (this.options.modal) {
			var self = this;
			if (!Viewer.resizeConfigured) {
				Viewer.resizeConfigured = true;
				jQuery(window).resize(function () {
					Viewer._handleResize();
				});
			}
			this.modalBlackout = jQuery('<div class="viewer-modal-blackout"></div>')
				.css({
					zIndex: Viewer._nextZ(),
					//opacity: 0.5,//0.7
					width: (jQuery.browser.msie) ? jQuery(document).width() - 17 : jQuery(document).width(),//modified for ie-it adds extra horizontal scrollbars whn viewer shows
					height: jQuery(document).height()
				})
				.appendTo(document.body);
			this.toTop();
			if (this.options.closeable) {
				jQuery(document.body).bind('keydown.viewer', function (evt) {
					var key = evt.which || evt.keyCode;
					if (key == 27) {
						self.hide();
						jQuery(document.body).unbind('keydown.viewer');
					}
				});
			}
		}

		if ($('.viewer-wrapper:last select').length > 0) {
			$('.viewer-wrapper:last select:not([name*="DataTables_Table_"])').each(function () {
				$(this).select2({
					width: '100%',
					allowClear: true,
					placeholder: $(this).attr("placeholder")
				});
			});
		}

		this.viewer.stop().css({opacity: 1}).show();
		this.visible = true;
		this.moveToY(0);
		this._fire('afterShow');

		return this;
	},

	// Hide this viewer instance
	hide: function (after) {
		if (!this.visible) return;
		var self = this;
		if (this.options.modal) {
			jQuery(document.body).unbind('keydown.viewer');
			this.modalBlackout.animate({opacity: 0}, function () {
				jQuery(this).remove();
			});
		}
		this.viewer.stop().animate({opacity: 0}, 300, function () {
			self.viewer.css({display: 'none'});
			self.visible = false;
			self._fire('afterHide');
			if (after) after(self);
			if (self.options.unloadOnHide) self.unload();
		});
		return this;
	},


	toggle: function () {
		this[this.visible ? 'hide' : 'show']();
		return this;
	},

	hideAndUnload: function (after) {
		this.options.unloadOnHide = true;
		this.hide(after);
		return this;
	},

	unload: function () {
		this._fire('beforeUnload');
		$(this.viewer).find('select').each(function () {
			$(this).select2("destroy");
		});
		//todo: destroy any datepicker. there doesn't seem to exist a method there
		this.viewer.remove();
		if (this.options.actuator) {
			jQuery.data(this.options.actuator, 'active.viewer', false);
		}
	},

	// Move this dialog box above all other viewer instances
	toTop: function () {
		this.viewer.css({zIndex: Viewer._nextZ()});
		return this;
	},

	reload: function (address, options) {
		options = options || {};

		this.address = url;

		var ajax = {
			url: address, type: 'GET', dataType: 'html', cache: false, success: function (html) {
				html = jQuery(html);
				if (options.filter) html = jQuery(options.filter, html);
				new Viewer(html, options);
			},
			error: function () {
				//$("#content_loader", parent.document.body).fadeOut('fast');
				Viewer.alert('Sorry, we couldn\'t load the resource. Verify you are connected and try again. If the problem persists, contact <a href="/">help</a>');
			},
			beforeSend: function (s) {
				//$("#content_loader", parent.document.body).fadeIn('fast');
			}
		};

		jQuery.each(['type', 'cache'], function () {
			if (this in options) {
				ajax[this] = options[this];
				delete options[this];
			}
		});
		jQuery.ajax(ajax);
	},
	// Returns the title of this dialog
	getTitle: function () {
		return jQuery('> .title-bar h2', this.getInner()).html();
	},

	// Sets the title of this dialog
	setTitle: function (t) {
		jQuery('> .title-bar h2', this.getInner()).html(t);
		return this;
	},

	//
	// Don't touch these privates

	_getBoundsForResize: function (width, height) {
		var csize = this.getContentSize();
		var delta = [width - csize[0], height - csize[1]];
		var p = this.getPosition();
		return [Math.max(p[0] - delta[0] / 2, 0),
			Math.max(p[1] - delta[1] / 2, 0), width, height];
	},

	_setupTitleBar: function () {
		if (this.options.title) {
			var self = this;
			//var tb = jQuery("<div class='title-bar'></div>").html("<h2><i class='icon-list-alt' style='font-size: 1.2em'></i> " + this.options.title + "</h2>");
			var tb = jQuery("<div class='title-bar'></div>").html("");
			//if (this.options.closeable) {
			tb.append(jQuery("<a href='#' class='close' title='Press ESC to close'></a>").html(this.options.closeText));
			//}
			if (this.options.draggable) {
				tb[0].onselectstart = function () {
					return false;
				}
				tb[0].unselectable = 'on';
				tb[0].style.MozUserSelect = 'none';
				if (!Viewer.dragConfigured) {
					jQuery(document).mousemove(Viewer._handleDrag);
					Viewer.dragConfigured = true;
				}
				tb.mousedown(function (evt) {
					self.toTop();
					Viewer.dragging = [self, evt.pageX - self.viewer[0].offsetLeft, evt.pageY - self.viewer[0].offsetTop];
					jQuery(this).addClass('dragging');
				}).mouseup(function () {
					jQuery(this).removeClass('dragging');
					Viewer.dragging = null;
					self._fire('afterDrop');
				});
			}
			this.getInner().prepend(tb);
			this._setupDefaultBehaviours(tb);
		}
	},

	_setupDefaultBehaviours: function (root) {
		var self = this;
		if (this.options.clickToFront) {
			root.click(function () {
				self.toTop();
			});
		}
		jQuery('.close', root).click(function () {
			self.hide();
			return false;
		}).mousedown(function (evt) {
			evt.stopPropagation();
		});
	},

	_fire: function (event) {
		this.options[event].call(this);
	}
};