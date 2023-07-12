import { ref } from 'vue';
import NodeProperty from './NodeProperty';
// import NodeProperty from './NodeProperty';

abstract class NodeObj {
    protected id: string;
    protected className: string = '';

    // Pointer to the next array of Node
    public children : NodeObj[] = [];
    public parent : NodeObj|null = null;

    // Using for reactivity on Vue
    protected refs : any;
    protected static staticRefs : any;

    constructor(id: string) {
        this.id = id;
    }

    public getActiveProperties() : NodeProperty[] {
        var retArr = [];
        for(var p of NodeObj.staticRefs.value.properties) {
            if(p.active) {
                retArr.push(p);
            }
        }
        return retArr;
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
        return this.refs.value.properties;
    }

    public static getAllProperies() {
        return NodeObj.staticRefs.value.properties;
    }

    public static getUnique() : Boolean {
        return NodeObj.staticRefs.value.unique;
    }

    public static setUnique(boolVal : Boolean) {
        return NodeObj.staticRefs.value.unique = boolVal;
    }

    public static usesUnique() : Boolean {
        return NodeObj.staticRefs.value.hasUnique;
    }
}

export default NodeObj;