import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';
import type Epost from '../interfaces/epost';
import type SMS from '../interfaces/epost';


class Nominasjon extends NodeObj implements SMS, Epost {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    private static properties : NodeProperty[] = [
        new NodeProperty('getNominasjonNavn', 'Navn', true),
        new NodeProperty('getMobil', 'Mobil', false),
        new NodeProperty('getEpost', 'Epost', false),
        new NodeProperty('harVoksenskjema', 'Voksenskjema', true),
        new NodeProperty('harDeltakerskjema', 'Deltakerskjema', true),
        new NodeProperty('erVideresendt', 'Videresendt', true),
        new NodeProperty('getStatus', 'Status', true),
    ];

    static className = 'Nominasjon';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Nominasjon.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Nominasjon);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Nominasjon);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Nominasjon);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Nominasjon);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Nominasjon, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Nominasjon);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Nominasjon';
    private navn : string;
    private voksenskjema : boolean;
    private deltakerskjema : boolean;
    private videresendt : boolean;
    private status : string;
    private mobil : string | number;
    private epost : string;
    
    constructor(id : string, navn : string, voksenskjema : boolean, deltakerskjema : boolean, videresendt : boolean, status : string, mobil : string, epost : string) {
        super(id);
        this.navn = navn;
        this.voksenskjema = voksenskjema;
        this.deltakerskjema = deltakerskjema;
        this.videresendt = videresendt;
        this.status = status;
        this.mobil = mobil;
        this.epost = epost;

        // Making variables reactive on static
        Nominasjon.staticRefs = ref({
            unique : Nominasjon.unique,
            properties : Nominasjon.properties,
        });
    }

    public getNominasjonNavn() {
        return this.navn;
    }

    public getNavn() : string {
        return this.navn;
    }

    public harVoksenskjema() : boolean {
        return this.voksenskjema;
    }

    public harDeltakerskjema() : boolean {
        return this.deltakerskjema;
    }

    public erVideresendt() : boolean {
        return this.videresendt;
    }

    public getStatus() : string {
        return this.status;
    }

    public hasMobil(): boolean {
        return this.mobil !== null && this.mobil !== '';
    }
    public getMobil() : string|number {
        return this.mobil;
    }

    public hasEpost(): boolean {
        return this.epost !== null && this.epost !== '';
    }
    public getEpost() : string {
        return this.epost;
    }

    /**
     * @override
     */
    public getUniqueId() : string {
        return this.navn;
    }

    public getData() {

    }

}

export default Nominasjon;
