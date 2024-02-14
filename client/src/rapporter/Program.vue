<template> 
    <div v-if="dataFetched">
        <div v-if="alleHendelser.length < 1" class="no-data">
            <NoData />
        </div>
        <div v-else>
            <div class="as-container container">
                <div class="as-margin-top-space-8 as-margin-bottom-space-8">
                    <h1 class="">{{ rapportName }}</h1>
                </div>
            </div>
    
            <DownloadsVue :repo="repo" />
    
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
import Arrangement from '../objects/rapporter/Arrangement';
import DefaultNode from '../objects/rapporter/DefaultNode';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import Innslag from '../objects/rapporter/Innslag';
import Subnode from '../objects/Subnode';
import SubnodeItem from '../objects/SubnodeItem';
import NodeProperty from './../objects/NodeProperty';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global


const spaInteraction = new SPAInteraction(null, ajaxurl);
var loading = ref(true);
var dataFetched = ref(false);
var alleHendelser = ref([]);
var rapportName = 'Program';

var nodeStructure = [DefaultNode, Innslag].reverse();

// Activating properies
Innslag.properties.push(new NodeProperty('getSjanger', 'Sjanger', true));
Innslag.properties.push(new NodeProperty('getVarighet', 'Varighet', true));
Innslag.properties.push(new NodeProperty('getFylke', 'Fylke', false));
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
        controller: 'rapport_program',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    
    var hendelser = (<any>response.root.children);

    console.log(hendelser);
    if(hendelser.length < 1) {
        dataFetched.value = true;
        return;
    }

    alleHendelser = hendelser;

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

            var innslagNode = new Innslag(innslagObj.id, innslagObj.navn, innslagObj.type.name, innslagObj.sesong);

            // Adding extra properies
            innslagNode.setSjanger(innslagObj.sjanger ? innslagObj.sjanger : '-');
            innslagNode.setVarighet(secondsToTimeFormat(innslagObj.tid));
            innslagNode.setFylke(innslagObj.fylke);
            innslagNode.setKommune(innslagObj.kommune);
            innslagNode.setBeskrivelse(innslagObj.beskrivelse);
            innslagNode.setRolle(innslagObj.rolle);

            if(innslagObj['alle_titler'].length > 0) {
                var titlerSubnode = new Subnode();
                var titler = [];
                for(var tittel of innslagObj['alle_titler']) {
                    titler.push(tittel['tittel'] + (tittel['sekunder'] ? ' ('+ secondsToTimeFormat(tittel['sekunder']) +')' : ''));
                }
                titlerSubnode.addItem(new SubnodeItem('Titler', titler));
                innslagNode.addSubnode(titlerSubnode);
            }

            if(innslagObj['alle_personer']) {
                var personerSubnode = new Subnode();
                var personer = [];
                for(var person of innslagObj['alle_personer']) {
                    personer.push(person['fornavn'] +' '+ person['etternavn'] +' '+ person['alder'] +' - '+ person['rolle']);
                }
                personerSubnode.addItem(new SubnodeItem('Personer', personer));
                innslagNode.addSubnode(personerSubnode);
            }

            hendelseNode.addChild(innslagNode);
        }

        repo = new Repo(root, nodeStructure, Innslag, rapportName);
        loading.value = false;
        rootNodes = repo.getRootNodes();
        repo.telling.value = true;
    }

    if(hendelser) {
        dataFetched.value = true;
    }
}

function secondsToTimeFormat(duration : number) {
    const hrs = ~~(duration / 3600);
    const mins = ~~((duration % 3600) / 60);
    const secs = ~~duration % 60;

    let ret = "";

    if (hrs > 0) {
        ret += "" + hrs + "t";
        if(mins > 0) {
            ret += " ";
        }
    }

    if(mins > 0) {
        ret += "" + mins + "min";
        if(secs > 0) {
            ret += " ";
        }
    }

    if(secs > 0) {
        ret += "" + secs + "sek";
    }

    return ret;
}

</script>