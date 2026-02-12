var UKMrapporter = function(jQuery) {
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
        getId: function() {
            return jQuery(loader.selector).attr('data-id');
        },
        visible: function() {
            jQuery(loader.selector).is(':visible');
        },
        show: function(e) {
            preventDefault(e);
            jQuery(loader.selector).show();
            emitter.emit('loader.show');
        },
        hide: function(e) {
            preventDefault(e);
            jQuery(loader.selector).hide();
            emitter.emit('loader.hide');
        },
        bind: function() {
            jQuery(document).on('click', '.hideReportLoader', loader.hide);
            jQuery(document).on('click', '.showReportLoader', loader.show);

            emitter.on('templatePicker.hide', loader.header.showCustomize);
            emitter.on('templatePicker.show', loader.header.showTemplate);
        },
        header: {
            showCustomize: function() {
                jQuery(loader.selector + ' #templateHeader').hide();
                jQuery(loader.selector + ' #customizeHeader').show();
            },
            showTemplate: function() {
                jQuery(loader.selector + ' #customizeHeader').hide();
                jQuery(loader.selector + ' #templateHeader').show();
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
            jQuery(customizer.selector).slideDown();
            if (typeof window.mountDiplomCustomizerApp === 'function') {
                window.mountDiplomCustomizerApp();
            }
            if (typeof window.mountIdKortCustomizerApp === 'function') {
                window.mountIdKortCustomizerApp();
            }
            jQuery('#idkortCustomizerApp').show();
            jQuery(generator.selector.container).hide();
            if (!loader.visible()) {
                loader.show();
            }
        },
        hide: function(e) {
            preventDefault(e);
            jQuery(customizer.selector).hide();
        },
        bind: function() {
            jQuery(document).on('click', '.hideCustomizer', customizer.hide);
            jQuery(document).on('click', '.showCustomizer', customizer.show);
            jQuery(document).on('click', '.saveAsTemplate', customizer.saveAsTemplate);

            emitter.on('templatePicker.hide', customizer.show);
            emitter.on('templatePicker.show', customizer.hide);
        },
        saveAsTemplate: function() {
            templateSaver.setConfig(
                customizer.getConfig()
            );
        },
        reset: function() {
            jQuery(customizer.selector + ' input[type="checkbox"]').prop("checked", false);
            jQuery(customizer.selector + ' input[type="radio"]').prop("checked", false);
        },
        fill: function(check) {
            jQuery.each(check, function(key, value) {
                var input = jQuery(customizer.selector + ' input[name="' + key + '"]');
                switch (input.attr('type')) {
                    case 'hidden':
                        input.val(value);
                        if (input.attr('data-radiobutton')) {
                            jQuery(customizer.selector + ' .radioButtons[data-name="' + key + '"] button[value="' + value + '"]').click();
                        }
                        break;
                    case 'text':
                    case 'textarea':
                        input.val(value);
                        break;
                    case 'radio':
                        jQuery(customizer.selector + ' input[name="' + key + '"][value="' + value + '"]').prop('checked', true);
                        break;
                    default:
                        input.prop('checked', true);
                        break;
                }
            });
        },
        getConfig: function() {
            return jQuery(customizer.selector + ' form').serialize();
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
            jQuery.post(ajaxurl, {
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
            jQuery.post(ajaxurl, {
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
            jQuery(document).on(
                'change',
                templateSaver.selector + ' ' + templateSelector.selector,
                templateSaver.selectedTemplate
            );
            jQuery(document).on('click', '.hideTemplateSaver', templateSaver.hide);
            jQuery(document).on('click', '.showTemplateSaver', templateSaver.show);
            jQuery(document).on('click', templateSaver.selector + ' .saveTemplate', templateSaver.save);
            emitter.on('template.saved', templateSaver.status.show);
            emitter.on('template.saved', templateSaver.reset);
            emitter.on('templatePicker.hide', templateSaver.hide);
            emitter.on('templatePicker.hide', templateSaver.status.hide);

            emitter.on('templatePicker.show', templateSaver.hide);

            jQuery("#saveTemplateSelector").change(function(event) {
                
            });
        },
        selectedTemplate: function(e) {
            if (jQuery(e.target).val() == 'new') {
                templateSaver.name.show();
                jQuery("#saveTemplateBeskrivelse").html("");
            } else {
                templateSaver.name.hide();
                jQuery("#saveTemplateBeskrivelse").html(jQuery(event.target.selectedOptions[0]).data("beskrivelse"));
            }
        },
        hide: function() {
            jQuery(templateSaver.selector).hide();
        },
        hideForm: function() {
            jQuery(templateSaver.selector + ' form').slideUp(300);
        },
        showForm: function() {
            jQuery(templateSaver.selector + ' form').show();
        },
        show: function() {
            customizer.hide();
            templateSaver.showForm();
            jQuery(templateSaver.selector).fadeIn(200);
        },
        reset: function() {
            jQuery(templateSaver.getFormSelector()).trigger('reset');
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
            return jQuery(templateSaver.getFormSelector() + ' .templateSelector select').val();
        },
        getName: function() {
            return jQuery(templateSaver.getFormSelector() + ' input[name="navn"]').val();
        },
        getDescription: function() {
            return jQuery(templateSaver.getFormSelector() + ' textarea[name="beskrivelse"]').val();
        },
        getConfig: function() {
            return jQuery(templateSaver.getFormSelector() + ' input[name="config"]').val();
        },
        // NAVNE-FELTET
        name: {
            selector: '#templateName',
            show: function() {
                jQuery(templateSaver.selector + ' ' + templateSaver.name.selector).slideDown();
            },
            hide: function() {
                jQuery(templateSaver.selector + ' ' + templateSaver.name.selector).slideUp();
            },
        },
        // STATUS-BOKSEN
        status: {
            selector: '.status',
            show: function() {
                console.log('SHOW ' + templateSaver.selector + ' ' + templateSaver.status.selector);
                templateSaver.hideForm();
                jQuery(templateSaver.selector + ' ' + templateSaver.status.selector).slideDown();
                //templateSaver.status.setTimer();
            },
            hide: function() {
                jQuery(templateSaver.selector + ' ' + templateSaver.status.selector).slideUp();
            },
            setTimer: function() {
                setTimeout(function() {
                        jQuery(templateSaver.selector).fadeOut(200);
                        templateSaver.status.hide();
                        templateSaver
                    },
                    2500
                );
            }
        },
        setConfig: function(config) {
            jQuery(templateSaver.getFormSelector() + ' input[name="config"]').val(config);
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
            return jQuery(templatePicker.selector).is(':visible')
        },
        show: function(e) {
            preventDefault(e);
            loader.show();
            customizer.hide();
            jQuery(templatePicker.selector).slideDown();
            jQuery('#malSelect, #apenMalSelect').hide();
            emitter.emit('templatePicker.show');
        },
        hide: function(e) {
            preventDefault(e);
            jQuery(templatePicker.selector).hide();
            emitter.emit('templatePicker.hide');
            jQuery('#malSelect, #apenMalSelect').show();

        },
        loadFromDB: function() {
            templates.load(loader.getId());
        },
        bind: function() {
            jQuery(document).on('click', '.hideTemplatePicker', templatePicker.hide);
            jQuery(document).on('click', '.showTemplatePicker', templatePicker.show);
            jQuery(document).on('click', templatePicker.selector + ' li.template', templatePicker.loadFromClick);
            jQuery(document).on('click', '.template-button', templatePicker.loadFromClick);
            jQuery(document).on('click', '#apenMalSelect', templatePicker.apenMalClick);
            emitter.on('templates.loaded', templatePicker.render);
        },
        init: function() {
            templatePicker.loadFromDB();
        },
        apenMalClick: function(e) {
            if(jQuery('#malSelect').val() != null) {
                return templatePicker.load(jQuery('#malSelect').val());
            }
        },
        render: function() {
            jQuery(templatePicker.selector).html(
                twigJS_templatePicker.render({
                    templates: templates.getAll()
                })
            );
        },
        loadFromClick: function(e) {
            return templatePicker.load(jQuery(e.target).attr('data-id'));
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
            jQuery(templateSelector.selector).html(
                twigJS_templateSelector.render({
                    templates: templates.getAll()
                })
            );
        },
        bind: function() {
            emitter.on('templates.loaded', templateSelector.render);
        }
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
                title: '#reportLoading'
            },
            title: '#reportTitle',
            download: {
                gui: '#reportDownload',
                link: '#downloadLink'
            },
            actions: '#reportActions',
            email: '#reportEmail'
        },
        show: function(format) {
            if (format == null || format == undefined) {
                format = 'html';
            }
            loader.hide();
            jQuery(generator.selector.container).slideDown();
            emitter.emit('generator.show');
            generator.loader.fire(format);
        },
        /* Eksisterer for at js ikke skal få panikk ved klikk på .generateReport */
        loadAndShowHtml: function() {
            generator.show('html');
        },
        hide: function() {
            jQuery(generator.selector.container).hide();
            jQuery(generator.selector.email).hide();
            generator.loader.hide();
        },
        bind: function() {
            emitter.on('loader.show', generator.hide);
            jQuery(document).on('click', '.generateReport', generator.loadAndShowHtml);
        },
        actions: {
            hide: function() {
                jQuery(generator.selector.actions).hide();
            },
            show: function() {
                jQuery(generator.selector.actions).fadeIn(300);
            }
        },
        loader: {
            hide: function() {
                jQuery(generator.selector.title).show();
                jQuery(generator.selector.loading.html).hide();
                jQuery(generator.selector.loading.title).hide();
                jQuery(generator.selector.loading.download).hide();
                jQuery(generator.selector.download.gui).hide();
                jQuery(generator.selector.email).hide();
            },
            show: function(format) {
                switch (format) {
                    case 'html':
                        jQuery(generator.selector.title).hide();
                        jQuery(generator.selector.loading.title).show();
                        jQuery(generator.selector.loading.html).show();
                        break;
                    case 'pdf':
                    case 'pdf_print':
                    case 'excel':
                        jQuery(generator.selector.loading.download).show();
                        break;
                }
            },
            fire: function(format) {
                if (format == 'html') {
                    jQuery(generator.selector.content).html('');
                }
                jQuery(generator.selector.content).hide();
                generator.loader.show(format);
                jQuery.post(
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
                            case 'pdf':
                            case 'pdf_print':
                            case 'excel':
                            case 'word':
                                generator.showDownload(response);
                                break;
                        }
                        emitter.emit('report.loaded');
                    }
                );
            }
        },
        showHTML: function(response) {
            jQuery(generator.selector.content).html(response.html);
            jQuery(generator.selector.content).show();
            var isIdKort = (loader.getId && loader.getId().toLowerCase && loader.getId().toLowerCase() === 'idkort');
            if (isIdKort) {
                jQuery('#idkortReportApp').hide();
            } else {
                jQuery('#idkortReportApp').show();
            }
            if (typeof window.mountDiplomReportApp === 'function') {
                window.mountDiplomReportApp();
            }
            if (typeof window.mountIdKortReportApp === 'function') {
                window.mountIdKortReportApp();
            }
        },
        hideHTML: function() {
            jQuery(generator.selector.download.gui).slideUp();
            jQuery(generator.selector.content).hide()
            jQuery(generator.selector.loading.download).slideDown();
        },
        downloadExcel: function() {
            generator.hideHTML();
            generator.show('excel');
        },
        downloadPdf: function() {
            var isDiplom = (loader.getId && loader.getId().toLowerCase && loader.getId().toLowerCase() === 'diplom');
            var isIdKort = (loader.getId && loader.getId().toLowerCase && loader.getId().toLowerCase() === 'idkort');
            if (isDiplom || isIdKort) {
                generator.hideHTML();
                if (isIdKort) {
                    jQuery(generator.selector.content).html('');
                    jQuery('#idkortCustomizerApp').hide();
                    jQuery('#idkortReportApp').hide();
                }
                generator.show('pdf');
                return;
            }
            // Ikke skjul innholdet – html2canvas/jsPDF trenger synlig DOM
            generator.actions.hide();
            jQuery(generator.selector.loading.download).show();
            var pdfOverlay = jQuery('#pdfGeneratingOverlay');
            if (pdfOverlay.length) {
                pdfOverlay.show();
            }

            var jsPdfLib = (window.jspdf || window.jsPDF);
            var JsPDFConstructor = jsPdfLib ? jsPdfLib.jsPDF || jsPdfLib : null;
            if (!JsPDFConstructor) {
                alert('Fant ikke jsPDF. Oppdater siden og prøv igjen.');
                generator.loader.hide();
                generator.actions.show();
                if (pdfOverlay.length) {
                    pdfOverlay.hide();
                }
                return;
            }

            var contentEl = document.querySelector(generator.selector.content);
            if (!contentEl || !contentEl.innerHTML.trim()) {
                alert('Fant ikke rapportinnholdet. Generer rapporten først.');
                generator.loader.hide();
                generator.actions.show();
                if (pdfOverlay.length) {
                    pdfOverlay.hide();
                }
                return;
            }

            var title = jQuery(generator.selector.title).is(':visible') ? jQuery(generator.selector.title).text() : jQuery('#reportTitle').text();
            var safeTitle = title && title.length ? title : 'rapport';

            if (typeof window.html2canvas !== 'function') {
                alert('Fant ikke html2canvas. Oppdater siden og prøv igjen.');
                generator.loader.hide();
                generator.actions.show();
                if (pdfOverlay.length) {
                    pdfOverlay.hide();
                }
                return;
            }

            // For diplomer: sørg for at data (navn/sted) vises under PDF-generering
            var restoreDiplomData = null;
            if (isDiplom && typeof window.diplomSetShowData === 'function') {
                var previous = Boolean(window.diplomShowData);
                window.diplomSetShowData(true);
                restoreDiplomData = function() {
                    window.diplomSetShowData(previous);
                };
            }

            var pdfUnit = isDiplom ? 'mm' : 'pt';
            var pdf = new JsPDFConstructor('p', pdfUnit, 'a4');

            var proceed = function() {
                // Klon og gjør synlig i offscreen container for stabil rendering
                var cloned = contentEl.cloneNode(true);
                if (isDiplom) {
                    cloned.style.width = '210mm';
                    cloned.style.maxWidth = '210mm';
                } else {
                    cloned.style.width = contentEl.offsetWidth + 'px';
                    cloned.style.maxWidth = contentEl.offsetWidth + 'px';
                }
                cloned.style.display = 'block';

                var container = document.createElement('div');
                container.style.position = 'fixed';
                container.style.left = '-9999px';
                container.style.top = '0';
                container.style.width = isDiplom ? '210mm' : (contentEl.offsetWidth + 'px');
                container.appendChild(cloned);
                document.body.appendChild(container);

                var finish = function() {
                    document.body.removeChild(container);
                    if (restoreDiplomData) {
                        restoreDiplomData();
                    }
                    generator.loader.hide();
                    jQuery(generator.selector.loading.download).hide();
                    if (pdfOverlay.length) {
                        pdfOverlay.hide();
                    }
                    generator.actions.show();
                };

                var onError = function(err) {
                    console.error('PDF-generering feilet', err);
                    finish();
                    alert('Klarte ikke lage PDF akkurat nå. Forsøk igjen eller bruk Word/Excel.');
                };

                if (isDiplom) {
                    var pages = cloned.querySelectorAll('.page');
                    if (!pages || pages.length < 1) {
                        pages = [cloned];
                    }

                    var inlineSvgImages = function(root) {
                        var images = Array.prototype.slice.call(root.querySelectorAll('img'));
                        if (images.length < 1 || typeof fetch !== 'function') {
                            return Promise.resolve();
                        }

                        var parser = new DOMParser();

                        return Promise.all(images.map(function(img) {
                            var src = img.getAttribute('src');
                            if (!src || src.indexOf('.svg') === -1) {
                                return Promise.resolve();
                            }

                            return fetch(src, { mode: 'cors', credentials: 'same-origin' })
                                .then(function(response) {
                                    if (!response.ok) {
                                        throw new Error('SVG fetch failed');
                                    }
                                    return response.text();
                                })
                                .then(function(svgText) {
                                    var doc = parser.parseFromString(svgText, 'image/svg+xml');
                                    var svg = doc.querySelector('svg');
                                    if (!svg) {
                                        return;
                                    }

                                    var className = img.getAttribute('class');
                                    if (className) {
                                        svg.setAttribute('class', className);
                                    }

                                    var styleAttr = img.getAttribute('style');
                                    if (styleAttr) {
                                        svg.setAttribute('style', styleAttr);
                                    }

                                    Array.prototype.slice.call(img.attributes).forEach(function(attr) {
                                        if (attr && attr.name && attr.name.indexOf('data-v-') === 0) {
                                            svg.setAttribute(attr.name, attr.value);
                                        }
                                    });

                                    svg.setAttribute('aria-label', img.getAttribute('alt') || 'logo');
                                    img.parentNode.replaceChild(svg, img);
                                })
                                .catch(function() {
                                    return Promise.resolve();
                                });
                        }));
                    };

                    var inlineImages = function(root) {
                        var images = Array.prototype.slice.call(root.querySelectorAll('img'));
                        if (images.length < 1) {
                            return Promise.resolve();
                        }

                        return Promise.all(images.map(function(img) {
                            var src = img.getAttribute('src');
                            if (!src || src.indexOf('data:') === 0) {
                                return Promise.resolve();
                            }

                            img.setAttribute('crossorigin', 'anonymous');

                            if (typeof fetch !== 'function') {
                                return Promise.resolve();
                            }

                            return fetch(src, { mode: 'cors', credentials: 'same-origin' })
                                .then(function(response) {
                                    if (!response.ok) {
                                        throw new Error('Image fetch failed');
                                    }
                                    return response.blob();
                                })
                                .then(function(blob) {
                                    return new Promise(function(resolve) {
                                        var reader = new FileReader();
                                        reader.onload = function() {
                                            img.src = reader.result;
                                            resolve();
                                        };
                                        reader.onerror = function() { resolve(); };
                                        reader.readAsDataURL(blob);
                                    });
                                })
                                .catch(function() {
                                    return Promise.resolve();
                                });
                        }));
                    };

                    var waitForImages = function(root) {
                        var images = Array.prototype.slice.call(root.querySelectorAll('img'));
                        if (images.length < 1) {
                            return Promise.resolve();
                        }

                        return Promise.all(images.map(function(img) {
                            return new Promise(function(resolve) {
                                if (img.complete) {
                                    resolve();
                                    return;
                                }

                                var done = function() { resolve(); };
                                img.addEventListener('load', done, { once: true });
                                img.addEventListener('error', done, { once: true });
                            });
                        }));
                    };

                    inlineSvgImages(cloned).then(function() {
                        return inlineImages(cloned);
                    }).then(function() {
                        return waitForImages(cloned);
                    }).then(function() {
                        var chain = Promise.resolve();
                        pages.forEach(function(pageEl, index) {
                            chain = chain.then(function() {
                                return html2canvas(pageEl, { scale: 2, useCORS: true, allowTaint: false }).then(function(canvas) {
                                    if (index > 0) {
                                        pdf.addPage();
                                    }

                                    var imgData = canvas.toDataURL('image/png');
                                    pdf.addImage(imgData, 'PNG', 0, 0, 210, 297, undefined, 'FAST');
                                });
                            });
                        });

                        return chain.then(function() {
                            pdf.save(safeTitle + '.pdf');
                            finish();
                        });
                    }).catch(onError);

                    return;
                }

                if (typeof pdf.html === 'function') {
                    try {
                        var htmlPromise = pdf.html(cloned, {
                            x: 20,
                            y: 20,
                            html2canvas: {
                                scale: 0.9,
                                useCORS: true
                            },
                            callback: function(doc) {
                                doc.save(safeTitle + '.pdf');
                                finish();
                            }
                        });
                        if (htmlPromise && typeof htmlPromise.catch === 'function') {
                            htmlPromise.catch(onError);
                        }
                    } catch (e) {
                        onError(e);
                    }
                    return;
                }

                // Fallback: screenshot via html2canvas og legg inn som bilde
                html2canvas(cloned, { scale: 0.9, useCORS: true }).then(function(canvas) {
                    var imgData = canvas.toDataURL('image/png');
                    var pageWidth = pdf.internal.pageSize.getWidth() - 40; // margins 20
                    var pageHeight = pdf.internal.pageSize.getHeight() - 40;
                    var imgWidth = pageWidth;
                    var imgHeight = canvas.height * imgWidth / canvas.width;

                    var y = 20;
                    var x = 20;
                    var heightLeft = imgHeight;
                    var position = y;

                    pdf.addImage(imgData, 'PNG', x, position, imgWidth, imgHeight, undefined, 'FAST');
                    heightLeft -= pageHeight;

                    while (heightLeft > 0) {
                        pdf.addPage();
                        position = 20 - (imgHeight - heightLeft);
                        pdf.addImage(imgData, 'PNG', x, position, imgWidth, imgHeight, undefined, 'FAST');
                        heightLeft -= pageHeight;
                    }

                    pdf.save(safeTitle + '.pdf');
                    finish();
                }).catch(onError);
            };

            // Gi Vue-treet et kort pust for å renderes med data før vi tar snapshot
            if (isDiplom && typeof window.requestAnimationFrame === 'function') {
                requestAnimationFrame(function() { setTimeout(proceed, 50); });
            } else {
                proceed();
            }
        },
        downloadPdfPrint: function() {
            var isIdKort = (loader.getId && loader.getId().toLowerCase && loader.getId().toLowerCase() === 'idkort');
            if (isIdKort) {
                generator.hideHTML();
                jQuery(generator.selector.content).html('');
                jQuery('#idkortCustomizerApp').hide();
                jQuery('#idkortReportApp').hide();
                generator.show('pdf_print');
                return;
            }
        },
        showDownload: function(response) {
            jQuery(generator.selector.download.link).attr('href', response.link);
            jQuery(generator.selector.download.gui).slideDown();
        },
        downloadWord: function() {
            generator.hideHTML();
            generator.show('word');
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
            jQuery('link').each(function(index, element) {
                if (jQuery(element).attr('id') == undefined) {
                    return;
                }

                if (jQuery(element).attr('id').includes('WPbootstrap3_css') || jQuery(element).attr('id').includes('UKMrapporter_css')) {
                    styles.push(jQuery(element).attr('href'));
                }
            });

            // OPEN PRINTAREA
            var print_area = window.open("printMode", "_printMode", jQuery.param(config).replace(/&/g, ','));
            print_area.document.write(
                twigJS_print.render({
                    content: jQuery('#reportContent').html(),
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
        downloadPdf: function() {
            generator.downloadPdf();
        },
        downloadPdfPrint: function() {
            generator.downloadPdfPrint();
        },
        downloadWord: function() {
            generator.downloadWord();
        },
        showEmail: function() {
            var emails = [];
            jQuery(generator.selector.content).find('a[href^="mailto:"]').each(function() {
                var email = jQuery(this).attr('href').replace('mailto:', '');
                if (email && !emails.includes(email)) {
                    emails.push(email);
                }
            });
            jQuery(generator.selector.content).hide();
            jQuery(generator.selector.email).html(
                twigJS_email.render({ emails: emails })
            ).slideDown();
        },
        hideEmail: function() {
            jQuery(generator.selector.email).hide();
            jQuery(generator.selector.content).slideDown();
        },
        bind: function() {
            jQuery(document).on('click', '.printReport', self.print);
            jQuery(document).on('click', '.downloadExcel', self.downloadExcel);
            jQuery(document).on('click', '.downloadPdf', self.downloadPdf);
            jQuery(document).on('click', '.downloadPdfPrint', self.downloadPdfPrint);
            jQuery(document).on('click', '.downloadWord', self.downloadWord);
            jQuery(document).on('click', '.sendEmail', self.showEmail);
            jQuery(document).on('click', '.hideEmail', self.hideEmail);
        }
    }
    return self;
}(jQuery);


function loadReport() {
    UKMrapporter.templatePicker.load(33);
}

jQuery(document).ready(function() {
    UKMrapporter.init();

    //UKMrapporter.once('templates.loaded', loadReport);
    //UKMrapporter.once('report.loaded', UKMrapporter.downloadExcel);
});