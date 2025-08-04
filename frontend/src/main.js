import App from "@/App.vue";
import { registerPlugins } from "@core/utils/plugins";
import axios from "axios";
import { createApp } from "vue";
import "vue-loading-overlay/dist/css/index.css";
import VueToast from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";

// Styles
import "@core/scss/template/index.scss";
import "@styles/styles.scss";

// Ambil token dari localStorage
const token = localStorage.getItem("token");
if (token) {
  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}
axios.defaults.baseURL = "http://127.0.0.1:8000/api";

// Create vue app
const app = createApp(App);

// Register plugins
registerPlugins(app);

//vue-toast-notification
app.use(VueToast);

// Mount vue app
app.mount("#app");
