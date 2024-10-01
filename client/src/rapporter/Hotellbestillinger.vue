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
        
                <MenyVue :root="root" :visAntall="true" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :groupingNode="DefaultNode" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>
        
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
import { ref, watch, defineEmits } from 'vue';
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
import PhantomLoading from '../components/PhantomLoading.vue';
import Leder from '../objects/rapporter/Leder';



var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Overnatting';
var loading = ref(true);
var dataFetched = ref(false);
var alleNetter = ref([]);
var rapportName = 'Hotellbestillinger';


DefaultNode.properties = [];
DefaultNode.properties.push(new NodeProperty('getNavn', 'Natt', true));

Leder.properties = [
    new NodeProperty('getNavn', 'Navn', true),
    new NodeProperty('getType', 'Type', true),
    new NodeProperty('getMobil', 'Mobil', false),
    new NodeProperty('getEpost', 'Epost', false),
    new NodeProperty('getFylke', 'Fylke', true),
];

var nodeStructure = [DefaultNode, Leder].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Leder, rapportName);
var rootNodes : any = repo.getRootNodes();

const emit = defineEmits();
const noData = ref(true);
// Watch for changes to noData
watch(noData, (newVal) => {
    emit('update:noData', newVal); // Emit an event when noData changes
});

defineExpose({ noData });

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

        var dateParts = natt.obj.dato.split('_');
        var day = parseInt(dateParts[0]);
        var month = parseInt(dateParts[1]) - 1; // Months are zero-based in JavaScript Date object
        var monthStr = String(month);
        if (month < 10) {
            monthStr = '0' + String(month);
        }
        var year = parseInt(dateParts[2])

        var nattNode = new DefaultNode(natt.obj.natt_id, (day + '.' + monthStr + '.' + year));
        nattNode.setClassName('Natt');
        root.addChild(nattNode);
        
            
        // Ledere
        for(var key of Object.keys(natt.children)) {

            var leder = natt.children[key];
            var lederObj = leder.obj;
            
            var lederNode = new Leder(lederObj.id, lederObj.navn, lederObj.type, lederObj.mobil, lederObj.epost, lederObj.fylkeNavn);
            nattNode.addChild(lederNode);
        }
        
        repo = new Repo(root, nodeStructure, Leder, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
        repo.antall.value = true;
        
        console.log(root);
    }

    if(netter.length > 0) {
        noData.value = false;
    }
}

</script>