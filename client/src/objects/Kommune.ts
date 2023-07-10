import { ref } from 'vue';
import NodeObj from "./NodeObj";
import NodeProperty from './NodeProperty';


class Kommune extends NodeObj {
    // Static context
    private static unique: Boolean = false;
    private static properties : NodeProperty[] = [
        new NodeProperty('navn', 'Kommune navn', true),
        new NodeProperty('fylke', 'Fylke'),
    ];
    protected className = 'Kommune';
    // Using for reactivity on Vue
    protected refs : any;


    // Class attributes
    private navn : string;
    private fylkeNavn : string;
    
    constructor(id : string, navn : string, fylkeNavn : string) {
        super(id);
        this.navn = navn;
        this.fylkeNavn = fylkeNavn;

        // Making variables reactive
        this.refs = ref({
            unique : Kommune.unique,
            properties : Kommune.properties,
        });
    }

    public getData() {

    }

}

export default Kommune;
