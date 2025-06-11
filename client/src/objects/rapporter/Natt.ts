import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Natt extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    public static properties : NodeProperty[] = [
        new NodeProperty('getNattNavn', 'Natt navn', true)
    ];

    static className = 'Natt';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Natt.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Natt);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Natt);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Natt);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Natt);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Natt, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Natt);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Natt';
    private navn : string;
    
    constructor(id : string, navn : string) {
        super(id);
        this.navn = navn;

        // Making variables reactive on static
        Natt.staticRefs = ref({
            unique : Natt.unique,
            properties : Natt.properties,
        });
    }

    public getNattNavn() {
        return this.navn;
    }

    public getNavn() : string {
        return this.navn;
    }

    /**
     * @override
     */
    public getUniqueId() : string {
        return this.navn;
    }

    public getData() {

    }

}

export default Natt;
