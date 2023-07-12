<template> 
    <div>
        <MenyVue :root="root" />

        <div class="container as-container">
            <div class="as-margin-top-space-7">
                <Table :loading="loading" :keys="tableKeys" :values="values" :root="root" :nodes="[]" />
            </div>
        </div>
    </div>
</template>
  
<script setup lang="ts">
// Fra pakke UKM Komponenter
import Table from '../components/test/Table.vue'
import MenyVue from '../components/Meny.vue';
import Person from '../objects/Person';
import Kommune from '../objects/Kommune';
import RootNode from '../objects/RootNode';
import NodeObj from '../objects/NodeObj';
import NodeProperty from '../objects/NodeProperty';



Person.hasUnique = true;

var loading : boolean = false;
// Må automatiseres på Tab
var tableKeys : {node : Object, value : NodeProperty[]}[] = [
    {'node' : Person, 'value' : Person.getKeysForTable()},
    {'node' : Kommune, 'value' : Kommune.getKeysForTable()},
];


var values : NodeObj[] = [];



console.log(tableKeys);
console.log(values);
// ----- DATA -----
var root = new RootNode();

// Adding Kommune(s)
var k0 = new Kommune('k1', "Tana", 'Nordland');
var k1 = new Kommune('k2', "Lillestrøm", 'Viken');
var k2 = new Kommune('k3', "Oslo", 'Viken');


var p1 = new Person('p1', 'Ole Nordby', 18);
var p2 = new Person('p2', 'Lene Langvei', 21);
var p3 = new Person('p3', 'Dag Steinfjell', 17);
var p4 = new Person('p4', 'Stein Olavsgård', 16);
var p5 = new Person('p5', 'Gerard Sørgård', 20);


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

getLeafNodes(root, values)

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

// Recursive function to get leaf nodes
function getLeafNodes(node : NodeObj, leafNodes : NodeObj[]) {
    if (node.children.length === 0) {
        leafNodes.push(node);
    } else {
        for (var i = 0; i < node.children.length; i++) {
            getLeafNodes(node.children[i], leafNodes);
        }
    }
}

</script>