<template> 
    <div v-if="dataFetched">
        <div class="as-container container">
            <div class="as-margin-top-space-8 as-margin-bottom-space-8">
                <h1 class="">{{ rapportName }}</h1>
            </div>
        </div>

        <DownloadsVue :repo="repo" />

        <MenyVue :root="root" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>

        <div class="container as-container">
            <div v-for="(r, key) in rootNodes" :key="key">
                <div class="as-margin-top-space-7" >
                    <Table :leafNode="Nominasjon" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
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
import DownloadsVue from '../components/Downloads.vue';
import Nominasjon from '../objects/rapporter/Nominasjon';
import Fylke from '../objects/rapporter/Fylke';
import Arrangement from '../objects/rapporter/Arrangement';
import Innslag from '../objects/rapporter/Innslag';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
var loading = ref(true);
var dataFetched = ref(false);
var rapportName = 'Nominasjoner';

// Nominasjon.hasUnique = true;

var nodeStructure = [Arrangement, Nominasjon].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Nominasjon, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_nominasjoner',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    var arrangementer = (<any>response.root.children);
    
    // Arrangementer
    for(var key of Object.keys(arrangementer)) {
        var arrangement = arrangementer[key];
        var arrangementObj = arrangementer[key];

        var arrangementNode = new Arrangement(arrangementObj.id, arrangementObj.navn, arrangementObj.type, arrangementObj.sted);

        root.addChild(arrangementNode);
        
        // Kommuner
        for(var key of Object.keys(arrangement.children)) {
            var nominasjon = arrangement.children[key];
            var nominasjonObj = nominasjon.obj;


            var nominasjonNode = new Nominasjon(nominasjonObj.id, nominasjonObj.navn, nominasjonObj.voksenskjema, nominasjonObj.deltakerskjema, nominasjonObj.videresendt);
            arrangementNode.addChild(nominasjonNode);
        }
        
        repo = new Repo(root, nodeStructure, Nominasjon, rapportName);
        loading.value = false;
        dataFetched.value = true;
        rootNodes = repo.getRootNodes();

    }
}

</script>