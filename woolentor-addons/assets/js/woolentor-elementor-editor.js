;(function($){

    var wooLentorElementorEditorMode = {

        init: function(){
            // Promosion Widget
            if ( !woolentorSetting.hasPro || !_.isEmpty( woolentorSetting.proWidgets ) ){
                this.addPromutionWidget();
                this.handleDialogBox();
            }
        },

        getWidgetInfo: function( value, key ) {
            let widgetObj = woolentorSetting.proWidgets.find(function (widget, index) {
                if ( widget[key] == value ) return true;
            });
            return widgetObj;
        },

        addPromutionWidget: function(){
            elementor.hooks.addFilter("panel/elements/regionViews", function (panel) {

                var proWidgets = woolentorSetting.proWidgets;

                if ( _.isEmpty( proWidgets ) ) return panel;

                // When Pro is active, only show widgets whose required module is disabled
                if ( woolentorSetting.hasPro ) {
                    proWidgets = proWidgets.filter(function( widget ){
                        return widget.module_disabled === true;
                    });
                    if ( _.isEmpty( proWidgets ) ) return panel;
                }

                let freeCategoryIndex,
                    proCategoryIndex,
                    proCategory     = "woolentor-addons-pro",
                    elementsView    = panel.elements.view,
                    categoriesPannelView  = panel.categories.view,
                    widgets         = panel.elements.options.collection,
                    allCategories   = panel.categories.options.collection,
                    woolentorProcategroy = [];


                    _.each(proWidgets, function (widget, index) {
                        widgets.add({
                            name: widget.name,
                            title: widget.title,
                            icon: widget.icon,
                            categories: [proCategory],
                            editable: !1
                        });
                    });

                    widgets.each(function (widget) {
                        widget.get("categories")[0] === proCategory && woolentorProcategroy.push(widget);
                    });

                    freeCategoryIndex = allCategories.findIndex({
                        name: "woolentor-addons"
                    });

                    proCategoryIndex = allCategories.findIndex({
                        name: "woolentor-addons-pro"
                    });


                    if( proCategoryIndex !== -1 && woolentorSetting.hasPro ){
                        // Pro active: keep category position, just add widgets
                        let existingCategory = allCategories.at( proCategoryIndex );
                        let existingItems = existingCategory.get("items");
                        if ( existingItems && existingItems.length !== undefined ) {
                            _.each(woolentorProcategroy, function( widget ){
                                existingItems.push( widget );
                            });
                        } else {
                            existingCategory.set("items", woolentorProcategroy);
                        }
                    } else {
                        // Pro deactive: remove existing empty category and re-add below free
                        if( proCategoryIndex !== -1 ){
                            allCategories.remove( allCategories.at( proCategoryIndex ) );
                            freeCategoryIndex = allCategories.findIndex({
                                name: "woolentor-addons"
                            });
                        }

                        if( freeCategoryIndex !== -1 ){
                            allCategories.add({
                                name: proCategory,
                                title: wp.i18n.__("ShopLentor Pro",'woolentor'),
                                icon: "woolentor-category-icon",
                                defaultActive: 1,
                                sort: !0,
                                hideIfEmpty: !0,
                                items: woolentorProcategroy,
                                promotion: !1
                            }, { at: freeCategoryIndex + 1 });
                        }
                    }

                return panel;

            });
        },

        handleDialogBox: function(){

            parent.document.addEventListener("mousedown", function (e) {

                let allWidgets = parent.document.querySelectorAll(".elementor-element--promotion");

                if ( allWidgets.length > 0 ) {
                    for ( let i = 0; i < allWidgets.length; i++ ) {
                        if ( allWidgets[i].contains( e.target ) ) {

                            let promotionDialog = parent.document.querySelector("#elementor-element--promotion__dialog"),
                                icon = allWidgets[i].querySelector(".icon > i"),
                                widgetTitleWrap = allWidgets[i].querySelector(".title-wrapper > .title"),
                                widgetTitle = widgetTitleWrap.innerHTML,
                                widgetObject = wooLentorElementorEditorMode.getWidgetInfo(widgetTitle, 'title'),
                                isModuleDependent = woolentorSetting.hasPro && widgetObject && widgetObject.module_disabled,
                                actionURL = isModuleDependent ? woolentorSetting.settingsURL : widgetObject?.action_url,
                                buttonLabel = isModuleDependent ? wp.i18n.__('Enable Module', 'woolentor') : wp.i18n.__('Upgrade Now', 'woolentor'),
                                widgetDescription = isModuleDependent && widgetObject.module_description
                                    ? widgetObject.module_description
                                    : ( widgetObject?.description ? wp.i18n.sprintf( widgetObject.description, widgetTitle ) : wp.i18n.sprintf( wp.i18n.__('Use %s widget and dozens more pro features to extend your toolbox and build sites faster and better.', 'woolentor'), widgetTitle ) );


                            if ( icon.classList.contains('woolentor-pro-promotion') ) {

                                promotionDialog.classList.add('woolentor-pro-widget');
                                promotionDialog.querySelector(".dialog-buttons-message").innerHTML = widgetDescription;

                                if (promotionDialog.querySelector("a.woolentor-pro-dialog-button-action") === null) {

                                    let buttonElement = document.createElement("a"),
                                        buttonText = document.createTextNode( buttonLabel );

                                    buttonElement.classList.add(
                                        "dialog-button",
                                        "dialog-action",
                                        "dialog-buttons-action",
                                        "elementor-button",
                                        "woolentor-pro-dialog-button-action"
                                    );

                                    buttonElement.setAttribute("href", actionURL);
                                    buttonElement.setAttribute("target", isModuleDependent ? "_self" : "_blank");
                                    buttonElement.appendChild(buttonText);

                                    promotionDialog.querySelector(".dialog-buttons-action").insertAdjacentHTML("afterend", buttonElement.outerHTML);
                                    promotionDialog.querySelector(".woolentor-pro-dialog-button-action").style.backgroundColor = "#93003f";
                                    promotionDialog.querySelector(".woolentor-pro-dialog-button-action").style.textAlign = "center";
                                    promotionDialog.querySelector(".elementor-button.go-pro.dialog-buttons-action").classList.add('woolentor-elementor-pro-hide');

                                } else {
                                    let existingButton = promotionDialog.querySelector(".woolentor-pro-dialog-button-action");
                                    existingButton.setAttribute("href", actionURL);
                                    existingButton.setAttribute("target", isModuleDependent ? "_self" : "_blank");
                                    existingButton.textContent = buttonLabel;
                                    existingButton.style.textAlign = "center";
                                    promotionDialog.querySelector(".elementor-button.go-pro.dialog-buttons-action").classList.add('woolentor-elementor-pro-hide');
                                }
                            } else {
                                promotionDialog?.classList.remove('woolentor-pro-widget');
                                if ( promotionDialog.querySelector(".woolentor-pro-dialog-button-action") !== null ) {
                                    promotionDialog.querySelector(".woolentor-pro-dialog-button-action").style.display = "none";
                                }
                            }
                            // Break The loop if target element has found
                            break;
                        }
                    }
                }


            });
        },

    };

    wooLentorElementorEditorMode.init();

})(jQuery);
