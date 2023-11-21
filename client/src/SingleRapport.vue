<template>
    <div>
        <AlleDeltakere v-if="getRapportId() == 'alleDeltakere'" />
        <LedereOversikt v-if="getRapportId() == 'ledereOversikt'" />
        <Intoleranser v-if="getRapportId() == 'intoleranser'" />
        <Nominasjoner v-if="getRapportId() == 'nominasjoner'" /> 
        <Personvern v-if="getRapportId() == 'personvern'" /> 
        <Kunstkatalog v-if="getRapportId() == 'kunstkatalog'" /> 
        <div v-else>
            <p>Rapport finnes ikke</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import AlleDeltakere from './rapporter/AlleDeltakere.vue'
import LedereOversikt from './rapporter/LedereOversikt.vue'
import Intoleranser from './rapporter/Intoleranser.vue';
import Nominasjoner from './rapporter/Nominasjoner.vue';
import Personvern from './rapporter/Personvern.vue';
import Kunstkatalog from './rapporter/Kunstkatalog.vue';
import { ref, onMounted } from 'vue'


const director = (<any>window).director;

function getRapportId() : string {
    return director.getParam('rapportId');
}

</script>

<style scoped>

</style>