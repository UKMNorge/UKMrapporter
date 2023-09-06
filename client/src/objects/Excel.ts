import RootNode from "../objects/RootNode";
import { read, utils, writeFileXLSX } from 'xlsx';
import NodeObj from './../objects/NodeObj';
import Repo from "./Repo";
import { toRaw } from 'vue';




class Excel {
    private nodes : NodeObj[] = [];
    private root : NodeObj;
    private leafNode : NodeObj;
    private repo : Repo;

    constructor(repo : Repo) {
        this.root = repo.root;
        this.leafNode = repo.leafNode;
        this.repo = repo;
    }

    public generateFile() {
        var grupperingNode = this.repo.getGroupingNode();
        var grupperingNodes : NodeObj[] = [];
        this.getAllNodesAtLevel(this.root, grupperingNodes, grupperingNode);

        // Grouping is used
        if(grupperingNodes.length > 0) {
            var originalRoot = this.root;
            var allLines = [];
            for (var node of grupperingNodes) {
                this.root = node;
                this.updateNodes();
                allLines.push([node.getNavn(), this.getItems()]);

            }
            this.downloadFile(allLines);
            this.root = originalRoot;
        }
        else {
            this.updateNodes();
            var lines = this.getItems();
            this.downloadFile([['Side 1', lines]]);
        }
    }

    private downloadFile(pages : any[]) {
        // HUSK: det burkes kun leafNode for hente unike informasjon om unike er aktivert
        var isLeafUnique = (<any>this.leafNode).getUnique();
        var date = new Date();

        // Gruppering
        var wb = utils.book_new();
        for(var page of pages) {
            var lines = page[1];
            // Antall inkludering unike
            if(this.repo.antall.value == true) {
                var antall = lines.length;
                var antallKey = isLeafUnique ? 'Antall unike' : 'Antall';
                var antalObj : any = {};
                antalObj[antallKey] = antall;
    
                lines.push(antalObj);
            }
            
            const ws = utils.json_to_sheet(lines);
            utils.book_append_sheet(wb, ws, page[0]);
        }

        if(wb) {
            writeFileXLSX(wb,  'rapport_' + date.toLocaleTimeString() + ".xlsx");
        }
    }

    public updateNodes() {
        this.nodes = [];
        this.getLeafNodes(this.root, this.nodes);
    }

    private getLeafNodes(node : NodeObj, leafNodes : any[]) {

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
    private getItems() {
        var items : any[] = [];
        var count = 0;
        for(var node of this.nodes) { 
            if(node.isActive() && node instanceof (<any>this.leafNode)){ 
                items.push(this._getProperty(node, count++));
            }
        }
        return items;
    }

    // Get properties including properties on parents 
    private _getProperty(node : RootNode, count : number) : any[] {
        var objProperies : any = [];

        if(this.repo.telling.value == true) {
            objProperies['#'] = ++count;
        }

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

export default Excel;
