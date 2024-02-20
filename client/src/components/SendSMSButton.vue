<template>
    <div>
        <button class="as-btn-simple as-btn-hover-default btn-with-icon" @click="smsDialogOpen()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M12 2C6.486 2 2 5.589 2 10c0 2.908 1.897 5.516 5 6.934V22l5.34-4.004C17.697 17.852 22 14.32 22 10c0-4.411-4.486-8-10-8zm-2.5 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"></path></svg>
            <span>Send SMS</span>
        </button>
    </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

const props = defineProps<{
    smsDialogRef: any,
}>();

// emit is a function that is used to emit events
// const emit = defineEmits(['sendSMS']);


function smsDialogOpen() {
    if (props.smsDialogRef && typeof props.smsDialogRef.openSMSDialog === 'function') {
        props.smsDialogRef.openSMSDialog();
        return;
    }
    console.error('SMSDialog or openSMSDialog method not found.');
}

</script>