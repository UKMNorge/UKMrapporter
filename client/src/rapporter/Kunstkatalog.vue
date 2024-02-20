<template> 
    <div v-if="dataFetched">
        <div v-if="alleHendelser.length < 1" class="no-data">
            <NoData :oldRapportLenke="oldRapportLenke" />
        </div>
        <div v-else>
            <div class="as-container container">
                <div class="as-margin-top-space-8 as-margin-bottom-space-8">
                    <h1 class="">{{ rapportName }}</h1>
                </div>
            </div>
    
            <div class="as-container buttons container as-margin-bottom-space-8 as-display-flex">
                <DownloadsVue :repo="repo" />
                <ToOldRapport :redirectLink="oldRapportLenke" />
            </div>
    
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
import ToOldRapport from '../components/ToOldRapport.vue';
import Arrangement from '../objects/rapporter/Arrangement';
import DefaultNode from '../objects/rapporter/DefaultNode';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import Innslag from '../objects/rapporter/Innslag';
import Subnode from '../objects/Subnode';
import SubnodeItem from '../objects/SubnodeItem';
import NodeProperty from './../objects/NodeProperty';
import SubnodePerson from '../objects/subnodesLeafs/SubnodePerson';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Kunstkatalog';
var loading = ref(true);
var dataFetched = ref(false);
var alleHendelser = ref([]);
var rapportName = 'Kunstkatalog';

var nodeStructure = [DefaultNode, Innslag].reverse();

// Activating properies
Innslag.properties.push(new NodeProperty('getSjanger', 'Sjanger', true));
Innslag.properties.push(new NodeProperty('getTid', 'Tid', true));
Innslag.properties.push(new NodeProperty('getFylke', 'Fylke', true));
Innslag.properties.push(new NodeProperty('getKommune', 'Kommune', false));
Innslag.properties.push(new NodeProperty('getBeskrivelse', 'Beskrivelse', false));
Innslag.properties.push(new NodeProperty('getRolle', 'Rolle', false));
// Person.properties.push(new NodeProperty('getTekstIntoleranser', 'Melding Intoleranser', true))

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Innslag, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_kunstkatalog',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    
    var hendelser = (<any>response.root.children);

    console.log(hendelser);
    if(hendelser.length < 1) {
        dataFetched.value = true;
        return;
    }

    alleHendelser = hendelser;

    var counter = 0;
    for(var key of Object.keys(hendelser)) {
        var hendelse = hendelser[key];
        var hendelseObj = hendelse.obj;

        var hendelseNode = new DefaultNode(hendelseObj.id, hendelseObj.navn);
        hendelseNode.setClassName('Hendelse');
        root.addChild(hendelseNode);
        

        // Innslag
        for(var key of Object.keys(hendelse.children)) {
            var innslag = hendelse.children[key];
            var innslagObj = innslag.obj;

            var innslagNode = new Innslag(innslagObj.id, ++counter + '. ' + innslagObj.navn, innslagObj.type.name, innslagObj.sesong);

            // Adding extra properies
            innslagNode.setSjanger(innslagObj.sjanger ? innslagObj.sjanger : '-');
            innslagNode.setTid(innslagObj.tid);
            innslagNode.setFylke(innslagObj.fylke);
            innslagNode.setKommune(innslagObj.kommune);
            innslagNode.setBeskrivelse(innslagObj.beskrivelse);
            innslagNode.setRolle(innslagObj.rolle);

            console.error(innslagObj['alle_personer']);
            if(innslagObj['alle_personer']) {
                var personerSubnode = new Subnode();
                var personer = [];
                for(var person of innslagObj['alle_personer']) {
                    console.warn(person);
                    personer.push(new SubnodePerson('rolle: ' + person['rolle'], person['fornavn'], person['etternavn'], person['mobil'], person['epost']));
                }
                personerSubnode.addItem(new SubnodeItem('Personer', personer));
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