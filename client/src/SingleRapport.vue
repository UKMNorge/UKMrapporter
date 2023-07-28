<template>
    <div>
        <AlleDeltakere v-if="getRapportId() == 'alleDeltakere'" />
        <LedereOversikt v-if="getRapportId() == 'ledereOversikt'" />
        <Intoleranser v-if="getRapportId() == 'intoleranser'" />
    </div>
</template>

<script setup lang="ts">
import AlleDeltakere from './rapporter/AlleDeltakere.vue'
import LedereOversikt from './rapporter/LedereOversikt.vue'
import Intoleranser from './rapporter/Intoleranser.vue';
import { ref, onMounted } from 'vue'


const director = (<any>window).director;

function getRapportId() : string {
    return director.getParam('rapportId');
}

</script>

<style scoped>

</style>