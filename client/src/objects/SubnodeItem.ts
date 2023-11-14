class SubnodeItem {

    public key : String = '';
    public value : String = '';

    constructor(key : String, value : String) {
        this.key = key;
        this.value = value;
    }

    public getKey() {
        return this.key;
    }

    public getValue() {
        return this.value;
    }
}

export default SubnodeItem;