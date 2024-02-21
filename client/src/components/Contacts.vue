<template>
    <div>
        <div class="as-display-flex">
            <div>
                <button class="as-btn-simple as-btn-hover-default btn-with-icon as-margin-left-space-3" @click="openSelector('sms')">
                    <svg class="as-margin-right-space-1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M12 2C6.486 2 2 5.589 2 10c0 2.908 1.897 5.516 5 6.934V22l5.34-4.004C17.697 17.852 22 14.32 22 10c0-4.411-4.486-8-10-8zm-2.5 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"></path></svg>
                    <span>Send SMS</span>
                </button>
            </div>
    
            <div>
                <button class="as-btn-simple as-btn-hover-default btn-with-icon as-margin-left-space-3" @click="openSelector('epost')">
                    <svg class="as-margin-right-space-1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M12 2C6.486 2 2 5.589 2 10c0 2.908 1.897 5.516 5 6.934V22l5.34-4.004C17.697 17.852 22 14.32 22 10c0-4.411-4.486-8-10-8zm-2.5 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"></path></svg>
                    <span>Send Epost</span>
                </button>
            </div>
        </div>

        <div>
            <!-- Send SMS to contacts -->
            <SendSMS ref="smsComponent" :repo="repo" :getAllContacts="getContacts" :send="sendSMS" :contactComponentName="'SMS'" />
            
            <!-- Send email to contacts -->
            <SendSMS ref="emailComponent" :repo="repo" :getAllContacts="getContacts" :send="sendEmail" :contactComponentName="'epost'" />
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Repo from '../objects/Repo';
import SendSMS from './SendSMS.vue';
import type Contact from '../objects/interfaces/Contact';
import MobilContact from '../objects/MobilContact';
import EmailContact from '../objects/EmailContact';



const props = defineProps<{
    repo: Repo,
}>();


const smsComponent = ref(null);
const emailComponent = ref(null);

function getContacts(contactComponentName : string, allContacts : Contact[], activeContacts : Contact[]) {
    if(allContacts.length > 0) {
        // return;
    }
    for(var node of props.repo.getAllNodes()) {
        for(var sunode of node.getSubnodes()) {
            for(var subnodeItem of sunode.getItems()) {
                for(var subnodeLeaf of subnodeItem.values) {
                    const navn = subnodeLeaf.getNavn();

                    if(contactComponentName == 'epost') {
                        if (typeof subnodeLeaf.hasEpost === 'function') {
                            const epost = subnodeLeaf.getEpost();

                            if (typeof navn === "string" && typeof epost === "string") {
                                (<any>emailComponent.value).addContact(new EmailContact(epost, navn), allContacts, activeContacts);
                            }
                        }
                    }
                    else if(contactComponentName == 'SMS') {
                        if(subnodeLeaf.hasMobil()) {
                            const mobil = subnodeLeaf.getMobil();
    
                            if (typeof navn === "string" && (typeof mobil === "string" || typeof mobil === "number")) {
                                 (<any>smsComponent.value).addContact(new MobilContact(mobil, navn), allContacts, activeContacts);
                            }
                        }
                    }

                }
            }
        }
        if(contactComponentName == 'epost') {
            if (typeof (node as any).getEpost === 'function') {
                const epost = (node as any).getEpost();
                const navn = node.getNavn();

                if (typeof navn === "string" && typeof epost === "string") {
                    (<any>emailComponent.value).addContact(new EmailContact(epost, navn), allContacts, activeContacts);
                }
            }

        }
        else if(contactComponentName == 'SMS') {
            if(node.hasMobil()) {
                const navn = node.getNavn();
                let mobil = '';
    
                if (typeof (node as any).getMobil === 'function') {
                    mobil = (node as any).getMobil();
                    if (typeof navn === "string" && (typeof mobil === "string" || typeof mobil === "number")) {
                         (<any>smsComponent.value).addContact(new MobilContact(mobil, navn), allContacts, activeContacts);
                    }
                }
            }
        }
    }
}


/**
 * Sends an email to the active contacts.
 * 
 * @param {Contact[]} activeContacts - The active contacts to send the email to.
 * @returns {void}
 */
function sendEmail(activeContacts: Contact[]): void {
    // Convert email contacts to a comma-separated string
    const emailContacts = activeContacts.map(contact => {
        // Checking if the contact has a method called getEpost
        if (typeof (contact as any).getEpost === 'function') {
            (contact as any).getEpost()
        }
    }).join(', ');

    console.log('Sending email to: ' + emailContacts);
}

/**
 * Sends an SMS to the active contacts.
 */
function sendSMS(activeContacts: Contact[]): void {
    
    // Create a form element
    const form = document.createElement('form');
    form.method = 'post';
    form.action = '?page=UKMSMS_gui'; // Set the destination URL

    // Convert mobile contacts to a comma-separated string
    const mobilContacts = activeContacts.map(contact => {
        // Checking if the contact has a method called getMobil
        if (typeof (contact as any).getMobil === 'function') {
            (contact as any).getMobil()
        }
    }).join(',');


    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'UKMSMS_recipients'; // The name of the input field
    input.value = mobilContacts; // The comma-separated string of mobile contacts
    form.appendChild(input);

    document.body.appendChild(form);

    form.submit();
}

/**
 * Opens the contact selector for the given component.
 * 
 * @param componentName The name of the component to open the selector for.
 */
function openSelector(componentName : string) {
    if(componentName == 'sms') {
        if(smsComponent.value == null) {
            console.error('SMS component is not defined');
            return;
        }
        (<any>smsComponent.value).openSelector();
    } else {
        if(emailComponent.value == null) {
            console.error('Email component is not defined');
            return;
        }
        (<any>emailComponent.value).openSelector();
    }
}

</script>

<style scoped>
    
</style>