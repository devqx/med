/**
 * Created by robot on 12/7/15.
 */
$.fn.serializeObject = function () {
	var o = {};
	var a = this.serializeArray();
	$.each(a, function () {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};

function ucwords(str) {
	return str.charAt(0).toUpperCase() + str.substring(1, str.length).toLowerCase();
}

function trim(str) {
	return str.replace(/^\s+|\s+$/g, "");
}

function implode(glue, pieces) {
	//  discuss at: http://phpjs.org/functions/implode/
	// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// improved by: Waldo Malqui Silva
	// improved by: Itsacon (http://www.itsacon.net/)
	// bugfixed by: Brett Zamir (http://brett-zamir.me)
	//   example 1: implode(' ', ['Kevin', 'van', 'Zonneveld']);
	//   returns 1: 'Kevin van Zonneveld'
	//   example 2: implode(' ', {first:'Kevin', last: 'van Zonneveld'});
	//   returns 2: 'Kevin van Zonneveld'

	var i = '',
		retVal = '',
		tGlue = '';
	if (arguments.length === 1) {
		pieces = glue;
		glue = '';
	}
	if (typeof pieces === 'object') {
		if (Object.prototype.toString.call(pieces) === '[object Array]') {
			return pieces.join(glue);
		}
		for (i in pieces) {
			retVal += tGlue + pieces[i];
			tGlue = glue;
		}
		return retVal;
	}
	return pieces;
}

function in_array(needle, haystack, argStrict) {
	//  discuss at: http://phpjs.org/functions/in_array/
	// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// improved by: vlado houba
	// improved by: Jonas Sciangula Street (Joni2Back)
	//    input by: Billy
	// bugfixed by: Brett Zamir (http://brett-zamir.me)
	//   example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']); returns 1: true
	//   example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'}); returns 2: false
	//   example 3: in_array(1, ['1', '2', '3']); example 3: in_array(1, ['1', '2', '3'], false); returns 3: true returns 3: true
	//   example 4: in_array(1, ['1', '2', '3'], true); returns 4: false

	var key = '',
		strict = !!argStrict;

	//we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
	//in just one for, in order to improve the performance
	//deciding wich type of comparation will do before walk array
	if (strict) {
		for (key in haystack) {
			if (haystack[key] === needle) {
				return true;
			}
		}
	} else {
		for (key in haystack) {
			if (haystack[key] == needle) {
				return true;
			}
		}
	}

	return false;
}

function showInsuranceNotice(pid, evt) {
	if (pid !== "") {
		$.post('/api/get_insurance_item_cost.php', {code: evt.added.code, patient_id: pid}, function (response) {
			if (response !== null) {
				evt.added.basePrice = response.selling_price - ((100 - response.co_pay) / 100) * response.selling_price;
				if (response.pay_type === 'insurance' && Boolean(parseInt(response.capitated)) && response.type === "primary") {
					$.notify2(response.item_description + " is capitated", "info");
					// swal("", response.item_description + " is capitated", "info");
				}

				if (response.pay_type === 'insurance' && !Boolean(parseInt(response.capitated)) && response.type === "secondary") {
					$.notify2(response.item_description + " is secondary", "info");
					// swal("", response.item_description + " is secondary", "info");
				}
			} else if (response === null) {
				// total = $('#procedure_id').select2("data").basePrice;
				// else, if null, then item is not covered for patient
				// don`t alert if patient is under self-pay scheme
				$.post('/api/get_patient_slim_insurance.php', {pid: pid}, function (result) {
					if (result.pay_type !== "self") {
						$.notify2("Selected item is not covered for patient,\nDefault price will be charged", "info");
						// swal("", "Selected item is not covered for patient,\nDefault price will be charged", "info");
					}
				}, 'json');
			}
		}, 'json');
	} else {
		// swal("", "Select patient first \nso the actual price can be determined", "warning");
		$.notify2("Select Patient first", "warn");// \nso the actual price can be determined
		$(evt.currentTarget).select2("val", "");
	}
}

function showPinBox(callbackFn) {
	vex.dialog.prompt({
		message: 'Please enter your User PIN',
		placeholder: 'User PIN Code or Password',
		value: null,
		overlayClosesOnClick: false,
		beforeClose: function (e) {
			e.preventDefault();
		},
		callback: function (value) {
			if (value !== false && value !== '') {
				//do the auth and proceed the action
				$.post('/api/user_pin_validator.php', {pin: value}, function (response) {
					var status = response.split(":");
					if (status[0] === "session" && status[1] === "active") {
						if (typeof callbackFn !== "undefined") {
							callbackFn();
						}
					} else if (status[0] === "error") {
						alert("Invalid PIN/Password");
						//todo prevent the dialog from closing? Forget it for now
					}
				});
			} else {

			}
		}, afterOpen: function ($vexContent) {
			$('.vex-dialog-prompt-input').attr('type', 'password');
			$submit = $($vexContent).find('[type="submit"]');
			$submit.attr('disabled', true);
			$vexContent.find('input').on('input', function () {
				if ($(this).val()) {
					$submit.removeAttr('disabled');
				} else {
					$submit.attr('disabled', true);
				}
			}).trigger('input');
		}
	});
}

function showUserPinBox(userId, callbackFn) {
	vex.dialog.prompt({
		message: 'Please enter the selected User\'s PIN',
		placeholder: 'User PIN Code or Password',
		value: null,
		overlayClosesOnClick: false,
		beforeClose: function (e) {
			e.preventDefault();
		},
		callback: function (value) {
			if (value !== false && value !== '') {
				//do the auth and proceed the action
				$.post('/api/user_pin_validator.php', {pin: value, user_id: userId},
					function (response) {
						var status = response.split(":");
						callbackFn(status);
					});
			} else {
				// alert("Empty PIN/Password");
				callbackFn("empty");
			}
		}, afterOpen: function ($vexContent) {
			$('.vex-dialog-prompt-input').attr('type', 'password');
			$submit = $($vexContent).find('[type="submit"]');
			$submit.attr('disabled', true);
			$vexContent.find('input').on('input', function () {
				if ($(this).val()) {
					$submit.removeAttr('disabled');
				} else {
					$submit.attr('disabled', true);
				}
			}).trigger('input');
		}
	});
}

function witnessUser(e) {
	return $.when(new $.Deferred(function () { // <-- see returning Deferred object
		var self = this;
		showUserPinBox(e.choice.id, function (status) {
			if ((status[0] === "session" || status[0] === "success")/* && status[1] === "active"*/) {
				self.resolve(true);
			} else if (status[0] === "error") {
				self.resolve(false);
			} else {
				self.resolve('false2');
			}
		});
		//return self.promise();
	})).done(function (s) {
		if(s!==true){
			Boxy.warn("Validation failed!", function () {
				new_data = $.grep($(e.target).select2('data'), function (value) {
					return value['id'] !== e.choice.id;
				});
				$(e.target).select2('data', new_data);
			});

		}
	});
}

$(document).ajaxStop(function () {
	$('[data-witnesses]').on("select2-selecting", function (e) {
		if(!e.handled){
			e.handled = true;
			return witnessUser(e);
		}
	})
});
function processFormResponse(response, callback) {
	var data = response.split(':');
	if (data[0] === 'error') {
		Boxy.warn(data[1]);
	} else if (data[0] === 'success') {
		Boxy.get($('.close')).hideAndUnload();
		if (typeof callback === 'function') {
			setTimeout(function () {
				callback();
			}, 10);
		}
	}
}