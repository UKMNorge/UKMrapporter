<template>
    <div>
        <!-- Popup -->
        <div @click="closeSelector($event)" v-if="selectorPopupSMS" class="as-popup-fixed close-selector">
            <div class="as-popup-box selector as-card-1 as-padding-space-5 container">
                <button @click="closeSelector($event)" class="close-selector as-popup-btn as-btn-hover-default">
                    <div class="icon close-selector">
                        <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                        </svg>
                    </div>
                </button>

                <h4>Send {{ contactComponentName }}</h4>

                <!-- Notifications -->
                <PermanentNotification v-if="isFilteringActive" typeNotification="warning" :tittel="`${contactComponentName.charAt(0).toUpperCase() + contactComponentName.slice(1)} vil kun inkludere kontakter som er tilpasset dine innstillinger`" :description="'På grunn av filtreringen du anvender, kan noen kontakter være utilgjengelige'" />

                <!-- Search contacts -->
                <div class="as-container as-margin-top-space-3">
                    <InputTextOverlay :placeholder="'Søk etter kontakter'" v-model="search" />
                </div>

                <div class="attributes as-margin-top-space-2">
                    <div v-show="allContacts.length > activeContacts.length " class="showed-contacts object item as-card-2 as-padding-space-3 as-padding-bottom-space-2">
                        <div v-for="contact in filteredContacts" :key="contact.getId()">
                            <div @click="addToActive(contact)">
                                <div :class="{ 'active': contact.activeSearch }" class="contact not-selected attribute as-padding-space-1 as-margin-right-space-1 as-margin-bottom-space-1 as-btn-hover-default">
                                    <span>{{ contact.getNavn() }} - <b>{{ contact.getId() }}</b></span>
                                </div>
                            </div>
                        </div>                        
                    </div>

                    <div v-if="allContacts.length != activeContacts.length" class="as-margin-top-space-2 as-margin-bottom-space-4">
                        <button class="as-btn-default as-btn-hover-default" @click="selectAll">Legg til alle</button>
                    </div>
                    
                    <div class="showed-contacts object item as-card-2 as-padding-space-3 as-padding-bottom-space-2">
                        <div class="info-contacts as-margin-bottom-space-2">
                            <h5>Sender {{ contactComponentName }} til:</h5>
                        </div>
                        <div v-for="contact in activeContacts" :key="contact.getId()">
                            <div @click="removeContactFromActive(contact)">
                            <div :class="{ 'active': contact.activeSearch }" class="contact attribute as-padding-space-1 as-margin-right-space-1 as-margin-bottom-space-1 as-btn-hover-default">
                                    <span>{{ contact.getNavn() }} - <b>{{ contact.getId() }}</b></span>
                                    <div class="icon as-display-flex"><svg class="remove-icon" width="15" height="15" viewBox="-2 -1 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"></path></svg></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="activeContacts.length < 1">Ingen</div>
                    </div>

                    <div v-if="activeContacts.length > 0" class="as-margin-top-space-2">
                        <button class="as-btn-default as-btn-hover-default" @click="deselectAll">Fjern alle</button>
                    </div>

                    <div class="as-margin-top-space-4 send-btn-div">
                        <div class="as-margin-auto">
                            <button @click="send()" :disabled="activeContacts.length < 1" :class="{'not-possible' : activeContacts.length < 1}" class="as-btn-simple button-send as-btn-hover-default success">Gå videre</button>
                            <div class="as-margin-top-space-2">
                                <button v-if="sendButton1 !== undefined" @click="send1()" :disabled="activeContacts.length < 1" :class="{'not-possible' : activeContacts.length < 1}" class="as-btn-simple button-send as-btn-hover-default success under as-btn-simple-small">{{ sendButton1.name }}</button>
                                <button v-if="sendButton2 !== undefined" @click="send2()" :disabled="activeContacts.length < 1" :class="{'not-possible' : activeContacts.length < 1}" class="as-btn-simple button-send as-btn-hover-default success under as-btn-simple-small">{{ sendButton2.name }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
</template>

<script setup lang="ts">
import Repo from '../objects/Repo';
import { ref, defineExpose, computed, watch } from 'vue';
import { defineProps } from 'vue';
import type Contact from '../objects/interfaces/Contact';
import { PermanentNotification } from 'ukm-components-vue3';
import { InputTextOverlay } from 'ukm-components-vue3';
// import AsInput from './AsInput.vue';



var selectorPopupSMS : any = ref(false);
const search = ref('');


const props = defineProps<{
    repo: Repo,
    getAllContacts : (contactComponentName : string, allContacts : Contact[], activeContacts : Contact[]) => void,
    contactComponentName: string,
    send: (activeContacts : Contact[], sendType? : string) => void,
    sendButton1?: {name : string, method : (activeContacts : Contact[], sendType? : string) => void},
    sendButton2?: {name : string, method : (activeContacts : Contact[], sendType? : string) => void},
}>();



var smsDialog = ref(false);

/**
 * Holds an array of active nodes.
 * @type {Array<Node>}
 */
var activeContacts = ref<Contact[]>([]);
var allContacts = ref<Contact[]>([]);

var selectorPopupSMS : any = ref(false);

watch(search, (newVal, oldVal) => {
    searchInputChanged();
});

function searchInputChanged() {
    searchContacts(search.value);
}

function searchContacts(searchStr : string) {
    if(searchStr.length < 1) {
        for(var contact of allContacts.value) {
            (<any>contact).activeSearch = true;
        }
        return;
    }
    for(var contact of allContacts.value) {
        var navn = String(contact.getNavn());
        var id = String(contact.getId());
        const nameMatch = navn.toLowerCase().includes(searchStr.toLowerCase());
        const idRes = id.includes(searchStr);
        if(nameMatch || idRes) {
            (<any>contact).activeSearch = true;
        } else {
            (<any>contact).activeSearch = false;
        }
    }
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
    activeContacts.value = activeContacts.value.filter((c : any) => c.getId() != contact.getId());
}

function addToActive(contact : any) {
    activeContacts.value.push(contact);
}

function selectAll() {
    activeContacts.value = [];
    allContacts.value = [];
    // _getAllContacts();
    getAllContactsFromParent();
}

function deselectAll() {
    activeContacts.value = [];
}

function getAllContactsFromParent() {
    props.getAllContacts(props.contactComponentName, allContacts.value, activeContacts.value);
    for(var contact of allContacts.value) {
        (<any>contact).activeSearch = true;
    }
}

function openSMSDialog() {
    selectAll();
}

const filteredContacts = computed(() => {
    // Filter the contacts that are in allContacts but not in activeContacts
    return allContacts.value.filter(contact => !activeContacts.value.includes(contact));
});


function addContact(contact : Contact) {
    // Add if contact.getMobil() does not it doesnt exist
    if(activeContacts.value.filter((c) => c.getId() == contact.getId()).length < 1) {
        activeContacts.value.push(contact);
        allContacts.value.push(contact);
    }
}

function send() {
    props.send(activeContacts.value);
}
function send1() {
    if(props.sendButton1) {
        props.sendButton1.method(activeContacts.value, props.sendButton1.name);
    }
}
function send2() {
    if(props.sendButton2){
        props.sendButton2.method(activeContacts.value, props.sendButton2.name);
    }
}

// Expose the method to the parent component
defineExpose({
    openSelector,
    addContact
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
.send-btn-div {
    display: flex;
    width: 100%;
}
.send-btn-div button {
    margin: auto
}
.send-btn-div button.under {
    margin-top: var(--initial-space-box);
}
.info-contacts {
    width: 100%;
}

.attributes {
    min-width: 100%;
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
    opacity: 0.2;
}
.attributes .attribute.active {
    opacity: 1;
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
.button-send.not-possible, 
.button-send.not-possible:hover,
.button-send.not-possible:active {
    background: var(--color-primary-grey-light) !important;
    color: var(--color-primary-grey-dark) !important;
}

</style>