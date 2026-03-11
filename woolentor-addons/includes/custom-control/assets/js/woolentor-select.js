(function ($) {
    'use strict';

    // Cache: stores fetched titles by ID so we never re-fetch
    var titlesCache = {};

    var controlView = elementor.modules.controls.BaseData.extend({

        onReady: function () {
            var self = this;
            var $select = this.ui.select;
            var isAjax = $select.data('ajax-search');
            var postType = $select.data('post-type') || 'product';
            var isMultiple = $select.prop('multiple');
            var customPlaceholder = $select.data('placeholder');

            if (isAjax) {
                var defaultPlaceholder = customPlaceholder || 'Search...';
                this._initAjaxSelect2($select, postType, isMultiple, defaultPlaceholder);
                this._loadSavedTitles($select, postType);
            } else {
                $select.select2({
                    width: '100%',
                    multiple: isMultiple,
                    allowClear: true,
                    placeholder: customPlaceholder || 'Select'
                });
            }

            $select.on('change', function () {
                self.saveValue();
            });
        },

        _initAjaxSelect2: function ($select, postType, isMultiple, placeholder) {
            $select.select2({
                width: '100%',
                multiple: isMultiple,
                allowClear: true,
                placeholder: placeholder,
                minimumInputLength: 3,
                ajax: {
                    url: woolentorSelectControl.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    delay: 300,
                    cache: true,
                    data: function (params) {
                        return {
                            action: 'woolentor_select_search',
                            s: params.term,
                            post_type: postType,
                            nonce: woolentorSelectControl.nonce
                        };
                    },
                    processResults: function (response) {
                        if (response.success && response.data && response.data.results) {
                            // Cache each result
                            $.each(response.data.results, function (i, item) {
                                titlesCache[item.id] = item.text;
                            });
                            return { results: response.data.results };
                        }
                        return { results: [] };
                    }
                }
            });
        },

        _loadSavedTitles: function ($select, postType) {
            var currentValue = this.getControlValue();
            if (!currentValue || (Array.isArray(currentValue) && currentValue.length === 0) || currentValue === '') {
                return;
            }

            var ids = Array.isArray(currentValue) ? currentValue : [currentValue];

            // Split IDs into cached and uncached
            var uncachedIds = [];
            $.each(ids, function (i, id) {
                if (titlesCache[id]) {
                    // Already cached — append option directly
                    if ($select.find('option[value="' + id + '"]').length === 0) {
                        var option = new Option(titlesCache[id], id, true, true);
                        $select.append(option);
                    }
                } else {
                    uncachedIds.push(id);
                }
            });

            // If all IDs were cached, just refresh Select2
            if (uncachedIds.length === 0) {
                $select.trigger('change.select2');
                return;
            }

            // Fetch only uncached IDs
            $.ajax({
                url: woolentorSelectControl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'woolentor_select_get_titles',
                    ids: uncachedIds,
                    post_type: postType,
                    nonce: woolentorSelectControl.nonce
                },
                success: function (response) {
                    if (response.success && response.data && response.data.results) {
                        $.each(response.data.results, function (index, item) {
                            // Store in cache
                            titlesCache[item.id] = item.text;
                            if ($select.find('option[value="' + item.id + '"]').length === 0) {
                                var option = new Option(item.text, item.id, true, true);
                                $select.append(option);
                            }
                        });
                        $select.trigger('change.select2');
                    }
                }
            });
        },

        saveValue: function () {
            var value = this.ui.select.val();
            this.setValue(value);
        },

        onBeforeDestroy: function () {
            if (this.ui.select.data('select2')) {
                this.ui.select.select2('destroy');
            }
        },

        ui: function () {
            return {
                select: 'select.woolentor-select-control'
            };
        }
    });

    elementor.addControlView('woolentor-select', controlView);

})(jQuery);
