<template>
    <div>
        <div class="as-container container">
            <PermanentNotification typeNotification="warning" :tittel="'Justering av diplomer'" :description="'På grunn av ulikheter i skriverinnstillinger, må diplomer ofte justeres for å passe korrekt inn i malene.'" />
        </div>

        <div class="aFourPageDiv as-margin-top-space-4 as-margin-bottom-space-4">
            <div class="margin-on-page top-margin"></div>
            <div class="person" :style="{ bottom: (bottomPosition*0.6) + 'mm', left: (leftPosition*0.6) + 'mm' }">
                <h4>Ola Normann</h4>
                <span>UKM-Festivalen</span>
            </div>
            <div class="margin-on-page bottom-margin"></div>
        </div>

        <div class="input-position">
            <div>
                <input type="range" v-model="leftPosition" min="-250" max="250" step="1" />
                <input type="number" class="form-group input-bottom-value" name="diplom_positon_y" :value="leftPosition" id="diplom_positon_y" readonly >
                <span> millimeter</span>
            </div>
        </div>

        <div class="input-position">
            <div>
                <input type="range" v-model="bottomPosition" min="-30" max="275" step="1" />
                <input type="number" class="form-group input-bottom-value" name="diplom_positon_x" :value="bottomPosition" id="diplom_positon_x" readonly >
                <span> millimeter</span>
            </div>
        </div>

        <div class="as-container container space-message">
            <PermanentNotification v-if="bottomPosition < -4 || bottomPosition > 250 || leftPosition < -240 || leftPosition > 240" typeNotification="danger" :tittel="'Teksten kan flytte utenfor siden'" :description="'På grunn av margininnstillinger kan teksten flytte utenfor siden. For å løse dette, kan du justere margin i Word-dokumentet.'" />
        </div>
    </div>
</template>

<script lang="ts">
import { PermanentNotification } from 'ukm-components-vue3';

export default {
    data() {
        return {
            bottomPosition : 0 as number,
            leftPosition: 0 as number,
        }
    },
    components : {
        PermanentNotification : PermanentNotification
    },
}
</script>

<style scoped>
.as-container {
    width: calc(21cm);
    padding: 10px;
}
.aFourPageDiv {
    width: calc(21cm*0.6);
    height: calc(29.7cm*0.6);
    margin-left: auto;
    margin-right: auto;
    background-color: white;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #e0e0e0;
    position: relative;
    overflow: hidden;
    font-size: 12px;
    font-family: Arial, sans-serif;
    color: #333;
    line-height: 1.5;
    box-sizing: border-box;
    page-break-after: always;
}
.aFourPageDiv .person{
    position: absolute;
    font-size: 12px;
    font-weight: bold;
    color: #333;
    line-height: 1.5;
    box-sizing: border-box;
    page-break-after: always;
    left: 0;
    right: 0;
    text-align: center;
    margin-bottom: calc(20mm*0.6); /* Dette er margin som Word har. 0.6 er scala på hele siden */
}
.input-position {
    width: 100%;
    display: flex;
}
.input-position div {
    margin: auto;
}
.input-bottom-value {
    width: 55px;
    text-align: center;
    margin-left: 10px;
}
.margin-on-page {
    position: absolute;
    width: 100%;
    left: 0;
    right: 0;
    height: 2px;
}
.margin-on-page.top-margin {
    top: calc(10mm);
    border: dashed 1px var(--color-primary-grey-light);
}
.margin-on-page.bottom-margin {
    bottom: calc(10mm);
    border: dashed 1px var(--color-primary-grey-light);
}
.space-message {
    min-height: 130px;
}
</style>