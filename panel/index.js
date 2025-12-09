import { usePanel  } from "kirbyuse";
const panel = usePanel();

// For now we only provide modules which you can import.
// Register the plugin name (empty) anyways.

panel.plugin("daandelange/helpers", {
    use: {
        // See https://github.com/getkirby/kirby/issues/4796#issuecomment-1285304129
        // Helps vue devtools detecting Fiber's Vue instance
        plugin(Vue) {
            // Check for VueDevtools Browser plugin
            if (panel.debug && window.__VUE_DEVTOOLS_GLOBAL_HOOK__) {
                window.__VUE_DEVTOOLS_GLOBAL_HOOK__.Vue = Vue;
            }
        },
    },
});