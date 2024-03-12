<template>
    <div>
        <div class="tab-button">
            <button @click="openTab('first')">First Tab</button>
            <button @click="openTab('second')">Second Tab</button>
        </div>

        <div class="tabs">
            <!-- Tabs -->
            <FirstTab ref="firstTab" />
        </div>
    </div>
</template>

<script lang="ts">
import FirstTab from './tabs/FirstTab.vue';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';
import { Director } from 'ukm-spa/Director';
import { ref, onMounted } from 'vue'

export default {

    data() {
        return {
            name : "World" as String,
            activeTab : 'first' as String
        }
    },

    components : {
        FirstTab : FirstTab
    },

    mounted: function () {
        console.log('mounted!');
        console.log('App.vue:  onMounted()');
        console.log(SPAInteraction);
        console.log(Director);
        
        this.openTab('first');
    },
    
    methods: {
        openTab(tabId : string) {
            if(tabId == 'first') {
                console.log(this.$refs);
                (<any>this.$refs.firstTab).setActive();
            }
            else if(tabId == 'second') {
    
            }
        }
    }
}
</script>
