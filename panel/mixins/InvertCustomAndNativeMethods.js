
// A simple mixin to throw into your vue2 components
// Allows over-riding a child method while still being able to call the original
// Example:
// myComponent : {
//     extends: 'nativeComponent', // Extends nativeComponent
//     mounted(){
//         // Switch the functions on mount
//         this.invertCustomAndNativeFunctions(['method']);
//         // // Line above does exactly this :
//         // this.method() = this.methodCustom()
//         // this.methodNative = nativeComponent.method();
//     },
//     methods: {
//         // User defined version of nativeComponent.method()
//         methodCustom(){
//             // do stuff before
//             methodNative(); // call native
//             // do stuff after
//         },
//     },
// }

import { usePanel } from "kirbyuse";

// Param 1: array of function names
// Returns true if all provided functions have been bound, false otherwise.
export function invertCustomAndNativeFunctions(funcNames){
    const panel = usePanel();
    let result = true;
    // Kirby-style string to array
    if(typeof funcNames === 'string'){
        funcNames=[funcNames];
    }
    // Loop args = methods to bind
    for(const fn of funcNames){
        // Check if functions have been created correctly
        if( !this[fn] ){ // original doesn't exist !
            if(panel.debug){ 
                window.console.warn("Native function replacement hack: `"+fn+"` doesn't exist anymore. Please fix me.");
            }
            result &= false;
            continue;
        }
        if( !this[fn + 'Custom'] ){ // Target
            if(panel.debug){
                window.console.warn("Native function replacement hack: `"+fn+"Custom` doesn't exist. Please implement it !");
            }
            result &= false;
            continue;
        }

        // if Native is set, this has already been bound
        if(this[fn + 'Native']) continue;

        // Bind Native to native fn
        this[fn + 'Native'] = this[fn];
        
        // Set original to custom fn
        this[fn] = this[fn + 'Custom'];
    }
    return result;
}

// A mixin to inject the method
export const invertCustomAndNativeFunctionsMixin = {
    methods: {
        ...invertCustomAndNativeFunctions,
    }
}