<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

async function login() {
  errorMessage.value = ''
  loading.value = true

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', response.data.token)
    router.push('/dashboard')
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <form class="login-card" @submit.prevent="login">
      <h1>User Management</h1>
      <p class="subtitle">เข้าสู่ระบบเพื่อดำเนินการต่อ</p>

      <label for="email">Email</label>
      <input
        id="email"
        v-model.trim="email"
        type="email"
        placeholder="name@example.com"
        autocomplete="username"
        required
      />

      <label for="password">Password</label>
      <input
        id="password"
        v-model="password"
        type="password"
        placeholder="กรอกรหัสผ่าน"
        autocomplete="current-password"
        required
      />

      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <button type="submit" :disabled="loading">
        {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'Login' }}
      </button>
    </form>
  </main>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 24px;
  box-sizing: border-box;
  background: #f3f4f6;
  font-family: Arial, sans-serif;
}

.login-card {
  width: 100%;
  max-width: 400px;
  padding: 32px;
  box-sizing: border-box;
  border-radius: 12px;
  background: white;
  box-shadow: 0 8px 30px #00000010;
}

h1 {
  margin: 0 0 8px;
  color: #111827;
  font-size: 25px;
}

.subtitle {
  margin-bottom: 24px;
  color: #6b7280;
}

label {
  display: block;
  margin: 16px 0 6px;
  font-weight: 600;
}

input {
  width: 100%;
  padding: 11px;
  box-sizing: border-box;
  border: 1px solid #d1d5db;
  border-radius: 6px;
}

button {
  width: 100%;
  margin-top: 24px;
  padding: 12px;
  border: 0;
  border-radius: 6px;
  background: #2563eb;
  color: white;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
}

.error {
  color: #dc2626;
}
</style>
