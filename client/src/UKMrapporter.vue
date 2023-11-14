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
    <div class="as-container">
        <div class="rapporter-tabs container-fluid nop as-margin-top-space-4">
            <button @click="openTab(0)" :class="activeTab == 0 ? 'active' : ''" class="tab as-padding-space-1">Personer</button>
            <button @click="openTab(1)" :class="activeTab == 1 ? 'active' : ''" class="tab as-padding-space-1">Program</button>
            <button @click="openTab(2)" :class="activeTab == 2 ? 'active' : ''" class="tab as-padding-space-1">Videresending</button>
            <button @click="openTab(3)" :class="activeTab == 3 ? 'active' : ''" class="tab as-padding-space-1">Statistikk</button>
            <button @click="openTab(4)" :class="activeTab == 4 ? 'active' : ''" class="tab as-padding-space-1">UKM-festivalen</button>
        </div>
        
        <div class="tab-spaces as-margin-top-space-8 container">
            <!-- Personer -->
            <div class="rapport-side-tab">
                <div v-for="btn in tabsButtons[activeTab]" :key="btn.title" class="outer col-xs-6 nop as-margin-bottom-space-4">
                    <a :href="btn.link">
                        <div class="rapport-button as-padding-space-2 as-box-shadow-card">
                            <div class="left as-margin-right-space-2">
                                <div class="icon as-padding-space-1 as-margin-right-space-2" v-html="btn.icon">
                                </div>
                                <div class="text">
                                    <h4>{{ btn.title }}</h4>
                                    <p>{{ btn.description }}</p>
                                </div>
                            </div>
                            <div class="right"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg></div>
                        </div>
                    </a>              
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

