import Repo from "./Repo";
import NodeObj from './../objects/NodeObj';
import RootNode from "../objects/RootNode";



abstract class FileGenerator {
    protected repo : Repo;
    protected nodes : NodeObj[] = [];
    protected root : NodeObj;
    protected leafNode : NodeObj;
    
    constructor(repo : Repo) {
        this.repo = repo;
        this.root = repo.root;
        this.leafNode = repo.leafNode;
    }

    protected updateNodes() {
        this.nodes = [];
        this.getLeafNodes(this.root, this.nodes);
    }

    protected getLeafNodes(node : NodeObj, leafNodes : any[]) {

        if (node.children.length === 0 && node instanceof (<any>this.leafNode)) {
            if(this._checkUniqueAdding(node)) {
                leafNodes.push(node);
            }
        } else {
            for (var i = 0; i < node.children.length; i++) {
                if((node instanceof RootNode) || (<any>node).isActive()) {
                    this.getLeafNodes(node.children[i], leafNodes);
                }
            }
        }
    }

    /* 
    Returns true or false if the node is added.
    uniqueId is used to determine the value
    */
    private _checkUniqueAdding(node : NodeObj) : Boolean {
        // Unique is not activated
        if((<any>node.constructor).getUnique() == false) {
            return true;
        }

        // Checking all added nodes for the same uniqueId, if it is found, the method returns false
        for(var n of this.nodes) {
            if(n.getUniqueId() == node.getUniqueId()) return false;
        }
        
        return true;
    }

    // Get all items from the classes sendt as array of values
    protected getItems() {
        var sortActivated = this.root.getSortActivated();

        var items : any[] = [];
        var count = 0;
        for(var node of this.nodes) { 
            if(node.isActive() && node instanceof (<any>this.leafNode)){ 
                items.push(this._getProperty(node, count++));
                // Node has subnodes
                if(node.getSubnodes().length > 0) {
                    for(var subnode of node.getSubnodes()) {
                        for(var subnodeItem of subnode.getItems()) {
                            items.push(['   ' + subnodeItem.getKey() + ': ' + subnodeItem.getValue()]);
                        }
                    }
                }
            }
        }
        return sortActivated ? this.sortBy(items) : items;  
    }

    // Sort items
    private sortBy(items : any[]) {
        var nodeProp = this.root.getSortProperty();

        // If NodeProperty is not defined or it is not active, then return items without sorting
        if(nodeProp == null || nodeProp.active == false) {
            // Reset sorting
            this.root.resetSorting();
            return items;
        }

        var sortPosition = this.root.getSortPosition();
        var ascSort = this.root.getAscSort();

        var pos = sortPosition;
        var sortedItems = items.sort((a : any, b : any) => {
            a = (<any>Object).values(a);
            b = (<any>Object).values(b);
            return a[pos] > b[pos] ? 1 : (a[pos] < b[pos] ? -1 : 0)
        });

        return ascSort == true ? sortedItems : sortedItems.reverse();
    }

    // Get properties including properties on parents 
    protected _getProperty(node : RootNode, count : number) : any[] {
        var objProperies : any = [];

        if(this.repo.telling.value == true) {
            objProperies['#'] = ++count;
        }

        for(var activeProp of node.getActiveProperties()) {
            try {
                var value = (<any>node)[activeProp.method]();

                if(typeof value == "boolean") {
                    value = value ? 'Ja' : 'Nei';
                }

                objProperies[activeProp.navn] = value;
            } catch (e: unknown) {
                console.error('Method: ' + activeProp.method + ' does not exist on ' + node.getRepresentativeName() + '. Please check the properties and methods');
            }
        }

        var parent = node.parent;
        while(parent) {
            for(var activeProp of parent.getActiveProperties()) {
                try {
                    objProperies[activeProp.navn] = (<any>parent)[activeProp.method]();
                } catch (e: unknown) {
                    console.error('Method: ' + activeProp.method + ' does not exist on ' + node.getRepresentativeName() + '. Please check the properties and methods');
                }
            }
            parent = parent.parent;
        }

        return objProperies;
    }

    public getAllNodesAtLevel(node : NodeObj, filteredNodes : NodeObj[], filterNode : NodeObj) {
        if ((<NodeObj>node).className === (<any>(<any>filterNode).value).className) {
            filteredNodes.push(node);
        } else {
            for (var i = 0; i < node.children.length; i++) {
                this.getAllNodesAtLevel(node.children[i], filteredNodes, filterNode);
            }
        }
    }

}

export default FileGenerator;