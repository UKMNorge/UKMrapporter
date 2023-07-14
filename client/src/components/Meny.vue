<template>
    <div class="as-container container">
        <div class="rapport-meny-components nop as-card-1 as-padding-space-4">
            <NodeVue :root="root" />
            <FilterVue :root="root" :nodeFilter="kommune" />
        </div>
    </div>
</template>

<script setup lang="ts">

import NodeVue from './Node.vue';
import FilterVue from './Filter.vue';
import RootNode from '../objects/RootNode';
import Kommune from '../objects/rapporter/Kommune';


var kommune = Kommune;

const props = defineProps<{
    root: RootNode|null
}>();



// Accepts filtering
// Accepts grupping

// Callback on change

</script>


<style scoped>
    .rapport-meny-components {
        width: 100%;
        display: flex;
        border-radius: var(--radius-medium);
    }
</style>