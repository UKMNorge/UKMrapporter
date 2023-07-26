<template>
    <div>
        <h1>Hello from single rapport</h1>
        <AlleDeltakere v-if="getRapportId() == 'alleDeltakere'" />
        <LedereOversikt v-if="getRapportId() == 'ledereOversikt'" />
    </div>
</template>

<script setup lang="ts">
import AlleDeltakere from './rapporter/AlleDeltakere.vue'
import LedereOversikt from './rapporter/LedereOversikt.vue'
import { ref, onMounted } from 'vue'


const director = (<any>window).director;

function getRapportId() : string {
    return director.getParam('rapportId');
}

</script>

<style scoped>

</style>