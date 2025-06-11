<template> 
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading :rapportName="rapportName" />
        </div>
        <div v-else>
            <div v-if="alleInnslags.length < 1" class="no-data">
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
                            <Table :leafNode="Innslag" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</template>
  
<script setup lang="ts">
import Table from '../components/table/Table.vue';
import { ref, watch, defineEmits } from 'vue';
import MenyVue from '../components/Meny.vue';
import NoData from '../components/NoData.vue';
import DownloadsVue from '../components/Downloads.vue';
import ToOldRapport from '../components/ToOldRapport.vue';
import RootNode from '../objects/RootNode';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';
import Innslag from '../objects/rapporter/Innslag';
import Subnode from '../objects/Subnode';
import SubnodeItem from '../objects/SubnodeItem';
import SubnodeStringItem from '../objects/SubnodeStringItem';
import SubnodePerson from '../objects/subnodesLeafs/SubnodePerson';
import NodeProperty from './../objects/NodeProperty';
import Contacts from '../components/Contacts.vue';
import PhantomLoading from '../components/PhantomLoading.vue';
import DefaultNode from '../objects/rapporter/DefaultNode';

var ajaxurl : string = (<any>window).ajaxurl;

const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Personvern';
var loading = ref(true);
var dataFetched = ref(false);
var alleInnslags = ref([]);
var rapportName = 'Personvern';

var nodeStructure = [DefaultNode, Innslag].reverse();
const director = (<any>window).director;

var is_landsfestivalen = director.getParam('isLand');

if(is_landsfestivalen == 'true' || is_landsfestivalen == true) {
    Innslag.properties.push(new NodeProperty('getArrangement', 'Arrangement', true));
    Innslag.properties.push(new NodeProperty('getFylke', 'Fylke', true));
}

const smsDialogRef = ref();

getDataAjax();

var root = new RootNode();
var repo = new Repo(root, nodeStructure, Innslag, rapportName);
var rootNodes : any = repo.getRootNodes();

const emit = defineEmits();
const noData = ref(true);
watch(noData, (newVal) => {
    emit('update:noData', newVal);
});

defineExpose({ noData });

async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_personvern',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);
    dataFetched.value = true;
    var statusNodes = (<any>response.root.children);

    alleInnslags.value = statusNodes;

    for(var statusKey of Object.keys(statusNodes)) {
        var statusNodeRaw = statusNodes[statusKey];
        var statusNode = new DefaultNode(statusNodeRaw.obj.id, statusNodeRaw.obj.navn);
        root.addChild(statusNode);

        for(var key of Object.keys(statusNodeRaw.children)) {
            var innslag = statusNodeRaw.children[key];
            var innslagObj = innslag.obj;

            if (statusNode.hasChild(innslagObj.id)) continue;

            var innslagNode = new Innslag(innslagObj.id, innslagObj.navn, innslagObj.type.name, innslagObj.sesong);
            innslagNode.setArrangement(innslagObj.arrangement);
            innslagNode.setFylke(innslagObj.fylke);

            if(innslagObj['alle_titler']) {
                var tittelSubnode = new Subnode();
                var tittelText = '';
                for(var tittel of innslagObj['alle_titler']) {
                    tittelText += tittel['tittel'] + ' ';
                }
                tittelSubnode.addItem(new SubnodeItem('Titler', [new SubnodeStringItem(tittelText)]));
                innslagNode.addSubnode(tittelSubnode);
            }

            if(innslagObj['alle_personer']) {
                var personerSubnode = new Subnode();
                var personerStringItems = [];
                for(var i = 0; i < innslagObj['alle_personer'].length; i++) {
                    var person = innslagObj['alle_personer'][i];

                    var personText = '';
                    if(person['kategori'] == 'u13') {
                        personText += '(under 13 år) - ' + person['status'];
                        if(person['foresatt']) {
                            personText += ' (foresatt: ' + person['foresatt'] + ' ' + (person['foresatt_mobil'] ? person['foresatt_mobil'] : '') + ' | foresatt svar: ' + person['foresatt_status'] + ')';
                        }
                    } else {
                        personText += person['status'] + (person['kategori'] == 'u15' ? ' (under 15 år)' : '');
                    }

                    let DOMClass = person['godkjent'] ? 'success-subnode-item' : 'danger-subnode-item';
                    let persStringItem = new SubnodePerson(personText, person['fornavn'], person['etternavn'], person['mobil'], person['epost']);
                    persStringItem.setDOMClass(DOMClass);
                    personerStringItems.push(persStringItem);
                }
                personerSubnode.addItem(new SubnodeItem('Personer', personerStringItems));
                innslagNode.addSubnode(personerSubnode);
            }

            statusNode.addChild(innslagNode);
        }
    }

    repo = new Repo(root, nodeStructure, Innslag, rapportName);
    loading.value = false;
    rootNodes = repo.getRootNodes();

    if(Object.keys(statusNodes).length > 0) {
        noData.value = false;
    }
}
</script>