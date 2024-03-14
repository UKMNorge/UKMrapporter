<template>
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading />
        </div>
        <div v-else>
            <div v-if="alleQuestions.length < 1" class="no-data">            
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
import SubnodePerson from '../objects/subnodesLeafs/SubnodePerson';
import Subnode from '../objects/Subnode';
import SubnodeItem from '../objects/SubnodeItem';
import Contacts from '../components/Contacts.vue';
import PhantomLoading from '../components/PhantomLoading.vue';





var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Deltakerskjema';
var loading = ref(true);
var dataFetched = ref(false);
var alleQuestions = ref([]);
var rapportName = 'Deltakerskjema';

Person.hasUnique = false;
Person.properties.push(new NodeProperty('getSvar', 'Svar', true));


DefaultNode.properties = [];
DefaultNode.properties.push(new NodeProperty('getNavn', 'Spørsmål', true));


var nodeStructure = [DefaultNode, Person].reverse();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Person, rapportName);
var rootNodes : any = repo.getRootNodes();


async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_deltakerSkjema',
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

    var questions = (<any>response.root.children);
    alleQuestions.value = questions;
    
    
    // Spørsmål
    for(var key of Object.keys(questions)) {
        var question = questions[key];
        var questionNode = new DefaultNode(question.obj.id, question.obj.sporsmal);
        questionNode.setClassName('Spørsmål');
        root.addChild(questionNode);
        
        // Personer
        for(var key of Object.keys(question.children)) {
            var person = question.children[key];
            var personObj = person.obj;
            
            var personNode = new Person(personObj.id, personObj.fornavn + ' ' + personObj.etternavn, personObj.fodselsdato, personObj.mobil, personObj.epost);
            

            // Deltakerskjema har en spesiell type som heter kontakt
            if(question.obj.type == 'kontakt' && personObj.svar != 'Ikke besvart') {
                let subnode = new Subnode();

                let personerSubnode = [new SubnodePerson('', personObj.svar['navn'], '', personObj.svar['mobil'], personObj.svar['epost'])]
                subnode.addItem(new SubnodeItem('Personer', personerSubnode));
                personNode.addSubnode(subnode);
            }
            else {
                personNode.setSvar(personObj.svar);
            }
            questionNode.addChild(personNode);

        }
        
        repo = new Repo(root, nodeStructure, Person, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
        
    }
}

</script>