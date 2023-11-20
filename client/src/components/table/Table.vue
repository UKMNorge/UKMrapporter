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
                    <tr class="col">
                        <th v-if="visTelling">
                            <div class="inner-div">
                                <button class="sort-button">
                                    #
                                </button>
                                <button class="remove-row ukm-botton-style not-correct-button not-visible">
                                    <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                                    </svg>
                                </button>
                            </div>
                        </th>
                        <!-- <template v-for="(keyObj, index) in keys" :key="index"> -->
                            <template v-for="(key, pos) in getActiveKeys()" :key="pos">
                                <th v-if="key.active" scope="col">
                                    <div class="inner-div">
                                        <button :class="{'active-sort' : root.getSortActivated() && root.getSortPosition() == pos}" @click="setSort(pos, key)" class="sort-button">
                                            <span class="title">{{ key.navn }}</span>
                                            <div class="indicators">
                                                <div>
                                                    <svg :class="{'not-active' : root.getSortActivated() && root.getAscSort() == false}" class="sort-indicator" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 6 19 1" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="m6.293 13.293 1.414 1.414L12 10.414l4.293 4.293 1.414-1.414L12 7.586z"></path></svg>
                                                </div>
                                                <div>
                                                    <svg :class="{'not-active' : root.getSortActivated() && root.getAscSort() == true}" class="sort-indicator" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 7 19 18" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M16.293 9.293 12 13.586 7.707 9.293l-1.414 1.414L12 16.414l5.707-5.707z"></path></svg>
                                                </div>
                                            </div>
                                        </button>
                                        <!-- <button @click="removeProperty(key)" class="remove-row ukm-botton-style not-correct-button">
                                            <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                                            </svg>
                                        </button> -->
                                        <button @click="removeProperty(key)" class="close-selector close-btn remove-prop as-btn-hover-default">
                                            <div class="icon close-selector">
                                                <svg class="remove-icon close-selector" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path class="close-selector" d="M11.5 4.24264L10.0858 2.82843L7.25736 5.65685L4.42893 2.82843L3.01472 4.24264L5.84315 7.07107L3.01472 9.89949L4.42893 11.3137L7.25736 8.48528L10.0858 11.3137L11.5 9.89949L8.67157 7.07107L11.5 4.24264Z" fill="#9B9B9B"/>
                                                </svg>
                                            </div>
                                        </button>
                                    </div>
                                </th>
                            </template>
                        <!-- </template> -->
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(value, key) in getItems()">
                        <tr :class="value[0].getSubnodes().length > 0 ? 'has-subnodes' : ''">
                            <td class="as-padding-space-4" v-for="item in value[1]">
                                <p>{{ item }}</p>
                            </td>
                        </tr>
                        <!-- Subnodes -->
                        <tr class="subnode-no-top-line" v-for="(subnode, key) in value[0].getSubnodes()" :key="key">
                            <td class="subnode-td" v-for="(subnodeItem, subnodeItemKey) in subnode.getItems()" :key="subnodeItemKey">
                                <p><b>{{ subnodeItem.getKey() }}</b>: {{ subnodeItem.getValue() }}</p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div v-if="visAntall" class="antall-leafs as-margin-top-space-2">
                <h5>Antall{{ uniqueCount ? ' unike' : '' }}: {{ countLeafNodesItems() }}</h5>
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
        leafNode: NodeObj,
        loading: boolean,
        root: RootNode,
        visAntall : any,
        visTelling : any,
    }>();

    var values : any = ref([]);
    var uniqueCount : any = ref(false);

    // var ascSort : any = ref(true);
    // var sortPosition : any = ref(-1);
    // var sortActivated : any = ref(false);

    var visAntall = ref(props.visAntall);
    var visTelling = ref(props.visTelling);

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

    function getActiveKeys() : NodeProperty[] {
        var ret : NodeProperty[] = [];

        for(var np of props.keys) {
            for(var value of np.value) {
                if(value.active) {
                    ret.push(value);
                }
            }
        }

        return ret;
    }

    function setSort(sortPos : number, nodeProp : NodeProperty) {
        props.root.setSortProperty(nodeProp);

        var sortPosition = props.root.getSortPosition();
        var sortActivated = props.root.getSortActivated();
        var ascSort = props.root.getAscSort();

        if(sortPosition != sortPos) {
            props.root.setSortActivated(true);
            props.root.setAscSort(true);
        }
        else if(sortActivated == true && !ascSort) {
            props.root.setSortActivated(false);
            props.root.setAscSort(true);
        }
        else if(sortActivated == false) {
            props.root.setSortActivated(true);
        }
        else {
            props.root.setSortActivated(true);
            props.root.setAscSort(false);
        }


        props.root.setSortPosition(sortPos);
    }

    // Sort items
    function sortBy(items : any[]) {
        var nodeProp = props.root.getSortProperty();

        // If NodeProperty is not defined or it is not active, then return items without sorting
        if(nodeProp == null || nodeProp.active == false) {
            // Reset sorting
            props.root.resetSorting();
            return items;
        }
        var sortPosition = props.root.getSortPosition();
        var ascSort = props.root.getAscSort();


        var pos = sortPosition;
        var sortedItems = items.sort((a : any, b : any) => {return a[pos] > b[pos] ? 1 : (a[pos] < b[pos] ? -1 : 0)});

        return ascSort == true ? sortedItems : sortedItems.reverse();
    }

    // Get all items from the classes sendt as array on values
    function getItems() {
        var sortActivated = props.root.getSortActivated();
        var items : any[] = [];
        for(var node of values.value) { 
            if(node.isActive() && node instanceof (<any>props.leafNode)){ 
                items.push([node, _getProperty(node)]);
            }
        }

        return sortActivated == true ? sortBy(items) : items;
    }

    /* 
    Returns true or false if the node is added.
    uniqueId is used to determine the value
    */
    function _checkUniqueAdding(node : NodeObj) : Boolean {
        // Unique is not activated
        if((<any>node.constructor).getUnique() == false) {
            return true;
        }

        // Checking all added nodes for the same uniqueId, if it is found, the method returns false
        for(var n of values.value) { 
            if(n.getUniqueId() == node.getUniqueId()) return false;
        }
        
        return true;
    }

    function getLeafNodes(node : NodeObj, leafNodes : any[]) {

        if (node.children.length === 0 && node instanceof (<any>props.leafNode)) {
            if(_checkUniqueAdding(node)) {
                leafNodes.push(node);
            }
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
            uniqueCount.value = false;
            return values.value.length;
        }
        else {
            uniqueCount.value = true;
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
                var value = (<any>node)[activeProp.method]();

                if(typeof value == "boolean") {
                    value = value ? 'Ja' : 'Nei';
                }
                objProperies.push(value);
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

<style scoped>
    .ukm-vue-table-row {
        margin: 0;
    }
    .ukm-vue-table-row thead tr th .inner-div {
        display: flex;
    }
    .ukm-vue-table-row thead tr th .inner-div .indicators {
        margin-top: 2px;
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
    .ukm-vue-table-row thead tr th button.remove-row.not-visible {
        visibility: hidden !important;
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
    .ukm-vue-table-row thead tr th .sort-button.active-sort .indicators div  {
        visibility: visible;
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
    button.close-btn.remove-prop {
        visibility: hidden;
        opacity: 0;
        border: none;
        border-radius: 50%;
        height: 35px;
        width: 35px;
        display: flex;
        background: transparent;
        transition: opacity .1s;
        margin-left: 5px;
    }
    .ukm-vue-table-row thead tr th .inner-div:hover button.close-btn.remove-prop {
        visibility: visible;
        opacity: 1;
        transition: opacity .1s;
    }
    button.close-btn.remove-prop:hover {
        background: var(--color-primary-grey-light) !important;        
    }
    button.close-btn.remove-prop:hover .icon svg path {
        fill: var(--color-primary-grey-dark) !important;
    }
    button.close-btn.remove-prop .icon {
        margin: auto
    }
    button.close-btn.remove-prop .icon svg {
        display: flex;
    }
    .subnode-td {
        padding-top: 0px;
        padding-bottom: 4px;
        padding-left: calc(var(--initial-space-box)*3);
        color: var(--color-primary-grey-dark);
    }
    .has-subnodes td p {
        font-weight: bold;
        font-size: 14px;
    }
    .has-subnodes td {
        padding-bottom: 4px !important;
    }
    .subnode-no-top-line {
        border-bottom: none;
        border-top: solid 2px white !important;
    }
    .table th, .table tr.has-subnodes td {
        padding-bottom: 0px;
    }
</style>
