<template> 
    <div>
        <div v-if="!dataFetched">
            <PhantomLoading :rapportName="rapportName" />
        </div>
        <div v-else>
            <div v-if="alleNetter.length < 1" class="no-data">
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

                <MenyVue :root="root" :groupingNode="Natt" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" :tableCallback="(antall, telling) => {repo.tableCallback(antall, telling)}"/>

                <div class="container as-container">
                    <div v-for="(r, key) in rootNodes" :key="key">
                        <div class="as-margin-top-space-7">
                            <Table :leafNode="Leder" :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" :visAntall="repo.antall" :visTelling="repo.telling" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
  
<script setup lang="ts">
import { ref, watch, defineEmits } from 'vue';
import Table from '../components/table/Table.vue';
import MenyVue from '../components/Meny.vue';
import NoData from '../components/NoData.vue';
import DownloadsVue from '../components/Downloads.vue';
import ToOldRapport from '../components/ToOldRapport.vue';
import Contacts from '../components/Contacts.vue';
import PhantomLoading from '../components/PhantomLoading.vue';

import Arrangement from '../objects/rapporter/Arrangement';
import Leder from '../objects/rapporter/Leder';
import Fylke from '../objects/rapporter/Fylke';
import RootNode from '../objects/RootNode';
import Repo from '../objects/Repo';
import DefaultNode from '../objects/rapporter/DefaultNode';
import Natt from '../objects/rapporter/Natt';
import NodeProperty from './../objects/NodeProperty';


import { SPAInteraction } from 'ukm-spa/SPAInteraction';

const ajaxurl: string = (<any>window).ajaxurl;
const oldRapportLenke = '?page=UKMrapporter&action=rapport&rapport=LedereOversikt';
const spaInteraction = new SPAInteraction(null, ajaxurl);

var loading = ref(true);
var dataFetched = ref(false);
var alleNetter = ref([]);
var rapportName = 'Ledere Oversikt';

Leder.hasUnique = true;

Leder.properties.push(new NodeProperty('getSted', 'Sted', true));
Arrangement.properties = [
    new NodeProperty('getNavn', 'Arrangement navn', false),
    new NodeProperty('getType', 'Type', false),
    new NodeProperty('getSted', 'Sted', false),
];



// Structure: Root → Natt → LederType → Fylke → Arrangement → Leder
const nodeStructure = [Natt, DefaultNode, Fylke, Arrangement, Leder].reverse();

const root = new RootNode();
let repo = new Repo(root, nodeStructure, Leder, rapportName);
let rootNodes: any = repo.getRootNodes();

const emit = defineEmits();
const noData = ref(true);

watch(noData, (newVal) => {
    emit('update:noData', newVal);
});

defineExpose({ noData });

async function getDataAjax() {
    const data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_ledereOvernatting',
    };

    const response = await spaInteraction.runAjaxCall('/', 'POST', data);
    const netter = (<any>response.root.children);
    alleNetter.value = netter;

    for (const nattKey of Object.keys(netter)) {
        const natt = netter[nattKey];
        const nattNode = new Natt(nattKey, natt.obj.dato);
        root.addChild(nattNode);

        for (const lederTypeKey of Object.keys(natt.children)) {
            const lederType = natt.children[lederTypeKey];
            const lederTypeNode = new DefaultNode(lederTypeKey, lederType.obj.type);
            lederTypeNode.setClassName('LederType');
            nattNode.addChild(lederTypeNode);

            for (const fylkeKey of Object.keys(lederType.children)) {
                const fylke = lederType.children[fylkeKey];
                const fylkeObj = fylke.obj;

                const fylkeNode = new Fylke(fylkeObj.id, fylkeObj.navn);
                lederTypeNode.addChild(fylkeNode);

                for (const arrangementKey of Object.keys(fylke.children)) {
                    const arrangement = fylke.children[arrangementKey];
                    const arrangementObj = arrangement.obj;

                    const arrangementNode = new Arrangement(
                        arrangementObj.id,
                        arrangementObj.navn,
                        arrangementObj.type,
                        arrangementObj.sted
                    );
                    fylkeNode.addChild(arrangementNode);

                    for (const lederKey of Object.keys(arrangement.children)) {
                        const leder = arrangement.children[lederKey];
                        const lederObj = leder.obj;

                        const lederNode = new Leder(
                            lederObj.id,
                            lederObj.navn,
                            lederObj.type,
                            lederObj.mobil,
                            lederObj.epost,
                            '',
                            lederObj.godkjent
                        );
                        arrangementNode.addChild(lederNode);

                        lederNode.setSted(lederObj.sted);
                    }
                }
            }
        }

        repo = new Repo(root, nodeStructure, Leder, rapportName);

        loading.value = false;
        rootNodes = repo.getRootNodes();
    }

    console.log('root BEFORE SORTING');
    console.log(root);

    // Sort root children by date
    root.children = root.children.sort((a, b) => {
        if (a instanceof Natt && b instanceof Natt) {
            const currentYear = new Date().getFullYear();

            const [dayA, monthA] = a.getNavn().split('-').map(Number);
            const [dayB, monthB] = b.getNavn().split('-').map(Number);


            const dateA = new Date(currentYear, monthA - 1, dayA);
            const dateB = new Date(currentYear, monthB - 1, dayB);

            console.log(`Comparing dates: ${dateA} vs ${dateB}`);
            console.log(`Comparing dates: ${dateA.getTime()} vs ${dateB.getTime()}`);

            return dateA.getTime() - dateB.getTime();
        }
        return 0;
    });

    console.log('root AFTER SORTING');
    console.log(root);


    if (Object.keys(netter).length > 0) {
        noData.value = false;
    }

    if (netter) {
        dataFetched.value = true;
    }
}

getDataAjax();
</script>
