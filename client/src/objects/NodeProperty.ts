class NodeProperty {
    public key : string;
    public name : string;
    public active : Boolean = true;

    constructor(key : string, name : string, active = false) {
        this.key = key;
        this.name = name;
        this.active = active;
    }
}

export default NodeProperty;
