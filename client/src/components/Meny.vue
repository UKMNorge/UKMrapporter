<template>
    <div class="as-container container">
        <div class="rapport-meny-components nop as-card-1 as-padding-space-3">
            <NodeVue :root="root" />
            <div class="filter-grouping-div col-xs-3 nop">
                <FilterVue :root="root" :filteringNode="filteringNode" />
                <GroupingVue :root="root" :groupingNode="groupingNode" :gruppingUpdateCallback="gruppingUpdateCallback" />
                <TableFilterVue :tableCallback="tableCallback"  />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">

import NodeVue from './Node.vue';
import FilterVue from './Filter.vue';
import GroupingVue from './Grouping.vue';
import TableFilterVue from './TableFilter.vue';
import RootNode from '../objects/RootNode';
import NodeObj from '../objects/NodeObj';
import type { PropType } from 'vue';

const props = defineProps({
    root: {
        type: Object as () => RootNode | null,
        required: true,
    },
    gruppingUpdateCallback: {
        type: Function as PropType<(n: NodeObj) => void>,
        required: true,
    },
    tableCallback: {
        type: Function as PropType<(antall: Boolean, telling: Boolean) => void>,
        required: true,
    },
    groupingNode: {
        type: Object as () => NodeObj,
        default: null, // Set a default value of null
    },
    filteringNode: {
        type: Object as () => NodeObj,
        required: false,
        default: null, // Set a default value of null
    },
});


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
        display: block;
    }
</style>