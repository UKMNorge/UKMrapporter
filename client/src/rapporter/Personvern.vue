<template> 
    <div v-if="dataFetched">
        <div v-if="alleHendelser.length < 1" class="no-data">
            <NoData />
        </div>
        <div v-else>
            <div class="as-container container">
                <div class="as-margin-top-space-8 as-margin-bottom-space-8">
                    <h1 class="">{{ rapportName }}</h1>
                </div>
            </div>
    
            <DownloadsVue :repo="repo" />
    
            <MenyVue :root="root" :groupingNode="DefaultNode" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>
    
            <div class="container as-container">
                <div v-for="(r, key) in rootNodes" :key="key">
                    <div class="as-margin-top-space-7" >
                        <Table :leafNode="Innslag" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
  
<script setup lang="ts">
// Fra pakke UKM Komponenter
import Table from '../components/table/Table.vue'
import { ref } from 'vue';
import MenyVue from '../components/Meny.vue';
import NoData from '../components/NoData.vue';
import DownloadsVue from '../components/Downloads.vue';
import Arrangement from '../objects/rapporter/Arrangement';
import DefaultNode from '../objects/rapporter/DefaultNode';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import Innslag from '../objects/rapporter/Innslag';
import Subnode from '../objects/Subnode';
import SubnodeItem from '../objects/SubnodeItem';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
var loading = ref(true);
var dataFetched = ref(false);
var alleHendelser = ref([]);
var rapportName = 'Personvern';

var nodeStructure = [DefaultNode, Innslag].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Innslag, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_personvern',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    
    var hendelser = (<any>response.root.children);

    console.log(hendelser);
    if(hendelser.length < 1) {
        dataFetched.value = true;
        return;
    }

    alleHendelser = hendelser;

    for(var key of Object.keys(hendelser)) {
        var hendelse = hendelser[key];
        var hendelseObj = hendelse.obj;

        var hendelseNode = new DefaultNode(hendelseObj.id, hendelseObj.navn);
        hendelseNode.setClassName('Hendelse');
        root.addChild(hendelseNode);
        console.log(root);

        // Innslag
        for(var key of Object.keys(hendelse.children)) {
            var innslag = hendelse.children[key];
            var innslagObj = innslag.obj;

            var innslagNode = new Innslag(innslagObj.id, innslagObj.navn, innslagObj.type.name, innslagObj.sesong);

            // adding subnodes
            if(innslagObj['alle_titler']) {
                var tittelSubnode = new Subnode();
                var tittelText = '';
                for(var tittel of innslagObj['alle_titler']) {
                    tittelText += tittel['tittel'] + ' ';
                }
                tittelSubnode.addItem(new SubnodeItem('Titler', tittelText));
                innslagNode.addSubnode(tittelSubnode);
            }

            if(innslagObj['alle_personer']) {
                var personerSubnode = new Subnode();
                var personText = '';
                for(var i = 0; i < innslagObj['alle_personer'].length; i++) {
                    var person = innslagObj['alle_personer'][i];
                    personText += person['navn'] + ' ';
                    personText += (person['kategori'] == 'u15' ? '(under 15 år) - ' : '');
                    personText += person['status'];
                    personText += (person['kategori'] == 'u15' ? ' (foresatt: ' + person['foresatt'] + ' ' + (person['foresatt_mobil'] ? person['foresatt_mobil'] : '') + ' | foresatt svar: ' + person['foresatt_status'] + ')' : '');

                    if(i+1 < innslagObj['alle_personer'].length) {
                        personText += ', ';
                    }
                }
                personerSubnode.addItem(new SubnodeItem('Personer', personText));
                innslagNode.addSubnode(personerSubnode);
            }

            hendelseNode.addChild(innslagNode);
        }

        repo = new Repo(root, nodeStructure, Innslag, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
        
    }

    if(hendelser) {
        dataFetched.value = true;
    }
}

</script>