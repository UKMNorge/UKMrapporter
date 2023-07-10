<template>
    <div class="as-container container">
        <Node :nodes="nodes" />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import $ from "jquery";
import Node from './Node.vue'
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';


var properties = [
    new NodeProperty('Navn'),
    new NodeProperty('Kommune'),
]

// Accepts objects
var nodes: NodeObj[] =[
    new NodeObj(0, 'Fylke', properties),
    new NodeObj(1, 'Kommune', []),
    new NodeObj(2, 'Person', []),
];


// Accepts filtering
// Accepts grupping

// Callback on change

function toggleFunction(event : any) {
    $(event.currentTarget).toggleClass('active');
}
</script>


<style scoped>

</style>