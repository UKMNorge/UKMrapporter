class SubnodeStringItem {
    public value : String;
    public DOMClassName : string = '';

    constructor(value : string) {
        this.value = value;
    }

    public getValue() : String {
        return this.value;
    }
    
    public setDOMClass(DOMClassName : string) {
        this.DOMClassName = DOMClassName;
    }

    public getDOMClass() : string {
        return this.DOMClassName;
    }
}

export default SubnodeStringItem;