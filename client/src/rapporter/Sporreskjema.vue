<template>
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading />
        </div>
        <div v-else>
            <div v-if="alleArrangementer.length < 1" class="no-data">            
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
                            <Table :leafNode="DefaultNode" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
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
import Person from '../objects/rapporter/Person';
import DefaultNode from '../objects/rapporter/DefaultNode';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import NodeProperty from './../objects/NodeProperty';
import Contacts from '../components/Contacts.vue';
import Sporsmaal from '../objects/rapporter/Sporsmaal';
import Arrangement from '../objects/rapporter/Arrangement';
import Subnode from '../objects/Subnode';
import SubnodeItem from '../objects/SubnodeItem';
import SubnodePerson from '../objects/subnodesLeafs/SubnodePerson';
import PhantomLoading from '../components/PhantomLoading.vue';



var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Infoskjema';
var loading = ref(true);
var dataFetched = ref(false);
var alleArrangementer = ref([]);
var rapportName = 'Spørreskjema';


DefaultNode.properties = [];
DefaultNode.properties.push(new NodeProperty('getNavn', 'Svar', true));

Arrangement.properties = [
    new NodeProperty('getNavn', 'Arrangement navn', false),
    new NodeProperty('getType', 'Type', false),
    new NodeProperty('getSted', 'Sted', false),
];

var nodeStructure = [Arrangement, Sporsmaal, DefaultNode].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, DefaultNode, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_sporreskjema',
    };

    try {
       var response = await spaInteraction.runAjaxCall('/', 'POST', data);
       dataFetched.value = true;
    // The Promise was fulfilled, you can use the response here.
    } catch (error) {
        // The Promise was rejected, you can handle the error here.
        console.error(error);
        alert('error');
    }

    var arrangementer = (<any>response.root.children);
    alleArrangementer.value = arrangementer;
    
    
    // Spørsmål
    for(var key of Object.keys(arrangementer)) {
        var arrangement = arrangementer[key];

        var arrangementNode = new Arrangement(arrangement.obj.id, arrangement.obj.navn, arrangement.obj.type, arrangement.obj.sted);
        root.addChild(arrangementNode);
        
        // Questions
        for(var key of Object.keys(arrangement.children)) {
            var question = arrangement.children[key];
            var questionObj = question.obj;
            
            var questionNode = new Sporsmaal(questionObj.id, questionObj.name, questionObj.type);
            arrangementNode.addChild(questionNode);
            
            // Answers
            for(var key of Object.keys(question.children)) {
                var svar = question.children[key];
                var svarObj = svar.obj;


                var svar = svarObj.svar;
                if(svarObj.type == 'kontakt') {
                    svar = 'Kontakt';
                }
                else if(svarObj.type == 'janei') {
                    svar = svarObj.svar == true || svarObj.svar == 'true' ? 'Ja' : 'Nei';
                }

                var svarNode = new DefaultNode(svarObj.id, svar);
                svarNode.setClassName('Svar');
                questionNode.addChild(svarNode);

                // Add person as subnode if type is 'kontakt'
                if(svarObj.type == 'kontakt') {
                    // Add person as subnode
                    var subnode = new Subnode();
                    
                    var person = {
                        navn: svarObj.svar.navn,
                        mobil: svarObj.svar.mobil,
                        epost: svarObj.svar.epost,
                    };

                    var personSubnode = new SubnodePerson('', person['navn'], '', person['mobil'], person['epost']);
                    subnode.addItem(new SubnodeItem('Person', [personSubnode]));
                    svarNode.addSubnode(subnode);
                }
            }
        }
        
        repo = new Repo(root, nodeStructure, DefaultNode, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
        
        console.log(root);
    }
}

</script>