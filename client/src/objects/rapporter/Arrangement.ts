import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Arrangement extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    public static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Arrangement navn', true),
        new NodeProperty('getType', 'Type', false),
        new NodeProperty('getSted', 'Sted', false),
    ];

    static className = 'Arrangement';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Arrangement.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Arrangement);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Arrangement);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Arrangement);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Arrangement);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Arrangement, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Arrangement);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Arrangement';
    private navn : string;
    private type : string;
    private sted : string;
    
    constructor(id : string, navn : string, type : string, sted : string ) {
        super(id);
        this.navn = navn;
        this.type = type;
        this.sted = sted;


        // Making variables reactive on static
        Arrangement.staticRefs = ref({
            unique : Arrangement.unique,
            properties : Arrangement.properties,
        });
    }

    public getNavn() {
        return this.navn;
    }
    
    public getType() {
        return this.type;
    }
    
    public getSted() {
        return this.sted;
    }

    public getData() {

    }

}

export default Arrangement;
