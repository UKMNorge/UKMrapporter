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
    
            <DownloadsVue :repo="repo" />
    
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
import DownloadsVue from '../components/Downloads.vue';
import Person from '../objects/rapporter/Person';
import Kommune from '../objects/rapporter/Kommune';
import Fylke from '../objects/rapporter/Fylke';
import Innslag from '../objects/rapporter/Innslag';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
var loading = ref(true);
var dataFetched = ref(false);
var alleFylker = ref([]);
var rapportName = 'Alle Deltakere';

Person.hasUnique = true;

var nodeStructure = [Fylke, Kommune, Innslag, Person].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Person, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_alleDeltakere',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    var fylker = (<any>response.root.children);
    alleFylker.value = fylker;
    
    console.log(alleFylker.value.length);
    // Fylker
    for(var key of Object.keys(fylker)) {
        var fylke = fylker[key];
        var fylkeNode = new Fylke(fylke.obj.id, fylke.obj.navn);
        root.addChild(fylkeNode);
        
        // Kommuner
        for(var key of Object.keys(fylke.children)) {
            var kommune = fylke.children[key];
            var kommuneObj = kommune.obj;

            var kommuneNode = new Kommune(kommuneObj.id, kommuneObj.navn);
            fylkeNode.addChild(kommuneNode);
            
            // Innslag
            for(var key of Object.keys(kommune.children)) {
                var innslag = kommune.children[key];
                var innslagObj = innslag.obj;

                var innslagNode = new Innslag(innslagObj.id, innslagObj.navn, innslagObj.type.name, innslagObj.sesong);
                kommuneNode.addChild(innslagNode);
                
                for(var key of Object.keys(innslag.children)) {
                    var person = innslag.children[key];
                    console.log(person);
                    
                    innslagNode.addChild(new Person(person.id, (person.fornavn + ' ' + person.etternavn), person.fodselsdato, person.mobil, person.epost));
                }
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