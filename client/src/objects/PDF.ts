import NodeObj from './../objects/NodeObj';
import Repo from "./Repo";


class PDF {
    private jsPDF = (<any>window).jspdf.jsPDF; // Used from ukmrapporter.php <script>

    constructor(repo : Repo) {
        
    }

    public generateFile() {
        const pdf = new this.jsPDF();
    
        // Define the data for the table
        const data = [
          ['Name', 'Age', 'Country'],
          ['John Doe', '30', 'USA'],
          ['Alice Smith', '25', 'Canada'],
          ['Bob Johnson', '35', 'UK'],
        ];
    
        // Create the table using the autoTable plugin
        pdf.autoTable({
          head: [data[0]], // Display the first row as the table headers
          body: data.slice(1), // Display the remaining rows as table data
        });
    
        // Save or open the PDF
        pdf.save('table.pdf');
      }
}



export default PDF;