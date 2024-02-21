import type SMS from "./interfaces/sms";

class MobilContact {
    private id : string;
    private mobil: string|number;
    private navn: string;

    constructor(mobil : string|number, navn : string) {
        this.id = String(mobil);
        this.mobil = mobil;
        this.navn = navn;
    }

    public getId() {
        return this.id;
    }

    public hasMobil(): boolean {
        return this.mobil !== null && this.mobil !== '';
    }

    public getMobil() : string|number {
        return this.mobil;
    }

    public getNavn() : string {
        return this.navn;
    }
}

export default MobilContact;
