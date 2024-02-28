import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Hendelse extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    private static properties : NodeProperty[] = [
        new NodeProperty('getHendelseNavn', 'Navn', true)
    ];

    static className = 'Hendelse';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Hendelse.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Hendelse);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Hendelse);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Hendelse);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Hendelse);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Hendelse, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Hendelse);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Hendelse';
    private navn : string;
    
    constructor(id : string, navn : string) {
        super(id);
        this.navn = navn;

        // Making variables reactive on static
        Hendelse.staticRefs = ref({
            unique : Hendelse.unique,
            properties : Hendelse.properties,
        });
    }

    public getHendelseNavn() {
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

export default Hendelse;
