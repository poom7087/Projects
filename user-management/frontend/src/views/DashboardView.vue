
<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const user = ref(null)
const loading = ref(true)
const errorMessage = ref('')

onMounted(async () => {
  try {
    const response = await api.get('/me')
    user.value = response.data.user
  } catch (error) {
    localStorage.removeItem('token')
    router.replace('/login')
  } finally {
    loading.value = false
  }
})

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
  <div class="dashboard">
    <header class="topbar">
      <h2>User Management</h2>

      <div class="account">
        <span v-if="user">{{ user.name }}</span>
        <button class="logout-button" @click="logout">Logout</button>
      </div>
    </header>

    <main class="content">
      <p v-if="loading">กำลังโหลดข้อมูล...</p>

      <p v-else-if="errorMessage" class="error">
        {{ errorMessage }}
      </p>

      <template v-else>
        <h1>Dashboard</h1>
        <p class="subtitle">ยินดีต้อนรับเข้าสู่ระบบจัดการผู้ใช้งาน</p>

        <section class="cards">
          <router-link to="/users" class="card">
            <div class="card-icon">👥</div>
            <h3>Users Management</h3>
            <p>ดู เพิ่ม แก้ไข และลบข้อมูลผู้ใช้งาน</p>
            <span>จัดการผู้ใช้งาน →</span>
          </router-link>

          <router-link to="/profile" class="card">
            <div class="card-icon">👤</div>
            <h3>My Profile</h3>
            <p>ดูและแก้ไขข้อมูลส่วนตัวของคุณ</p>
            <span>แก้ไขโปรไฟล์ →</span>
          </router-link>
        </section>
      </template>
    </main>
  </div>
</template>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f3f4f6;
  font-family: Arial, sans-serif;
  color: #111827;
}

.topbar {
  min-height: 72px;
  padding: 0 40px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  box-shadow: 0 1px 4px #00000010;
}

.topbar h2 {
  font-size: 21px;
}

.account {
  display: flex;
  align-items: center;
  gap: 20px;
}

.logout-button {
  border: none;
  border-radius: 6px;
  padding: 10px 16px;
  background: #dc2626;
  color: white;
  cursor: pointer;
}

.content {
  max-width: 1100px;
  margin: 0 auto;
  padding: 40px 24px;
}

.content h1 {
  margin-bottom: 8px;
}

.subtitle {
  color: #6b7280;
  margin-bottom: 32px;
}

.cards {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 24px;
}

.card {
  display: block;
  padding: 28px;
  background: white;
  border-radius: 12px;
  text-decoration: none;
  color: inherit;
  box-shadow: 0 4px 16px #00000008;
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px #00000012;
}

.card-icon {
  font-size: 32px;
}

.card h3 {
  margin: 18px 0 10px;
}

.card p {
  color: #6b7280;
  line-height: 1.6;
}

.card span {
  display: inline-block;
  margin-top: 12px;
  color: #2563eb;
  font-weight: 600;
}

.error {
  color: #dc2626;
}

@media (max-width: 640px) {
  .topbar {
    padding: 0 16px;
  }

  .account {
    gap: 10px;
  }

  .cards {
    grid-template-columns: 1fr;
  }

  .content {
    padding: 24px 16px;
  }
}
</style>
