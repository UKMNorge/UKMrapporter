<template>
    <div class="as-container container">
        <NodeVue :root="root" />
    </div>
</template>

<script setup lang="ts">

import NodeVue from './Node.vue'
import RootNode from '../objects/RootNode';


const props = defineProps<{
    root: RootNode|null
}>();



// Accepts filtering
// Accepts grupping

// Callback on change

</script>


<style scoped>

</style>