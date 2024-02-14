import { ref } from 'vue';
import NodeObj from './../NodeObj';
import NodeProperty from './../NodeProperty';


class Innslag extends NodeObj {

    /* -- Static start -- */
    private static unique: Boolean = false;
    public static hasUnique : Boolean = false;

    public static properties : NodeProperty[] = [
        new NodeProperty('getNavn', 'Innslag navn', true),
        new NodeProperty('getType', 'Type'),
        new NodeProperty('getSesong', 'Sesong'),
    ];

    static className = 'Innslag';

    // Using for reactivity on Vue
    protected static staticRefs : any;
    
    // Mutual methods
    public getActiveProperties() : NodeProperty[] {
        return Innslag.getActiveProperties();
    }

    public static getAllProperies() : NodeProperty[] {
        return super.getAllProperies(Innslag);
    }
    
    public static getActiveProperties() : NodeProperty[] {
        return super.getActiveProperties(Innslag);
    }
    public static getUnique() : Boolean {
        return super.getUnique(Innslag);
    }

    public static usesUnique() : Boolean {
        return super.usesUnique(Innslag);
    }

    public static setUnique(boolVal : Boolean) {
        return super.setUnique(Innslag, boolVal);
    }

    public static getKeysForTable() : NodeProperty[] {
        return super.getKeysForTable(Innslag);
    }
    /* -- Static end -- */


    
    
    // Class attributes
    public className = 'Innslag';
    private navn : string;
    private type : string;
    private sesong : string;

    // Kan aktiveres
    private sjanger : string = '';
    private varighet : string = '';
    private fylke : string = '';
    private kommune : string = '';
    private beskrivelse : string = '';
    private rolle : string = '';
    private tid : string = '';

    
    constructor(id : string, navn : string, type : string, sesong : string) {
        super(id);
        this.navn = navn;
        this.type = type;
        this.sesong = sesong;


        // Making variables reactive on static
        Innslag.staticRefs = ref({
            unique : Innslag.unique,
            properties : Innslag.properties,
        });
    }

    public getNavn() {
        return this.navn;
    }

    public getType() {
        return this.type;
    }

    public getSesong() {
        return this.sesong;
    }

    public getVarighet() {
        return this.varighet;
    }

    public setVarighet(varighet : string) {
        this.varighet = varighet;
    }

    public getSjanger() {
        return this.sjanger;
    }

    public setSjanger(sjanger : string) {
        this.sjanger = sjanger;
    }

    public getFylke() {
        return this.fylke;
    }

    public setFylke(fylke : string) {
        this.fylke = fylke;
    }

    public getKommune() {
        return this.kommune;
    }

    public setKommune(kommune : string) {
        this.kommune = kommune;
    }

    public getBeskrivelse() {
        return this.beskrivelse;
    }

    public setBeskrivelse(beskrivelse : string) {
        this.beskrivelse = beskrivelse;
    }

    public getRolle() {
        return this.rolle;
    }

    public setRolle(rolle : string) {
        this.rolle = rolle;
    }

    public setTid(tid : string) {
        this.tid = tid;
    }

    public getData() {

    }

}

export default Innslag;
