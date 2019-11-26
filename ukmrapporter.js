var UKMrapporter = function($) {
    var templateCollection = new Map();
    var emitter = UKMresources.emitter('UKMrapporter');
    emitter.enableDebug();

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
        getId: function() {
            return $(loader.selector).attr('data-id');
        },
        visible: function() {
            $(loader.selector).is(':visible');
        },
        show: function(e) {
            preventDefault(e);
            $(loader.selector).show();
            emitter.emit('loader.show');
        },
        hide: function(e) {
            preventDefault(e);
            $(loader.selector).hide();
            emitter.emit('loader.hide');
        },
        bind: function() {
            $(document).on('click', '.hideReportLoader', loader.hide);
            $(document).on('click', '.showReportLoader', loader.show);

            emitter.on('templatePicker.hide', loader.header.showCustomize);
            emitter.on('templatePicker.show', loader.header.showTemplate);
        },
        header: {
            showCustomize: function() {
                $(loader.selector + ' #templateHeader').hide();
                $(loader.selector + ' #customizeHeader').show();
            },
            showTemplate: function() {
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
        hide: function(e) {
            preventDefault(e);
            $(customizer.selector).hide();
        },
        bind: function() {
            $(document).on('click', '.hideCustomizer', customizer.hide);
            $(document).on('click', '.showCustomizer', customizer.show);
            $(document).on('click', '.saveAsTemplate', customizer.saveAsTemplate);

            emitter.on('templatePicker.hide', customizer.show);
            emitter.on('templatePicker.show', customizer.hide);
        },
        saveAsTemplate: function() {
            templateSaver.setConfig(
                customizer.getConfig()
            );
        },
        reset: function() {
            $(customizer.selector + ' input[type="checkbox"]').prop("checked", false);
            $(customizer.selector + ' input[type="radio"]').prop("checked", false);
        },
        fill: function(check) {
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
        getConfig: function() {
            return $(customizer.selector + ' form').serialize();
        }
    }

    /**
     * Template-håndtering
     * Håndterer relasjoner mellom GUI og Database
     */
    var templates = {
        array: [],
        load: function(rapport_id) {
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
            }, function(response) {
                if (response.templates.length > 0) {
                    for (var i = 0; i < response.templates.length; i++) {
                        templates.add(response.templates[i]);
                    }
                }
                // Lag lokal kopi for iterering
                templates.array = [];
                templateCollection.forEach(function(value, key) {
                    templates.array.push(value);
                });

                emitter.emit('templates.loaded');
            });
        },
        save: function(rapport, template, name, description, config) {
            $.post(ajaxurl, {
                action: 'UKMrapporter_ajax',
                controller: 'saveTemplate',
                rapport: rapport,
                template_id: template,
                name: name,
                description: description,
                config: config,
            }, function(response) {
                templates.load();
                emitter.emit('template.saved');
            });
        },
        add: function(template) {
            templateCollection.set('template_' + template.id, template);
        },
        get: function(template_id) {
            return templateCollection.get(template_id);
        },
        getAll: function() {
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
        bind: function() {
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
        selectedTemplate: function(e) {
            if ($(e.target).val() == 'new') {
                templateSaver.name.show();
            } else {
                templateSaver.name.hide();
            }
        },
        hide: function() {
            $(templateSaver.selector).hide();
        },
        hideForm: function() {
            $(templateSaver.selector + ' form').slideUp(300);
        },
        showForm: function() {
            $(templateSaver.selector + ' form').show();
        },
        show: function() {
            customizer.hide();
            templateSaver.showForm();
            $(templateSaver.selector).fadeIn(200);
        },
        reset: function() {
            $(templateSaver.getFormSelector()).trigger('reset');
        },
        save: function() {
            templates.save(
                loader.getId(),
                templateSaver.getTemplateId(),
                templateSaver.getName(),
                templateSaver.getDescription(),
                templateSaver.getConfig()
            );
        },
        getFormSelector: function() {
            return templateSaver.selector + ' form';
        },
        getTemplateId: function() {
            return $(templateSaver.getFormSelector() + ' .templateSelector select').val();
        },
        getName: function() {
            return $(templateSaver.getFormSelector() + ' input[name="navn"]').val();
        },
        getDescription: function() {
            return $(templateSaver.getFormSelector() + ' textarea[name="beskrivelse"]').val();
        },
        getConfig: function() {
            return $(templateSaver.getFormSelector() + ' input[name="config"]').val();
        },
        // NAVNE-FELTET
        name: {
            selector: '#templateName',
            show: function() {
                $(templateSaver.selector + ' ' + templateSaver.name.selector).slideDown();
            },
            hide: function() {
                $(templateSaver.selector + ' ' + templateSaver.name.selector).slideUp();
            },
        },
        // STATUS-BOKSEN
        status: {
            selector: '.status',
            show: function() {
                console.log('SHOW ' + templateSaver.selector + ' ' + templateSaver.status.selector);
                templateSaver.hideForm();
                $(templateSaver.selector + ' ' + templateSaver.status.selector).slideDown();
                //templateSaver.status.setTimer();
            },
            hide: function() {
                $(templateSaver.selector + ' ' + templateSaver.status.selector).slideUp();
            },
            setTimer: function() {
                setTimeout(function() {
                        $(templateSaver.selector).fadeOut(200);
                        templateSaver.status.hide();
                        templateSaver
                    },
                    2500
                );
            }
        },
        setConfig: function(config) {
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
        visible: function() {
            return $(templatePicker.selector).is(':visible')
        },
        show: function(e) {
            preventDefault(e);
            loader.show();
            customizer.hide();
            $(templatePicker.selector).slideDown();
            emitter.emit('templatePicker.show');
        },
        hide: function(e) {
            preventDefault(e);
            $(templatePicker.selector).hide();
            emitter.emit('templatePicker.hide');
        },
        loadFromDB: function() {
            templates.load(loader.getId());
        },
        bind: function() {
            $(document).on('click', '.hideTemplatePicker', templatePicker.hide);
            $(document).on('click', '.showTemplatePicker', templatePicker.show);
            $(document).on('click', templatePicker.selector + ' li.template', templatePicker.loadFromClick);
            emitter.on('templates.loaded', templatePicker.render);
        },
        init: function() {
            templatePicker.loadFromDB();
        },

        render: function() {
            $(templatePicker.selector).html(
                twigJS_templatePicker.render({
                    templates: templates.getAll()
                })
            );
        },
        loadFromClick: function(e) {
            return templatePicker.load($(e.target).attr('data-id'));
        },
        load: function(id) {
            customizer.reset();
            customizer.fill(
                templateCollection.get(
                    'template_' + id
                ).config
            );
            templatePicker.hide();
            generator.show();
        }
    }

    /**
     * Template-selector
     * Viser alle templates i select-liste
     */
    var templateSelector = {
        selector: '.templateSelector',
        render: function() {
            $(templateSelector.selector).html(
                twigJS_templateSelector.render({
                    templates: templates.getAll()
                })
            );
        },
        bind: function() {
            emitter.on('templates.loaded', templateSelector.render);
        },
    }

    /**
     * Generering av rapporter
     */
    var generator = {
        selector: {
            container: '#reportContainer',
            content: '#reportContent',
            loading: {
                download: '#reportDownloadLoading',
                html: '#reportGenerating',
                title: '#reportLoading',
            },
            title: '#reportTitle',
            download: {
                gui: '#reportDownload',
                link: '#downloadLink'
            },
            actions: '#reportActions',
            email: '#reportEmail'
        },
        show: function(format = 'html') {
            loader.hide();
            $(generator.selector.container).slideDown();
            emitter.emit('generator.show');
            generator.loader.fire(format);
        },
        /* Eksisterer for at js ikke skal få panikk ved klikk på .generateReport */
        loadAndShowHtml: function() {
            generator.show('html');
        },
        hide: function() {
            $(generator.selector.container).hide();
            $(generator.selector.email).hide();
            generator.loader.hide();
        },
        bind: function() {
            emitter.on('loader.show', generator.hide);
            $(document).on('click', '.generateReport', generator.loadAndShowHtml);
        },
        actions: {
            hide: function() {
                $(generator.selector.actions).hide();
            },
            show: function() {
                $(generator.selector.actions).fadeIn(300);
            }
        },
        loader: {
            hide: function() {
                $(generator.selector.title).show();
                $(generator.selector.loading.html).hide();
                $(generator.selector.loading.title).hide();
                $(generator.selector.loading.download).hide();
                $(generator.selector.download.gui).hide();
                $(generator.selector.email).hide();
            },
            show: function(format) {
                switch (format) {
                    case 'html':
                        $(generator.selector.title).hide();
                        $(generator.selector.loading.title).show();
                        $(generator.selector.loading.html).show();
                        break;
                    case 'excel':
                        $(generator.selector.loading.download).show();
                        break;
                }
            },
            fire: function(format) {
                if (format == 'html') {
                    $(generator.selector.content).html('');
                }
                $(generator.selector.content).hide();
                generator.loader.show(format);
                $.post(
                    ajaxurl, {
                        action: 'UKMrapporter_ajax',
                        controller: 'getReport',
                        format: format,
                        rapport: loader.getId(),
                        config: customizer.getConfig()
                    },
                    function(response) {
                        generator.loader.hide();
                        generator.actions.show();
                        switch (response.POST.format) {
                            case 'html':
                                generator.showHTML(response);
                                break;
                            case 'excel':
                                generator.showExcel(response);
                                break;
                        }
                        emitter.emit('report.loaded');
                    }
                );
            }
        },
        showHTML: function(response) {
            $(generator.selector.content).html(response.html);
            $(generator.selector.content).show();
        },
        downloadExcel: function() {
            $(generator.selector.download.gui).slideUp();
            $(generator.selector.content).hide()
            $(generator.selector.loading.download).slideDown();
            generator.show('excel');
        },
        showExcel: function(response) {
            $(generator.selector.download.link).attr('href', response.link);
            $(generator.selector.download.gui).slideDown();
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
        init: function() {
            self.templatePicker.init()
            self.loader.show();
            self.customizer.show();
            self.bind();
        },
        on: function(event, callback) {
            emitter.on(event, callback);
        },
        once: function(event, callback) {
            emitter.once(event, callback);
        },

        print: function() {
            var w = window.outerWidth * 0.8;
            var h = window.outerHeight * 0.8;

            // CENTER-POSITION CREDITS: https://stackoverflow.com/a/16861050
            var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
            var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;
            var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
            var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
            var systemZoom = width / window.screen.availWidth;

            var config = {
                location: 'yes',
                statusbar: 'no',
                directories: 'no',
                menubar: 'no',
                titlebar: 'no',
                toolbar: 'no',
                dependent: 'no',
                resizable: 'yes',
                personalbar: 'no',
                scrollbars: 'no',
                top: (height - h) / 2 / systemZoom + dualScreenTop,
                left: (width - w) / 2 / systemZoom + dualScreenLeft,
                width: w / systemZoom,
                height: h / systemZoom,
            }

            // INCLUDE STYLESHEETS
            var styles = [];
            $('link').each(function(index, element) {
                if ($(element).attr('id') == undefined) {
                    return;
                }

                if ($(element).attr('id').includes('WPbootstrap3_css') || $(element).attr('id').includes('UKMrapporter_css')) {
                    styles.push($(element).attr('href'));
                }
            });

            // OPEN PRINTAREA
            var print_area = window.open("printMode", "_printMode", $.param(config).replace(/&/g, ','));
            print_area.document.write(
                twigJS_print.render({
                    content: $('#reportContent').html(),
                    styles: styles,
                })
            );
            print_area.focus();
            setTimeout(function() {
                    print_area.print();
                    print_area.document.close();
                },
                1200
            );
        },
        downloadExcel: function() {
            generator.downloadExcel();
        },
        downloadWord: function() {
            alert('Beklager, word-nedlasting er ikke støttet enda, men kommer snart. Kontakt support@ukm.no hvis det haster.');
        },
        showEmail: function() {
            var emails = [];
            $(generator.selector.content).find('a[href^="mailto:"]').each(function() {
                var email = $(this).attr('href').replace('mailto:', '');
                if (email && !emails.includes(email)) {
                    emails.push(email);
                }
            });
            $(generator.selector.content).hide();
            $(generator.selector.email).html(
                twigJS_email.render({ emails: emails })
            ).slideDown();
        },
        hideEmail: function() {
            $(generator.selector.email).hide();
            $(generator.selector.content).slideDown();
        },
        bind: function() {
            $(document).on('click', '.printReport', self.print);
            $(document).on('click', '.downloadExcel', self.downloadExcel);
            $(document).on('click', '.downloadWord', self.downloadWord);
            $(document).on('click', '.sendEmail', self.showEmail);
            $(document).on('click', '.hideEmail', self.hideEmail);
        }
    }
    return self;
}(jQuery);


function loadReport() {
    UKMrapporter.templatePicker.load(33);
}

$(document).ready(function() {
    UKMrapporter.init();

    //UKMrapporter.once('templates.loaded', loadReport);
    //UKMrapporter.once('report.loaded', UKMrapporter.downloadExcel);
});