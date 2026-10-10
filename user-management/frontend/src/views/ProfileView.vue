
<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  name: '',
  email: '',
  password: '',
})

onMounted(async () => {
  try {
    const response = await api.get('/me')
    const user = response.data.user

    form.value.name = user.name || ''
    form.value.email = user.email || ''
  } catch (error) {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.replace('/login')
    } else {
      errorMessage.value = 'ไม่สามารถโหลดข้อมูลโปรไฟล์ได้'
    }
  } finally {
    loading.value = false
  }
})

async function saveProfile() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  const payload = {
    name: form.value.name,
    email: form.value.email,
  }

  if (form.value.password) {
    payload.password = form.value.password
  }

  try {
    const response = await api.put('/profile', payload)

    form.value.name = response.data.data.name
    form.value.email = response.data.data.email
    form.value.password = ''

    successMessage.value = 'บันทึกโปรไฟล์สำเร็จ'
  } catch (error) {
    const data = error.response?.data

    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.replace('/login')
      return
    }

    if (data?.errors) {
      errorMessage.value = Object.values(data.errors).flat().join(' ')
    } else {
      errorMessage.value = data?.message || 'บันทึกโปรไฟล์ไม่สำเร็จ'
    }
  } finally {
    saving.value = false
  }
}

async function logout() {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error('Logout API error:', error)
  } finally {
    localStorage.removeItem('token')
    router.replace('/login')
  }
}
</script>

<template>
  <main class="page">
    <header class="topbar">
      <router-link to="/dashboard" class="brand">
        User Management
      </router-link>

      <nav>
        <router-link to="/dashboard">Dashboard</router-link>
        <router-link to="/users">Users</router-link>
        <button class="logout" @click="logout">Logout</button>
      </nav>
    </header>

    <section class="content">
      <h1>Edit Profile</h1>
      <p class="subtitle">จัดการข้อมูลบัญชีของคุณ</p>

      <section v-if="loading" class="card">
        กำลังโหลดข้อมูลโปรไฟล์...
      </section>

      <section v-else class="card">
        <p v-if="successMessage" class="success">
          {{ successMessage }}
        </p>

        <p v-if="errorMessage" class="error">
          {{ errorMessage }}
        </p>

        <form @submit.prevent="saveProfile">
          <label for="name">Name</label>
          <input
            id="name"
            v-model.trim="form.name"
            type="text"
            maxlength="255"
            required
          />

          <label for="email">Email</label>
          <input
            id="email"
            v-model.trim="form.email"
            type="email"
            maxlength="191"
            required
          />

          <label for="password">New Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            minlength="8"
            autocomplete="new-password"
            placeholder="เว้นว่างหากไม่ต้องการเปลี่ยน"
          />

          <p class="hint">
            หากไม่ต้องการเปลี่ยนรหัสผ่าน ให้เว้นช่องนี้ว่าง
          </p>

          <div class="actions">
            <button type="submit" class="primary" :disabled="saving">
              {{ saving ? 'กำลังบันทึก...' : 'Save Changes' }}
            </button>

            <router-link to="/dashboard" class="cancel">
              Cancel
            </router-link>
          </div>
        </form>
      </section>
    </section>
  </main>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.page {
  min-height: 100vh;
  background: #f3f4f6;
  color: #111827;
  font-family: Arial, sans-serif;
}

.topbar {
  min-height: 72px;
  padding: 16px 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  background: white;
  box-shadow: 0 1px 4px #00000010;
}

.brand {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  text-decoration: none;
}

nav {
  display: flex;
  align-items: center;
  gap: 20px;
}

nav a {
  color: #374151;
  text-decoration: none;
}

button {
  border: 0;
  border-radius: 6px;
  padding: 11px 16px;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.logout {
  background: #dc2626;
  color: white;
}

.content {
  max-width: 800px;
  margin: 0 auto;
  padding: 36px 24px;
}

.content h1 {
  margin-bottom: 8px;
}

.subtitle,
.hint {
  color: #6b7280;
}

.card {
  margin-top: 24px;
  padding: 28px;
  border-radius: 10px;
  background: white;
  box-shadow: 0 2px 10px #00000008;
}

label {
  display: block;
  margin: 20px 0 7px;
  font-weight: 600;
}

input {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
}

.hint {
  font-size: 13px;
}

.actions {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 24px;
}

.primary {
  background: #2563eb;
  color: white;
}

.cancel {
  color: #4b5563;
  text-decoration: none;
}

.success {
  padding: 12px;
  border-radius: 6px;
  background: #dcfce7;
  color: #166534;
}

.error {
  padding: 12px;
  border-radius: 6px;
  background: #fee2e2;
  color: #991b1b;
}

@media (max-width: 640px) {
  .topbar {
    padding: 16px;
    align-items: flex-start;
    flex-direction: column;
  }

  nav {
    flex-wrap: wrap;
    gap: 12px;
  }

  .content {
    padding: 24px 16px;
  }
}
</style>
