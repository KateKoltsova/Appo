import './bootstrap';
import {createApp} from 'vue';
import MainComponent from './components/MainComponent.vue';
import router from './router/index.js';

const app = createApp(MainComponent);

app.use(router);

app.mount('#app');
