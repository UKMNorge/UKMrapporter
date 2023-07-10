<template>
    <div class="as-container container">
        <NodeVue :nodes="nodes" />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import NodeVue from './Node.vue'
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';
import Person from '../objects/Person';
import Kommune from '../objects/Kommune';


// var properties = [
//     new NodeProperty('Navn'),
//     new NodeProperty('Kommune'),
// ]

// Accepts objects
var nodes: NodeObj[] =[
    new Person('aa3kjsd', 'Ole Nordby', 18),
    new Kommune('unnd25', "Asker", 'Viken'),
    // new NodeObj(1, 'Kommune', []),
    // new NodeObj(2, 'Person', []),
];


// Accepts filtering
// Accepts grupping

// Callback on change

</script>


<style scoped>

</style>