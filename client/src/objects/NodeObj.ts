import { ref } from 'vue';
// import NodeProperty from './NodeProperty';

abstract class NodeObj {
    protected id: string;
    protected className: string = '';

    // Pointer to the next array of Node
    private children : NodeObj[] = [];

    // Using for reactivity on Vue
    protected refs : any

    constructor(id: string) {
        this.id = id;

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

    public getUnique() : Boolean {
        return this.refs.value.unique;
    }

    public setUnique(boolVal : Boolean) {
        console.log('here');
        this.refs.value.unique = boolVal;
    }

}

export default NodeObj;