<template>
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading :rapportName="rapportName" />
        </div>
        <div v-else>
            <div v-if="alleNetter.length < 1" class="no-data">            
                <div class="as-display-flex as-margin-top-space-4">
                    <h5 class="as-margin-auto">
                        Du må <a href="index.php">lage et skjema med ekstra spørsmål til deltakerne</a> for å få noe utav denne rapporten.
                    </h5>
                </div>

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
        
                <MenyVue :root="root" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :groupingNode="Arrangement" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>
        
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
import DefaultNode from '../objects/rapporter/DefaultNode';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import NodeProperty from './../objects/NodeProperty';
import Contacts from '../components/Contacts.vue';
import Fylke from '../objects/rapporter/Fylke';
import Arrangement from '../objects/rapporter/Arrangement';
import Subnode from '../objects/Subnode';
import SubnodeItem from '../objects/SubnodeItem';
import SubnodePerson from '../objects/subnodesLeafs/SubnodePerson';
import PhantomLoading from '../components/PhantomLoading.vue';
import Leder from '../objects/rapporter/Leder';



var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Infoskjema';
var loading = ref(true);
var dataFetched = ref(false);
var alleNetter = ref([]);
var rapportName = 'Hotellbestillinger';


DefaultNode.properties = [];
DefaultNode.properties.push(new NodeProperty('getNavn', 'Svar', true));

Arrangement.properties = [
    new NodeProperty('getNavn', 'Arrangement navn', false),
    new NodeProperty('getType', 'Type', false),
    new NodeProperty('getSted', 'Sted', false),
];

var nodeStructure = [DefaultNode, Fylke, Leder].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Leder, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_hotellbestillinger',
    };

    try {
       var response = await spaInteraction.runAjaxCall('/', 'POST', data);
       dataFetched.value = true;
    // The Promise was fulfilled, you can use the response here.
    } catch (error) {
        // The Promise was rejected, you can handle the error here.
        console.error(error);
    }

    var netter = (<any>response.root.children);
    alleNetter.value = netter;
    
    
    // Netter
    for(var key of Object.keys(netter)) {
        var natt = netter[key];

        var nattNode = new DefaultNode(natt.obj.id, natt.obj.navn);
        nattNode.setClassName('Svar');
        root.addChild(nattNode);
        
        // Fylker
        for(var key of Object.keys(natt.children)) {
            var fylke = natt.children[key];
            var fylkeObj = fylke.obj;
            
            var fylkeNode = new Fylke(fylkeObj.id, fylkeObj.navn);
            nattNode.addChild(fylkeNode);
            
            // Ledere
            for(var key of Object.keys(fylke.children)) {
                var leder = fylke.children[key];
                var lederObj = leder.obj;
                
                var lederNode = new Leder(lederObj.id, lederObj.navn, lederObj.type, lederObj.mobil, lederObj.epost);
                fylkeNode.addChild(lederNode);
            }
        }
        
        repo = new Repo(root, nodeStructure, Leder, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
        
        console.log(root);
    }
}

</script>