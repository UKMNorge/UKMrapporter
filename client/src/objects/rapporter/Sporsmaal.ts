import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Sporsmaal extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    public static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Spørsmål', false),
        new NodeProperty('getType', 'Type', false),
    ];

    static className = 'Sporsmaal';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Sporsmaal.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Sporsmaal);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Sporsmaal);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Sporsmaal);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Sporsmaal);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Sporsmaal, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Sporsmaal);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Sporsmaal';
    private navn : string;
    private type : string;
    
    constructor(id : string, navn : string, type : string) {
        super(id);
        this.navn = navn;
        this.type = type;


        // Making variables reactive on static
        Sporsmaal.staticRefs = ref({
            unique : Sporsmaal.unique,
            properties : Sporsmaal.properties,
        });
    }

    public getNavn() {
        return this.navn;
    }
    
    public getType() {
        return this.type;
    }

    public getData() {

    }

}

export default Sporsmaal;
