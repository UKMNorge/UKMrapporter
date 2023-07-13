import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Kommune extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;
    private static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Kommune navn', true),
        new NodeProperty('getFylkeNavn', 'Fylke'),
    ];    

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Kommune.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Kommune);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Kommune);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Kommune);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Kommune);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Kommune, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Kommune);
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

    public getNavn() {
        return this.navn;
    }

    public getFylkeNavn() {
        return this.fylkeNavn;
    }

    public getData() {

    }

}

export default Kommune;
