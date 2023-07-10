import { ref } from 'vue';
import NodeProperty from './NodeProperty';

class NodeObj {
    private id: number;
    private name: string;
    private unique: Boolean = false;
    private properties : NodeProperty[];

    // Using for reactivity on Vue
    private refs : any;

    constructor(id: number, name: string, properties : NodeProperty[]) {
        this.id = id;
        this.name = name;
        this.properties = properties;

        // Making variables reactive
        this.refs = ref({
            id : this.id,
            name : this.name,
            unique : this.unique,
            properties : this.properties,
        });


    }

    public getName() : string {
        return this.name;
    }

    public getId() : number {
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