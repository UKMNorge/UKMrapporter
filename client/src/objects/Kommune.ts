import { ref } from 'vue';
import NodeObj from "./NodeObj";
import NodeProperty from './NodeProperty';


class Kommune extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    private static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Kommune navn', true),
        new NodeProperty('getFylkeNavn', 'Fylke', true),
    ];    

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    public static getAllProperies() {
        return Kommune.staticRefs.value.properties;
    }

    public getActiveProperties() : NodeProperty[] {
        var retArr = [];
        for(var p of Kommune.staticRefs.value.properties) {
            if(p.active) {
                retArr.push(p);
            }
        }
        return retArr;
    }

    public static getUnique() : Boolean {
        return Kommune.staticRefs.value.unique;
    }

    public static setUnique(boolVal : Boolean) {
        return Kommune.staticRefs.value.unique = boolVal;
    }

    public static getKeysForTable() : NodeProperty[] {
        // Making variables reactive on static
        Kommune.staticRefs = ref({
            unique : Kommune.unique,
            properties : Kommune.properties,
        });

        return Kommune.getAllProperies()

        // return retArr;
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
