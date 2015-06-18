define('BrauneDigital/FormUtilityBundle/Components/ImageUploader', [
	'jquery',
    'underscore',
	'backbone',
	'jquery.ui.widget',
	'canvas-to-blob',
	'jquery.iframe-transport',
	'jquery.fileupload',
	'jquery.fileupload-process',
	'jquery.iframe-transport',
	'jquery.fileupload-validate'
], function ($, _, Backbone) {



  var ImageUploader = function(options) {

		var self = this;
		this.options = _.extend({}, options);
		this.$container = $(this.options.selector);
		this.$fileUploadField = this.$container.find('[data-js-select="imageupload"]');
		this.$hiddenField = this.$container.find('[data-js-select="imagehidden"]');
		this.$imageContainer = this.$container.find('[data-js-select="image-container"]');
		this.$imageViews = this.$container.find('[data-js-select="image-view"]');
		this.$progress = this.$container.find('[data-js-select="progress"]');
		this.$progressBar = this.$container.find('[data-js-select="progress"] .progress-bar');
		this.$button = this.$container.find('[data-js-select="upload-button"]')
		this.$flashMessageContainer = this.$container.find('[data-js-select="flash-message-container"]');
		this.collection = new Backbone.Collection();


		Backbone.emulateHTTP = true;


		/**
		 * Media Model
		 */
		var MediaModel = Backbone.Model.extend({
			url: function(type) {
				switch (type) {
					case 'delete':
						return self.options.deleteUrl + '/' + this.get('id')
				}
			}
		});

		this.flashMessage = new Backbone.Model();

		var FlashMessageView = Backbone.View.extend({
			el: this.$flashMessageContainer,
			initialize: function() {
				this.listenTo(this.model, 'change:messages', this.render);
			},
			render: function() {
				var template = _.template(self.$container.find('.flash-message-template').html());
				this.$el.empty().append(template({
					type: 'error',
					messages: this.model.get('messages')
				}));
			}
		});

		this.flashMessageView = new FlashMessageView({
			model: this.flashMessage
		});

		/**
		 * Media View
		 */
		var MediaView = Backbone.View.extend({
			initialize: function(options) {
				this.options = options;
				var view = this;
				if (this.options.initFromMarkup) {
					if (this.model.get('id')) {
						this.addRemoveButton();
					}
				} else {
					this.render();
				}

				this.$el.on('click', '[data-js-select="remove-button"]', function(e) {
					e.preventDefault();
					view.$el.addClass('loading');
					view.model.destroy({
						url: view.model.url('delete'),
						success: function() {
							view.remove();
							if (!self.options.multiple) {
								self.$container.find('[data-js-select="upload-button"] .label').text(self.options.uploadNewLabel);
							}
							self.updateButton();
							self.flashMessageView.$el.empty();
						},
						error: function() {
							view.$el.removeClass('loading');
						}
					});
				});

				this.listenTo(this.model, 'change', this.render);
			},
			addRemoveButton: function() {
				var removeButtonTemplate = _.template(self.$container.find(self.options.removeButtonTemplate).html());
				this.$el.find('[data-js-select="image"]').append(removeButtonTemplate());
			},
			render: function() {
				var template = _.template(self.$container.find(self.options.thumbnailTemplate).html());
				this.$el.append(template({media: this.model.toJSON()}));

				if (!self.options.multiple) {
					self.$container.find('[data-js-select="upload-button"] .label').text(self.options.uploadReplaceLabel);
					this.$el.appendTo(self.$imageContainer.empty());
				} else {
					this.$el.appendTo(self.$imageContainer);
				}

			}
		});

		this.updateButton = function() {
			if (self.options.multiple) {
				if (self.collection.length >= self.options.limit) {
					this.$button.addClass('hide');
				} else {
					this.$button.removeClass('hide');
				}
			}
		}


		/**
		 * Init Views
		 */
		this.initViews = function() {
			this.$imageViews.each(function() {
				var media = new MediaModel({
					id: $(this).data('id')
				});
				new MediaView({
					el: $(this),
					model: media,
					initFromMarkup: true
				});
				self.collection.add(media);
			});
		}

		this.$button.click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			self.$fileUploadField.trigger('click');
		});


		this.initViews();
		this.updateButton();
		this.count = self.collection.length;


		/**
		 * Init Fileupload
		 */
		this.$fileUploadField.fileupload({
			dataType: 'json',
			url: this.options.uploadUrl,
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			maxFileSize: 5000000,
			replaceFileInput: false,
			limitMultiFileUploads: self.options.limit - self.collection.length,
			disableImageResize: /Android(?!.*Chrome)|Opera/
				.test(window.navigator.userAgent),
			done: function (e, data) {
				self.$progress.addClass('hide');
				if (data.result.error) {
					self.flashMessage.set('messages', data.result.messages);
				} else {
					if (self.options.multiple) {
						if (data.result.gallery && data.result.gallery.id) {
							self.options.gallery = data.result.gallery.id;
							self.$hiddenField.val(data.result.gallery.id);
							if (data.result.media) {
								var media = new MediaModel(data.result.media);
								new MediaView({model: media});
								self.collection.add(media);
							}
						}
						self.updateButton();
					} else {
						if (data.result.media.id) {
							var media = new MediaModel(data.result.media);
							new MediaView({model: media});
							self.collection.add(media);
							self.$hiddenField.val(data.result.media.id);
						}
					}
					self.count = self.collection.length;
				}
			}
		}).bind('fileuploadsubmit', function (e, data) {
			self.flashMessageView.$el.empty();
			data.formData = {
				maxWidth: self.options.thumbnailMaxWidth,
				maxHeight: self.options.thumbnailMaxHeight,
				mode: self.options.thumbnailMode,
				limit: self.options.limit,
				multiple: (self.options.multiple) ? 1 : 0,
				gallery: self.options.gallery ? self.options.gallery : 0,
				setOperator: self.options.setOperator ? self.options.setOperator : 0
			}

			if (self.collection.length < self.options.limit) {
				data.abort();
			}
		}).on('fileuploadadd', function (e, data) {
			self.$progress.removeClass('hide');
			self.$fileUploadField.val('');
		}).on('fileuploadprogressall', function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			self.$progressBar.css(
				'width',
				progress + '%'
			);
		}).on('fileuploadprocessfail', function (e, data) {
			self.flashMessage.set('messages', data.messages);
			self.$progress.addClass('hide');
			self.count--;
		});

	}

  return ImageUploader;
});
