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
        var items = this.getItems();
        var data = [];
        var keys : any[] = [];

        for(var item of items) {
            if(keys.length < 1) {
                keys = Object.keys(item);
            }
            data.push((<any>Object).values(item));
        }

        // Grouping

        const pdf = new this.jsPDF();
        
        pdf.setFontSize(20); // Set the font size to 12 points
        pdf.text(this.repo.getRapportName(), 15, 20);

        // Create the table using the autoTable plugin
        pdf.autoTable({
            startY: 30,
            head: [keys], // Keys
            body: data, // array of data
        });
    
        // Save or open the PDF
        pdf.save('table.pdf');
    }
}



export default PDF;