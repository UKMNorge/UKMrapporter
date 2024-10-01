<template> 
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading :rapportName="rapportName" />
        </div>
        <div v-else>
            <div v-if="alleInnslagTyper.length < 1" class="no-data">
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
        
                <MenyVue :root="root" :groupingNode="DefaultNode" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>
        
                <div class="container as-container">
                    <div v-for="(r, key) in rootNodes" :key="key">
                        <div class="as-margin-top-space-7" >
                            <Table :leafNode="Nominasjon" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
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
import Nominasjon from '../objects/rapporter/Nominasjon';
import Arrangement from '../objects/rapporter/Arrangement';
import DefaultNode from '../objects/rapporter/DefaultNode';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import Contacts from '../components/Contacts.vue';
import PhantomLoading from '../components/PhantomLoading.vue';


var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Nominasjoner';
var loading = ref(true);
var dataFetched = ref(false);
var alleInnslagTyper = ref([]);
var rapportName = 'Nominasjoner';

var nodeStructure = [DefaultNode, Arrangement, Nominasjon].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Nominasjon, rapportName);
var rootNodes : any = repo.getRootNodes();

Nominasjon.hasUnique = true;

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
        controller: 'rapport_nominasjoner',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    var innslagTyper = (<any>response.root.children);
    
    alleInnslagTyper = innslagTyper;

    if(innslagTyper.length < 1) {
        dataFetched.value = true;
        return;
    }
    
    // Innslag type (brukes DefaultNode)
    for(var key of Object.keys(innslagTyper)) {
        var type = innslagTyper[key];
        var typeObj = type.obj;

        var typeNode = new DefaultNode(typeObj.id, typeObj.name);
        typeNode.setClassName('Type');
        root.addChild(typeNode);

        
        // Arrangementer
        for(var key of Object.keys(type.children)) {
            var arrangement = type.children[key];
            var arrangementObj = arrangement.obj;
    
            var arrangementNode = new Arrangement(arrangementObj.id, arrangementObj.navn, arrangementObj.type, arrangementObj.sted);
    
            typeNode.addChild(arrangementNode);
            
            // Nominasjoner
            for(var key of Object.keys(arrangement.children)) {
                var nominasjon = arrangement.children[key];
                var nominasjonObj = nominasjon.obj;
    
    
                var nominasjonNode = new Nominasjon(
                    nominasjonObj.id, 
                    nominasjonObj.navn, 
                    nominasjonObj.voksenskjema, 
                    nominasjonObj.deltakerskjema, 
                    nominasjonObj.videresendt, 
                    nominasjonObj.status,
                    nominasjonObj.mobil,
                    nominasjonObj.epost
                );
                arrangementNode.addChild(nominasjonNode);
            }
            
            repo = new Repo(root, nodeStructure, Nominasjon, rapportName);
            loading.value = false;
            rootNodes = repo.getRootNodes();
        }
    }

    // Change property name at Arrangement
    for(var prop of Arrangement.getAllProperies()) {
        if(prop.method == 'getNavn') {
            prop.navn = 'Avsender';
        }
    }

    // Change property name at DefaultNode
    for(var prop of DefaultNode.getAllProperies()) {
        if(prop.method == 'getNavn') {
            prop.navn = 'Innslag type';
        }
    }

    if(innslagTyper.length > 0) {
        noData.value = false;
    }

    if(innslagTyper) {
        dataFetched.value = true;
    }
}

</script>