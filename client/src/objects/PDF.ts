import RootNode from "../objects/RootNode";
import NodeObj from './../objects/NodeObj';
import FileGenerator from './FileGenerator';
import Repo from "./Repo";



class PDF extends FileGenerator {
    private jsPDF = (<any>window).jspdf.jsPDF; // Used from ukmrapporter.php <script>

    constructor(repo : Repo) {
        super(repo);
    }

    public generateFile() {
        this.updateNodes();
        var data = [];
        var keys : any[] = [];

        var grupperingNode = this.repo.getGroupingNode();
        var grupperingNodes : NodeObj[] = [];
        this.getAllNodesAtLevel(this.root, grupperingNodes, grupperingNode);

        var allItems : any[] = [];
        // Grouping is used
        if(grupperingNodes.length > 0) {
            var originalRoot = this.root;

            for (var node of grupperingNodes) {
                this.root = node;
                this.updateNodes();
                allItems.push([node.getNavn(), this.getItems()]);
            }

            this.root = originalRoot;
        }
        else {
            this.updateNodes();
            allItems.push([this.repo.getRapportName(), this.getItems()]);
        }

        
        // Grouping
        const pdf = new this.jsPDF();

        var allItems = allItems.filter((a : any) => a[1].length > 0)
        var count = 0;
        for(var item of allItems) {
            var data : any[] = [];
            pdf.setFontSize(20); // Set the font size to 12 points
            pdf.text(item[0], 15, 20);

            for(var innerItem of item[1]) {
                if(keys.length < 1) {
                    keys = Object.keys(innerItem);
                }
                data.push((<any>Object).values(innerItem));
            }
            
            // Create the table using the autoTable plugin
            pdf.autoTable({
                startY: 30,
                head: [keys], // Keys
                body: data, // array of data
            });

            if(++count < allItems.length) {
                pdf.addPage(); // Add a new page for the next table
            }
        }
        
    
        // Save or open the PDF
        pdf.save(this.repo.getRapportName().toLowerCase() + '.pdf');
    }
}



export default PDF;