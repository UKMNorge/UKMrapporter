class SubnodeLeaf {
    protected value : String;
    protected DOMClassName : string = '';
    protected mobil : null|string = null;
    protected epost : null|string = null;

    constructor(value : string) {
        this.value = value;
    }

    public getValue() : String {
        return this.value;
    }

    public getNavn() : string {
        return '';
    }
    
    public setDOMClass(DOMClassName : string) {
        this.DOMClassName = DOMClassName;
    }

    public getDOMClass() : string {
        return this.DOMClassName;
    }

    public toString() {
        return this.value;
    }

    public hasMobil() : boolean {
        return this.mobil !== null && this.mobil !== '';
    }

    public setMobil(mobil : string) {
        this.mobil = mobil;
    }

    public getMobil() : string|null {
        return this.mobil;
    }

    public hasEpost() : boolean {
        return this.epost !== null && this.epost !== '';
    }

    public setEpost(epost : string) {
        this.epost = epost;
    }

    public getEpost() : string|null {
        return this.epost;
    }
}

export default SubnodeLeaf;