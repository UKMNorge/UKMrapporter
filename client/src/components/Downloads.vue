<template>
    <div class="as-container container">
        <button @click="downloadExcelFile()">Last ned excel-fil</button>
    </div>
</template>

<script setup lang="ts">
import Repo from '../objects/Repo';


const props = defineProps<{
    repo: Repo,
}>();


function downloadExcelFile() {
    props.repo.generateExcel();
}

</script>


<style scoped>

</style>