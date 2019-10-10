var UKMrapporter = function($) {
    var emitter = UKMresources.emitter('UKMrapporter');

    var preventDefault = function(e) {
        if (e !== undefined) {
            e.preventDefault();
        }
    }

    var loader = {
        selector: '#reportLoader',
        visible: () => {
            $(loader.selector).is(':visible');
        },
        show: (e) => {
            preventDefault(e);
            $(loader.selector).show();
        },
        hide: (e) => {
            preventDefault(e);
            $(loader.selector).hide();
        },
        bind: () => {
            $(document).on('click', '.hideReportLoader', loader.hide);
            $(document).on('click', '.showReportLoader', loader.show);
        },
        init: () => {
            loader.bind();
        }
    }

    var customizer = {
        selector: '#reportCustomizer',
        show: function(e) {
            preventDefault(e);
            $(customizer.selector).show();
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
        },
        init: () => {
            customizer.bind();
        }
    }


    var templateLoader = {
        selector: '#templateLoader',
        visible: () => {
            return $(templateLoader.selector).is(':visible')
        },
        show: (e) => {
            preventDefault(e);
            customizer.show();
            selector.slideDown();
        },
        hide: (e) => {
            preventDefault(e);
            $('#templateLoader').hide();
        },
        bind: () => {
            $(document).on('click', '.hideTemplateLoader', templateLoader.hide);
            $(document).on('click', '.showTemplateLoader', templateLoader.show);
        },
        init: () => {
            templateLoader.bind();
        },
    }

    var self = {
        loader: loader,
        customizer: customizer,
        templateLoader: templateLoader,
        init: () => {
            self.loader.init();
            self.customizer.init();
            self.templateLoader.init()

            self.loader.show();
            self.customizer.show();
        }
    }


    self.init();

    return self;
}(jQuery);

$(document).ready(() => {
    UKMrapporter.init();
});
/*



$(document).on('click', '#openTemplate, #saveAsTemplate', (e) => {
    $('#tilpass').fadeOut(200, (e) => {
        $('#maler').fadeIn(300);
    })
});

$(document).on('click', '#saveAsTemplate', (e) => {
    $('#saveTemplate').show();
    $('#templateConfig').val($('#rapportTilpasninger').serialize());
});

$(document).on('click', '.hideTemplateManager', (e) => {
    $('#maler').fadeOut(200, (e) => {
        $('#tilpass').fadeIn(300);
    })
});

var templates = new Map();
$(document).ready(fetchTemplates);
$(document).on('change', '#saveTemplate .templateSelector', showTemplateSelector);
$(document).on('click', '#doSaveTemplate', saveTemplate);
$(document).on('click', '.generateReport', generateReport);
$(document).on('click', '.openTemplateLoader .template', loadTemplate);

function showTemplateSelector(e) {
    var select = $(e.target);
    if (select.val() == 'new') {
        $('#saveTemplateName').slideDown();
    } else {
        $('#saveTemplateName').slideUp();
    }
}

function saveTemplate(e) {
    $.post(ajaxurl, {
        action: 'UKMrapporter_ajax',
        controller: 'saveTemplate',
        rapport: '{{ rapport.getId() }}',
        template_id: $('#saveTemplate .templateSelector').val(),
        name: $('#saveTemplate input[name="navn"]').val(),
        description: $('#saveTemplate textarea[name="beskrivelse"]').val(),
        config: $('#templateConfig').val()
    }, (response) => {
        fetchTemplates();
        $('#saveTemplate input[name="navn"]').val('');
        $('#saveTemplate textarea[name="beskrivelse"]').val('');
        $('#saveTemplate').fadeOut(200);
        $('#doSaveTemplateDone').fadeIn();
    });
}

function loadTemplate(e) {
    console.log('loadTemplate');
    var clicked = $(e.target);
    var config = templates.get('template_' + clicked.attr('data-id'));

    // Reset form
    $('#rapportTilpasninger input[type="checkbox"]').prop("checked", false);
    $('#rapportTilpasninger input[type="radio"]').prop("checked", false);

    // Set form
    $.each(config, function(key, value) {
        console.log('CHECK ' + 'input[name="' + key + '"]');
        $('input[name="' + key + '"]').prop('checked', true);
        generateReport();
    });
}

function preventIfE(e) {
    if (e !== undefined) {
        e.preventDefault();
    }
}


function showPrep(e) {
    preventIfE(e);
    $('#showReport').hide();
    $('#reportCustomizer').slideDown();
}

function generateReport(e) {
    $('#reportCustomizer').hide();
    $('#showReport').slideDown();
}

function fetchTemplates() {
    $.post(ajaxurl, {
        action: 'UKMrapporter_ajax',
        controller: 'listTemplates',
        rapport: '{{ rapport.getId() }}'
    }, (response) => {
        $('#templateSelector').html(twigJS_templates.render(response));

        if (response.templates.length > 0) {
            $('#overwriteExistingTemplate').html(twigJS_templateSelector.render(response)).show();
            for (var i = 0; i < response.templates.length; i++) {
                var template = response.templates[i];
                templates.set('template_' + template.id, template);
            }
        }
    });
}*/