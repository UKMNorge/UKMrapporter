import { ref } from 'vue';
import NodeObj from './NodeObj';
import SubnodeItem from './SubnodeItem';

class Subnode {
    // protected parent: NodeObj;
    private items: SubnodeItem[];

    constructor() {
        // this.parent = parent;
        this.items = [];
    }

    public addItem(item : SubnodeItem) {
        this.items.push(item);
    }
    
    public getItems() {
        return this.items;
    }
    
}

export default Subnode;
