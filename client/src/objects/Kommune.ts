import { ref } from 'vue';
import NodeObj from "./NodeObj";
import NodeProperty from './NodeProperty';


class Kommune extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    private static properties : NodeProperty[] = [
        new NodeProperty('navn', 'Kommune navn', true),
        new NodeProperty('fylke', 'Fylke'),
    ];    

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    public static getAllProperies() {
        return Kommune.staticRefs.value.properties;
    }

    public static getUnique() : Boolean {
        return Kommune.staticRefs.value.unique;
    }

    public static setUnique(boolVal : Boolean) {
        return Kommune.staticRefs.value.unique = boolVal;
    }
    /* -- Static end -- */


    
    
    // Class attributes
    protected className = 'Kommune';
    private navn : string;
    private fylkeNavn : string;
    
    constructor(id : string, navn : string, fylkeNavn : string) {
        super(id);
        this.navn = navn;
        this.fylkeNavn = fylkeNavn;


        // Making variables reactive on static
        Kommune.staticRefs = ref({
            unique : Kommune.unique,
            properties : Kommune.properties,
        });
    }

    public getData() {

    }

}

export default Kommune;
