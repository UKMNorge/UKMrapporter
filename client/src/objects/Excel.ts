import RootNode from "../objects/RootNode";
import { read, utils, writeFileXLSX } from 'xlsx';
import NodeObj from './../objects/NodeObj';
import Repo from "./Repo";



class Excel {
    private nodes : RootNode[] = [];
    private root : NodeObj;
    private leafNode : NodeObj;
    private repo : Repo;

    constructor(repo : Repo) {
        this.root = repo.root;
        this.leafNode = repo.leafNode;
        this.repo = repo;
    }

    public generateFile() {
        // HUSK: det burkes kun leafNode for hente unike informasjon om unike er aktivert
        var isLeafUnique = (<any>this.leafNode).getUnique();
        
        this.updateNodes();
        var date = new Date();
        var lines = this.getItems();

        // Antall inkludering unike
        if(this.repo.antall.value == true) {
            var antall = lines.length;
            var antallKey = isLeafUnique ? 'Antall unike' : 'Antall';
            var antalObj : any = {};
            antalObj[antallKey] = antall;

            lines.push(antalObj);
        }
        
        const ws = utils.json_to_sheet(lines);
        const wb = utils.book_new();
        utils.book_append_sheet(wb, ws, "Data");
        writeFileXLSX(wb,  'rapport_' + date.toLocaleTimeString() + ".xlsx");
    }

    public updateNodes() {
        this.nodes = [];
        this.getLeafNodes(this.root, this.nodes);
    }

    private getLeafNodes(node : RootNode, leafNodes : any[]) {

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
    private _checkUniqueAdding(node : RootNode) : Boolean {
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
    private getItems() {
        var items : any[] = [];
        for(var node of this.nodes) { 
            if(node.isActive() && node instanceof (<any>this.leafNode)){ 
                items.push(this._getProperty(node));
            }
        }
        return items;
    }

    // Get properties including properties on parents 
    private _getProperty(node : RootNode) : any[] {
        var objProperies : any = []          
        for(var activeProp of node.getActiveProperties()) {
            try {
                objProperies[activeProp.navn] = (<any>node)[activeProp.method]();
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
}

export default Excel;
