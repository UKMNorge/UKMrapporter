import SubnodeStringItem from './SubnodeStringItem';

class SubnodeItem {

    public key : String = '';
    public values : SubnodeStringItem[] = [];
    private className : string = '';

    constructor(key : String, value : SubnodeStringItem[]) {
        this.key = key;
        this.values = value;
    }

    public getKey() {
        return this.key;
    }

    public getValues() : SubnodeStringItem[] {
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