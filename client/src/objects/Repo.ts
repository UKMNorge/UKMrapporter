import NodeObj from "./NodeObj";
import NodeProperty from "./NodeProperty";
import RootNode from "./RootNode";
import { ref } from 'vue';
import Excel from './Excel';
import PDF from './PDF';



class Repo {
    public root : NodeObj;
    public leafNode : NodeObj;
    
    private refs = {};
    public antall = ref(false);
    public telling = ref(false);
    private uniqueNodeObjects : any[] = [];
    private excel : Excel;
    private pdf : PDF;
    private rapportName : string;

    constructor(root : NodeObj, uniqueNodeObjects : any[], leafNode : any, rapportName : string) {
        this.root = root;
        this.uniqueNodeObjects = uniqueNodeObjects;
        this.leafNode = leafNode;
        this.excel = new Excel(this);
        this.pdf = new PDF(this);
        this.rapportName = rapportName;


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

    public generatePDF() {
        this.pdf.generateFile();
    }

    public getRapportName() : string {
        return this.rapportName;
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
    
    /**
     * Public method to get all nodes in the tree.
     * It initializes an empty array and passes it along with the root node to the private recursive method.
     * @returns {NodeObj[]} An array of all nodes in the tree.
     */
    public getAllNodes(): NodeObj[] { 
        var allNodes : NodeObj[] = [];
        return this._getAllNodes(this.root, allNodes);
    }

    /**
     * Private recursive method to get all nodes in the tree.
     * It pushes the current node to the array and calls itself for each child of the current node.
     * @param {NodeObj} node - The current node.
     * @param {NodeObj[]} nodes - The array of nodes.
     * @returns {NodeObj[]} The array of nodes.
     */
    private _getAllNodes(node: NodeObj, nodes: NodeObj[] = []): NodeObj[] {
        nodes.push(node);
    
        for (let child of node.children) {
            this._getAllNodes(child, nodes);
        }
    
        return nodes;
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