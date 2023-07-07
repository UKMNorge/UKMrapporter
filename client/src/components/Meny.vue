<template>
<div class="as-container container">
    <div class="rapport-meny nop as-card-1 as-padding-space-4">
        <!-- objects -->
        <div class="object item as-card-2 as-padding-space-2 as-margin-right-space-2">
            <h4>Person</h4>
            <div class="attributes as-margin-top-space-2">
                <div class="attribute toggle-function active as-padding-space-1 as-margin-right-space-1">
                    <span>Unique</span>
                </div>
                <div class="attribute as-padding-space-1 as-margin-right-space-1">
                    <span>Navn</span>
                </div>
                <div class="attribute as-padding-space-1 as-margin-right-space-1">
                    <span>Kommune</span>
                </div>
            </div>
        </div>

        <div class="object item as-card-2 as-padding-space-2 as-margin-right-space-2">
            <h4>Innslag</h4>
            <div class="attributes as-margin-top-space-2">
                <div class="attribute as-padding-space-1 as-margin-right-space-1">
                    <span>Navn</span>
                </div>
                <div class="attribute as-padding-space-1 as-margin-right-space-1">
                    <span>Type</span>
                </div>
            </div>
        </div>

    </div>
</div>
</template>

<script setup lang="ts">  
// Accepts objects
// Accepts filtering
// Accepts grupping

// Callback on change
</script>

<style scoped>
    .rapport-meny {
        width: 100%;
        display: flex;
        border-radius: var(--radius-medium);
    }
    .rapport-meny .item {
        background: var(--color-primary-grey-lightest);
        box-shadow: none;
        width: auto;
    }
    .rapport-meny .item .attributes {
        display: flex;
        min-width: 200px;
    }
    .rapport-meny .item .attributes .attribute {
        background: var(--color-primary-bla-50);
        border-radius: var(--radius-minimum);
        font-weight: 300;
        letter-spacing: 1px;
    }
    .rapport-meny .item .attributes .attribute.toggle-function {
        background: var(--color-primary-grey-light);
    }
    
</style>