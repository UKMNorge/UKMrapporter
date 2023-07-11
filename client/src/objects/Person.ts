import { ref } from 'vue';
import NodeObj from "./NodeObj";
import NodeProperty from './NodeProperty';
import type TableItemInterface from './table/TableInterface';


class Person extends NodeObj implements TableItemInterface {
    // Static context
    private static unique: Boolean = false;
    private static properties : NodeProperty[] = [
        new NodeProperty('navn', 'Navn', true),
        new NodeProperty('alder', 'Alder', true),
    ];
    protected className = 'Person';
    // Using for reactivity on Vue
    protected refs : any;


    // Class attributes
    private navn : string;
    private alder : number;

    constructor(id : string, navn : string, alder : number) {
        super(id);

        this.navn = navn;
        this.alder = alder;

        // Making variables reactive
        this.refs = ref({
            unique : Person.unique,
            properties : Person.properties,
        });
    }

    public getNavn() {
        return this.navn;
    }

    public static getKeysForTable() : {navn : string, method : string}[] {
        return [
            {navn : 'navn', method :'getNavn'},
        ];
    }

    public getKeysForTable() : {navn : string, method : string}[] {
        return [
            {navn : 'navn', method :'getNavn'},
        ];
    }
}

export default Person;
