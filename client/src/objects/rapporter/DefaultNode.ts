import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';

/*
DefaultNode uses for nodes that do not have clear definition but the use of a node is necessary.
An example is when grouping is needed and a node between 2 nodes on the tree must be used.
*/


class DefaultNode extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    public static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Navn', false),
    ];

    static className = 'DefaultNode';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return DefaultNode.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(DefaultNode);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(DefaultNode);
    }
    public static getUnique() : Boolean {
        return super.getUnique(DefaultNode);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(DefaultNode);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(DefaultNode, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(DefaultNode);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'DefaultNode';
    private navn : string;
    
    constructor(id : string, navn : string) {
        super(id);
        this.navn = navn;


        // Making variables reactive on static
        DefaultNode.staticRefs = ref({
            unique : DefaultNode.unique,
            properties : DefaultNode.properties,
        });
    }

    public setClassName(name : string) {
        DefaultNode.className = name;
        this.className = name;
    }

    public getNavn() {
        return this.navn;
    }
    
    public getData() {

    }

}

export default DefaultNode;
