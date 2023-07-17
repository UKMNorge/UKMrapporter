<template> 
    <div>
        <MenyVue :root="root" :gruppingUpdateCallback="gruppingUpdateCallback" />

        <div class="container as-container">
            <div v-for="(r, key) in rootNodes" :key="key">
                <div class="as-margin-top-space-7" >
                    <Table :key="key" :loading="loading" :keys="tableKeys" :root="r" />
                </div>
            </div>
        </div>

    </div>
</template>
  
<script setup lang="ts">
// Fra pakke UKM Komponenter
import Table from '../components/table/Table.vue'
import { ref } from 'vue';
import MenyVue from '../components/Meny.vue';
import Person from '../objects/rapporter/Person';
import Kommune from '../objects/rapporter/Kommune';
import RootNode from '../objects/RootNode';
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';



Person.hasUnique = true;

var loading = ref(true);

setTimeout(() => {
    loading.value = false;
}, 1000);

// Må automatiseres på Tab
var tableKeys : {node : Object, value : NodeProperty[]}[] = [
    {'node' : Person, 'value' : Person.getKeysForTable()},
    {'node' : Kommune, 'value' : Kommune.getKeysForTable()},
];


// ----- DATA -----
var root = new RootNode();

// var values : NodeObj[] = [];
var values : any = ref([]);
var groupingNode : any = ref(RootNode);
var rootNodes : any = ref([root]);



// Adding Kommune(s)
var k0 = new Kommune('k1', "Tana", 'Nordland');
var k1 = new Kommune('k2', "Lillestrøm", 'Viken');
var k2 = new Kommune('k3', "Oslo", 'Viken');


var p1 = new Person('p1', 'Ole Nordby', 18, '46516256',  'Ole_Nordby@gmail.com');
var p2 = new Person('p2', 'Lene Langvei', 21, '55516200', 'Lene_Langvei@gmail.com');
var p3 = new Person('p3', 'Dag Steinfjell', 17, '12316211', 'Dag_Steinfjell@gmail.com');
var p4 = new Person('p4', 'Stein Olavsgård', 16, '56516222', 'Stein_Olavsgård@gmail.com');
var p5 = new Person('p5', 'Gerard Sørgård', 20, '47816353', 'Gerard_Sørgård@gmail.com');


// Adding Person(s) to Kommune(s)
k0.addChildren([
    p1, p2, p3
]);

k1.addChildren([
    p4
]);

k2.addChildren([
    p5
]);

// Adding children to Kommune(s) to Root
root.addChildren([
    k0, k1, k2
]);



addParents(root);
// getLeafNodes(root, values.value)


/* ------- TO SUPERCLASS ------- */

// Recursive function to add parents to NodeObj
function addParents(node : NodeObj, parent : NodeObj|null = null) {
    // var obj : any = {};
    if(!(parent instanceof RootNode)) {
        node.parent = parent;
    }
    
    if (node.children.length > 0) {
        // obj.children = [];
        for (var i = 0; i < node.children.length; i++) {
            // obj.children.push(addParents(node.children[i], node));
            addParents(node.children[i], node);
        }
    }
}

function gruppingUpdateCallback(node : NodeObj) : void {
    groupingNode.value = node;

    // Get nodes at level
    var rootNodesArr : NodeObj[] = [];
    if(root != null) {
        getAllNodesAtLevel(root, rootNodesArr, node);
    }

    rootNodes.value = [];
    rootNodes.value = rootNodesArr;

}


// Move to graph repo or class
function getAllNodesAtLevel(node : NodeObj, filteredNodes : NodeObj[], filterNode : NodeObj) {
    if (node.constructor.name === (<any>filterNode).name) {
        filteredNodes.push(node);
    } else {
        for (var i = 0; i < node.children.length; i++) {
            getAllNodesAtLevel(node.children[i], filteredNodes, filterNode);
        }
    }
}

// Recursive function to get leaf nodes
// function getLeafNodes(node : NodeObj, leafNodes : NodeObj[]) {
//     console.log('ffff');
//     if (node.children.length === 0) {
//         leafNodes.push(node);
//     } else {
//         for (var i = 0; i < node.children.length; i++) {
//             if(node.isActive()) {
//                 getLeafNodes(node.children[i], leafNodes);
//             }
//         }
//     }
// }

</script>