<script setup>
import {ref} from 'vue';
import {useRouter} from 'vue-router';
import {register} from "../services/UserService.js";

const router = useRouter();

const firstname = ref('');
const lastname = ref('');
const birthdate = ref('');
const email = ref('');
const phone_number = ref('');
const password = ref('');
const confirmPassword = ref('');
const errors = ref({});
const isSubmitting = ref(false);
const isLoading = ref(false);

const handleSubmit = async () => {
    errors.value = {};

    if (!validateForm()) {
        return;
    }

    isSubmitting.value = true;

    const formData = {
        firstname: firstname.value,
        lastname: lastname.value,
        birthdate: birthdate.value,
        email: email.value,
        phone_number: phone_number.value,
        password: password.value,
    };

    try {
        isLoading.value = true;
        const response = await register(formData);
        if (response.status === 200) {
            router.push({ name: 'login' });
        } else {
            console.error('Ошибки при регистрации пользователя');
            if (response.data.errors) {
                errors.value = response.data.errors;
            }
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
    } finally {
        isSubmitting.value = false;
        isLoading.value = false;
    }
};

// Валидация на фронтенде
const validateForm = () => {
  let valid = true;
  // Валидация имени
  if (!firstname.value) {
    errors.value.firstname = ['Имя обязательно'];
    valid = false;
  }
  // Валидация фамилии
  if (!lastname.value) {
    errors.value.lastname = ['Фамилия обязательна'];
    valid = false;
  }
  // Валидация email
  const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!email.value || !emailPattern.test(email.value)) {
    errors.value.email = ['Некорректный email'];
    valid = false;
  }
  // Валидация номера телефона
  const phonePattern = /^\+380[0-9]{9}$/;
  if (!phone_number.value || !phonePattern.test(phone_number.value)) {
    errors.value.phone_number = ['Некорректный номер телефона'];
    valid = false;
  }
  // Валидация пароля
  if (!password.value || password.value.length < 3) {
    errors.value.password = ['Пароль должен содержать минимум 3 символа'];
    valid = false;
  }
  // Валидация подтверждения пароля
  if (password.value !== confirmPassword.value) {
    errors.value.confirmPassword = ['Пароли не совпадают'];
    valid = false;
  }
  return valid;
};
</script>

<template>
  <div class="registration-form">
    <h1>Регистрация</h1>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="firstname">Имя</label>
        <input type="text" id="firstname" v-model="firstname" :class="{ 'is-invalid': errors.firstname }" required />
        <div v-if="errors.firstname" class="invalid-feedback">
          <span>{{ errors.firstname[0] }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="lastname">Фамилия</label>
        <input type="text" id="lastname" v-model="lastname" :class="{ 'is-invalid': errors.lastname }" required />
        <div v-if="errors.lastname" class="invalid-feedback">
          <span>{{ errors.lastname[0] }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="birthdate">Дата рождения</label>
        <input type="date" id="birthdate" v-model="birthdate" :class="{ 'is-invalid': errors.birthdate }" />
        <div v-if="errors.birthdate" class="invalid-feedback">
          <span>{{ errors.birthdate[0] }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="email">Электронная почта</label>
        <input type="email" id="email" v-model="email" :class="{ 'is-invalid': errors.email }" required />
        <div v-if="errors.email" class="invalid-feedback">
          <span>{{ errors.email[0] }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="phone_number">Номер телефона</label>
        <input type="tel" id="phone_number" v-model="phone_number" :class="{ 'is-invalid': errors.phone_number }"
          required />
        <div v-if="errors.phone_number" class="invalid-feedback">
          <span>{{ errors.phone_number[0] }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Пароль</label>
        <input type="password" id="password" v-model="password" :class="{ 'is-invalid': errors.password }" required />
        <div v-if="errors.password" class="invalid-feedback">
          <span>{{ errors.password[0] }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Подтвердите пароль</label>
        <input type="password" id="confirmPassword" v-model="confirmPassword"
          :class="{ 'is-invalid': errors.confirmPassword }" required />
        <div v-if="errors.confirmPassword" class="invalid-feedback">
          <span>{{ errors.confirmPassword[0] }}</span>
        </div>
      </div>

      <div class="form-group">
        <button type="submit" :disabled="isSubmitting">Зарегистрироваться</button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.registration-form {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f7f7f7;
  border-radius: 10px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
}

.form-group input {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.form-group input.is-invalid {
  border-color: #e74c3c;
}

.invalid-feedback {
  color: #e74c3c;
  font-size: 12px;
}

button {
  background-color: #3498db;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:disabled {
  background-color: #bdc3c7;
}

button:hover:enabled {
  background-color: #2980b9;
}
</style>
