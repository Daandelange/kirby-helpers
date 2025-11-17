
// A simple mixin to throw into your vue2 components
// Allows over-riding a child method while still being able to call the original
// Example:
// myComponent : {
//     extends: 'nativeComponent', // Extends nativeComponent
//     mounted(){
//         // Switch the functions on mount
//         this.invertCustomAndNativeFunctions(['method']);
//         // this.method() = this.methodCustom()
//         // this.methodNative = nativeComponent.method();
//     },
//     methods: {
//         // User defined version of nativeComponent.method()
//         methodCustom(){
//             // do stuff
//             methodNative(); // call native
//             // do stuff
//         },
//     },
// }

import { usePanel } from "kirbyuse";

export function invertCustomAndNativeFunctions(funcNames){
    const panel = usePanel();
    for(const fn of funcNames){
        // Check if functions have been created correctly
        if( !this[fn] ){ // original doesn't exist !
            if(panel.debug){ 
                window.console.warn("Native function replacement hack: `"+fn+"` doesn't exist anymore. Please fix me.");
            }
            continue;
        }
        if( !this[fn + 'Custom'] ){ // Target
            if(panel){
                window.console.warn("Native function replacement hack: `"+fn+"Custom` doesn't exist. Please implement it !");
            }
            continue;
        }

        // if Native is set, this has already been bound
        if(this[fn + 'Native']) continue;

        // Bind Native to over-ridden fn
        this[fn + 'Native'] = this[fn];
        
        // Set original to custom
        this[fn] = this[fn + 'Custom'];
    }
}

// A mixin to inject the method
export const invertCustomAndNativeFunctionsMixin = {
    methods: {
        ...invertCustomAndNativeFunctions,
    }
}