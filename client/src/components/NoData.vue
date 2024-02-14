<template>
    <div class="as-container container">
        <div class="nop as-margin-top-space-5 as-card-1 as-padding-space-3">
            <h5 class="text-info-no-data">Det finnes ingen tilgjengelige data for denne rapporten.</h5>                

            <div class="img-no-data">
                <img :src="imgNoData" alt="Ingen data" />
            </div>
            
        </div>
    </div>
</template>
<script setup lang="ts">
    import { ref } from 'vue';
    const ukmHostname = (<any>window).UKM_HOSTNAME;
    let imgNoData = "https://assets."+ ukmHostname +"/img/rapporter/no-data-for-rapport.png";
</script>

<style scoped>
    .text-info-no-data {
        text-align: center;
        margin-bottom: 50px;
    }
    .img-no-data {
        text-align: center;
    }
    .img-no-data img {
        width: 100%;
        max-width: 100px;
    }
</style>