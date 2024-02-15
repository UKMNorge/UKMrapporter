<template> 
    <div v-if="dataFetched">
        <div v-if="alleFylker.length < 1" class="no-data">
            <NoData />
        </div>
        <div v-else>
            <div class="as-container container">
                <div class="as-margin-top-space-8 as-margin-bottom-space-8">
                    <h1 class="">{{ rapportName }}</h1>
                </div>
            </div>
    
            <div class="as-container buttons container as-margin-bottom-space-8 as-display-flex">
                <DownloadsVue :repo="repo" />
                <ToOldRapport :redirectLink="'?page=UKMrapporter&action=rapport&rapport=Intoleranser'" />
            </div>
            
            <MenyVue :root="root" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>
    
            <div class="container as-container">
                <div v-for="(r, key) in rootNodes" :key="key">
                    <div class="as-margin-top-space-7" >
                        <Table :leafNode="Person" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
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
import Arrangement from '../objects/rapporter/Arrangement';
import DownloadsVue from '../components/Downloads.vue';
import ToOldRapport from '../components/ToOldRapport.vue';
import Fylke from '../objects/rapporter/Fylke';
import Person from '../objects/rapporter/Person';
import RootNode from '../objects/RootNode';
import NodeProperty from './../objects/NodeProperty';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
var loading = ref(true);
var dataFetched = ref(false);
var alleFylker = ref([]);
var rapportName = 'Intoleranse / allergi';

Person.hasUnique = true;
// Adding property tekstIntoleranser and listeIntoleranser
Person.properties.push(new NodeProperty('getListeIntoleranser', 'Intoleranser', true))
Person.properties.push(new NodeProperty('getTekstIntoleranser', 'Melding Intoleranser', true))

const nodeStructure = [Fylke, Arrangement, Person].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Person, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_intoleranser',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    
    var fylker = (<any>response.root.children);
    alleFylker = fylker;

    // Fylker
    for(var key of Object.keys(fylker)) {
        var fylke = fylker[key];
        var fylkeNode = new Fylke(fylke.obj.id, fylke.obj.navn);
        root.addChild(fylkeNode);
        
        // Arrangementer
        for(var key of Object.keys(fylke.children)) {
            var arrangement = fylke.children[key];
            var arrangementObj = arrangement.obj;

            console.log(arrangementObj.navn);
            var arrangementNode = new Arrangement(arrangementObj.id, arrangementObj.navn, arrangementObj.type, arrangementObj.sted);
            fylkeNode.addChild(arrangementNode);
            
            // Person
            for(var key of Object.keys(arrangement.children)) {
                var person = arrangement.children[key];
                var personObj = person.obj;

                var personNode = new Person(personObj.id, personObj.navn, personObj.alder, personObj.mobil, personObj.epost)
                
                // Those values are activatated at Person statically by adding NodeProperty
                personNode.setTekstIntoleranser(personObj.tekstIntoleranser);
                personNode.setListeIntoleranser(personObj.listeIntoleranser);
                
                arrangementNode.addChild(personNode);
            }

        }
        
        repo = new Repo(root, nodeStructure, Person, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
    }

    if(fylker) {
        dataFetched.value = true;
    }
}

</script>