class SubnodeItem {

    public key : String = '';
    public values : String[] = [];

    constructor(key : String, value : String[]) {
        this.key = key;
        this.values = value;
    }

    public getKey() {
        return this.key;
    }

    public getValues() : String|String[] {
        return this.values;
    }
}

export default SubnodeItem;