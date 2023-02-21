'use strict';

export class DomWithData {
    dom;
    data;
    
    constructor($dom, data) {
        this.dom  = $dom;
        if (typeof(data) !== "undefined" && data !== null){
            this.data = data;
        } else {
            let data = this.dom.data();
            this.data = data;
        }
    }
    
    static getFromChild(name_class, $child_object, data){
        let $obj = new this($child_object.parents(name_class), data);
        return $obj;
    }
    
    static getFirstFromParent(name_class, $parent_object){
        return new this($parent_object.find(name_class+':first'));
    }
}