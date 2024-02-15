<template>
    <div>
        <div v-if="getRapportId() == 'alleDeltakere'">
            <AlleDeltakere />
        </div>
        <div v-else-if="getRapportId() == 'ledereOversikt'">
            <LedereOversikt />
        </div>
        <div v-else-if="getRapportId() == 'intoleranser'">
            <Intoleranser />
        </div>
        <div v-else-if="getRapportId() == 'nominasjoner'">
            <Nominasjoner />
        </div>
        <div v-else-if="getRapportId() == 'personvern'">
            <Personvern />
        </div>
        <div v-else-if="getRapportId() == 'kunstkatalog'">
            <Kunstkatalog />
        </div>
        <div v-else-if="getRapportId() == 'deltakerSkjema'">
            <DeltakerSkjema />
        </div>
        <div v-else-if="getRapportId() == 'program'">
            <Program />
        </div>
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
import Program from './rapporter/Program.vue';
import DeltakerSkjema from './rapporter/DeltakerSkjema.vue';

import { ref, onMounted } from 'vue'


const director = (<any>window).director;

function getRapportId() : string {
    return director.getParam('rapportId');
}

</script>

<style scoped>

</style>