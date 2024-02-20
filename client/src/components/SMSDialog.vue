<template>
    <div>    
        <div v-if="smsDialog" class="rapport-contact-sms-div container">
            <div class="rapport-contacts-sms-dialog as-padding-space-4 as-card-2">
                <div class="as-margin-bottom-space-6">
                    <button class="as-btn-default as-btn-hover-default" @click="selectAll">Velg alle</button>
                </div>
                <div class="selected-contacts">
                    <div v-for="contact in activeContacts">
                        <div v-if="typeof contact.getMobil === 'function' && typeof contact.getMobil === 'function'" @click="removeContact(contact.getMobil())">
                            <div class="contact attribute as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                                <span>{{ contact.getNavn() }} - <b>{{ contact.getMobil() }}</b></span>
                                <div data-v-1e2d8299="" class="icon"><svg data-v-1e2d8299="" class="remove-icon" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path data-v-1e2d8299="" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"></path></svg></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="as-margin-top-space-4">
                    <button class="as-btn-simple as-btn-hover-default success">Send</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Repo from '../objects/Repo';
import { ref, defineExpose } from 'vue';
import { defineProps } from 'vue';
import NodeObj from '../objects/NodeObj';
import SubnodeLeaf from '../objects/SubnodeLeaf';
import MobilContact from '../objects/MobilContact';



const props = defineProps<{
    repo: Repo,
}>();

var smsDialog = ref(false);

/**
 * Holds an array of active nodes.
 * @type {Array<Node>}
 */
var activeContacts = ref<Array<MobilContact>>([]);
var allContacts = ref<Array<MobilContact>>([]);



function removeContact(mobil : string|number) {
    for(var contact of activeContacts.value) {
        if(contact.getMobil() == mobil) {
            activeContacts.value.splice(activeContacts.value.indexOf(contact), 1);
        }
    }
}

function selectAll() {
    activeContacts.value = [];
    allContacts.value = [];
    _getAllContacts();
}   



function openSMSDialog() {
    smsDialog.value = true;
    _getAllContacts();
}

/**
 * This function iterates over all nodes in the Repo, and their subnodeLeaf.
 * If a node or subnode leaf has a mobile number (checked by the `hasMobil` method), it is considered active and is added to the `activeNodes` array.
 * The `activeNodes` array is a reactive variable (a Ref) and will trigger reactivity in Vue when changed.
 */
function _getAllContacts() {
    if(allContacts.value.length > 0) {
        return;
    }
    for(var node of props.repo.getAllNodes()) {
        for(var sunode of node.getSubnodes()) {
            for(var subnodeItem of sunode.getItems()) {
                for(var subnodeLeaf of subnodeItem.values) {
                    if(subnodeLeaf.hasMobil()) {
                        const navn = subnodeLeaf.getNavn();
                        const mobil = subnodeLeaf.getMobil();

                        if (typeof navn === "string" && (typeof mobil === "string" || typeof mobil === "number")) {
                            activeContacts.value.push(new MobilContact(mobil, navn));
                            allContacts.value.push(new MobilContact(mobil, navn));
                        }
                    }
                }
            }
        }
        if(node.hasMobil()) {
            const navn = node.getNavn();
            const mobil = '';

            if (typeof (node as any).getMobil === 'function') {
                if (typeof navn === "string" && (typeof mobil === "string" || typeof mobil === "number")) {
                    activeContacts.value.push(new MobilContact(mobil, navn));
                    allContacts.value.push(new MobilContact(mobil, navn));
                }
            }
        }
    }
}

// Expose the method to the parent component
defineExpose({
    openSMSDialog
})
</script>

<style scoped>
.rapport-contact-sms-div {
    position: fixed;
    padding: 0 30px 0 0;
    margin: auto;
    bottom: 190px;
    width: auto;
}
.selected-contacts {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-between;
}
.contact {
    background: var(--as-color-primary-success-lighter);
    border-radius: var(--radius-minimum);
    font-weight: 300;
    letter-spacing: 1px;
    display: flex;
    height: 100%;
    min-width: 40px;
    min-height: 35px;
}
.contact:hover {
    background: var(--as-color-primary-success-light) !important;
}
.contact .icon {
    display: flex;
}
.contact .icon svg {
    margin: auto -3px auto 3px;
}


</style>