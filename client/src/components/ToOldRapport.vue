<template>
    <button class="as-btn-simple as-btn-hover-default btn-with-icon" @click="openOldRapport()">
        <!-- <svg  xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M18.944 11.112C18.507 7.67 15.56 5 12 5 9.244 5 6.85 6.61 5.757 9.149 3.609 9.792 2 11.82 2 14c0 2.657 2.089 4.815 4.708 4.971V19H17.99v-.003L18 19c2.206 0 4-1.794 4-4a4.008 4.008 0 0 0-3.056-3.888zM8 12h3V9h2v3h3l-4 5-4-5z"></path></svg> -->
        <svg class="as-margin-right-space-1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path></svg>
        <span>Til gammel rapport</span>
    </button>
</template>

<script setup lang="ts">

const props = defineProps<{
    redirectLink: string,
}>();

function openOldRapport() {
    console.log(props.redirectLink);
    window.location.href = props.redirectLink;
}

</script>


<style scoped>
.btn-with-icon svg {
    fill: var(--color-primary-bla-900) !important;
}

</style>