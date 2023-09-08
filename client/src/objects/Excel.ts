import RootNode from "../objects/RootNode";
import { read, utils, writeFileXLSX } from 'xlsx';
import NodeObj from './../objects/NodeObj';
import Repo from "./Repo";
import { toRaw } from 'vue';
import FileGenerator from "./FileGenerator";




class Excel extends FileGenerator {
    
    constructor(repo : Repo) {
        super(repo);        
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
        var count = 0;
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
            utils.book_append_sheet(wb, ws, (++count + '. ' + page[0]));
        }

        if(wb) {
            writeFileXLSX(wb,  'rapport_' + date.toLocaleTimeString() + ".xlsx");
        }
    }
}

export default Excel;
