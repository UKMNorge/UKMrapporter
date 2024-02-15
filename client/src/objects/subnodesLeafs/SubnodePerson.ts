import SubnodeLeaf from "../SubnodeLeaf";
import type SMS from "../interfaces/sms";
import type Epost from "../interfaces/epost";

class SubnodePerson extends SubnodeLeaf implements SMS, Epost {
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

    public toString(): string {
        return this.fornavn + ' ' + 
        this.etternavn + 
        (this.hasMobil() ? ' - ' + this.getMobil() + '' : '') + 
        (this.hasEpost() ? ' (' + this.getEpost() + ')' : '') +
        ' - ' + this.value;
    }

}

export default SubnodePerson;