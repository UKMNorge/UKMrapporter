import { ref } from 'vue';
import NodeObj from "./NodeObj";
import NodeProperty from './NodeProperty';


class Person extends NodeObj {
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
}

export default Person;
