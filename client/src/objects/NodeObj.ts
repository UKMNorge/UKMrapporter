import { ref } from 'vue';
import NodeProperty from './NodeProperty';
// import NodeProperty from './NodeProperty';

abstract class NodeObj {
    protected id: string;
    protected className: string = '';

    private static subclass : any = null;

    // Pointer to the next array of Node
    public children : NodeObj[] = [];
    public parent : NodeObj|null = null;

    // Using for reactivity on Vue
    protected refs : any;
    protected static staticRefs : any;

    constructor(id: string) {
        this.id = id;
        NodeObj.subclass = this.constructor;

    }

    public static getKeysForTable(subclass : any) : NodeProperty[] {
        subclass.staticRefs = ref({
            unique : subclass.unique,
            properties : subclass.properties,
            hasUnique : subclass.hasUnique,
        });

        return subclass.getAllProperies()
    }

    public addChildren(children : NodeObj[]) {
        this.children = children;
    }

    public getChildren() : NodeObj[] {
        return this.children;
    }

    public getRepresentativeName() : string {
        return this.className;
    }

    public getId() : string {
        return this.id;
    }

    public getAllProperies() {
        return NodeObj.getAllProperies(NodeObj);
    }

    public static getAllProperies(subclass : any) : NodeProperty[] {
        return subclass.staticRefs.value.properties;
    }

    public getActiveProperties() : NodeProperty[] { 
        return NodeObj.getActiveProperties(NodeObj);
    }

    public static getActiveProperties(subclass : any) : NodeProperty[] { 
        var retArr = [];
        for(var p of subclass.staticRefs.value.properties) {
            if(p.active) {
                retArr.push(p);
            }
        }
        return retArr;
    }

    public static getUnique(subclass : any) : Boolean {
        return subclass.staticRefs.value.unique;
    }

    public static setUnique(subclass : any, boolVal : Boolean) {
        return subclass.staticRefs.value.unique = boolVal;
    }

    public static usesUnique(subclass : any) : Boolean {
        return subclass.staticRefs.value.hasUnique;
    }
}

export default NodeObj;