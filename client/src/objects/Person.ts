import { ref } from 'vue';
import NodeObj from "./NodeObj";
import NodeProperty from './NodeProperty';
import type TableItemInterface from './table/TableInterface';


class Person extends NodeObj implements TableItemInterface {

    /* -- Static start -- */
    private static unique: Boolean = false;
    private static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Navn', true),
        new NodeProperty('getAlder', 'Alder', true),
    ];

    // Using for reactivity on Vue
    protected static staticRefs : any;
    public static getAllProperies() : NodeProperty[] {
        return Person.staticRefs.value.properties;
    }

    public getActiveProperties() : NodeProperty[] {
        var retArr = [];
        for(var p of Person.staticRefs.value.properties) {
            if(p.active) {
                retArr.push(p);
            }
        }
        return retArr;
    }

    public static getUnique() : Boolean {
        return Person.staticRefs.value.unique;
    }

    public static setUnique(boolVal : Boolean) {
        return Person.staticRefs.value.unique = boolVal;
    }

    public static getKeysForTable() : NodeProperty[] {
        // Making variables reactive on static
        Person.staticRefs = ref({
            unique : Person.unique,
            properties : Person.properties,
        });

        return Person.getAllProperies()

        // return retArr;
    }
    /* -- Static end -- */

    

    // Class attributes
    protected className = 'Person';
    private navn : string;
    private alder : number;

    constructor(id : string, navn : string, alder : number) {
        super(id);

        this.navn = navn;
        this.alder = alder;
    }


    public getNavn() : string {
        return this.navn;
    }

    public getAlder() : number {
        return this.alder;
    }

    // Returnerer data fra static
    public getKeysForTable() : NodeProperty[] {
        return Person.getKeysForTable();
    }
}

export default Person;
