<template>
    <div class="as-container container">
        <div class="rapport-meny-components nop as-card-1 as-padding-space-3">
            <NodeVue :root="root" />
        <div class="filter-grouping-div col-xs-3 nop">
            <FilterVue :root="root" />
            <GroupingVue :root="root" :gruppingUpdateCallback="gruppingUpdateCallback" />
        </div>
        </div>
    </div>
</template>

<script setup lang="ts">

import NodeVue from './Node.vue';
import FilterVue from './Filter.vue';
import GroupingVue from './Grouping.vue';
import RootNode from '../objects/RootNode';
import NodeObj from '../objects/NodeObj';



const props = defineProps<{
    root: RootNode|null,
    gruppingUpdateCallback : (n : NodeObj)=>void,
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
    .filter-grouping-div {
        display: flex;
        flex-wrap: wrap;
        float: revert;
    }
</style>