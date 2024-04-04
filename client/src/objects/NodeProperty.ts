class NodeProperty {
    public method : string;
    public navn : string;
    public active : Boolean = true;

    constructor(method : string, navn : string, active = false) {
        this.method = method;
        this.navn = navn;
        this.active = active;
    }
}

export default NodeProperty;
