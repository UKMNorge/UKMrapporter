import { createApp } from "vue";
import UKMrapporter from "./UKMrapporter.vue";
import Singlerapport from "./SingleRapport.vue";
import DiplomApp from "./DiplomApp.vue";

import hljs from "highlight.js/lib/core";
import javascript from "highlight.js/lib/languages/javascript";
import css from "highlight.js/lib/languages/css";
import xml from "highlight.js/lib/languages/xml";
import hljsVuePlugin from "@highlightjs/vue-plugin";
import "highlight.js/styles/base16/dracula.css";

hljs.registerLanguage("xml", xml);
hljs.registerLanguage("javascript", javascript);
hljs.registerLanguage("css", css);

// Import CSS for ukm-components-vue3
import "../node_modules/ukm-components-vue3/dist/style.css";

import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
    components,
    directives,
});

const app = createApp(UKMrapporter);

// Make Director global
import { Director } from 'ukm-spa/director';

var director = new Director();
(<any>window).director = director;


app.use(hljsVuePlugin);
app.use(vuetify);


app.mount("#rapporterApp");


const app2 = createApp(Singlerapport);
app2.use(hljsVuePlugin);
app2.use(vuetify);
app2.mount("#singleRapport");

const mountDiplomApp = (selector: string) => {
    const el = document.querySelector(selector) as HTMLElement | null;
    if (!el || (el as any).__vue_app__) {
        return;
    }

    const diplomApp = createApp(DiplomApp);
    diplomApp.use(hljsVuePlugin);
    diplomApp.use(vuetify);
    diplomApp.mount(el);
};

mountDiplomApp("#diplomCustomizerApp");
mountDiplomApp("#diplomReportApp");

// Allow late-mounting after DOM updates or AJAX-rendered content loads
(window as any).mountDiplomCustomizerApp = () => mountDiplomApp("#diplomCustomizerApp");
(window as any).mountDiplomReportApp = () => mountDiplomApp("#diplomReportApp");