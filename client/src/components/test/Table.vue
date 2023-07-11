<template>
    <div>
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

        <div class="removed-keys">
            <!-- <button v-for="key in removedKeys" @click="addKey(key)" class="key ukm-botton-style correct-button">
                <span>{{ key.navn }}</span>
            </button> -->
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
                                    <button class="remove-row ukm-botton-style not-correct-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="3 4 19 18" style="fill: #272727"><path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path></svg>
                                    </button>
                                </div>
                            </th>
                        </template>
                    </template>
                </tr>
            </thead>
            <tbody>
                <tr v-for="value in getItems()">
                    <td v-for="item in value">{{ item }}</td>
                </tr>
            </tbody>
        </table>
        <!-- <button @click="getNodeChildren(root)">Test recurs</button> -->

        <h1>here we go</h1>
        <div>
            <RecursiveTableComp :obj="root" :parents="[]" />
        </div>

        <button @click="getLeafClick(root, [])">Get leafs with parents</button>

    </div>
</template>


<script setup lang="ts">
    import { ref } from 'vue';
    import NodeObj from '../../objects/NodeObj';
    import NodeProperty from '../../objects/NodeProperty';
    import RootNode from '../../objects/RootNode';
    import RecursiveTableComp from './RecursiveTableComp.vue';

    // var keys!: {node : Object, value : NodeProperty[]}[];
    // var values!: any[];
    // var loading!: boolean;

    const props = defineProps<{
        keys: {node : Object, value : NodeProperty[]}[],
        values: any[],
        loading: boolean,
        root: RootNode,
        nodes : any[],
    }>();

    var removedKeys : {node : Object, value : NodeProperty[]}[] = [];

    var currentSort : string = '';
    var ascSort : boolean = true;

    function sortBy(key : {navn : string, method : string , active: Boolean}) {
        currentSort = key.navn;

        props.values.sort((a, b) => {return a[key.method]() > b[key.method]() ? 1 : (a[key.method]() < b[key.method]() ? -1 : 0)})

        if(ascSort) {
            props.values.reverse();
        }

        ascSort = !ascSort
    }

    // Delete row
    // function removeRow(key : {navn : string, method : string , active: Boolean}) {           
    //     for(var i = 0; i < props.keys.length; i++) {
    //         if(props.keys[i].navn == key.navn) { 
    //             removedKeys.push(props.keys[i]);
    //             props.keys.splice(i, 1);
    //         }
    //     }

    // }

    // function addKey(key : {navn : string, method : string , active: Boolean}) {
    //     for(var i = 0; i < removedKeys.length; i++) {
    //         if(key.active && removedKeys[i].navn == key.navn) {
    //             props.keys.push(key);
    //             removedKeys.splice(i, 1);
    //         }
    //     }
    // }

    // function getNodes() : NodeObj[] {
    //     var root = props.root;
        
    //     var children : NodeObj[] = [];
        
        
    //     while(root != null) {
    //         var children = getNodeChildren(root);
    //         for(var c of )
    //         if(children.length > 0) {
    //             children.push(children);
    //         }
    //         else {
    //             break;
    //         }
    //     }

    //     return children;

    // }
    // var objs = {};
    
    // Print children recursively
    // function getNodeChildren(node : NodeObj) { //: NodeObj[] {

    //     objs[node.getId()] = [];
        
    //     // console.warn(node);
        
    //     if(node.getChildren().length > 0) {
    //         for(var c of node.getChildren()) {
    //             objs[node.getId()].push(c);
    //             // console.log(c);
    //             if(c.getChildren().length > 0) {
    //                 getNodeChildren(c);
    //             }
    //         }
    //     }

    //     console.log(objs);
    // }

    function getLeafClick() {
        console.log(getLeaf(props.root, [], props));
        console.log(props.nodes);
    }

    function getLeaf(root : NodeObj, parents : NodeObj[], props : any) {

        if(root.getChildren().length > 1) {
            for(var c of root.getChildren()) {
                console.log('b')
                return [...getLeaf(c, [...parents, root], props)]
            }
        }

        if(root.getChildren().length < 1) {
            console.log('a')
            return [{'node' : root, 'parents' : parents }]
        }

    }



    // Get all items from the classes sendt as array on values
    function getItems() {
        console.log(props.root);
        var items = [];
        for(var value of props.values) { 
            var item = []          
            for(var keyObj of props.keys) {
                for(var key of keyObj.value) {
                    console.log('yoyo');
                    if(key.active) {
                        item.push(value[key.method]());
                    }
                }
            }
            items.push(item);
        }

        return items;
    }
</script>

<style>
    
    .ukm-vue-table-row thead tr th .inner-div {
        display: flex;
    }
    .ukm-vue-table-row thead tr th .inner-div .indicators div {
        display: flex;
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
        margin-left: -20px;
        display: flex;
    }
    .ukm-vue-table-row thead tr th .sort-button .title {
        margin: auto;
        margin-right: 5px;
    }
    .removed-keys {
        display: flex;
    }
</style>
