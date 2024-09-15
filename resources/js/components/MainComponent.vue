<script setup>
import {useRouter} from 'vue-router';
import HeaderComponent from './HeaderComponent.vue';
import FooterComponent from './FooterComponent.vue';

const router = useRouter();

</script>

<template>
    <div class="main-page">
        <header>
            <HeaderComponent/>
        </header>
        <div class="content">
            <div>
                <h1>Hello from Vue Component!</h1>
            </div>
            <router-view/>
        </div>
        <footer>
            <FooterComponent/>
        </footer>
    </div>
</template>

<style scoped>
.main-page {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.content {
    flex-grow: 1;
    padding: 20px;
}
</style>
