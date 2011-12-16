(function(jQuery){
	jQuery.fn.imageCropper = function(options) {
		var defaults = {
			'field': null,
			'uploader': null,
			'path': '',
			'url': ''
		};
		var options = jQuery.extend(defaults, options);
		return this.each(function() {
			var element = jQuery(this);
			var scale = 1;
			var aspectRatio = options.aspectRatio;
			var large = element.find('.large img');
			var original = large.attr('src');
			var cropper = jQuery.Jcrop(large);
			var maxWidth = options.maxWidth;
			var maxHeight = options.maxHeight;
			var thumbnail = element.find('.preview img');
			var thumbWidth = element.find('.preview .img-div').width();
			var thumbHeight = element.find('.preview .img-div').height();
			var button = element.find('.button button.crop');
			var reset = element.find('.button button.reset');
			var cropData;

			function makeCropper() {
				if (cropper) {
					cropper.destroy();
				};
				cropper = jQuery.Jcrop(large);
				cropper.setOptions({
					"aspectRatio": aspectRatio,
					"bgOpacity": .3,
					"onChange": function(coordinates) {
						var scaleX = large.width() / coordinates.w;
						var scaleY = large.height() / coordinates.h;
						var newWidth = scaleX * options.previewWidth;
						var newHeight = scaleY * options.previewHeight;
						var offsetRatioX = coordinates.x / large.width();
						var offsetRatioY = coordinates.y / large.height();
						var newOffsetX = offsetRatioX * newWidth;
						var newOffsetY = offsetRatioY * newHeight;
						thumbnail.css({
							"width": Math.round(newWidth) + "px",
							"height": Math.round(newHeight) + "px",
							"marginLeft": "-" + Math.round(newOffsetX) + "px",
							"marginTop": "-" + Math.round(newOffsetY) + "px"
						});
						coordinates.scale = scale;
						coordinates.x *= scale;
						coordinates.y *= scale;
						coordinates.w *= scale;
						coordinates.h *= scale;
						coordinates.x2 *= scale;
						coordinates.y2 *= scale;
						cropData = coordinates;
						button.show();
						reset.show();
					}
				});
			};

			function adjustScale() {
				if (large.width() > maxWidth) {
					scale = large.width() / maxWidth;
				} else {
					scale = 1;
				};
				large.css({
					"max-width": maxWidth + 'px'
				});
				thumbnail.css({
					"width": options.previewWidth + 'px',
					"height": (options.previewHeight * (large.height() / large.width())) + 'px',
					"marginLeft": '0px',
					"marginTop": '0px'
				});
			};

			function updateField() {
				if (options.field) {
					options.field.val(large.attr('src').replace(options.path, ''));
				};
			};

			if (options.uploader) {
				options.uploader.bind('FileUploaded', function(up, file, info) {
					original = options.path + file.name;
					large.attr('src', options.path + file.name);
					thumbnail.attr('src', options.path + file.name);
					element.show();
					thumbnail.show();
				});
			};
			if (large.hasClass('placeholder')) {
				element.hide();
				thumbnail.hide();
			};

			large.load(function() {
				large.css({width: 'auto', height: 'auto', maxWidth: 'auto'});
				setTimeout(function() {
					adjustScale();
					makeCropper();
				}, 0);
			});

			reset.hide();
			reset.click(function(event) {
				large.attr('src', original);
				thumbnail.attr('src', original);
				updateField();
			});

			button.hide();
			button.click(function(event) {
				jQuery.ajax({
					url: options.url,
					type: 'post',
					data: {
						'imageFile': large.attr('src'),
						'cropData': cropData
					},
					complete: function(request) {
						var src = request.responseText;
						large.css({width: 'auto', height: 'auto', maxWidth: 'auto'});
						large.attr('src', options.path + src);
						thumbnail.attr('src', options.path + src);
						updateField();
					}
				})
				event.cancelled = true;
				event.stopPropagation();
			});
		});
	};
})(jQuery);


