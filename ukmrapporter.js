var UKMrapporter = function($) {
    var templateCollection = new Map();
    var emitter = UKMresources.emitter('UKMrapporter');
    //emitter.enableDebug();

    var preventDefault = function(e) {
        if (e !== undefined) {
            e.preventDefault();
        }
    }

    /**
     * Rapport-loader
     * Alt av customization, inkludert hent fra mal, og lagre mal
     */
    var loader = {
        selector: '#reportLoader',
        getId: () => {
            return $(loader.selector).attr('data-id');
        },
        visible: () => {
            $(loader.selector).is(':visible');
        },
        show: (e) => {
            preventDefault(e);
            $(loader.selector).show();
            emitter.emit('loader.show');
        },
        hide: (e) => {
            preventDefault(e);
            $(loader.selector).hide();
            emitter.emit('loader.hide');
        },
        bind: () => {
            $(document).on('click', '.hideReportLoader', loader.hide);
            $(document).on('click', '.showReportLoader', loader.show);

            emitter.on('templatePicker.hide', loader.header.showCustomize);
            emitter.on('templatePicker.show', loader.header.showTemplate);
        },
        header: {
            showCustomize: () => {
                $(loader.selector + ' #templateHeader').hide();
                $(loader.selector + ' #customizeHeader').show();
            },
            showTemplate: () => {
                $(loader.selector + ' #customizeHeader').hide();
                $(loader.selector + ' #templateHeader').show();
            }
        }

    }

    /**
     * Rapport-customizer
     * Faktisk customizer, hvor brukeren velger hvilke felt som skal hentes
     */
    var customizer = {
        selector: '#reportCustomizer',
        show: function(e) {
            preventDefault(e);
            $(customizer.selector).slideDown();
            if (!loader.visible()) {
                loader.show();
            }
        },
        hide: (e) => {
            preventDefault(e);
            $(customizer.selector).hide();
        },
        bind: () => {
            $(document).on('click', '.hideCustomizer', customizer.hide);
            $(document).on('click', '.showCustomizer', customizer.show);
            $(document).on('click', '.saveAsTemplate', customizer.saveAsTemplate);

            emitter.on('templatePicker.hide', customizer.show);
            emitter.on('templatePicker.show', customizer.hide);
        },
        saveAsTemplate: () => {
            templateSaver.setConfig(
                customizer.getConfig()
            );
        },
        reset: () => {
            $(customizer.selector + ' input[type="checkbox"]').prop("checked", false);
            $(customizer.selector + ' input[type="radio"]').prop("checked", false);
        },
        fill: (check) => {
            $.each(check, function(key, value) {
                var input = $(customizer.selector + ' input[name="' + key + '"]');
                switch (input.attr('type')) {
                    case 'hidden':
                        input.val(value);
                        if (input.attr('data-radiobutton')) {
                            $(customizer.selector + ' .radioButtons button[value="' + value + '"]').click();
                        }
                        break;
                    case 'radio':
                        $(customizer.selector + ' input[name="' + key + '"][value="' + value + '"]').prop('checked', true);
                        break;
                    default:
                        input.prop('checked', true);
                        break;
                }
            });
        },
        getConfig: () => {
            return $(customizer.selector + ' form').serialize();
        }
    }

    /**
     * Template-håndtering
     * Håndterer relasjoner mellom GUI og Database
     */
    var templates = {
        array: [],
        load: (rapport_id) => {
            if (rapport_id == undefined) {
                rapport_id = loader.getId();
                if (rapport_id == undefined) {
                    return;
                }
            }
            $.post(ajaxurl, {
                action: 'UKMrapporter_ajax',
                controller: 'listTemplates',
                rapport: rapport_id
            }, (response) => {
                if (response.templates.length > 0) {
                    for (var i = 0; i < response.templates.length; i++) {
                        templates.add(response.templates[i]);
                    }
                }
                // Lag lokal kopi for iterering
                templates.array = [];
                templateCollection.forEach((value, key) => {
                    templates.array.push(value);
                });

                emitter.emit('templates.loaded');
            });
        },
        save: (rapport, template, name, description, config) => {
            $.post(ajaxurl, {
                action: 'UKMrapporter_ajax',
                controller: 'saveTemplate',
                rapport: rapport,
                template_id: template,
                name: name,
                description: description,
                config: config,
            }, (response) => {
                templates.load();
                emitter.emit('template.saved');
            });
        },
        add: (template) => {
            templateCollection.set('template_' + template.id, template);
        },
        get: (template_id) => {
            return templateCollection.get(template_id);
        },
        getAll: () => {
            return templates.array;
        }
    };

    /**
     * Template-saver
     * Skjema for lagring av template
     */
    var templateSaver = {
        selector: '#templateSaver',
        // SKJEMAET
        bind: () => {
            $(document).on(
                'change',
                templateSaver.selector + ' ' + templateSelector.selector,
                templateSaver.selectedTemplate
            );
            $(document).on('click', '.hideTemplateSaver', templateSaver.hide);
            $(document).on('click', '.showTemplateSaver', templateSaver.show);
            $(document).on('click', templateSaver.selector + ' .saveTemplate', templateSaver.save);
            emitter.on('template.saved', templateSaver.status.show);
            emitter.on('template.saved', templateSaver.reset);
            emitter.on('templatePicker.hide', templateSaver.hide);
            emitter.on('templatePicker.hide', templateSaver.status.hide);

            emitter.on('templatePicker.show', templateSaver.hide);

        },
        selectedTemplate: (e) => {
            if ($(e.target).val() == 'new') {
                templateSaver.name.show();
            } else {
                templateSaver.name.hide();
            }
        },
        hide: () => {
            $(templateSaver.selector).hide();
        },
        hideForm: () => {
            $(templateSaver.selector + ' form').slideUp(300);
        },
        showForm: () => {
            $(templateSaver.selector + ' form').show();
        },
        show: () => {
            customizer.hide();
            templateSaver.showForm();
            $(templateSaver.selector).fadeIn(200);
        },
        reset: () => {
            $(templateSaver.getFormSelector()).trigger('reset');
        },
        save: () => {
            templates.save(
                loader.getId(),
                templateSaver.getTemplateId(),
                templateSaver.getName(),
                templateSaver.getDescription(),
                templateSaver.getConfig()
            );
        },
        getFormSelector: () => {
            return templateSaver.selector + ' form';
        },
        getTemplateId: () => {
            return $(templateSaver.getFormSelector() + ' .templateSelector select').val();
        },
        getName: () => {
            return $(templateSaver.getFormSelector() + ' input[name="navn"]').val();
        },
        getDescription: () => {
            return $(templateSaver.getFormSelector() + ' textarea[name="beskrivelse"]').val();
        },
        getConfig: () => {
            return $(templateSaver.getFormSelector() + ' input[name="config"]').val();
        },
        // NAVNE-FELTET
        name: {
            selector: '#templateName',
            show: () => {
                $(templateSaver.selector + ' ' + templateSaver.name.selector).slideDown();
            },
            hide: () => {
                $(templateSaver.selector + ' ' + templateSaver.name.selector).slideUp();
            },
        },
        // STATUS-BOKSEN
        status: {
            selector: '.status',
            show: () => {
                console.log('SHOW ' + templateSaver.selector + ' ' + templateSaver.status.selector);
                templateSaver.hideForm();
                $(templateSaver.selector + ' ' + templateSaver.status.selector).slideDown();
                //templateSaver.status.setTimer();
            },
            hide: () => {
                $(templateSaver.selector + ' ' + templateSaver.status.selector).slideUp();
            },
            setTimer: () => {
                setTimeout(() => {
                        $(templateSaver.selector).fadeOut(200);
                        templateSaver.status.hide();
                        templateSaver
                    },
                    2500
                );
            }
        },
        setConfig: (config) => {
            $(templateSaver.getFormSelector() + ' input[name="config"]').val(config);
            templateSaver.show();
        }
    }

    /**
     * Template-picker
     * Viser alle templates, slik at brukeren kan velge disse
     */
    var templatePicker = {
        selector: '#templatePicker',
        visible: () => {
            return $(templatePicker.selector).is(':visible')
        },
        show: (e) => {
            preventDefault(e);
            loader.show();
            customizer.hide();
            $(templatePicker.selector).slideDown();
            emitter.emit('templatePicker.show');
        },
        hide: (e) => {
            preventDefault(e);
            $(templatePicker.selector).hide();
            emitter.emit('templatePicker.hide');
        },
        loadFromDB: () => {
            templates.load(loader.getId());
        },
        bind: () => {
            $(document).on('click', '.hideTemplatePicker', templatePicker.hide);
            $(document).on('click', '.showTemplatePicker', templatePicker.show);
            $(document).on('click', templatePicker.selector + ' li.template', templatePicker.load);
            emitter.on('templates.loaded', templatePicker.render);
        },
        init: () => {
            templatePicker.loadFromDB();
        },

        render: () => {
            $(templatePicker.selector).html(
                twigJS_templatePicker.render({
                    templates: templates.getAll()
                })
            );
        },
        load: (e) => {
            customizer.reset();
            customizer.fill(
                templateCollection.get(
                    'template_' + $(e.target).attr('data-id')
                ).config
            );
            templatePicker.hide();
        }
    }

    /**
     * Template-selector
     * Viser alle templates i select-liste
     */
    var templateSelector = {
        selector: '.templateSelector',
        render: () => {
            $(templateSelector.selector).html(
                twigJS_templateSelector.render({
                    templates: templates.getAll()
                })
            );
        },
        bind: () => {
            emitter.on('templates.loaded', templateSelector.render);
        },
    }

    /**
     * Generering av rapporter
     */
    var generator = {
        selector: '#reportContainer',
        show: () => {
            loader.hide();
            $(generator.selector).slideDown();
            emitter.emit('generator.show');
            generator.loader.fire();
        },
        hide: () => {
            $(generator.selector).hide();
        },
        bind: () => {
            emitter.on('loader.show', generator.hide);
            $(document).on('click', '.generateReport', generator.show);
        },
        actions: {
            hide: () => {
                $(generator.selector + ' #reportActions').hide();
            },
            show: () => {
                $(generator.selector + ' #reportActions').fadeIn(300);
            }
        },
        loader: {
            hide: () => {
                $(generator.selector + ' #reportLoader').hide();
                $(generator.selector + ' #reportTitle').show();
            },
            show: () => {
                $(generator.selector + ' #reportTitle').hide();
                $(generator.selector + ' #reportLoader').show();
            },
            fire: () => {
                generator.loader.show();
                $.post(
                    ajaxurl, {
                        action: 'UKMrapporter_ajax',
                        controller: 'getReport',
                        format: 'html',
                        rapport: loader.getId(),
                        config: customizer.getConfig()
                    },
                    (response) => {
                        generator.loader.hide();
                        generator.actions.show();
                        switch (response.POST.format) {
                            case 'html':
                                return generator.showHTML(response);
                        }
                    }
                );
            }
        },
        showHTML: (response) => {
            $(generator.selector + ' #reportContent').html(this['twigJS_' + response.template].render(response));
        }
    }

    loader.bind();
    customizer.bind();
    templatePicker.bind();
    templateSelector.bind();
    templatePicker.bind();
    templateSaver.bind();
    generator.bind();

    var self = {
        loader: loader,
        generator: generator,
        customizer: customizer,
        templatePicker: templatePicker,
        init: () => {
            self.templatePicker.init()
            self.loader.show();
            self.customizer.show();
        }
    }
    return self;
}(jQuery);

$(document).ready(() => {
    UKMrapporter.init();
});