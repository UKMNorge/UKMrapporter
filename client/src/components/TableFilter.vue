<template>
    <div class="rapport-meny">
        <div class="object item as-card-2 as-padding-space-2 as-margin-space-1 as-margin-top-space-2">
            <h4>Tabell</h4>

            <div class="attributes as-margin-top-space-2">
                
                <div>
                    <div @click="toggleAntall()" :class="visAntall ? 'active' : ''" class="attribute toggle-function as-padding-space-1 as-margin-right-space-1 as-padding-space-2 as-padding-top-space-1 as-padding-bottom-space-1 as-btn-hover-default">
                        <span>Antall</span>
                    </div>
                </div>

                <div>
                    <div @click="toglleTelling()" :class="visTelling ? 'active' : ''" class="attribute toggle-function as-padding-space-1 as-margin-right-space-1 as-padding-space-2 as-padding-top-space-1 as-padding-bottom-space-1 as-btn-hover-default">
                        <span>Nummerering</span>
                    </div>
                </div>
            </div>

        </div>        
    </div>
</template>

<script setup lang="ts">
import { Callbacks } from "jquery";
import NodeObj from "../objects/NodeObj";
import RootNode from "../objects/RootNode";
import { ref } from 'vue';
import { onUpdated, onMounted } from 'vue';


const props = defineProps({
    tableCallback: {
        type : Object as () => (antall : Boolean, telling : Boolean)=>void,
        required: true,
    },
    visAntall: {
        type: Boolean,
        default: false,
        required: false,
    },
    visTelling: {
        type: Boolean,
        default: false,
        required: false,
    }
});

var visAntall = ref(props.visAntall ? props.visAntall : false);
var visTelling = ref(props.visTelling ? props.visTelling : false);

function callback() {
    props.tableCallback((<Boolean>visAntall.value), (<Boolean>visTelling.value));
}

function toggleAntall() {
    visAntall.value = !visAntall.value;
    callback();
}

function toglleTelling() {
    visTelling.value = !visTelling.value;
    callback();
}

// onMounted(() => {
//     if(props.root) {
//         getGroupingNodes(props.root, keyValueNode.value);
//     }

//     for(var key of Object.keys(keyValueNode.value)) {
//         var val = (<any>keyValueNode.value)[key];
//         filterNodes.value.push(val);
//     }
//     console.warn(filterNodes.value);
// })

// function changeNode(event : any) {
//     var node = keyValueNode.value[event.target.value].constructor;
//     props.gruppingUpdateCallback(node);
// }

// function getNodeName(node : NodeObj) : string {

//     if(node.className == 'Root' || node.className.length < 1) {
//         return 'Ingen';
//     }
//     return node.className != undefined ? node.className : 'Ingen'; 
// }
 
// // Recursive function to get all unqiue objects
// function getGroupingNodes(node : NodeObj, leafNodes : {}) {
//     for (var i = 0; i < node.children.length; i++) {
//         getGroupingNodes(node.children[i], leafNodes);
//     }

//     // Add all nodes that are not leaf and root
//     if (node.children.length > 0) {
//         (<any>leafNodes)[(<any>node.constructor).name] = node;
//     }
// }

</script>

<style scoped>
.rapport-meny {
    width: 100%;
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
        border-radius: 20px;
        font-weight: 300;
        letter-spacing: 1px;
        display: flex;
        height: 100%;
        min-width: 40px;
        min-height: 35px;
        background: #fff;
        border: solid 1px var(--color-primary-grey-medium);
    }
    .rapport-meny .item .attributes .attribute.new svg {
        margin: auto;
    }
    .rapport-meny .item .attributes .attribute.new svg path {
        fill: var(--color-primary-black) !important;
    }

    .rapport-meny .item .attributes .attribute.toggle-function {
        background: #fff;
    }
    .rapport-meny .item .attributes .attribute.toggle-function.active {
        background: var(--color-primary-bla-500) !important;
        border-color: var(--color-primary-bla-500) !important;
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