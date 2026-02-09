import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Fylke extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    private static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Fylke navn', true),
    ];

    static className = 'Fylke';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Fylke.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Fylke);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Fylke);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Fylke);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Fylke);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Fylke, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Fylke);
    }
    public static setProperties(props: NodeProperty[]) {
        Fylke.properties = props;

        // Oppdater reaktiv referanse hvis den finnes
        if (Fylke.staticRefs?.value) {
            Fylke.staticRefs.value.properties = props;
        }
    }

    public static getProperties(): NodeProperty[] {
        return Fylke.properties;
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Fylke';
    private navn : string;
    
    constructor(id : string, navn : string) {
        super(id);
        this.navn = navn;


        // Making variables reactive on static
        Fylke.staticRefs = ref({
            unique : Fylke.unique,
            properties : Fylke.properties,
        });
    }

    public getNavn() {
        return this.navn;
    }

    public getData() {

    }

}

export default Fylke;
