<template>
    <div v-if="getItems().length > 0" class="as-container">
        <div v-if="root.getNavn()" class="as-margin-bottom-space-2">
            <h4>{{ root.getNavn() }}</h4>
        </div>
        <div class="as-padding-space-4 as-padding-top-space-2 as-padding-bottom-space-2 as-card-1">
            <!-- Phantom loading -->
            <div v-show="loading">
                <table class="table ukm-vue-table-row">
                    <thead>
                        <tr>
                            <th v-for="index in 5" :key="index"><span class="phantom-loading">---------</span></th>
                        </tr>
                    </thead>
                    <tbody>
                         <tr v-for="index in Math.floor((Math.random() * 15) + 5)" :key="index">
                            <td v-for="innerIndex in 5" :key="innerIndex"><span class="phantom-loading">-----</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
    
            <table v-show="!loading" class="table ukm-vue-table-row">
                <thead>
                    <tr>
                        <template v-for="keyObj in keys">
                            <template v-for="key in keyObj.value">
                                <th v-if="key.active" scope="col">
                                    <div  class="inner-div">
                                        <button @click="sortBy(key)" class="sort-button">
                                            <span class="title">{{ key.navn }}</span>
                                            <div class="indicators">
                                                <div>
                                                    <svg :class="{'not-active' : currentSort == key.navn && ascSort}" class="sort-indicator" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 6 19 1" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="m6.293 13.293 1.414 1.414L12 10.414l4.293 4.293 1.414-1.414L12 7.586z"></path></svg>
                                                </div>
                                                <div>
                                                    <svg :class="{'not-active' : currentSort == key.navn && !ascSort}" class="sort-indicator" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 7 19 18" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M16.293 9.293 12 13.586 7.707 9.293l-1.414 1.414L12 16.414l5.707-5.707z"></path></svg>
                                                </div>
                                            </div>
                                        </button>
                                        <button @click="removeProperty(key)" class="remove-row ukm-botton-style not-correct-button">
                                            <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                            </template>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="value in getItems()">
                        <td class="as-padding-space-4" v-for="item in value">{{ item }}</td>
                    </tr>
                </tbody>
            </table>

          
            <div v-if="visAntall" class="antall-leafs as-margin-top-space-2">
                <h5>Antall: {{ countLeafNodesItems() }}</h5>
            </div>
        </div>
        
    </div>
</template>


<script setup lang="ts">
    import { watchEffect, ref, onMounted, onUpdated, toRaw } from 'vue';
    import NodeObj from '../../objects/NodeObj';
    import NodeProperty from '../../objects/NodeProperty';
    import RootNode from '../../objects/RootNode';

    // var keys!: {node : Object, value : NodeProperty[]}[];
    // var values!: any[];
    // var loading!: boolean;


    const props = defineProps<{
        keys: {node : Object, value : NodeProperty[]}[],
        loading: boolean,
        root: RootNode,
        visAntall : any,
        visTelling : any,
    }>();

    var values : any = ref([]);

    var visAntall = ref(props.visAntall);

    function getValTest() {
        return props.visAntall;
    }

     // Watch changes of the root
     watchEffect(() => {
        const newVal = props.root;
        updateNodes();
    });

    function updateNodes() {
        values.value = [];
        getLeafNodes(props.root, values.value);
    }

    var currentSort : string = '';
    var ascSort : boolean = true;

    function sortBy(key : {navn : string, method : string , active: Boolean}) {
        currentSort = key.navn;

        values.sort((a : any, b : any) => {return a[key.method]() > b[key.method]() ? 1 : (a[key.method]() < b[key.method]() ? -1 : 0)})

        if(ascSort) {
            values.reverse();
        }

        ascSort = !ascSort
    }

    // Get all items from the classes sendt as array on values
    function getItems() {
        var items : any[] = [];
        for(var node of values.value) { 
            if(node.isActive()){ 
                items.push(_getProperty(node));
            }
        }

        return items;
    }

    function getLeafNodes(node : NodeObj, leafNodes : any[]) {
        if (node.children.length === 0) {
            console.log('yyy');
            leafNodes.push(node);
        } else {
            for (var i = 0; i < node.children.length; i++) {
                if((node instanceof RootNode) || toRaw(node).isActive()) {
                    getLeafNodes(node.children[i], leafNodes);
                }
            }
        }
    }

    function countLeafNodesItems() : Number {
        if(values.value.length < 1) {
            return 0;
        }

        var arrObj : any = {};
        var isUnique = values.value[0].constructor.getUnique();

        // Doesnt count unique
        if(!isUnique) {
            return values.value.length;
        }
        
        // It count unique
        for(var node of values.value) {
            arrObj[(<NodeObj>node).getUniqueId()] = node;
        }

        return Object.keys(arrObj).length;
    }

    function removeProperty(nodeProp : NodeProperty) {
        nodeProp.active = false;
    }

    // Get properties including properties on parents 
    function _getProperty(node : NodeObj) : any[] {
        var objProperies = []          
        for(var activeProp of node.getActiveProperties()) {
            try {
                objProperies.push((<any>node)[activeProp.method]());
            } catch (e: unknown) {
                console.error('Method: ' + activeProp.method + ' does not exist on ' + node.getRepresentativeName() + '. Please check the properties and methods');
            }
        }

        var parent = node.parent;
        while(parent) {
            for(var activeProp of parent.getActiveProperties()) {
                try {
                    objProperies.push((<any>parent)[activeProp.method]());
                } catch (e: unknown) {
                    console.error('Method: ' + activeProp.method + ' does not exist on ' + node.getRepresentativeName() + '. Please check the properties and methods');
                }
            }
            parent = parent.parent;
        }

        return objProperies;
    }
</script>

<style>
    .ukm-vue-table-row {
        margin: 0;
    }
    .ukm-vue-table-row thead tr th .inner-div {
        display: flex;
    }
    .ukm-vue-table-row thead tr th .inner-div .indicators div {
        display: flex;
        visibility: hidden;
    }
    .ukm-vue-table-row thead tr th .inner-div:hover .indicators div {
        visibility: visible;
    }
    .ukm-vue-table-row thead tr th .inner-div .indicators div svg.sort-indicator.not-active{
        fill: #bebebe !important;
    }
        
    .ukm-vue-table-row thead tr th button.remove-row {
        visibility: hidden;
        border-radius: 25px;
        width: 25px;
        height: 25px;
        border: solid 1px #bebebe;
        margin: auto;
        margin-left: -5px;
        padding: 4px 3px;
        border: solid 1px #00000040 !important;
        background: #fff !important;
    }
    .ukm-vue-table-row thead tr th:hover button.remove-row {
        visibility: visible;
    }
    /* .sort-indicator.asc {
        transform: rotate(180deg);
    } */
    .ukm-vue-table-row thead tr th .sort-button {
        border: none;
        font-size: 14px;
        font-weight: 400;
        padding: 10px 20px;
        background: #0000;
        display: flex;
        padding: 0;
    }
    .ukm-vue-table-row thead tr th .sort-button .title {
        margin: auto;
        margin-right: 5px;
    }
    .removed-keys {
        display: flex;
    }
    .table > thead > tr > th {
        
    }
</style>
