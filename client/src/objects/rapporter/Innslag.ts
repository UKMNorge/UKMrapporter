import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Innslag extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    private static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Innslag navn', true),
        new NodeProperty('getType', 'Type'),
        new NodeProperty('getSesong', 'Sesong'),
    ];

    static className = 'Innslag';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Innslag.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Innslag);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Innslag);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Innslag);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Innslag);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Innslag, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Innslag);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Innslag';
    private navn : string;
    private type : string;
    private sesong : string;
    
    constructor(id : string, navn : string, type : string, sesong : string) {
        super(id);
        this.navn = navn;
        this.type = type;
        this.sesong = sesong;


        // Making variables reactive on static
        Innslag.staticRefs = ref({
            unique : Innslag.unique,
            properties : Innslag.properties,
        });
    }

    public getNavn() {
        return this.navn;
    }

    public getType() {
        return this.type;
    }

    public getSesong() {
        return this.sesong;
    }

    public getData() {

    }

}

export default Innslag;
