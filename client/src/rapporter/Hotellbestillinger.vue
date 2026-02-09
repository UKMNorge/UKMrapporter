<script setup lang="ts">
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

var ajaxurl : string = (<any>window).ajaxurl;
const spaInteraction = new SPAInteraction(null, ajaxurl);
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=Overnatting';
var loading = ref(true);
var dataFetched = ref(false);
var alleFylker = ref([]);
var rapportName = 'Hotellbestillinger';

Fylke.setProperties([new NodeProperty('getNavn', 'Fylke', true)]);
DefaultNode.properties = [new NodeProperty('getNavn', 'Natt', true)];
Leder.properties = [
    new NodeProperty('getNavn', 'Navn', true),
    new NodeProperty('getType', 'Type', true),
    new NodeProperty('getMobil', 'Mobil', false),
    new NodeProperty('getEpost', 'Epost', false),
    new NodeProperty('getGodkjent', 'Godkjent', true),
];

var nodeStructure = [Fylke, DefaultNode, Leder].reverse();
var root = new RootNode();
var repo = new Repo(root, nodeStructure, Leder, rapportName);
var rootNodes : any = repo.getRootNodes();
const emit = defineEmits();
const noData = ref(true);

watch(noData, (newVal) => {
    emit('update:noData', newVal);
});

defineExpose({ noData });

getDataAjax();

async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_hotellbestillinger',
    };

    try {
        var response = await spaInteraction.runAjaxCall('/', 'POST', data);
        dataFetched.value = true;
    } catch (error) {
        console.error(error);
    }

    var fylker = (<any>response.root.children);
    alleFylker.value = fylker;

    for (var fylkeKey of Object.keys(fylker)) {
        var fylke = fylker[fylkeKey];
        var fylkeNode = new Fylke(fylke.obj.id, fylke.obj.navn);
        root.addChild(fylkeNode);

        for (var nattKey of Object.keys(fylke.children)) {
            var natt = fylke.children[nattKey];
            var dateParts = natt.obj.dato.split('_');
            var day = parseInt(dateParts[0]);
            var month = parseInt(dateParts[1]) - 1;
            var monthStr = month < 10 ? '0' + String(month) : String(month);
            var year = parseInt(dateParts[2]);
            var nattNode = new DefaultNode(natt.obj.id, (day + '.' + monthStr + '.' + year));
            nattNode.setClassName('Natt');
            fylkeNode.addChild(nattNode);

            for (var lederKey of Object.keys(natt.children)) {
                var leder = natt.children[lederKey];
                var obj = leder.obj;

                var lederNode = new Leder(obj.id, obj.navn, obj.type, obj.mobil, obj.epost, obj.fylkeNavn, obj.godkjent);
                nattNode.addChild(lederNode);
            }
        }
    }

    repo = new Repo(root, nodeStructure, Leder, rapportName);
    loading.value = false;
    rootNodes = repo.getRootNodes();
    repo.antall.value = true;

    if (Object.keys(fylker).length > 0) {
        noData.value = false;
    }
}
</script>

<template>
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading :rapportName="rapportName" />
        </div>
        <div v-else>
            <div v-if="Object.keys(alleFylker).length < 1" class="no-data">
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
                <MenyVue :root="root" :visAntall="true" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :groupingNode="Fylke" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>
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
