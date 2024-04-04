import type SMS from "./interfaces/sms";

class EmailContact {
    private id : string;
    private epost: string;
    private navn: string;

    constructor(epost : string, navn : string) {
        this.id = epost;
        this.epost = epost;
        this.navn = navn;
    }

    public getId() {
        return this.id;
    }

    public hasEpost(): boolean {
        return this.epost !== null && this.epost !== '';
    }

    public getEpost() : string {
        return this.epost;
    }

    public getNavn() : string {
        return this.navn;
    }
}

export default EmailContact;
