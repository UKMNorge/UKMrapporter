<template>
    <div>
        <div>
            <button class="as-btn-simple as-btn-hover-default btn-with-icon" @click="openSelector()">
                <svg class="as-margin-right-space-1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M12 2C6.486 2 2 5.589 2 10c0 2.908 1.897 5.516 5 6.934V22l5.34-4.004C17.697 17.852 22 14.32 22 10c0-4.411-4.486-8-10-8zm-2.5 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"></path></svg>
                <span>Send SMS</span>
            </button>
        </div>


        <!-- Popup -->
        <div @click="closeSelector($event)" v-if="selectorPopupSMS" class="node-floating-selector close-selector">
            <div class="box selector as-card-1 as-padding-space-5">
                <button class="close-selector close-btn as-btn-hover-default">
                    <div class="icon close-selector">
                        <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                        </svg>
                    </div>
                </button>

                <h4>Send SMS</h4>

                <!-- Beskjeder -->
                <div v-if="isFilteringActive" class="sms-beskjed nosh-impt as-card-2 as-padding-space-2 as-margin-bottom-space-2 as-margin-top-space-2">
                    <h5 class="as-padding-bottom-space-1">SMS vil kun inkludere kontakter som er tilpasset dine innstillinger</h5>
                    <span class="nop">På grunn av filtreringen du anvender, kan noen kontakter være utilgjengelige</span>
                </div>

                <div class="attributes as-margin-top-space-3">
                    <div v-if="allContacts.length != activeContacts.length" class="as-margin-bottom-space-2">
                        <button class="as-btn-default as-btn-hover-default" @click="selectAll">Velg alle</button>
                    </div>

                    <div v-show="allContacts.length > activeContacts.length " class="showed-contacts object item as-card-2 as-padding-space-3 as-padding-bottom-space-2 as-margin-bottom-space-4">
                        <div v-for="contact in filteredContacts">
                            <div v-if="typeof contact.getMobil === 'function' && typeof contact.getMobil === 'function'" @click="addToActive(contact)">
                                <div class="contact not-selected attribute as-padding-space-1 as-margin-right-space-1 as-margin-bottom-space-1 as-btn-hover-default">
                                    <span>{{ contact.getNavn() }} - <b>{{ contact.getMobil() }}</b></span>
                                </div>
                            </div>
                        </div>                        
                    </div>

                    <div v-show="activeContacts.length > 0" class="showed-contacts object item as-card-2 as-padding-space-3 as-padding-bottom-space-2">
                        <div class="info-contacts as-margin-bottom-space-2">
                            <h5>Sender sms til:</h5>
                        </div>
                        <div v-for="contact in activeContacts">
                            <div v-if="typeof contact.getMobil === 'function' && typeof contact.getMobil === 'function'" @click="removeContactFromActive(contact)">
                                <div class="contact attribute as-padding-space-1 as-margin-right-space-1 as-margin-bottom-space-1 as-btn-hover-default">
                                    <span>{{ contact.getNavn() }} - <b>{{ contact.getMobil() }}</b></span>
                                    <div class="icon"><svg class="remove-icon" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"></path></svg></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="as-margin-top-space-4 send-btn-div">
                        <button @click="sendSMS()" :disabled="activeContacts.length < 1" class="as-btn-simple as-btn-hover-default success">Send</button>
                    </div>
                </div>

            </div>
        </div> 
    </div>
</template>

<script setup lang="ts">
import Repo from '../objects/Repo';
import { ref, defineExpose, computed } from 'vue';
import { defineProps } from 'vue';
import NodeObj from '../objects/NodeObj';
import SubnodeLeaf from '../objects/SubnodeLeaf';
import MobilContact from '../objects/MobilContact';
import { post } from 'jquery';



var selectorPopupSMS : any = ref(false);

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

var selectorPopupSMS : any = ref(false);



/**
 * Sends an SMS to the active contacts.
 */
function sendSMS() {
    // Create a form element
    const form = document.createElement('form');
    form.method = 'post';
    form.action = '?page=UKMSMS_gui'; // Set the destination URL

    // Convert mobile contacts to a comma-separated string
    const mobilContacts = activeContacts.value.map(contact => contact.getMobil()).join(',');

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'UKMSMS_recipients'; // The name of the input field
    input.value = mobilContacts; // The comma-separated string of mobile contacts
    form.appendChild(input);

    document.body.appendChild(form);

    form.submit();
}



const isFilteringActive = computed(() => {
    // Filter the contacts that are in allContacts but not in activeContacts
    console.log(props.repo.isFilteringActive());
    return props.repo.isFilteringActive()
});

// popup
function openSelector() {
    selectorPopupSMS.value = true;
    openSMSDialog();
}
function closeSelector(event : any) {
    if((<any>window).jQuery(event.target).hasClass('close-selector')) {
        selectorPopupSMS.value = false;
    }
}


function removeContactFromActive(contact : any) {
    // Remove contact from activeContacts
    activeContacts.value = activeContacts.value.filter((c : any) => c.getMobil() != contact.getMobil());
}

function addToActive(contact : any) {
    activeContacts.value.push(contact);
}

