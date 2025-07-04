import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';
import type TableItemInterface from './../table/TableInterface';
import type SMS from '../interfaces/sms';
import type Epost from '../interfaces/epost';


class Leder extends NodeObj implements TableItemInterface, SMS, Epost {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    public static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Navn', true),
        new NodeProperty('getType', 'Type', true),
        new NodeProperty('getGodkjent', 'Godkjent', false),
        new NodeProperty('getMobil', 'Mobil', false),
        new NodeProperty('getEpost', 'Epost', false),
        new NodeProperty('getFylke', 'Leder Fylke', false),
    ];

    // Using for reactivity on Vue
    protected static staticRefs : any;

    static className = 'Leder';
    

    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Leder.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Leder);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Leder);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Leder);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Leder);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Leder, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Leder);
    }
    /* -- Static end -- */

    

    // Class attributes
    public className = 'Leder';
    private navn : string;
    private type : string;
    private mobil : string;
    private epost : string;
    private fylke : string;
    private erGodkjent : boolean;
    private sted : boolean|string = '';

    constructor(id : string, navn : string, type : string, mobil : string, epost : string, fylke? : string, erGodkjent? : boolean) {
        super(id);

        this.navn = navn;
        this.type = type;
        this.mobil = mobil;
        this.epost = epost;
        this.fylke = fylke ? fylke : '';
        this.erGodkjent = erGodkjent != undefined ? erGodkjent : false;
    }


    public getNavn() : string {
        return this.navn;
    }

    public getType() : string {
        return this.type;
    }

    public hasMobil() : boolean {
        return this.mobil !== null && this.mobil !== '';
    }
    
    public getMobil() : string {
        return this.mobil;
    }

    public hasEpost() : boolean {
        return this.epost !== null && this.epost !== '';
    }

    public getEpost() : string {
        return this.epost;
    }

    public getFylke() : string {
        return this.fylke;
    }

    public getGodkjent() : boolean {
        return this.erGodkjent;
    }

    public setSted(sted: boolean|string) : void {
        this.sted = sted;
    }

    public getSted() : boolean|string {
        return this.sted;
    }

    // Returnerer data fra static
    public getKeysForTable() : NodeProperty[] {
        return Leder.getKeysForTable();
    }

}

export default Leder;
