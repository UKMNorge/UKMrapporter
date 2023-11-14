import { ref } from 'vue';
import NodeProperty from './NodeProperty';
import Subnode from './Subnode';
import SubnodeItem from './SubnodeItem';
// import NodeProperty from './NodeProperty';

abstract class NodeObj {
    protected id: string;
    public className: string = '';
    private active : Boolean = true;
    
    private ascSort = true;
    private sortPosition = -1;
    private sortActivated = false;
    private sortProperty : NodeProperty|null = null;
    
    protected subnodes : Subnode[];

    // Pointer to the next array of Node
    public children : NodeObj[] = [];
    public parent : NodeObj|null = null;

    // Using for reactivity on Vue
    protected refs : any;
    protected static staticRefs : any;


    constructor(id: string) {
        this.id = id;

        // Making reactive values outside static
        this.refs = ref({
            active : this.active,
            ascSort : this.ascSort,
            sortPosition : this.sortPosition,
            sortActivated : this.sortActivated,
            sortProperty : this.sortProperty,
        });

        this.subnodes = [];
    }

    public addSubnode(subnode : Subnode) {
        this.subnodes.push(subnode)
    }

    public getSubnodes() : Subnode[] {
        return this.subnodes;
    }

    public static getKeysForTable(subclass : any) : NodeProperty[] {
        subclass.staticRefs = ref({
            unique : subclass.unique,
            properties : subclass.properties,
            hasUnique : subclass.hasUnique,
        });

        return subclass.getAllProperies()
    }
    
    public setAscSort(ascSort : boolean) {
        this.refs.ascSort = ascSort;
    }

    public getAscSort() : boolean {
        return this.refs.ascSort != undefined ? this.refs.ascSort : this.refs.value.ascSort;
    }

    public setSortPosition(pos : number) {
        this.refs.sortPosition = pos;
    }

    public getSortPosition() : number {
        return this.refs.sortPosition != undefined ? this.refs.sortPosition : this.refs.value.sortPosition;
    }

    public setSortActivated(activated : boolean) {
        this.refs.sortActivated = activated;
    }

    public getSortActivated() : boolean {
        return this.refs.sortActivated != undefined ? this.refs.sortActivated : this.refs.value.sortActivated;
    }

    public setSortProperty(sortProp : NodeProperty) {
        this.refs.sortProperty = sortProp;
    }

    public getSortProperty() : NodeProperty {
        return this.refs.sortProperty != undefined ? this.refs.sortProperty : this.refs.value.sortProperty;
    }

    // Reset sorting for this node
    public resetSorting() {
        this.refs.ascSort = true;
        this.refs.sortPosition = -1;
        this.refs.sortActivated = false;
        this.refs.sortProperty = null;
    }

    public addChildren(children : NodeObj[]) {
        this.children = children;
    }

    public addChild(child : NodeObj) {
        this.children.push(child);
    }

    public getChildren() : NodeObj[] {
        return this.children;
    }

    public getRepresentativeName() : string {
        return this.className;
    }

    public setActive(val : Boolean) {
        this.refs.value.active = val;
    }

    public isActive() : Boolean {
        if(this.refs.value) {
            return this.refs.value.active;
        }
        return true;
    }

    public getId() : string {
        return this.id;
    }

    /* 
        This method can be implemented by subclasses to get the right representation of uniqueness for specific object
        Example: Person can have unique id as name+surname+mobil    
    */
    public getUniqueId() : string {
        return this.id;
    }

    public getNavn() : string {
        return this.getRepresentativeName();
    }

    public getAllProperies() {
        return NodeObj.getAllProperies(NodeObj);
    }

    public static getAllProperies(subclass : any) : NodeProperty[] {
        return subclass.staticRefs.value.properties;
    }

    public getActiveProperties() : NodeProperty[] { 
        return NodeObj.getActiveProperties(NodeObj);
    }

    public static getActiveProperties(subclass : any) : NodeProperty[] { 
        var retArr = [];
        for(var p of subclass.staticRefs.value.properties) {
            if(p.active) {
                retArr.push(p);
            }
        }
        return retArr;
    }

    public static getUnique(subclass : any) : Boolean {
        return subclass.staticRefs.value.unique;
    }

    public static setUnique(subclass : any, boolVal : Boolean) {
        return subclass.staticRefs.value.unique = boolVal;
    }

    public static usesUnique(subclass : any) : Boolean {
        return subclass.staticRefs.value.hasUnique;
    }
}

export default NodeObj;