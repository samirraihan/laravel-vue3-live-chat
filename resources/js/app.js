import './bootstrap';
import { createApp } from 'vue';
import ChatApp from './components/ChatApp.vue';

const app = createApp({
    components: {
        ChatApp
    }
});

app.mount('#app');
