;(function ( $, winelementor ) {
    window.woolentor = window.woolentor || {};

    var moduleExp = {
        Views: {},
        Models: {},
        Collections: {},
        Behaviors: {},
        Layout: null,
        Manager: null
    };

    // Current template type from PHP (e.g. "shop", "single", "cart", etc.)
    var currentTemplateType = WOOLENTORTMPL.templateType || '';

    // Default active tab: if editing a specific template type, default to that type's tab
    var activeTabFilter = currentTemplateType || 'all';

    // Mapping: templateType → shareId for tab filtering
    // These are WooCommerce-specific template categories
    var templateTypeToShareId = {
        'shop':       wp.i18n.__( 'Shop', 'woolentor' ),
        'single':     wp.i18n.__( 'Product Details', 'woolentor' ),
        'cart':       wp.i18n.__( 'Cart', 'woolentor' ),
        'checkout':   wp.i18n.__( 'Checkout Page', 'woolentor' ),
        'myaccount':  wp.i18n.__( 'My Account', 'woolentor' ),
        'thankyou':   wp.i18n.__( 'Thank You', 'woolentor' ),
        'popup':      wp.i18n.__( 'Popup Builder', 'woolentor' )
    };

    // Global shareIds - always shown regardless of template type
    var globalShareIds = [
        wp.i18n.__( 'Others', 'woolentor' ),
        wp.i18n.__( 'Home', 'woolentor' ),
        wp.i18n.__( 'Sales Notification', 'woolentor' )
    ];

    // WooCommerce-specific shareIds - only shown in their specific template context
    var wooSpecificShareIds = [
        wp.i18n.__( 'Shop', 'woolentor' ),
        wp.i18n.__( 'Product Details', 'woolentor' ),
        wp.i18n.__( 'Cart', 'woolentor' ),
        wp.i18n.__( 'Checkout Page', 'woolentor' ),
        wp.i18n.__( 'My Account', 'woolentor' ),
        wp.i18n.__( 'Thank You', 'woolentor' ),
        wp.i18n.__( 'Popup Builder', 'woolentor' )
    ];

    moduleExp.Models.Template = Backbone.Model.extend(
        {
            defaults: {
                template_id: 0,
                title: '',
                type: '',
                thumbnail: '',
                url: '',
                tags: [],
                isPro: false,
                shareId: ''
            }
        }
    );

    moduleExp.Collections.Template = Backbone.Collection.extend(
        {
            model: moduleExp.Models.Template
        }
    );

    moduleExp.Views.Logo = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-logo",
            className: "woolentor_templateLibrary_logo",
            templateHelpers: function () {
                return { title: this.getOption("title") };
            },
        }
    );

    moduleExp.Views.Actions = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-header-actions",
            id: "elementor-template-library-header-actions",
            ui: { sync: "#woolentor-template-library-header-sync i" },
            events: function () {
                return { click: "onClick" };
            },
            onClick: function () {
                var self = this;
                self.ui.sync.addClass("eicon-animation-spin"),
                woolentor.library.getLibraryData({
                    onUpdate: function () {
                        self.ui.sync.removeClass("eicon-animation-spin"), woolentor.library.updateBlocksView();
                    },
                    forceUpdate: true,
                    forceSync: true,
                });
            },
        }
    );

    moduleExp.Views.Menu = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-header-menu",
            id: "elementor-template-library-header-menu",
            className: "woolentor_templateLibrary_header_menu",
            ui: {
                items: "> .elementor-component-tab"
            },
            events: {
                "click @ui.items": "onTabItemClick"
            },
            onTabItemClick: function (event) {
                var currentTab = $( event.currentTarget ),
                    tabValue = currentTab.data("tab");
                activeTabFilter = tabValue;
                // Clear category filter silently, then set type filter (triggers re-render)
                woolentor.library.setFilter("category", "", true);
                woolentor.library.setFilter("type", tabValue);
                currentTab.addClass("elementor-active").siblings().removeClass("elementor-active");
                woolentor.library.updateCategoryFilter(activeTabFilter);
            },
            templateHelpers: function () {
                woolentor.library.setFilter("type", activeTabFilter);
                return woolentor.library.getTabs();
            },
        }
    );

    moduleExp.Views.ResponsiveMenu = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-header-menu-responsive",
            id: "elementor-template-library-header-menu-responsive",
            className: "woolentor-template-library-header-menu-responsive",
            ui: { items: "> .elementor-component-tab" },
            events: { "click @ui.items": "onTabItemClick" },
            onTabItemClick: function (event) {
                var $tab = $(event.currentTarget),
                    device = $tab.data("tab");
                woolentor.library.channels.tabs.trigger("change:device", device, $tab);
            }
        }
    );

    moduleExp.Views.BackButton = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-header-back",
            id: "elementor-template-library-header-preview-back",
            className: "woolentor_templateLibrary_back",
            events: function () {
                return { click: "onClick" };
            },
            onClick: function () {
                woolentor.library.showBlocksView();
                $('[data-tab="'+activeTabFilter+'"]').addClass("elementor-active").siblings().removeClass("elementor-active");
            },
        }
    );

    moduleExp.Behaviors.InsertTemplate = Marionette.Behavior.extend(
        {
            ui: {
                insertButton: ".woolentor-template-library-template-insert"
            },
            events: {
                "click @ui.insertButton": "onInsertButtonClick"
            },
            onInsertButtonClick: function () {
                woolentor.library.insertTemplate( { model: this.view.model } );
            },
        }
    );

    moduleExp.Views.EmptyTemplateCollection = Marionette.ItemView.extend(
        {
            id: "elementor-template-library-templates-empty",
            template: "#tmpl-elementor-woolentor-library-templates-empty",
            ui: {
                title: ".elementor-template-library-blank-title",
                message: ".elementor-template-library-blank-message"
            },
            modesStrings: {
                empty: {
                    title: wp.i18n.__( 'No Templates Found', 'woolentor' ),
                    message: wp.i18n.__( 'Try different category or sync for new templates.', 'woolentor' )
                },
                noResults: {
                    title: wp.i18n.__( 'No Results Found', 'woolentor' ),
                    message: wp.i18n.__( 'Please make sure your search is spelled correctly or try a different words.', 'woolentor' )
                },
            },
            getCurrentMode: function () {
                return woolentor.library.getFilter("text") ? "noResults" : "empty";
            },
            onRender: function () {
                var modeStrings = this.modesStrings[this.getCurrentMode()];
                this.ui.title.html(modeStrings.title), this.ui.message.html(modeStrings.message);
            },
        }
    );

    moduleExp.Views.TemplateCollection = Marionette.CompositeView.extend(
        {
            template: "#tmpl-woolentor-template-library-templates",
            id: "woolentor_template_library_templates",
            childViewContainer: "#woolentor-template-library-list",
            emptyView: function () {
                return new moduleExp.Views.EmptyTemplateCollection();
            },
            ui:{
                textFilter: "#woolentor-template-library-filter-text",
                categoryFilter: "#elementor-template-library-filter-category"
            },
            events:{
                "input @ui.textFilter": "onTextFilterInput",
                "change @ui.categoryFilter": "onCategoryFilterChange",
            },
            getChildView: function () {
                return moduleExp.Views.Template;
            },
            initialize: function () {
                this.listenTo(woolentor.library.channels.templates, "filter:change", this._renderChildren);
                this.listenTo(this.collection, "reset", this.onCollectionReset);
                setTimeout(function() {
                    try {
                        var $categorySelect = $('#elementor-template-library-filter-category');
                        if ($.fn.select2) {
                            $categorySelect.select2({
                                width: '100%',
                                placeholder: wp.i18n.__( 'Filter by Category', 'woolentor' ),
                                dropdownCssClass: 'elementor-template-library-filter-category',
                                allowClear: true,
                                minimumInputLength: 0,
                                minimumResultsForSearch: 0
                            });
                        }
                        // Restore previously selected category filter
                        var activeCategory = woolentor.library.getFilter("category");
                        if (activeCategory) {
                            $categorySelect.val(activeCategory).trigger('change.select2');
                        }
                    } catch(err) {}
                }, 100);
            },
            filter: function (model) {
                var filterTerms = woolentor.library.getFilterTerms(),
                    isMatch = true;
                return (
                    _.each(filterTerms, function (termConfig, filterKey) {
                        var filterValue = woolentor.library.getFilter(filterKey);
                        if (filterValue && termConfig.callback) {
                            var result = termConfig.callback.call(model, filterValue);
                            return result || (isMatch = false), result;
                        }
                    }),
                    isMatch
                );
            },

            onTextFilterInput: function () {
                var self = this;
                _.defer(function () {
                    woolentor.library.setFilter("text", self.ui.textFilter.val());
                });
            },

            onCategoryFilterChange: function() {
                var category = this.ui.categoryFilter.val();

                if (currentTemplateType) {
                    var typeShareId = templateTypeToShareId[currentTemplateType] || '';

                    if (category && category === typeShareId) {
                        // Selected category matches current type tab - activate type tab
                        activeTabFilter = currentTemplateType;
                    } else {
                        // Global category, different category, or cleared - switch to "All"
                        activeTabFilter = 'all';
                    }

                    // Update tab UI + type filter silently (category filter will trigger change)
                    woolentor.library.setFilter('type', activeTabFilter, true);
                    $('[data-tab="'+activeTabFilter+'"]').addClass("elementor-active").siblings().removeClass("elementor-active");
                }

                woolentor.library.setFilter('category', category);
            },

        }
    );

    moduleExp.Views.Template = Marionette.ItemView.extend(
        {
            template: "#woolentor-template-library-template",
            className: "woolentor_template_library_template",
            ui: {
                previewButton: ".woolentor-template-library-preview-button, .woolentor-template-library-preview"
            },
            events: {
                "click @ui.previewButton": "onPreviewButtonClick"
            },
            behaviors: {
                insertTemplate: { behaviorClass: moduleExp.Behaviors.InsertTemplate }
            },
            onPreviewButtonClick: function () {
                woolentor.library.showPreviewView(this.model);
            },
        }
    );

    moduleExp.Views.Loading = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-loading",
            id: "woolentor_templateLibrary_loading"
        }
    );

    moduleExp.Views.InsertWrapper = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-header-insert",
            id: "elementor-template-library-header-preview",
            behaviors: {
                insertTemplate: { behaviorClass: moduleExp.Behaviors.InsertTemplate }
            },
        }
    );

    moduleExp.Views.Preview = Marionette.ItemView.extend(
        {
            template: "#tmpl-woolentor-template-library-preview",
            className: "woolentor_templateLibrary_preview",
            ui: function () {
                return { iframe: "> iframe" };
            },
            onRender: function () {
                this.ui.iframe.attr("src", this.getOption("url")).hide();
                var self = this,
                    loadingView = new moduleExp.Views.Loading().render();
                this.$el.append(loadingView.el),
                this.ui.iframe.on("load", function () {
                    self.$el.find("#woolentor_templateLibrary_loading").remove(), self.ui.iframe.show();
                });
            },
        }
    );

    moduleExp.Modal = elementorModules.common.views.modal.Layout.extend({

        getModalOptions: function () {
            return {
                id: "woolentor-template-library-modal"
            };
        },

        getLogo: function ( title ) {
            this.getHeaderView().logoArea.show(new moduleExp.Views.Logo(title));
        },

        showDefaultHeader: function () {
            this.getLogo({ title: "SHOPLENTOR LIBRARY" });
            var headerView = this.getHeaderView();
            headerView.menuArea.show( new moduleExp.Views.Menu() ),
            headerView.tools.show( new moduleExp.Views.Actions() );
        },

        getTemplateActionButton: function (templateData) {
            var isPro = templateData.isPro && !WOOLENTORTMPL.hasPro;
            var buttonClass = isPro ? "get-pro-button" : "insert-button";
            var viewId = "#tmpl-woolentor-template-library-" + buttonClass;
            var tmpl = Marionette.TemplateCache.get(viewId);
            return Marionette.Renderer.render(tmpl);
        },

        showPreviewView: function (templateModel) {
            var headerView = this.getHeaderView();
            headerView.logoArea.show(new moduleExp.Views.BackButton()),
            headerView.menuArea.show(new moduleExp.Views.ResponsiveMenu()),
            headerView.tools.show(new moduleExp.Views.InsertWrapper({ model: templateModel })),
            this.modalContent.show(new moduleExp.Views.Preview({ url: templateModel.get("url") }));
        },

        showBlocksView: function (collection) {
            this.modalContent.show(new moduleExp.Views.TemplateCollection({ collection: collection }));
        },
    });

    moduleExp.Manager = function () {
        var modalInstance,
            deviceSizes,
            templateCollection,
            errorDialog,
            manager = this;

        deviceSizes = { desktop: "100%", tab: "768px", mobile: "360px" };

        /**
         * Tracks the section index where the user clicked "add" button,
         * so the imported template gets inserted at that specific position.
         */
        function onAddSectionButtonClick() {
            var $section = $(this).closest(".elementor-top-section"),
                sectionCid = $section.data("model-cid"),
                sections = window.elementor.sections;
            sections.currentView.collection.length &&
                _.each(sections.currentView.collection.models, function (model, index) {
                    sectionCid === model.cid && (manager.atIndex = index);
                }),
                $section.prev(".elementor-add-section").find(FIND_SELECTOR).before(libraryPopupButton);
        }

        /**
         * Injects the ShopLentor library button into the "Add Section" area
         * and binds click event on section settings "add" button.
         */
        function injectLibraryButton($previewContents) {
            var $dragTitle = $previewContents.find(FIND_SELECTOR);
            $dragTitle.length && $dragTitle.before(libraryPopupButton),
            $previewContents.on("click.onAddElement", ".elementor-editor-section-settings .elementor-editor-element-add", onAddSectionButtonClick);
        }

        /**
         * Handles responsive device change in preview mode.
         * Updates the preview iframe width based on device selection.
         */
        function onDeviceChange(device, $tab) {
            $tab.addClass("elementor-active").siblings().removeClass("elementor-active");
            var width = deviceSizes[device] || deviceSizes.desktop;
            $(".woolentor_templateLibrary_preview").css("width", width);
        }

        /**
         * Called when Elementor preview is fully loaded.
         * Sets up the library button injection and event bindings.
         */
        function onPreviewLoaded() {
            var $previewContents = window.elementor.$previewContents,
                pollInterval = setInterval(function () {
                    injectLibraryButton($previewContents),
                    $previewContents.find(".elementor-add-new-section").length > 0 && clearInterval(pollInterval);
                }, 100);

                $previewContents.on("click.onAddTemplateButton", ".elementor-add-woolentor-template-button", manager.showModal.bind(manager));
                this.channels.tabs.on("change:device", onDeviceChange);
        }

        this.updateBlocksView = function () {
            woolentor.library.setFilter("tags", "", !0),
            woolentor.library.setFilter("text", "", !0),
            woolentor.library.getModal(),
            woolentor.library.showBlocksView();
        };

        FIND_SELECTOR = ".elementor-add-new-section .elementor-add-section-drag-title";

        var libraryPopupButton = '<div class="elementor-add-section-area-button elementor-add-woolentor-template-button" title="'+wp.i18n.__( 'ShopLentor Library', 'woolentor' )+'"><img src="' + WOOLENTORTMPL.logo + '" /></div>';

        this.atIndex = -1;

        this.channels = {
            tabs: Backbone.Radio.channel("woolentor-tabs"),
            templates: Backbone.Radio.channel("woolentor-templates")
        };

        this.init = function () {
            winelementor.on("preview:loaded", onPreviewLoaded.bind(this));
        };

        this.showModal = function () {
            manager.getModal().showModal(), manager.showBlocksView();
        };

        this.getModal = function () {
            return modalInstance || (modalInstance = new moduleExp.Modal()), modalInstance;
        };

        this.getTabs = function () {
            var tabs = {
                all: {
                    title: wp.i18n.__( 'All', 'woolentor' ),
                    active: activeTabFilter === 'all'
                }
            };

            // If editing a specific template type, add that type as a tab
            if (currentTemplateType && templateTypeToShareId[currentTemplateType]) {
                tabs[currentTemplateType] = {
                    title: templateTypeToShareId[currentTemplateType],
                    active: activeTabFilter === currentTemplateType
                };
            }

            return { tabs: tabs };
        };

        this.setFilter = function (filterKey, filterValue, silent) {
            manager.channels.templates.reply("filter:" + filterKey, filterValue),
            silent || manager.channels.templates.trigger("filter:change");
        };

        this.getFilter = function (filterKey) {
            return manager.channels.templates.request("filter:" + filterKey);
        };

        this.getFilterTerms = function () {
            return {
                text: {
                    callback: function (searchText) {
                        searchText = searchText.toLowerCase();
                        return (
                            this.get("title").toLowerCase().indexOf(searchText) >= 0 ||
                                _.any(this.get("tags"), function (tag) {
                                    return tag.indexOf(searchText) >= 0;
                                })
                        );
                    },
                },
                type: {
                    callback: function (tabKey) {
                        var templateType = this.get("type").toLowerCase();
                        var shareId = this.get("shareId");
                        var activeCategoryFilter = woolentor.library.getFilter("category");

                        // Always exclude email templates
                        if (templateType.indexOf("email_") !== -1) {
                            return false;
                        }

                        // If a category is selected from dropdown, let category filter handle it
                        // Type filter just needs to allow all visible templates for this context
                        if (activeCategoryFilter) {
                            if (currentTemplateType) {
                                var targetShareId = templateTypeToShareId[currentTemplateType] || '';
                                return shareId === targetShareId || _.contains(globalShareIds, shareId);
                            }
                            return _.contains(globalShareIds, shareId);
                        }

                        // No category filter - apply tab-based filtering
                        if (currentTemplateType) {
                            var targetShareId = templateTypeToShareId[currentTemplateType] || '';

                            if (tabKey === 'all') {
                                // "All" tab: current type's templates + global only
                                if (shareId === targetShareId || _.contains(globalShareIds, shareId)) {
                                    return true;
                                }
                                return false;
                            }
                            // Specific type tab: show only that type's templates
                            return shareId === targetShareId;
                        }

                        // If editing a normal page (no specific template type)
                        if (tabKey === 'all') {
                            return _.contains(globalShareIds, shareId);
                        }

                        return false;
                    },
                },
                category: {
                    callback: function (category) {
                        if (!category) return true;
                        var shareId = this.get("shareId");
                        return shareId === category;
                    }
                }
            };
        };

        this.showBlocksView = function () {
            manager.getModal().showDefaultHeader();
            $('[data-tab="'+activeTabFilter+'"]').addClass("elementor-active").siblings().removeClass("elementor-active");
            manager.setFilter("text", "", true),
            manager.loadTemplates(function () {
                manager.getModal().showBlocksView(templateCollection);
            });
        };

        this.showPreviewView = function (templateModel) {
            manager.getModal().showPreviewView(templateModel);
        };

        this.loadTemplates = function (callback) {
            manager.getLibraryData({
                onBeforeUpdate: manager.getModal().showLoadingView.bind(manager.getModal()),
                onUpdate: function () {
                    manager.getModal().hideLoadingView(), callback && callback();
                },
            });
        };

        this.getLibraryData = function (options) {
            if (templateCollection && !options.forceUpdate) return void (options.onUpdate && options.onUpdate());
            options.onBeforeUpdate && options.onBeforeUpdate();
            var ajaxConfig = {
                data: {},
                success: function (response) {
                    templateCollection = new moduleExp.Collections.Template(response.templates);
                    if (response.tags) { deviceSizes = response.tags; }
                    options.onUpdate && options.onUpdate();
                },
            };
            options.forceSync && (ajaxConfig.data.sync = true),
            elementorCommon.ajax.addRequest("get_woolentor_library_data", ajaxConfig);
        };

        this.getTemplateContent = function (templateId, ajaxOptions) {
            var requestOptions = {
                unique_id: templateId,
                data: {
                    edit_mode: true,
                    display: true,
                    template_id: templateId
                }
            };
            ajaxOptions && jQuery.extend( true, requestOptions, ajaxOptions),
            elementorCommon.ajax.addRequest("get_woolentor_template_data", requestOptions);
        };

        this.insertTemplate = function (args) {
            var templateModel = args.model,
                self = this;
            self.getModal().showLoadingView(),
            self.getTemplateContent(templateModel.get("template_id"), {
                success: function (responseData) {
                    self.getModal().hideLoadingView(),
                    self.getModal().hideModal();
                    var importOptions = {};
                    -1 !== self.atIndex && (importOptions.at = self.atIndex),
                    $e.run(
                        "document/elements/import",
                        {
                            model: templateModel,
                            data: responseData,
                            options: importOptions
                        }
                    ),
                    (self.atIndex = -1);
                },
                error: function (errorData) {
                    self.showErrorDialog(errorData);
                },
                complete: function () {
                    self.getModal().hideLoadingView();
                },
            });
        };

        this.showErrorDialog = function (errorData) {
            if ("object" == typeof errorData) {
                var errorHtml = "";
                _.each(errorData, function (item) {
                    errorHtml += "<div>" + item.message + ".</div>";
                }),
                (errorData = errorHtml);
            } else errorData ? (errorData += ".") : (errorData = "<i>&#60;The error message is empty&#62;</i>");
            manager.getErrorDialog()
                .setMessage('The following error(s) occurred while processing the request:<div id="elementor-template-library-error-info">' + errorData + "</div>")
                .show();
        };

        this.getErrorDialog = function () {
            return errorDialog || (
                errorDialog = elementorCommon.dialogsManager.createWidget(
                    "alert",
                    {
                        id: "elementor-template-library-error-dialog",
                        headerMessage: "An error occurred"
                    }
                )
            ),
            errorDialog;
        };

        this.updateCategoryFilter = function(type) {
            var categories = manager.getCategories(type);
            var categorySelect = $('#elementor-template-library-filter-category');
            categorySelect.empty();

            categorySelect.append('<option value="">All Categories</option>');

            _.each(categories, function(category) {
                categorySelect.append('<option value="' + category + '">' + category + '</option>');
            });

            // Only update select2 visual, don't fire change event
            // (to avoid onCategoryFilterChange overriding the active tab)
            categorySelect.trigger('change.select2');
        };

        this.getCategories = function(tabKey) {
            tabKey = tabKey || 'all';
            if (!templateCollection) return [];
            try {
                // Categories always show all available for this context
                // (current type + globals), regardless of which tab is active
                var filteredModels = _.filter(templateCollection.models, function(model) {
                    var templateType = model.get('type').toLowerCase();
                    var shareId = model.get('shareId');

                    // Exclude email templates
                    if (templateType.indexOf('email_') !== -1) {
                        return false;
                    }

                    if (currentTemplateType) {
                        var typeShareId = templateTypeToShareId[currentTemplateType] || '';
                        return shareId === typeShareId || _.contains(globalShareIds, shareId);
                    }

                    // Normal page editing
                    return _.contains(globalShareIds, shareId);
                });

                var categories = _.uniq(_.compact(_.map(filteredModels, function(model) {
                    return model.get('shareId');
                })));

                return categories;
            } catch (err) {
                return [];
            }
        };

    };

    window.woolentor.library = new moduleExp.Manager();
    window.woolentor.library.init();

})(jQuery, window.elementor);