function selectAll() {
    activeContacts.value = [];
    allContacts.value = [];
    _getAllContacts();
}

function openSMSDialog() {
    selectAll();
}

/**
 * This function iterates over all nodes in the Repo, and their subnodeLeaf.
 * If a node or subnode leaf has a mobile number (checked by the `hasMobil` method), it is considered active and is added to the `activeNodes` array.
 * The `activeNodes` array is a reactive variable (a Ref) and will trigger reactivity in Vue when changed.
 */
function _getAllContacts() {
    if(allContacts.value.length > 0) {
        // return;
    }
    for(var node of props.repo.getAllNodes()) {
        for(var sunode of node.getSubnodes()) {
            for(var subnodeItem of sunode.getItems()) {
                for(var subnodeLeaf of subnodeItem.values) {
                    if(subnodeLeaf.hasMobil()) {
                        const navn = subnodeLeaf.getNavn();
                        const mobil = subnodeLeaf.getMobil();

                        if (typeof navn === "string" && (typeof mobil === "string" || typeof mobil === "number")) {
                            _addContact(new MobilContact(mobil, navn));
                        }
                    }
                }
            }
        }
        if(node.hasMobil()) {
            const navn = node.getNavn();
            let mobil = '';

            if (typeof (node as any).getMobil === 'function') {
                mobil = (node as any).getMobil();
                if (typeof navn === "string" && (typeof mobil === "string" || typeof mobil === "number")) {
                    _addContact(new MobilContact(mobil, navn));
                }
            }
        }
    }
}

const filteredContacts = computed(() => {
    // Filter the contacts that are in allContacts but not in activeContacts
    return allContacts.value.filter(contact => !activeContacts.value.includes(contact));
});


function _addContact(contact : MobilContact) {
    // Add if contact.getMobil() does not it doesnt exist
    if(activeContacts.value.filter((c) => c.getMobil() == contact.getMobil()).length < 1) {
        activeContacts.value.push(contact);
        allContacts.value.push(contact);
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
.showed-contacts {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: left;
    background: var(--color-primary-grey-lightest);
    box-shadow: none !important;
}
.contact {
    background: var(--color-primary-bla-50);
    border-radius: var(--radius-minimum);
    font-weight: 300;
    letter-spacing: 1px;
    display: flex;
    height: 100%;
    min-width: 40px;
    min-height: 35px;
}
.contact.not-selected {
    background: var(--color-primary-grey-light);
}
.contact .icon {
    display: flex;
}
.contact .icon svg {
    margin: auto -3px auto 3px;
}
.send-btn-div {
    display: flex;
    width: 100%;
}
.send-btn-div button {
    margin: auto
}
.info-contacts {
    width: 100%;
}
.sms-beskjed {
    background: var(--as-color-primary-warning-lightest);
    border: solid 2px var(--as-color-primary-warning-light);

}




.attributes {
    min-width: 200px;
    min-height: 50px;
    flex-wrap: wrap;
    max-width: 60vw;
}
    .attributes .attribute {
    font-weight: 300;
    letter-spacing: 1px;
    display: flex;
    height: 100%;
    min-width: 40px;
    min-height: 35px;
}
    .attributes .attribute.new svg {
    margin: auto;
}
    .attributes .attribute.new svg path {
    fill: var(--color-primary-black) !important;
}

    .attributes .attribute.toggle-function {
    background: var(--color-primary-grey-light);
}
    .attributes .attribute.toggle-function.active {
    background: var(--color-primary-bla-500) !important;
    color: #fff;
}
    .attributes .attribute.toggle-function.active span {
    color: #fff !important;
}
    .attributes .attribute .icon {
    display: flex;
}
    .attributes .attribute .icon svg.remove-icon {
    margin: auto -3px auto 3px
}
.node-floating-selector {
    position: fixed;
    z-index: 99999;
    background: #65656527;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    display: flex;
}
.node-floating-selector .box {
    margin: auto;
    min-width: 400px;
    position: relative;
    max-height: 80vh;
    overflow: auto;
}
.node-floating-selector .box button.close-btn {
    position: absolute;
    right: 10px;
    top: 10px;
    border: none;
    border-radius: 50%;
    height: 35px;
    width: 35px;
    display: flex;
    background: transparent;
}
.node-floating-selector .box button.close-btn:hover {
    background: var(--color-primary-grey-light) !important;
}
.node-floating-selector .box button.close-btn:hover .icon svg path {
    fill: var(--color-primary-grey-dark) !important;
}
.node-floating-selector .box button.close-btn .icon {
    margin: auto
}
.node-floating-selector .box button.close-btn .icon svg {
    display: flex;
}
    /* .box.selector .attributes {
        display: block;
    }
    .box.selector .attributes .prop {
        display: flex;
    }
    .buttons-selector {
        display: flex;
    }
    .buttons-selector .button-div {
        margin-right: 10px;
    }
    .buttons-selector .button-div button {
        border: none;
    }
    .buttons-selector .button-div button.active-node {
        background: var(--color-primary-bla-500)!important;
    }
    .buttons-selector .button-div button.active-node span {
        color: #fff !important;
    }
 */

</style>