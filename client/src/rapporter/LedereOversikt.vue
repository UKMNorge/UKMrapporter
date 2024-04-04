<template> 
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading :rapportName="rapportName" />
        </div>
        <div v-else>
            <div v-if="alleFylker.length < 1" class="no-data">
                <NoData :oldRapportLenke="oldRapportLenke" />
            </div>
            <div v-else>
                <div class="as-container container">
                    <div class="as-margin-top-space-8 as-margin-bottom-space-8">
                        <h1 class="">{{ rapportName }}</h1>
                    </div>
                </div>

                <div class="as-container buttons container as-margin-bottom-space-6 as-display-flex">
                    <DownloadsVue :repo="repo" />
                    <ToOldRapport :redirectLink="oldRapportLenke" />
                    <Contacts :repo="repo" />
                </div>

                <MenyVue :root="root" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>

                <div class="container as-container">
                    <div v-for="(r, key) in rootNodes" :key="key">
                        <div class="as-margin-top-space-7" >
                            <Table :leafNode="Leder" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
                        </div>
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
import Leder from '../objects/rapporter/Leder';
import Fylke from '../objects/rapporter/Fylke';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import Contacts from '../components/Contacts.vue';
import PhantomLoading from '../components/PhantomLoading.vue';


var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=LedereOversikt';
var loading = ref(true);
var dataFetched = ref(false);
var alleFylker = ref([]);
var rapportName = 'Ledere Oversikt';

Leder.hasUnique = true;

const nodeStructure = [Fylke, Arrangement, Leder].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Leder, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_ledereOversikt',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    
    var fylker = (<any>response.root.children);

    alleFylker.value = fylker;

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
            
            // Leder
            if(Object.keys(arrangement.children)) {
                
            }
            for(var key of Object.keys(arrangement.children)) {
                var leder = arrangement.children[key];
                var lederObj = leder.obj;

                var lederNode = new Leder(lederObj.id, lederObj.navn, lederObj.type, lederObj.mobil, lederObj.epost);
                arrangementNode.addChild(lederNode);
            }

        }
        
        repo = new Repo(root, nodeStructure, Leder, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
        
    }
    if(fylker) {
        dataFetched.value = true;
    }
}

</script>