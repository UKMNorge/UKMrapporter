import { createApp } from "vue";
import UKMrapporter from "./UKMrapporter.vue";
import Singlerapport from "./SingleRapport.vue";

import hljs from "highlight.js/lib/core";
import javascript from "highlight.js/lib/languages/javascript";
import css from "highlight.js/lib/languages/css";
import xml from "highlight.js/lib/languages/xml";
import hljsVuePlugin from "@highlightjs/vue-plugin";
import "highlight.js/styles/base16/dracula.css";

hljs.registerLanguage("xml", xml);
hljs.registerLanguage("javascript", javascript);
hljs.registerLanguage("css", css);

const app = createApp(UKMrapporter);

// Make Director global
import { Director } from 'ukm-spa/Director';

var director = new Director();
(<any>window).director = director;


app.use(hljsVuePlugin);

app.mount("#rapporterApp");


const app2 = createApp(Singlerapport);
app2.use(hljsVuePlugin);
app2.mount("#singleRapport");