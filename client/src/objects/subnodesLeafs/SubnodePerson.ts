import SubnodeLeaf from "../SubnodeLeaf";

class SubnodePerson extends SubnodeLeaf {
    protected fornavn: string;
    protected etternavn: string;

    // value inneholder teksten som skal vises i tabellen andre attributter
    constructor(value : string, fornavn : string, etternavn : string, mobil : string, epost : string) {
        super(value);
        this.fornavn = fornavn;
        this.etternavn = etternavn;
        this.mobil = mobil;
        this.epost = epost;
    }

    public getFornavn() {
        return this.fornavn;
    }

    public getEtternavn() {
        return this.etternavn;
    }

    public toString(): String {
        return this.fornavn + ' ' + 
        this.etternavn + 
        (this.hasMobil() ? ' - ' + this.getMobil() + '' : '') + 
        (this.hasEpost() ? ' (' + this.getEpost() + ')' : '') +
        ' - ' + this.value;
    }

}

export default SubnodePerson;