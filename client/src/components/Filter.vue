<template>
    <div class="rapport-meny">
        <div class="object item as-card-2 as-padding-space-2 as-margin-space-1">
            <h4>Filtrering etter {{ getNodeName(selectedNode) }}</h4>
            <div class="attributes as-margin-top-space-1">
                <div v-for="(node, key) in getProperties()" :key="key" class="attribute-outer as-padding-top-space-1">
                    <div @click="deactivateNode(node)" :key="key" v-if="node.isActive()" class="attribute as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                        <span>{{ node.getNavn() }}</span>
                        <div class="icon">
                            <svg class="remove-icon" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                            </svg>
                        </div>
                    </div>
                </div>

                
                <div class="attribute-outer as-padding-top-space-1">
                    <div @click="openSelector()" class="attribute new as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 0H4V4H0V6H4V10H6V6H10V4H6V0Z" fill=""/>
                        </svg>
                        
                    </div>
                </div>

                <div @click="closeSelector($event)" v-if="selectorPopup" class="node-floating-selector close-selector">
                    <div class="box selector as-card-1 as-padding-space-5">
                        <button class="close-selector close-btn as-btn-hover-default">
                            <div class="icon close-selector">
                                <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                                </svg>
                            </div>
                        </button>
        
                        <h4>Filtrering</h4>

                        <div class="attributes as-margin-top-space-2">
                            <div class="container nop buttons-selector">
                                <div class="" v-for="(n, key) in filterNodes" :key="key">
                                    <button class="btn" @click="selectNode(n)" >{{ getNodeName(n) }}</button>
                                </div>
                            </div>
                            <div class="prop as-margin-top-space-1" v-for="(node, key) in getProperties()" :key="key">
                                <div @click="activateNode(node)" v-if="!node.isActive()" class="attribute as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                                    <span>{{ node.getNavn() }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>        
    </div>
</template>

<script setup lang="ts">
import NodeObj from "../objects/NodeObj";
import RootNode from "../objects/RootNode";
import { ref } from 'vue';
import { onUpdated, onMounted } from 'vue';
import Kommune from '../objects/rapporter/Kommune';



const props = defineProps<{
    root: RootNode|null,
}>();

var selectorPopup : any = ref(false);
var filterNodes : any = ref([]);
var selectedNode : any = ref(props.root ? props.root.constructor : null)


onMounted(() => {
    selectedNode.value = props.root;
    var arrTest : {} = [];
    if(props.root) {
        getFilterNodes(props.root, arrTest);
    }

    for(var key of Object.keys(arrTest)) {
        var val = (<any>arrTest)[key];
        filterNodes.value.push(val.constructor);
    }
})

function selectNode(node : NodeObj) {
    selectedNode.value = node;
}

function getNodeName(node : NodeObj) : string {
    return node.className != undefined ? node.className : ''; 
}
 
// Recursive function to get all unqiue objects
function getFilterNodes(node : NodeObj, leafNodes : {}) {
    for (var i = 0; i < node.children.length; i++) {
        getFilterNodes(node.children[i], leafNodes);
    }

    // Add all nodes that are not leaf and root
    if (node.children.length > 0 && !(node instanceof RootNode)) {
        (<any>leafNodes)[(<any>node.constructor).name] = node;
    }
}

function getProperties() : NodeObj[] {
    var filteredNodes : NodeObj[] = [];
    // If it is root, there is no properties
    if(selectedNode.value instanceof RootNode) {
        return [];
    }
    if(props.root != null) {
        getAllNodesAtLevel(props.root, filteredNodes, selectedNode.value);
    }
    return filteredNodes;
}

function activateNode(node : NodeObj) {
    node.setActive(true);
}

function deactivateNode(node : NodeObj) {
    node.setActive(false);
    console.log('Deactivated');
    console.log(node);
}

function closeSelector(event : any) {
    if((<any>window).jQuery(event.target).hasClass('close-selector')) {
        selectorPopup.value = false;
    }
}
function openSelector() {
    selectorPopup.value = true;
}

// Recursive
function getAllNodesAtLevel(node : NodeObj, filteredNodes : NodeObj[], filterNode : NodeObj) {
    if (node.constructor.name === (<any>filterNode).name) {
        filteredNodes.push(node);
    } else {
        for (var i = 0; i < node.children.length; i++) {
            getAllNodesAtLevel(node.children[i], filteredNodes, filterNode);
        }
    }
}

</script>

<style scoped>
.rapport-meny {
    width: 100%;
}
.rapport-meny .item {
        background: var(--color-primary-grey-lightest);
        box-shadow: none;
    }
    .rapport-meny .item .attributes {
        display: flex;
        min-width: 200px;
        flex-wrap: wrap;
    }
    .rapport-meny .item .attributes .attribute {
        background: var(--color-primary-bla-50);
        border-radius: var(--radius-minimum);
        font-weight: 300;
        letter-spacing: 1px;
        display: flex;
        height: 100%;
        min-width: 40px;
        min-height: 35px;
    }
    .rapport-meny .item .attributes .attribute.new svg {
        margin: auto;
    }
    .rapport-meny .item .attributes .attribute.new svg path {
        fill: var(--color-primary-black) !important;
    }

    .rapport-meny .item .attributes .attribute.toggle-function {
        background: var(--color-primary-grey-light);
    }
    .rapport-meny .item .attributes .attribute.toggle-function.active {
        background: var(--color-primary-bla-500) !important;
        color: #fff;
    }
    .rapport-meny .item .attributes .attribute.toggle-function.active span {
        color: #fff !important;
    }
    .rapport-meny .item .attributes .attribute .icon {
        display: flex;
    }
    .rapport-meny .item .attributes .attribute .icon svg.remove-icon {
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
    .box.selector .attributes {
        display: block;
    }
    .box.selector .attributes .prop {
        display: flex;
    }
    .buttons-selector {
        display: flex;
    }
</style>