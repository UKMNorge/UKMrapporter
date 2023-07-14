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
    protected className = 'Person';
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
        return this.alder;
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
}

export default Person;
