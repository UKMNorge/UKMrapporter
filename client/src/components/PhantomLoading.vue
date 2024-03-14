<template>
    <div class="as-container container">
        <div class="as-margin-top-space-8 as-margin-bottom-space-8">
            <h1>{{ rapportName }}</h1>
        </div>

        <div class="as-container buttons container nop as-margin-bottom-space-6 as-display-flex">
            <div style="margin-left: 0" class="as-btn-simple as-padding-space-2 as-margin-space-2 as-margin-left-space-0 as-btn-hover-default phantom-loading">Button 1</div>
            <div class="as-btn-simple as-padding-space-2 as-margin-space-2 as-btn-hover-default phantom-loading">Button 2</div>
            <div class="as-btn-simple as-padding-space-2 as-margin-space-2 as-btn-hover-default phantom-loading">Button 3</div>
            <div class="as-btn-simple as-padding-space-2 as-margin-space-2 as-btn-hover-default phantom-loading">Button 4</div>
            <div class="as-btn-simple as-padding-space-2 as-margin-space-2 as-btn-hover-default phantom-loading">Button 5</div>
        </div>

        <div class="rapport-meny-components nop as-card-1 as-padding-space-8">
            <div>
                <h2 class="as-margin-bottom-space-4 phantom-loading">Meny</h2>
                <div class="as-margin-bottom-space-4 as-margin-top-space-6">
                    <span href="#" class="as-text-underline phantom-loading">Dette er link 1</span>
                </div>
                <div class="as-margin-bottom-space-4">
                    <span href="#" class="as-text-underline phantom-loading">Link 2</span>
                </div>
                <div class="as-margin-bottom-space-4">
                    <span href="#" class="as-text-underline phantom-loading">Link 3</span>
                </div>
                <div class="as-margin-bottom-space-4">
                    <span href="#" class="as-text-underline phantom-loading">Dette er link 4</span>
                </div>
                <div class="as-margin-bottom-space-4">
                    <span href="#" class="as-text-underline phantom-loading">Link 5</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    const props = defineProps<{
        rapportName: string,
    }>();
</script>


<style scoped>

</style>