<template> 
    <div>
        <MenyVue :root="root" />

        <div class="container as-container">
            <div class="as-margin-top-space-7">
                <Table :loading="loading" :keys="tableKeys" :values="personer" />
            </div>
        </div>
    </div>
</template>
  
<script setup lang="ts">
// Fra pakke UKM Komponenter
import Table from '../components/test/Table.vue'
import MenyVue from '../components/Meny.vue';
import Person from '../objects/Person';
import Kommune from '../objects/Kommune';
import RootNode from '../objects/RootNode';


var loading : boolean = false;
var tableKeys : {navn : string, method : string}[]= Person.getKeysForTable();
var personer : Person[] = [new Person('aa3kjsd', 'Ole Nordby', 18)];

console.log(tableKeys);
console.log(personer);
// ----- DATA -----
var root = new RootNode();

// Adding Kommune(s)
var k0 = new Kommune('unndgsg25', "Asker", 'Viken');
var k1 = new Kommune('aaawggd64', "Lillestrøm", 'Viken');
var k2 = new Kommune('igwdjhh98', "Oslo", 'Viken');


var p1 = new Person('aa3kjsd', 'Ole Nordby', 18);
var p2 = new Person('ag3ghss', 'Lene Langvei', 21);
var p3 = new Person('lkjdklj', 'Dag Steinfjell', 20);

personer = [p1, p2, p3];

// Adding Person(s) to Kommune(s)
k0.addChildren([
    p1, p2, p3
]);

// Adding children to Kommune(s) to Root
root.addChildren([
    k0, k1, k2
]);
        

</script>