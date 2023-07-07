<template>
    <!-- <div>
        <div class="tab-button">
            <button @click="openTab('first')">First Tab</button>
            <button @click="openTab('second')">Second Tab</button>
        </div>

        <div class="tabs">
            <FirstTab ref="firstTab" />
        </div>
    </div> -->
    <div>
        <div class="rapporter-tabs container-fluid nop as-margin-top-space-4">
            <button @click="openTab(0)" :class="activeTab == 0 ? 'active' : ''" class="tab as-padding-space-1">Personer</button>
            <button @click="openTab(1)" :class="activeTab == 1 ? 'active' : ''" class="tab as-padding-space-1">Program</button>
            <button @click="openTab(2)" :class="activeTab == 2 ? 'active' : ''" class="tab as-padding-space-1">Videresending</button>
            <button @click="openTab(3)" :class="activeTab == 3 ? 'active' : ''" class="tab as-padding-space-1">Statistikk</button>
        </div>
        
        <div class="tab-spaces as-margin-top-space-8 container">
            <!-- Personer -->
            <div v-if="activeTab == 0" class="rapport-side-tab">
                <div v-for="btn in tabsButtons[activeTab]" class="outer col-xs-6 nop as-margin-bottom-space-4">
                    <div class="rapport-button as-padding-space-2 as-box-shadow-card">
                        <div class="left as-margin-right-space-2">
                            <div class="icon as-padding-space-1 as-margin-right-space-2" v-html="btn.icon">
                            </div>
                            <div clas="text">
                                <h4>{{ btn.title }}</h4>
                                <p>{{ btn.description }}</p>
                            </div>
                        </div>
                        <div class="right"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg></div>
                    </div>
                </div>

               
            </div>


        </div>
    </div>

</template>

<script setup lang="ts">
import FirstTab from './tabs/FirstTab.vue';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import { Director } from 'ukm-spa/Director';
import { ref, onMounted } from 'vue'

var activeTab = ref(0);

var tabsButtons = {
    // tab 0
    [0]: [
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><circle cx="6" cy="4" r="2"></circle><path d="M9 7H3a1 1 0 0 0-1 1v7h2v7h4v-7h2V8a1 1 0 0 0-1-1z"></path><circle cx="17" cy="4" r="2"></circle><path d="M20.21 7.73a1 1 0 0 0-1-.73h-4.5a1 1 0 0 0-1 .73L12 14h2l-1 4h2v4h4v-4h2l-1-4h2z"></path></svg>'},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "#", icon: ''},
    ]
}

onMounted(function () {
    console.log('mounted!');
    console.log('UKMrapporter.vue:  onMounted()');
    console.log(SPAInteraction);
    console.log(Director);
    
    openTab(0);
});
    
function openTab(tabId : number) {
    activeTab.value = tabId;

    console.log(activeTab.value);
}
</script>

<style scoped>
    .rapporter-tabs {
        display: flex;
    }
    .rapporter-tabs .tab {
        width: 100%;
        font-size: 14px;
        background: transparent;
        border: none;
        border-bottom: solid 1px var(--color-primary-grey-light);
    }
    .rapporter-tabs .tab.active {
        border-bottom: solid 2px var(--color-primary-bla-500);
    }
    .rapport-side-tab .outer .rapport-button {
        border-radius: var(--radius-medium);
        background: var(--color-primary-white);
        display: flex;
    }
    .rapport-side-tab .outer .rapport-button .right {
        margin: auto;
        margin-right: 0;
        display: flex;
    }
    .rapport-side-tab .outer .rapport-button .right svg {
        fill: var(--color-primary-bla-500) !important;
    }
    .rapport-side-tab .outer:nth-child(odd) {
        padding-right: calc(2 * var(--initial-space-box));
    }
    .rapport-side-tab .outer:nth-child(even) {
        padding-left: calc(2 * var(--initial-space-box));
    }
    .rapport-side-tab .outer .rapport-button .left {
        display: flex
    }
    .rapport-side-tab .outer .rapport-button .left .icon {
        height: 40px;
        width: 40px;
        display: flex;
        border-radius: 50%;
        background: var(--color-primary-bla-500);
    }
    .rapport-side-tab .outer .rapport-button .left .icon svg {
        fill: var(--color-primary-white) !important;
    }

</style>