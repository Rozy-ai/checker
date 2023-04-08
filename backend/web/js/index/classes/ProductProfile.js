import { Ajax } from "./Ajax.js";

export const CLASS_ELEMENT_PROFILE = '.product-list-item__profile';
export const CLASS_ELEMENT_PROFILE_LIST = '.product-list-item__profile-list';

export class ProductProfile {
    static URL = "/product/change-profile"

    /**
     * @type {string|number}
     */
    pid = null
    /**
     * @type {string|number}
     */
    source_id = null
    /**
     * 
     * @param {string|number} pid 
     * @param {string|number} source_id 
     * @returns {ProductProfile}
     */
    constructor( pid, source_id ) {
        this.pid = pid;
        this.source_id = source_id;
    }

    /**
     * @param {{value: string, callback: Function|null, from_list: Boolean}} options 
     */
    change( options = {} ) {
        const { value = '', callback = null } = options;

        if ( !value ) {
            return;
        }
        Ajax.send(
            ProductProfile.URL,
            {
                pid: this.pid,
                source_id: this.source_id,
                value,
            },
            callback || null,
        );
    }
}