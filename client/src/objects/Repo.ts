import NodeObj from "./NodeObj";
import NodeProperty from "./NodeProperty";
import RootNode from "./RootNode";
import { ref } from 'vue';
import Excel from './Excel';



class Repo {
    public root : NodeObj;
    public leafNode : NodeObj;
    
    private refs = {};
    public antall = ref(false);
    public telling = ref(false);
    private uniqueNodeObjects : any[] = [];
    private excel : Excel;

    constructor(root : NodeObj, uniqueNodeObjects : any[], leafNode : any) {
        this.root = root;
        this.uniqueNodeObjects = uniqueNodeObjects;
        this.leafNode = leafNode;
        this.excel = new Excel(this);

        // Adding parent to all nodes on the tree
        this.addParents(this.root);

        // Defining ref
        this.refs = {
            groupingNode : ref(RootNode),
            rootNodes : ref([this.root]),
        }
    }

    public generateExcel() {
        this.excel.generateFile();
    }

    public getGroupingNode() : any {
        return (<any>this.refs).groupingNode;
    }

    // Get root nodes that uses for grouping
    public getRootNodes() : any {
        return (<any>this.refs).rootNodes;
    }

    // Recursive function to add parents to NodeObj
    private addParents(node : NodeObj, parent : NodeObj|null = null) {
        // var obj : any = {};
        if(!(parent instanceof RootNode)) {
            node.parent = parent;
        }
        
        if (node.children.length > 0) {
            // obj.children = [];
            for (var i = 0; i < node.children.length; i++) {
                // obj.children.push(addParents(node.children[i], node));
                this.addParents(node.children[i], node);
            }
        }
    }

    public gruppingUpdateCallback(node : NodeObj) : void {
        this.getGroupingNode().value = node;
    
        // Get nodes at level
        var rootNodesArr : NodeObj[] = [];
        if(this.root != null) {
            this.getAllNodesAtLevel(this.root, rootNodesArr, node);
        }
    
        this.getRootNodes().value = [];
        this.getRootNodes().value = rootNodesArr;
    
    }

    public getAllNodesAtLevel(node : NodeObj, filteredNodes : NodeObj[], filterNode : NodeObj) {
        if (node.constructor.name === (<any>filterNode).name) {
            filteredNodes.push(node);
        } else {
            for (var i = 0; i < node.children.length; i++) {
                this.getAllNodesAtLevel(node.children[i], filteredNodes, filterNode);
            }
        }
    }

    public getTableKeys() : {node : NodeObj, value : NodeProperty[]}[] {
        var retObj : {node : NodeObj, value : NodeProperty[]}[] = [];
    

        for(var objectNode of this.uniqueNodeObjects) {
            retObj.push({
                node : objectNode,
                value : objectNode.getKeysForTable(),
            })

        }
        return retObj;
    }

    public tableCallback(antall : Boolean, telling : Boolean) : void {
        (<any>this.antall.value) = antall;
        (<any>this.telling.value) = telling;
    }
}

export default Repo;