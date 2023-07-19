<template> 
    <div>
        <MenyVue :root="root" :gruppingUpdateCallback="(n)=>{repo.gruppingUpdateCallback(n)}" />

        <div class="container as-container">
            <div v-for="(r, key) in rootNodes" :key="key">
                <div class="as-margin-top-space-7" >
                    <Table :key="key" :loading="loading" :keys="repo.getTableKeys()" :root="r" />
                </div>
            </div>
        </div>

        <button @click="getDataAjax()">Get data</button>

    </div>
</template>
  
<script setup lang="ts">
// Fra pakke UKM Komponenter
import Table from '../components/table/Table.vue'
import { ref } from 'vue';
import MenyVue from '../components/Meny.vue';
import Person from '../objects/rapporter/Person';
import Kommune from '../objects/rapporter/Kommune';
import Fylke from '../objects/rapporter/Fylke';
import RootNode from '../objects/RootNode';
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import Repo from '../objects/Repo';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global

const spaInteraction = new SPAInteraction(null, ajaxurl);

async function getDataAjax() {
    var data = {
        action: 'UKMrapporter_ajax',
        controller: 'rapport_alleDeltakere',
    };

    var response = await spaInteraction.runAjaxCall('/', 'POST', data);

    return response;
}

Person.hasUnique = true;

var loading = ref(true);

setTimeout(() => {
    loading.value = false;
}, 1000);


// ----- DATA -----
var root = new RootNode();


// Adding Kommune(s)
var k0 = new Kommune('k1', "Tana", 'Nordland');
var k1 = new Kommune('k2', "Lillestrøm", 'Viken');
var k2 = new Kommune('k3', "Oslo", 'Viken');

var f0 = new Fylke('f1', 'Viken');
var f1 = new Fylke('f2', 'Nordland');

// Adding children to Kommune(s) to Fylke
f0.addChildren([
    k1, k2
]);
f1.addChildren([
    k0
])

// Adding children to Fylke(s) to Root
root.addChildren([
    f0, f1
]);


var p1 = new Person('p1', 'Ole Nordby', 18, '46516256',  'Ole_Nordby@gmail.com');
var p2 = new Person('p2', 'Lene Langvei', 21, '55516200', 'Lene_Langvei@gmail.com');
var p3 = new Person('p3', 'Dag Steinfjell', 17, '12316211', 'Dag_Steinfjell@gmail.com');
var p4 = new Person('p4', 'Stein Olavsgård', 16, '56516222', 'Stein_Olavsgård@gmail.com');
var p5 = new Person('p5', 'Gerard Sørgård', 20, '47816353', 'Gerard_Sørgård@gmail.com');


// Adding Person(s) to Kommune(s)
k0.addChildren([
    p1, p2, p3
]);

k1.addChildren([
    p4
]);

k2.addChildren([
    p5
]);


var repo = new Repo(root);
var rootNodes : any = repo.getRootNodes();

</script>