define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/modal/modal'
], function ($, ko, Component, modal) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Indexiz_SampleOrder/sample-view-modal',
            productId: null,
            samples: [],
            showFooterPopup: ko.observable(false),
            popupObject: null,
            itemsCount: ko.observable(0),
            firstName: ko.observable(''),
            lastName: ko.observable(''),
            address1: ko.observable(''),
            city: ko.observable(''),
            country: ko.observable(''),
            zip: ko.observable(''),
            telephone: ko.observable(''),
            email: ko.observable(''),
            orderComment: ko.observable(false),
            canRequestSamples: ko.observable(false), // Enable the button only when all fields are valid
        },

        /**
         * Init observable variables
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe([
                    'samples',
                    'productId',
                ]);

            return this;
        },

        initialize: function () {
            //initialize parent Component
            this._super();
            this.samples = ko.observableArray([]);
            this.canShowFooterPopup();
            this.popupObject = ko.observable(false);

            // Trigger canShowFooterPopup() when the 'FREE CUT SAMPLE' button is clicked
            let externalCuttButton = document.getElementById('cutt_sample_button');
            let externalFullButton = document.getElementById('full_sample_button');
            if (externalCuttButton) {
                const self = this;
                externalCuttButton.addEventListener('click', function() {
                    self.addSampleToCart();
                });
            }
            if (externalFullButton) {
                const self = this;
                externalFullButton.addEventListener('click', function() {
                    self.addSampleToCart();
                });
            }

            // Computed function to check if the form is valid
            this.isFormValid = ko.computed(function() {
                const self = this;

                // Check if all required fields have values
                let isValid = self.firstName() && self.lastName() && self.address1() &&
                    self.city() && self.country() && self.zip() &&
                    self.telephone() && self.email();

                // Additionally, check if email is valid
                const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
                isValid = isValid && emailPattern.test(self.email());

                // Update canRequestSamples based on form validity
                self.canRequestSamples(isValid);

                return isValid; // Return the validity status
            }, this);
        },

        // Function to add sample to the cart (via AJAX request)
        addSampleToCart: function() {
            let skuId = $('#cutt_sample_button').data('sku');
            this.productId(skuId);
            $.ajax({
                url: '/indexiz_sampleorder/index/sampleAdd',
                type: 'POST',
                showLoader: true,
                data: {product_id: this.productId._latestValue , sample: true},
                success: function (response) {
                    if (response.success) {
                        $('.messages').html('<div class="message message-success success"><div>' +
                            response.message + '</div></div>');
                        this.showSampleQuoteItems();
                    } else {
                        $('.messages').html('<div class="message message-error error"><div>' +
                            response.message + '</div></div>');
                    }
                }.bind(this),
                error: function (response) {
                    $('.messages').html('<div class="message message-error error"><div>' +
                        'Something went wrong while adding sample item.' + '</div></div>');
                }.bind(this)
            });
        },

        removeSampleFromCart: function(productId) {
            if (productId) {
                $.ajax({
                    url: '/indexiz_sampleorder/index/sampleRemove',
                    type: 'POST',
                    showLoader: true,
                    data: {product_id: productId},
                    success: function (response) {
                        if (response.success) {
                            $('.messages').html('<div class="message message-success success"><div>' +
                                response.message + '</div></div>');
                            this.getSampleItems();
                        } else {
                            $('.messages').html('<div class="message message-error error"><div>' +
                                response.message + '</div></div>');
                        }
                    }.bind(this),
                    error: function (response) {
                        $('.messages').html('<div class="message message-error error"><div>' +
                            'Something went wrong while removing sample item.' + '</div></div>');
                    }.bind(this)
                });
            }
        },

        /**
         * show footer sample popup on button click
         */
        showSampleFooterPopup: function () {
            if (this.itemsCount._latestValue > 0) {
                this.showFooterPopup(true);
                document.getElementById('modal-content-pop-up-1').style.display = 'block';
            }
        },

        /**
         * hide footer sample popup on button click
         */
        hideSampleFooterPopup: function () {
            this.showFooterPopup(false);
        },

        /**
         * close popup
         */
        closeSampleItemsModal: function () {
            this.popupObject.closeModal();
        },

        /**
         * can show footer sample popup on page load
         */
        canShowFooterPopup: function () {
            $.ajax({
                url: '/indexiz_sampleorder/index/sampleItems',
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        this.itemsCount(response.count);
                        if (this.itemsCount._latestValue > 0) {
                            this.showFooterPopup(true);
                            document.getElementById('modal-content-pop-up-1').style.display = 'block';
                        } else {
                            this.showFooterPopup(false);
                        }
                    }
                }.bind(this)
            });
        },

        /**
         * get added sample items
         */
        getSampleItems: function () {
            $.ajax({
                url: '/indexiz_sampleorder/index/sampleItems',
                type: 'GET',
                showLoader: true,
                success: function (response) {
                    if (response.success) {
                        this.samples(response.items);
                        this.itemsCount(response.count);
                        if (this.itemsCount._latestValue === 0) {
                            this.popupObject.closeModal();
                            this.showFooterPopup(false);
                        }
                    } else {
                        $('.messages').html('<div class="message message-error error"><div>' +
                            response.message + '</div></div>');
                    }
                }.bind(this)
            });
        },

        showSampleQuoteItems: function () {
            this.getSampleItems();

            let options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                modalClass: 'quote-popup',
                title: 'Totally Free  Samples! Add up to 3 Free  Samples to compare at home!',
                buttons: [],
                samples: this.samples._latestValue
            };

            /** @inheritdoc */
            options.closed = function () {
                this.showSampleFooterPopup();
            }.bind(this);

            /** @inheritdoc */
            options.opened = function () {
                this.hideSampleFooterPopup();
            }.bind(this);

            this.popupObject = modal(options, $('.indexiz-sample-view-modal'));
            this.popupObject.openModal();
        },

        // Function that gets triggered on form submission
        submitForm: function () {
            const self = this;

            // Basic validation (you can extend this with more rules)
            if (!self.firstName() || !self.lastName() || !self.address1() || !self.city() ||
                !self.country() || !self.zip() || !self.telephone() || !self.email()) {
                alert("All fields are required.");
                return;
            }

            // Check if the email is valid
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            if (!emailPattern.test(self.email())) {
                alert("Please enter a valid email address.");
                return;
            }

            // If validation passes, proceed with submitting the form (via AJAX or other means)
            let formData = {
                firstName: self.firstName(),
                lastName: self.lastName(),
                address1: self.address1(),
                city: self.city(),
                country: self.country(),
                zip: self.zip(),
                telephone: self.telephone(),
                email: self.email(),
                orderComment: self.orderComment()
            };

            // Send the data via AJAX
            $.ajax({
                url: '/indexiz_sampleorder/index/sampleOrder',
                type: 'POST',
                showLoader: true,
                data: formData,
                success: function(response) {
                    // Handle the response, show success message, reset form, etc.
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        $('.messages').html('<div class="message message-error error"><div>' +
                            response.message + '</div></div>');
                    }
                },
                error: function() {
                    $('.messages').html('<div class="message message-error error"><div>' +
                        'There was an error submitting the form. Please try again.' + '</div></div>');
                }
            });
        },

        // Function to reset the form fields
        resetForm: function () {
            this.firstName('');
            this.lastName('');
            this.address1('');
            this.city('');
            this.country('');
            this.zip('');
            this.telephone('');
            this.email('');
            this.orderComment(false);
            this.canRequestSamples(false);
        }
    });
});