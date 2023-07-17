<template>
    <div class="rapport-meny">
        <div class="object item as-card-2 as-padding-space-2 as-margin-right-space-2">
            <h4>Gruppering</h4>

            <div class="attributes as-margin-top-space-2">
                <select @change="changeNode($event)" class="custom-select custom-select-sm">
                    <!-- <option v-for="(node, key) in filterNodes" :key="key" selected>Ingen</option> -->
                    <option :selected="selectedNode.name == node.constructor.name ? true : false" v-for="(node, key) in filterNodes" :value="node.constructor.name" :key="key">{{ getNodeName(node) }}</option>
                </select>
            </div>

        </div>        
    </div>
</template>

<script setup lang="ts">
import NodeObj from "../objects/NodeObj";
import RootNode from "../objects/RootNode";
import { ref } from 'vue';
import { onUpdated, onMounted } from 'vue';


const props = defineProps<{
    root: RootNode|null,
    gruppingUpdateCallback : (n : NodeObj)=>void,
}>();

var selectorPopup : any = ref(false);
var filterNodes : any = ref([]);
var selectedNode : any = ref(props.root ? props.root.constructor : null);
var keyValueNode : any = ref({});


onMounted(() => {
    if(props.root) {
        getGroupingNodes(props.root, keyValueNode.value);
    }

    for(var key of Object.keys(keyValueNode.value)) {
        var val = (<any>keyValueNode.value)[key];
        filterNodes.value.push(val);
    }
    console.warn(filterNodes.value);
})

function changeNode(event : any) {
    var node = keyValueNode.value[event.target.value].constructor;
    props.gruppingUpdateCallback(node);
}

function getNodeName(node : NodeObj) : string {
    console.log(node.className);
    if(node.className == 'Root' || node.className.length < 1) {
        return 'Ingen';
    }
    return node.className != undefined ? node.className : 'Ingen'; 
}
 
// Recursive function to get all unqiue objects
function getGroupingNodes(node : NodeObj, leafNodes : {}) {
    for (var i = 0; i < node.children.length; i++) {
        getGroupingNodes(node.children[i], leafNodes);
    }

    // Add all nodes that are not leaf and root
    if (true || node.children.length > 0 && !(node instanceof RootNode)) {
        (<any>leafNodes)[(<any>node.constructor).name] = node;
    }
}

</script>

<style scoped>
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
</style>    