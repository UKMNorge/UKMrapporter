import NodeObj from "./NodeObj";
import NodeProperty from "./NodeProperty";
import RootNode from "./RootNode";
import { ref } from 'vue';



class Repo {
    public root : NodeObj;
    
    private refs = {};

    constructor(root : NodeObj) {
        this.root = root;


        // Adding parent to all nodes on the tree
        this.addParents(this.root);

        // Defining ref
        this.refs = {
            groupingNode : ref(RootNode),
            rootNodes : ref([this.root])
        }
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
        var obj = {};
        var retObj : {node : NodeObj, value : NodeProperty[]}[] = [];
        this.uniqueNodesObjects(this.root, obj);

        for(var key of Object.keys(obj)) {
            var objectNode = (<any>obj)[key];
            retObj.push({
                node : objectNode.constructor,
                value : objectNode.constructor.getKeysForTable(),
            })

        }

        return retObj;
    }

    // Recursive function to get all unqiue objects
    private uniqueNodesObjects(node : NodeObj, leafNodes : {}) {
        for (var i = 0; i < node.children.length; i++) {
            this.uniqueNodesObjects(node.children[i], leafNodes);
        }

        // Add all nodes that are not root
        if (!(node instanceof RootNode)) {
            (<any>leafNodes)[(<any>node.constructor).name] = node;
        }
    }
}

export default Repo;