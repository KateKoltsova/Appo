<script setup>
defineProps({
    editedUser: Object,
    isLoading: Boolean
});

const emit = defineEmits(['onSave']);
const onSave = () => {
    emit('onSave');
};

</script>

<template>
    <form @submit.prevent="onSave" :class="{ 'disabled': isLoading }">
        <div>
            <label for="firstname">FirstName:</label>
            <input type="text" id="firstname" v-model="editedUser.firstname"/>
        </div>

        <div>
            <label for="lastname">Lastname:</label>
            <input type="text" id="lastname" v-model="editedUser.lastname"/>
        </div>

        <div>
            <label for="birthdate">Birthdate:</label>
            <input type="date" id="birthdate" v-model="editedUser.birthdate"/>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" v-model="editedUser.email"/>
        </div>

        <div>
            <label for="phone_number">Phone number:</label>
            <input type="tel" id="phone_number" v-model="editedUser.phone_number"/>
        </div>

        <button type="submit" :disabled="isLoading">Save</button>
    </form>
</template>

<style scoped>
.disabled {
    opacity: 0.5;
    pointer-events: none;
}
</style>
