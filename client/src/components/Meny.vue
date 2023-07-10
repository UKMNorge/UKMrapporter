<template>
    <div class="as-container container">
        <NodeVue :root="root" />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import NodeVue from './Node.vue'
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';
import RootNode from '../objects/RootNode';
import Person from '../objects/Person';
import Kommune from '../objects/Kommune';


// var properties = [
//     new NodeProperty('Navn'),
//     new NodeProperty('Kommune'),
// ]

// Accepts objects[
var root = new RootNode();

// Adding Kommune(s)
var k0 = new Kommune('unndgsg25', "Asker", 'Viken');
var k1 = new Kommune('aaawggd64', "Lillestrøm", 'Viken');
var k2 = new Kommune('igwdjhh98', "Oslo", 'Viken');


var p1 = new Person('aa3kjsd', 'Ole Nordby', 18);
var p2 = new Person('ag3ghss', 'Lene Langvei', 21);
var p3 = new Person('lkjdklj', 'Dag Steinfjell', 20);

// Adding Person(s) to Kommune(s)
k0.addChildren([
    p1, p2, p3
]);

// Adding children to Kommune(s) to Root
root.addChildren([
    k0, k1, k2
]);

// root.addChildren([
//     new Person('aa3kjsd', 'Ole Nordby', 18),
//     new Person('ag3ghss', 'Lene Langvei', 21),
//     new Person('lkjdklj', 'Dag Steinfjell', 20),
// ]);
    

// Accepts filtering
// Accepts grupping

// Callback on change

</script>


<style scoped>

</style>