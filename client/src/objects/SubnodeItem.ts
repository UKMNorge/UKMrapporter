import SubnodeLeaf from './SubnodeLeaf';

class SubnodeItem {

    public key : String = '';
    public values : SubnodeLeaf[] = [];
    private className : string = '';

    constructor(key : String, value : SubnodeLeaf[]) {
        this.key = key;
        this.values = value;
    }

    public getKey() {
        return this.key;
    }

    public getValues() : SubnodeLeaf[] {
        return this.values;
    }
    
    public addDOMClass(className : string) {
        this.className = className;
    }

    public getDOMClass() : string {
        return this.className;
    }
}

export default SubnodeItem;