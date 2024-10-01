<template>
    <div>
        <div v-if="getRapportId() == 'alleDeltakere'">
            <AlleDeltakere @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'ledereOversikt'">
            <LedereOversikt @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'intoleranser'">
            <Intoleranser @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'nominasjoner'">
            <Nominasjoner @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'personvern'">
            <Personvern @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'kunstkatalog'">
            <Kunstkatalog @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'deltakerSkjema'">
            <DeltakerSkjema @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'program'">
            <Program @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'sporreskjema'">
            <Sporreskjema @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else-if="getRapportId() == 'hotellbestillinger'">
            <Hotellbestillinger @update:noData="handleNoDataUpdate" />
        </div>
        <div v-else>
            <p>Rapport finnes ikke</p>
        </div>

        <!-- Show the feedback component if there is data -->
        <div v-if="noData==false" class="as-margin-top-space-8">
            <Feedback class="as-padding-top-space-2 feedback" />
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
import Sporreskjema from './rapporter/Sporreskjema.vue';
import Hotellbestillinger from './rapporter/Hotellbestillinger.vue';
import Feedback from './components/feedback/Feedback.vue';


import { ref, onMounted } from 'vue'

const noData = ref(true);

const director = (<any>window).director;

function getRapportId() : string {
    return director.getParam('rapportId');
}

function handleNoDataUpdate(noDataValue: boolean) {
    noData.value = noDataValue;
    console.log(noDataValue);
}

</script>

<style scoped>

</style>