var tabsButtons : any = {
    // tab 0
    [0]: [
        {title: 'Alle innslag', description: 'Informasjon om alle som er påmeldt arrangementet.', link: "?page=UKMrapporter&action=rapport&rapport=AlleInnslag", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><circle cx="6" cy="4" r="2"></circle><path d="M9 7H3a1 1 0 0 0-1 1v7h2v7h4v-7h2V8a1 1 0 0 0-1-1z"></path><circle cx="17" cy="4" r="2"></circle><path d="M20.21 7.73a1 1 0 0 0-1-.73h-4.5a1 1 0 0 0-1 .73L12 14h2l-1 4h2v4h4v-4h2l-1-4h2z"></path></svg>'},
        {title: 'Alle deltakere', description: 'Informasjon om deltakere som er påmeldt arrangementet.', link: "?page=UKMrapporter&action=rapportVue&rapportId=alleDeltakere", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><circle cx="6" cy="4" r="2"></circle><path d="M9 7H3a1 1 0 0 0-1 1v7h2v7h4v-7h2V8a1 1 0 0 0-1-1z"></path><circle cx="17" cy="4" r="2"></circle><path d="M20.21 7.73a1 1 0 0 0-1-.73h-4.5a1 1 0 0 0-1 .73L12 14h2l-1 4h2v4h4v-4h2l-1-4h2z"></path></svg>'},
        {title: 'Diplomer', description: 'Last ned ferdig wordfil klar for utskrift.', link: "?page=UKMrapporter&action=rapport&rapport=Diplom", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 -1 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M2 7v1l11 4 9-4V7L11 4z"></path><path d="M4 11v4.267c0 1.621 4.001 3.893 9 3.734 4-.126 6.586-1.972 7-3.467.024-.089.037-.178.037-.268V11L13 14l-5-1.667v3.213l-1-.364V12l-3-1z"></path></svg>'},
        {title: 'Inn og utlevering', description: 'Innsjekk-lister for kunstverk og filmer', link: "?page=UKMrapporter&action=rapport&rapport=InnOgUtlevering", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 21 24" style="fill: #fff;transform: ;msFilter:;"><path d="M4 7h11v2H4zm0 4h11v2H4zm0 4h7v2H4zm15.299-2.708-4.3 4.291-1.292-1.291-1.414 1.415 2.706 2.704 5.712-5.703z"></path></svg>'},
        {title: 'Intoleranse / allergi', description: 'Deltakernes allergier og intoleranser.', link: "?page=UKMrapporter&action=rapportVue&rapportId=intoleranser", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="m11.13 4.41 4.23 4.23L14.3 9.7l-4.24-4.24-1.77 1.77 4.24 4.24-1.06 1.06-4.24-4.24-1.77 1.77L9.7 14.3l-1.06 1.06-4.23-4.23C1.86 14 1.55 18 3.79 20.21a5.38 5.38 0 0 0 3.85 1.5 8 8 0 0 0 5.6-2.47l6-6c2.87-2.87 3.31-7.11 1-9.45s-6.24-1.93-9.11.62z"></path></svg>'},
        {title: 'Ledere oversikt', description: 'Informasjon om ledere', link: "?page=UKMrapporter&action=rapportVue&rapportId=ledereOversikt", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M7.5 6.5C7.5 8.981 9.519 11 12 11s4.5-2.019 4.5-4.5S14.481 2 12 2 7.5 4.019 7.5 6.5zM20 21h1v-1c0-3.859-3.141-7-7-7h-4c-3.86 0-7 3.141-7 7v1h17z"></path></svg>'},
        {title: 'Nominasjoner', description: 'Alle nominsasjoner', link: "?page=UKMrapporter&action=rapportVue&rapportId=nominasjoner", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 21 24" style="fill: #fff;transform: ;msFilter:;"><path d="M8 12.052c1.995 0 3.5-1.505 3.5-3.5s-1.505-3.5-3.5-3.5-3.5 1.505-3.5 3.5 1.505 3.5 3.5 3.5zM9 13H7c-2.757 0-5 2.243-5 5v1h12v-1c0-2.757-2.243-5-5-5zm11.294-4.708-4.3 4.292-1.292-1.292-1.414 1.414 2.706 2.704 5.712-5.702z"></path></svg>'},
        {title: 'Personvern', description: 'Alle deltakere som ikke vil bli filmet eller tatt bilde av', link: "?page=UKMrapporter&action=rapport&rapport=Personvern", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 25" style="fill: #fff;transform: ;msFilter:;"><path d="M20 12c0-1.103-.897-2-2-2h-1V7c0-2.757-2.243-5-5-5S7 4.243 7 7v3H6c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8zM9 7c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9V7z"></path></svg>'},
        {title: 'Type innslag', description: 'Velg hvilken type deltakere du vil ha informasjon om', link: "?page=UKMrapporter&action=rapport&rapport=TypeInnslag", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M13 2.051V11h8.949c-.47-4.717-4.232-8.479-8.949-8.949zm4.969 17.953c2.189-1.637 3.694-4.14 3.98-7.004h-8.183l4.203 7.004z"></path><path d="M11 12V2.051C5.954 2.555 2 6.824 2 12c0 5.514 4.486 10 10 10a9.93 9.93 0 0 0 4.255-.964s-5.253-8.915-5.254-9.031A.02.02 0 0 0 11 12z"></path></svg>'},
        //TEST BARE
        {title: 'TEST SINGLE RAPPORT', description: 'Kun testing', link: "?page=UKMrapporter&action=rapportVue&rapportId=alleDeltakere", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M15.78 15.84S18.64 13 19.61 12c3.07-3 1.54-9.18 1.54-9.18S15 1.29 12 4.36C9.66 6.64 8.14 8.22 8.14 8.22S4.3 7.42 2 9.72L14.25 22c2.3-2.33 1.53-6.16 1.53-6.16zm-1.5-9a2 2 0 0 1 2.83 0 2 2 0 1 1-2.83 0zM3 21a7.81 7.81 0 0 0 5-2l-3-3c-2 1-2 5-2 5z"></path></svg>'},
    ],
    // tab 1
    [1]: [
        {title: 'Kunstkatalog', description: 'Alle innslag i utstillingen', link: "?page=UKMrapporter&action=rapport&rapport=Kunstkatalog", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M21.084 2.914c-1.178-1.179-3.234-1.179-4.412 0l-8.379 8.379a.999.999 0 0 0 0 1.414l3 3a.997.997 0 0 0 1.414 0l8.379-8.379a3.123 3.123 0 0 0-.002-4.414zm-1.412 3L12 13.586 10.414 12l7.672-7.672a1.146 1.146 0 0 1 1.586.002 1.123 1.123 0 0 1 0 1.584zM8 15c-1.265-.634-3.5 0-3.5 2 0 1.197.5 2-1.5 3 0 0 3.25 2.25 5.5 0 1.274-1.274 1.494-4-.5-5z"></path></svg>'},
        {title: 'Program', description: 'Program for dine hendelser', link: "?page=UKMrapporter&action=rapport&rapport=Program", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M5.282 12.064c-.428.328-.72.609-.875.851-.155.24-.249.498-.279.768h2.679v-.748H5.413c.081-.081.152-.151.212-.201.062-.05.182-.142.361-.27.303-.218.511-.42.626-.604.116-.186.173-.375.173-.578a.898.898 0 0 0-.151-.512.892.892 0 0 0-.412-.341c-.174-.076-.419-.111-.733-.111-.3 0-.537.038-.706.114a.889.889 0 0 0-.396.338c-.094.143-.159.346-.194.604l.894.076c.025-.188.074-.317.147-.394a.375.375 0 0 1 .279-.108c.11 0 .2.035.272.108a.344.344 0 0 1 .108.258.55.55 0 0 1-.108.297c-.074.102-.241.254-.503.453zm.055 6.386a.398.398 0 0 1-.282-.105c-.074-.07-.128-.195-.162-.378L4 18.085c.059.204.142.372.251.506.109.133.248.235.417.306.168.069.399.103.692.103.3 0 .541-.047.725-.14a1 1 0 0 0 .424-.403c.098-.175.146-.354.146-.544a.823.823 0 0 0-.088-.393.708.708 0 0 0-.249-.261 1.015 1.015 0 0 0-.286-.11.943.943 0 0 0 .345-.299.673.673 0 0 0 .113-.383.747.747 0 0 0-.281-.596c-.187-.159-.49-.238-.909-.238-.365 0-.648.072-.847.219-.2.143-.334.353-.404.626l.844.151c.023-.162.067-.274.133-.338s.151-.098.257-.098a.33.33 0 0 1 .241.089c.059.06.087.139.087.238 0 .104-.038.193-.117.27s-.177.112-.293.112a.907.907 0 0 1-.116-.011l-.045.649a1.13 1.13 0 0 1 .289-.056c.132 0 .237.041.313.126.077.082.115.199.115.352 0 .146-.04.266-.119.354a.394.394 0 0 1-.301.134zm.948-10.083V5h-.739a1.47 1.47 0 0 1-.394.523c-.168.142-.404.262-.708.365v.754a2.595 2.595 0 0 0 .937-.48v2.206h.904zM9 6h11v2H9zm0 5h11v2H9zm0 5h11v2H9z"></path></svg>'},
        {title: 'Tekniske Prøver', description: 'Kjøreplan for tekniske prøver', link: "?page=UKMrapporter&action=rapport&rapport=TekniskeProver", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M11 2h2v7h-2zm0 13h2v7h-2zm4-4h7v2h-7zM2 11h7v2H2z"></path></svg>'},
        {title: 'Tilbakemeldingsskjema', description: 'Noteringsskjema for fagpanelet', link: "?page=UKMrapporter&action=rapport&rapport=Tilbakemeldingsskjema", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M3 5v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2zm7 2h8v2h-8V7zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2zM6 7h2v2H6V7zm0 4h2v2H6v-2zm0 4h2v2H6v-2z"></path></svg>'},
    ],
    // tab 2
    [2]: [
        {title: 'Spørreskjema', description: 'Svar på spørreskjema til de som videresender', link: "?page=UKMrapporter&action=rapport&rapport=Infoskjema", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M12 4C9.243 4 7 6.243 7 9h2c0-1.654 1.346-3 3-3s3 1.346 3 3c0 1.069-.454 1.465-1.481 2.255-.382.294-.813.626-1.226 1.038C10.981 13.604 10.995 14.897 11 15v2h2v-2.009c0-.024.023-.601.707-1.284.32-.32.682-.598 1.031-.867C15.798 12.024 17 11.1 17 9c0-2.757-2.243-5-5-5zm-1 14h2v2h-2z"></path></svg>'},
    ],
    // tab 3
    [3]: [
        {title: 'Nøkkeltall', description: 'Hvor mange påmeldte er det i hver kategori?', link: "?page=UKMrapporter&action=rapport&rapport=Nokkeltall", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M6 21H3a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1zm7 0h-3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v17a1 1 0 0 1-1 1zm7 0h-3a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1z"></path></svg>'},
    ],
    // tab 4
    [4]: [
        {title: 'Hotellbestillinger', description: 'Informasjon om hotel, rom osv.', link: "?page=UKMrapporter&action=rapport&rapport=Overnatting", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><circle cx="8" cy="11" r="3"></circle><path d="M18.205 7H12v8H4V6H2v14h2v-3h16v3h2v-4c0-.009-.005-.016-.005-.024H22V11c0-2.096-1.698-4-3.795-4z"></path></svg>'},
        {title: 'Overnatting og ansvar i landsbyen', description: 'Informasjon om overnatting', link: "?page=UKMrapporter&action=rapport&rapport=OvernattingLandsbyen", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M18.991 2H9.01C7.899 2 7 2.899 7 4.01v5.637l-4.702 4.642A1 1 0 0 0 3 16v5a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4.009C21 2.899 20.102 2 18.991 2zm-8.069 13.111V20H5v-5.568l2.987-2.949 2.935 3.003v.625zM13 9h-2V7h2v2zm4 8h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V7h2v2z"></path><path d="M7 15h2v2H7z"></path></svg>'},
        {title: 'Timeplan', description: 'Informasjon om oppmøtetid.', link: "?page=UKMrapporter&action=rapport&rapport=Timeplan", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M13 8h-2v5h5v-2h-3z"></path><path d="M19.999 12c0-2.953-1.612-5.53-3.999-6.916V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v2.083C5.613 6.469 4.001 9.047 4.001 12a8.003 8.003 0 0 0 4.136 7H8v2.041a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V19h-.139a8 8 0 0 0 4.138-7zm-8 5.999A6.005 6.005 0 0 1 6.001 12a6.005 6.005 0 0 1 5.998-5.999c3.31 0 6 2.691 6 5.999a6.005 6.005 0 0 1-6 5.999z"></path></svg>'},
        {title: 'Totaloversikt videresending fra fylkene', description: 'Totaloversikt over innslag, deltakere, ledere og ledsager/turister', link: "?page=UKMrapporter&action=rapport&rapport=TotaloverisktVideresendingFylke", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M9.5 12c2.206 0 4-1.794 4-4s-1.794-4-4-4-4 1.794-4 4 1.794 4 4 4zm1.5 1H8c-3.309 0-6 2.691-6 6v1h15v-1c0-3.309-2.691-6-6-6z"></path><path d="M16.604 11.048a5.67 5.67 0 0 0 .751-3.44c-.179-1.784-1.175-3.361-2.803-4.44l-1.105 1.666c1.119.742 1.8 1.799 1.918 2.974a3.693 3.693 0 0 1-1.072 2.986l-1.192 1.192 1.618.475C18.951 13.701 19 17.957 19 18h2c0-1.789-.956-5.285-4.396-6.952z"></path></svg>'},
        {title: 'UKM-festivalen Hotell', description: 'Informasjon om overnattinger, romtype osv.', link: "?page=UKMrapporter&action=rapport&rapport=UKMFestivalenHotell", icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #fff;transform: ;msFilter:;"><path d="M7 14.001h2v2H7z"></path><path d="M19 2h-8a2 2 0 0 0-2 2v6H5c-1.103 0-2 .897-2 2v9a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4a2 2 0 0 0-2-2zM5 20v-8h6v8H5zm9-12h-2V6h2v2zm4 8h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V6h2v2z"></path></svg>'},
    ],
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
        background: var(--color-primary-white) !important;
        border-top-right-radius: 10px;
        border-top-left-radius: 10px;
    }
    .rapport-side-tab .outer .rapport-button {
        border-radius: var(--radius-medium);
        background: var(--color-primary-white);
        display: flex;
    }
    .rapport-side-tab .outer .rapport-button:hover {
        outline: solid 2px var(--color-primary-bla-500);
    }
    .rapport-side-tab .outer .rapport-button .left .text * {
        text-decoration: none !important;
        color: var(--color-primary-black);
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