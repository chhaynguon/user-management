import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import Aura from "@primeuix/themes/aura";
import PrimeVue from "primevue/config";
import ConfirmationService from "primevue/confirmationservice";
import ToastService from "primevue/toastservice";
import api from "./service/api";
import { createPinia } from "pinia";
import piniaPersist from "pinia-plugin-persistedstate";

const app = createApp(App);
const pinia = createPinia()

app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: ".app-dark",
        },
    },
});
pinia.use(piniaPersist);
app.use(pinia);
app.use(ToastService);
app.use(ConfirmationService);
app.config.globalProperties.$api = api;
app.mount("#app");
