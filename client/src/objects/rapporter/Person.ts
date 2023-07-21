import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';
import type TableItemInterface from './../table/TableInterface';


class Person extends NodeObj implements TableItemInterface {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    private static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Navn', true),
        new NodeProperty('getAlder', 'Alder', true),
        new NodeProperty('getMobil', 'Mobil', false),
        new NodeProperty('getEpost', 'Epost', false),
    ];

    // Using for reactivity on Vue
    protected static staticRefs : any;

    static className = 'Person';
    

    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Person.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Person);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Person);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Person);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Person);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Person, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Person);
    }
    /* -- Static end -- */

    

    // Class attributes
    public className = 'Person';
    private navn : string;
    private alder : number;
    private mobil : string;
    private epost : string;

    constructor(id : string, navn : string, alder : number, mobil : string, epost : string) {
        super(id);

        this.navn = navn;
        this.alder = alder;
        this.mobil = mobil;
        this.epost = epost;
    }


    public getNavn() : string {
        return this.navn;
    }

    public getAlder() : number {
        if(this.alder < 1) {
            return 0;
        }
        var alder = (<number>this.alder);
        var date = new Date(alder * 1000);

        return this.getYearDiff(date, new Date());
    }

    public getMobil() : string {
        return this.mobil;
    }

    public getEpost() : string {
        return this.epost;
    }

    // Returnerer data fra static
    public getKeysForTable() : NodeProperty[] {
        return Person.getKeysForTable();
    }

    private getYearDiff(date1 : Date, date2 : Date) : number{
      
        // Get the years of the input dates
        const year1 = date1.getFullYear();
        const year2 = date2.getFullYear();
      
        // Calculate the difference in years
        let yearDifference = year2 - year1;
      
        if (date2 < date1) {
            if (date2.getMonth() < date1.getMonth() || (date2.getMonth() === date1.getMonth() && date2.getDate() < date1.getDate())) {
            yearDifference--;
            }
        }
      
        return yearDifference;
      }
}

export default Person;
