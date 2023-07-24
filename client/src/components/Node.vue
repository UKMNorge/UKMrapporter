<template>
    <div class="as-container rapport-meny col-xs-9 nop">
        <!-- nodes -->
        <div v-for="(node, key) in getNodes()" :key="key" class="object item as-card-2 as-padding-space-2 as-margin-space-1">
            <h4>{{ node.getRepresentativeName() }}</h4>
            <div class="attributes as-margin-top-space-1">
                
                <div class="as-padding-top-space-1">
                    <div v-if="hasUnique(node)" @click="toggleFunction(node)" :class="uniqueNodeValue ? 'active' : ''" class="attribute toggle-function as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                        <span>Unike</span>
                    </div>
                </div>

                <!-- Node property -->
                <div v-for="(nodeProp, key2) in getAllProperties(node)" :key="key2" class="as-padding-top-space-1">
                    <div @click="removeNodeProperty(nodeProp)" v-if="nodeProp.active" class="attribute as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                        <span>{{ nodeProp.navn }}</span>
                        <div class="icon">
                            <svg class="remove-icon" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="as-padding-top-space-1">
                    <div v-if="getActiveProperties(node).length != getAllProperties(node).length" @click="openSelector(node.getRepresentativeName())" class="attribute new as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 0H4V4H0V6H4V10H6V6H10V4H6V0Z" fill=""/>
                        </svg>

                    </div>
                </div>

            </div>

            <div @click="closeSelector($event)" v-if="selectorPopup == node.getRepresentativeName()" class="node-floating-selector close-selector">
                <div class="box selector as-card-1 as-padding-space-5">
                    <button class="close-selector close-btn as-btn-hover-default">
                        <div class="icon close-selector">
                            <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                            </svg>
                        </div>
                    </button>
                    <h4>{{ node.getRepresentativeName() }}</h4>

                    <div class="attributes as-margin-top-space-2">
                        <div class="prop as-margin-top-space-1" v-for="(nodeProp, key) in getAllProperties(node)" :key="key">
                            <div @click="addNodeProperty(nodeProp)" v-if="!nodeProp.active" class="attribute as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                                <span>{{ nodeProp.navn }}</span>
                            </div>
                        </div>
                        <p></p>
                        <div v-if="getActiveProperties(node).length == getAllProperties(node).length">
                            <span>Du har lagt til alle feltene!</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import Vue from 'vue'
import { reactive } from 'vue';
import { ref } from 'vue';
import { defineProps } from 'vue';
import RootNode from '../objects/RootNode';
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';


const props = defineProps<{
    root: RootNode|null
}>();

var selectorPopup : any = ref('');

var uniqueNodeValue : any = ref(false);

function openSelector(name : string) {
    selectorPopup.value = name;
}

function closeSelector(event : any) {
    if((<any>window).jQuery(event.target).hasClass('close-selector')) {
        selectorPopup.value = '';
    }
}

function constructor() {
    alert('aa');
}

var showSelector = false;

function toggleFunction(node : NodeObj) {
    var classNode = (<any>node.constructor);
    classNode.setUnique(!classNode.getUnique());
    uniqueNodeValue.value = classNode.getUnique();
}

function hasUnique(node : NodeObj) {
    var classNode = (<any>node.constructor);
    
    return classNode.usesUnique();
    
}

function removeNodeProperty(nodeProp : NodeProperty) {
    nodeProp.active = false;
}

function addNodeProperty(nodeProp : NodeProperty) {
    console.log(nodeProp);
    nodeProp.active = true;
}

// Going through all NodeObj and getting object extending NodeObj
function getNodes() : NodeObj[] {
    var root = props.root;
    var nodeTypes : NodeObj[] = [];
    
    while(root != null) {
        var newNode = getChildNode(root);
        if(newNode) {
            nodeTypes.push(newNode);
        }
        
        root = newNode;
    }

    return nodeTypes;
}

function getAllProperties(node : NodeObj) : NodeProperty[] {
    // Getting class name from instance and calling static method
    return (<any>node.constructor).getAllProperies();
}

function getActiveProperties(node : NodeObj) : NodeProperty[] {
    var retArr : NodeProperty[] = [];
    for(var prop of (<any>node.constructor).getAllProperies()) {
        if(prop.active) {
            retArr.push(prop);
        }
    }
    return retArr;
}

function getUnique(node : NodeObj) : Boolean {
    console.log((<any>node.constructor));
    var unique = (<any>node.constructor).getUnique();

    return unique;
}

// Get child Nodeobj which is an extend of NodeObj
// Example: Person extending NodeObj
function getChildNode(node : NodeObj) : NodeObj|null {
    console.log(node.getId());
    var children = node.getChildren();
    return children.length > 0 ? children[0] : null;
}

</script>

<style scoped>
    .rapport-meny {
        display: flex;
        flex-wrap: wrap;
        border-radius: var(--radius-medium);
        height: fit-content;
    }
    .rapport-meny .item {
        background: var(--color-primary-grey-lightest);
        box-shadow: none;
        width: 31%;
    }
    .rapport-meny .item .attributes {
        display: flex;
        flex-wrap: wrap;
        min-width: 200px;
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

</style>