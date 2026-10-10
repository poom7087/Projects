
<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const users = ref([])
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showForm = ref(false)
const editingId = ref(null)
const saving = ref(false)

const form = ref({
  name: '',
  email: '',
  password: '',
})

async function fetchUsers() {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/users')
    users.value = response.data.data
  } catch (error) {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.replace('/login')
      return
    }

    errorMessage.value =
      error.response?.data?.message || 'ไม่สามารถโหลดรายชื่อผู้ใช้ได้'
  } finally {
    loading.value = false
  }
}

function openCreateForm() {
  editingId.value = null
  form.value = { name: '', email: '', password: '' }
  successMessage.value = ''
  errorMessage.value = ''
  showForm.value = true
}

function openEditForm(user) {
  editingId.value = user.id
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
  }
  successMessage.value = ''
  errorMessage.value = ''
  showForm.value = true
}

async function saveUser() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  const payload = {
    name: form.value.name,
    email: form.value.email,
  }

  if (!editingId.value || form.value.password) {
    payload.password = form.value.password
  }

  try {
    if (editingId.value) {
      await api.put(`/users/${editingId.value}`, payload)
      successMessage.value = 'แก้ไขผู้ใช้สำเร็จ'
    } else {
      await api.post('/users', payload)
      successMessage.value = 'เพิ่มผู้ใช้สำเร็จ'
    }

    showForm.value = false
    await fetchUsers()
  } catch (error) {
    const data = error.response?.data

    if (data?.errors) {
      errorMessage.value = Object.values(data.errors).flat().join(' ')
    } else {
      errorMessage.value = data?.message || 'บันทึกข้อมูลไม่สำเร็จ'
    }
  } finally {
    saving.value = false
  }
}

async function deleteUser(user) {
  const confirmed = window.confirm(
    `ต้องการลบผู้ใช้ ${user.name} (${user.email}) หรือไม่?`,
  )

  if (!confirmed) return

  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.delete(`/users/${user.id}`)
    successMessage.value = 'ลบผู้ใช้สำเร็จ'
    await fetchUsers()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'ลบผู้ใช้ไม่สำเร็จ'
  }
}

function logout() {
  api.post('/logout')
    .catch(() => {})
    .finally(() => {
      localStorage.removeItem('token')
      router.replace('/login')
    })
}

onMounted(fetchUsers)
</script>

<template>
  <main class="page">
    <header class="topbar">
      <router-link to="/dashboard" class="brand">
        User Management
      </router-link>

      <nav>
        <router-link to="/dashboard">Dashboard</router-link>
        <router-link to="/profile">My Profile</router-link>
        <button class="logout" @click="logout">Logout</button>
      </nav>
    </header>

    <section class="content">
      <div class="heading">
        <div>
          <h1>Users Management</h1>
          <p>จัดการข้อมูลผู้ใช้งานในระบบ</p>
        </div>

        <button class="primary" @click="openCreateForm">
          + Add User
        </button>
      </div>

      <p v-if="successMessage" class="success">{{ successMessage }}</p>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <section v-if="showForm" class="form-card">
        <h2>{{ editingId ? 'Edit User' : 'Add User' }}</h2>

        <form @submit.prevent="saveUser">
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

          <label for="password">
            Password
            <span v-if="editingId">(เว้นว่างหากไม่ต้องการเปลี่ยน)</span>
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            :required="!editingId"
            minlength="8"
            autocomplete="new-password"
          />

          <p v-if="editingId" class="hint">
            หากไม่ต้องการเปลี่ยนรหัสผ่าน ให้เว้นช่องนี้ว่าง
          </p>

          <div class="form-actions">
            <button class="primary" type="submit" :disabled="saving">
              {{ saving ? 'กำลังบันทึก...' : 'Save' }}
            </button>
            <button
              class="secondary"
              type="button"
              @click="showForm = false"
            >
              Cancel
            </button>
          </div>
        </form>
      </section>

      <section class="table-card">
        <p v-if="loading" class="empty">กำลังโหลดข้อมูล...</p>

        <div v-else class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Created Date</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  {{ user.created_at
                    ? new Date(user.created_at).toLocaleString()
                    : '-' }}
                </td>
                <td class="actions">
                  <button
                    class="edit"
                    @click="openEditForm(user)"
                  >
                    Edit
                  </button>
                  <button
                    class="delete"
                    @click="deleteUser(user)"
                  >
                    Delete
                  </button>
                </td>
              </tr>

              <tr v-if="users.length === 0">
                <td colspan="5" class="empty">
                  ไม่พบข้อมูลผู้ใช้
                </td>
              </tr>
            </tbody>
          </table>
        </div>
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
  padding: 10px 14px;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.logout,
.delete {
  background: #dc2626;
  color: white;
}

.content {
  max-width: 1200px;
  margin: auto;
  padding: 36px 24px;
}

.heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

.heading h1 {
  margin: 0 0 8px;
}

.heading p,
.hint {
  color: #6b7280;
}

.primary {
  background: #2563eb;
  color: white;
}

.secondary {
  background: #e5e7eb;
  color: #111827;
}

.form-card,
.table-card {
  padding: 24px;
  margin-bottom: 24px;
  border-radius: 10px;
  background: white;
  box-shadow: 0 2px 10px #00000008;
}

.form-card {
  max-width: 600px;
}

.form-card h2 {
  margin-top: 0;
}

label {
  display: block;
  margin: 16px 0 6px;
  font-weight: 600;
}

label span {
  color: #6b7280;
  font-size: 12px;
  font-weight: 400;
}

input {
  width: 100%;
  padding: 11px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

th,
td {
  padding: 14px 12px;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
}

th {
  background: #f9fafb;
}

.actions {
  display: flex;
  gap: 8px;
}

.edit {
  background: #dbeafe;
  color: #1d4ed8;
}

.success {
  padding: 12px;
  background: #dcfce7;
  color: #166534;
  border-radius: 6px;
}

.error {
  padding: 12px;
  background: #fee2e2;
  color: #991b1b;
  border-radius: 6px;
}

.empty {
  padding: 24px;
  color: #6b7280;
  text-align: center;
}

@media (max-width: 700px) {
  .topbar,
  .heading {
    align-items: flex-start;
    flex-direction: column;
  }

  nav {
    flex-wrap: wrap;
    gap: 12px;
  }

  .content {
    padding: 24px 12px;
  }
}
</style>
