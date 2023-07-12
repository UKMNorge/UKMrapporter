<template>
    <div class="as-container">
        <div class="rapport-meny nop as-card-1 as-padding-space-4">
            <!-- nodes -->
            <div v-for="(node, key) in getNodes()" :key="key" class="object item as-card-2 as-padding-space-2 as-margin-right-space-2">
                <h4>{{ node.getRepresentativeName() }}</h4>
                <div class="attributes as-margin-top-space-2">
                    
                    <div v-if="hasUnique(node)" @click="toggleFunction(node)" :class="getUnique(node) ? 'active' : ''" class="attribute toggle-function as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                        <span>Unike</span>
                    </div>

                    <!-- Node property -->
                    <div v-for="(nodeProp, key2) in getAllProperties(node)" :key="key2">
                        <div @click="removeNodeProperty(nodeProp)" v-if="nodeProp.active" class="attribute as-padding-space-1 as-margin-right-space-1 as-btn-hover-default">
                            <span>{{ nodeProp.navn }}</span>
                            <div class="icon">
                                <svg class="remove-icon" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                                </svg>
                            </div>
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
import { defineProps } from 'vue';
// import $ from "jquery";
import RootNode from '../objects/RootNode';
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';


const props = defineProps<{
    root: RootNode|null
}>();


function toggleFunction(node : NodeObj) {
    var classNode = (<any>node.constructor);
    classNode.setUnique(!classNode.getUnique());
}

function hasUnique(node : NodeObj) {
    var classNode = (<any>node.constructor);
    
    return classNode.usesUnique();
    
}

function removeNodeProperty(nodeProp : NodeProperty) {
    nodeProp.active = false;
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

function getUnique(node : NodeObj) : Boolean {
    return (<any>node.constructor).getUnique();
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
        display: flex;
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
</style>