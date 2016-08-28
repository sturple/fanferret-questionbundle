define(['jquery'],function ($) {
	return function (name, document) {
		document = $(document);
		var check = document.find('#' + name + ' input[type="checkbox"]');
		var hidden = document.find('#form_' + name);
		var set = function (value) {
			hidden.val(value ? 'true' : '');
			check.prop('checked',value);
		};
		set(check.prop('checked'));
		check.change(function () {
			set(this.checked);
		});
	};
});