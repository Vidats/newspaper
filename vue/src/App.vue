<script setup>
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import api from './services/api'

const route = useRoute()
const router = useRouter()
const menus = ref([])
const user = ref(null)
const searchQuery = ref('')
const showUserMenu = ref(false)

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/', query: { search: searchQuery.value.trim() } })
  } else {
    router.push({ path: '/' })
  }
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

const fetchMenus = async () => {
  try {
    const res = await api.get('/menus')
    menus.value = res.data
  } catch (error) {
    console.error('Error fetching menus:', error)
  }
}

const checkUser = () => {
  const userData = localStorage.getItem('user')
  if (userData) {
    user.value = JSON.parse(userData)
  } else {
    user.value = null
  }
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  user.value = null
  router.push('/')
}

const isAdminPage = computed(() => {
  const adminPaths = ['/menu', '/media', '/post']
  const isPathAdmin = adminPaths.includes(route.path) || route.path.startsWith('/admin')

  // Chỉ coi là trang admin nếu path thuộc admin VÀ user có role là admin
  return isPathAdmin && user.value?.role === 'admin'
})

// Bảo mật: Nếu vào link admin mà không phải admin thì đá về trang chủ
import { watch } from 'vue'
watch(() => route.path, (newPath) => {
  const adminPaths = ['/menu', '/media', '/post']
  const isPathAdmin = adminPaths.includes(newPath) || newPath.startsWith('/admin')

  if (isPathAdmin && (!user.value || user.value.role !== 'admin')) {
    router.push('/')
  }
}, { immediate: true })

onMounted(() => {
  fetchMenus()
  checkUser()
})

// Theo dõi route để cập nhật thông tin user (ví dụ sau khi login thành công)
router.afterEach(() => {
  checkUser()
})
</script>

<template>
  <div class="app-wrapper">
    <!-- --- PUBLIC LAYOUT --- -->
    <template v-if="!isAdminPage && route.path !== '/login'">
      <header class="public-header">
        <div class="header-top">
          <div class="full-container">
            <div class="header-flex">
              <div class="logo">
                <RouterLink to="/">NEWS<span>PORTAL</span></RouterLink>
              </div>
              <div class="header-right">
                <div class="search-bar">
                  <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Tìm kiếm theo tên bài viết..."
                    @keyup.enter="handleSearch"
                  />
                  <button @click="handleSearch">🔍</button>
                </div>
                <div class="auth-section">
                  <template v-if="user">
                    <div class="user-info-header" @click="toggleUserMenu">
                      <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=e41e2b&color=fff`" class="user-avatar-sm" />
                      <div class="user-dropdown" v-if="showUserMenu">
                        <span class="u-name-dropdown">{{ user.name }}</span>
                        <RouterLink to="/post" v-if="user.role === 'admin'">Quản trị</RouterLink>
                        <a href="#" @click.prevent="logout">Đăng xuất</a>
                      </div>
                    </div>
                  </template>
                  <template v-else>
                    <RouterLink to="/login" class="login-icon-link" title="Đăng nhập">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </RouterLink>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
        <nav class="public-nav">
          <div class="full-container">
            <ul>
              <li>
                <RouterLink
                  to="/"
                  :class="{ active: route.path === '/' && !route.query.menu }"
                >
                  Trang chủ
                </RouterLink>
              </li>
              <li v-for="menu in menus" :key="menu.id">
                <RouterLink
                  :to="{ path: '/', query: { menu: menu.id } }"
                  :class="{ active: route.query.menu == menu.id }"
                >
                  {{ menu.name }}
                </RouterLink>
              </li>
            </ul>
          </div>
        </nav>
      </header>

      <main class="public-main">
        <RouterView />
      </main>

      <footer class="public-footer">
        <div class="full-container">
          <div class="footer-grid">
            <div class="footer-about">
              <h3>NEWS<span>PORTAL</span></h3>
              <p>Trang tin tức tổng hợp cập nhật nhanh nhất, chính xác nhất các tin tức trong và ngoài nước.</p>
            </div>
            <div class="footer-links">
              <h4>Chuyên mục</h4>
              <ul>
                <li v-for="menu in menus" :key="menu.id">
                   <RouterLink :to="{ path: '/', query: { menu: menu.id } }">{{ menu.name }}</RouterLink>
                </li>
              </ul>
            </div>
            <div class="footer-contact">
              <h4>Liên hệ</h4>
              <p>Email: contact@newsportal.com</p>
              <p>Hotline: 1900 1234</p>
            </div>
          </div>
          <div class="footer-bottom">
            <p>&copy; 2026 NewsPortal. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </template>

    <!-- --- LOGIN PAGE --- -->
    <template v-else-if="route.path === '/login'">
      <RouterView />
    </template>

    <!-- --- ADMIN LAYOUT --- -->
    <template v-else>
      <div class="admin-layout">
        <aside class="admin-sidebar">
          <div class="sidebar-header">
            <h3>Admin<span>Panel</span></h3>
          </div>
          <nav class="sidebar-nav">
            <ul>
              <li><RouterLink to="/"><span class="icon">🏠</span> Ra Trang Chủ</RouterLink></li>
              <li><RouterLink to="/menu"><span class="icon">📂</span> Quản lý Menu</RouterLink></li>
              <li><RouterLink to="/post"><span class="icon">📝</span> Quản lý Bài viết</RouterLink></li>
              <li><RouterLink to="/media"><span class="icon">🖼️</span> Quản lý Media</RouterLink></li>
            </ul>
          </nav>
          <div class="sidebar-footer">
             <button @click="logout" class="admin-logout-btn">Đăng xuất</button>
          </div>
        </aside>

        <div class="admin-main-wrapper">
          <header class="admin-topbar">
            <div class="topbar-left">
              <h2>{{ route.name?.toUpperCase() || 'QUẢN TRỊ' }}</h2>
            </div>
            <div class="topbar-right">
              <div class="admin-user-info">
                <span>Xin chào, <strong>{{ user?.name || 'Admin' }}</strong></span>
                <img :src="`https://ui-avatars.com/api/?name=${user?.name || 'A'}&background=007bff&color=fff`" alt="avatar" />
              </div>
            </div>
          </header>
          <main class="admin-content">
            <RouterView />
          </main>
        </div>
      </div>
    </template>
  </div>
</template>

<style>
:root {
  --primary-color: #e41e2b;
  --secondary-color: #2c3e50;
  --dark-bg: #1a1a1a;
  --light-bg: #f8f9fa;
  --border-color: #dee2e6;
  --text-main: #333;
  --text-muted: #6c757d;
  --admin-sidebar-bg: #212529;
  --admin-accent: #007bff;
}
*, *::before, *::after {
  box-sizing: border-box;
}

body, html {
  margin: 0;
  padding: 0;
  font-family: 'Inter', sans-serif;
  color: var(--text-main);
  background-color: #fff;
  overflow-x: hidden;
  width: 100%;
}

.app-wrapper {
  width: 100%;
  overflow-x: hidden;
}

.full-container {
  width: 100%;
  max-width: 1440px; /* Giới hạn chiều rộng tối đa để đẹp trên desktop lớn */
  padding-left: 30px;
  padding-right: 30px;
  margin: 0 auto;
}

@media (max-width: 768px) {
  .full-container {
    padding-left: 15px;
    padding-right: 15px;
  }

  .header-flex {
    flex-direction: column;
    gap: 15px;
  }
  .search-bar {
    width: 100%;
  }
  .public-nav ul {
    overflow-x: auto;
    white-space: nowrap;
    display: flex;
    -webkit-overflow-scrolling: touch;
  }
  .public-nav li {
    flex: 0 0 auto;
  }
}

/* --- PUBLIC HEADER --- */
.public-header {
  border-bottom: 4px solid var(--primary-color);
  background: #fff;
  width: 100%;
}

.header-top { padding: 15px 0; }
.header-flex { display: flex; justify-content: space-between; align-items: center; }

.logo a {
  font-size: 2.2rem;
  font-weight: 900;
  color: #000;
  text-decoration: none;
  letter-spacing: -1.5px;
}
.logo span { color: var(--primary-color); }

.header-right { display: flex; align-items: center; gap: 30px; }

.search-bar {
  display: flex;
  background: #f1f3f5;
  border-radius: 25px;
  padding: 5px 20px;
  width: 350px;
  border: 1px solid #eee;
}
.search-bar input { background: transparent; border: none; flex: 1; padding: 10px; outline: none; }
.search-bar button { background: transparent; border: none; cursor: pointer; }

.auth-section { display: flex; align-items: center; position: relative; }
.login-icon-link {
  color: var(--text-main);
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid #ddd;
  transition: 0.3s;
}
.login-icon-link:hover {
  color: var(--primary-color);
  border-color: var(--primary-color);
  background: #fff1f2;
}

.user-info-header {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  position: relative;
}
.user-avatar-sm { width: 38px; height: 38px; border-radius: 50%; border: 2px solid #eee; }

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 10px;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  width: 180px;
  z-index: 1000;
  padding: 10px 0;
  display: flex;
  flex-direction: column;
}
.u-name-dropdown {
  padding: 8px 15px;
  font-weight: 800;
  border-bottom: 1px solid #f5f5f5;
  margin-bottom: 5px;
  color: #333;
}
.user-dropdown a {
  padding: 10px 15px;
  text-decoration: none;
  color: #555;
  font-size: 0.9rem;
  transition: 0.2s;
}
.user-dropdown a:hover {
  background: #f8f9fa;
  color: var(--primary-color);
}

.public-nav { background: var(--dark-bg); color: #fff; width: 100%; border-bottom: 1px solid #333; }
.public-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  justify-content: center; /* Căn giữa tất cả các menu */
  flex-wrap: wrap; /* Cho phép tự động xuống hàng nếu quá dài */
}
.public-nav li a {
  display: block;
  padding: 15px 20px;
  color: #adb5bd; /* Màu nhạt hơn khi không chọn */
  text-decoration: none;
  font-weight: 700;
  font-size: 0.9rem;
  transition: 0.3s;
  text-transform: uppercase;
  position: relative;
}

.public-nav li a:hover, .public-nav li a.active {
  color: #fff; /* Chữ trắng khi active hoặc hover */
  background: rgba(255,255,255,0.05);
}

.public-nav li a.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: var(--primary-color); /* Đường gạch đỏ dưới chân menu active */
}

@media (max-width: 768px) {
  .public-nav ul {
    justify-content: flex-start; /* Trên mobile thì để cuộn ngang, không cần căn giữa */
    flex-wrap: nowrap;
    overflow-x: auto;
  }
}

.public-main { min-height: 600px; padding: 30px 0; background: #f4f6f9; width: 100%; }

/* --- PUBLIC FOOTER --- */
.public-footer { background: var(--dark-bg); color: #fff; padding: 60px 0 30px; }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 60px; margin-bottom: 40px; }
.footer-about h3 { font-size: 1.8rem; font-weight: 900; margin-bottom: 20px; }
.footer-about h3 span { color: var(--primary-color); }
.footer-about p { color: #adb5bd; line-height: 1.6; }
.footer-links h4, .footer-contact h4 { font-size: 1.1rem; margin-bottom: 20px; text-transform: uppercase; }
.footer-links ul { list-style: none; padding: 0; }
.footer-links li { margin-bottom: 10px; }
.footer-links a { color: #adb5bd; text-decoration: none; }
.footer-links a:hover { color: #fff; }
.footer-bottom { border-top: 1px solid #333; padding-top: 25px; text-align: center; color: #666; font-size: 0.9rem; }

/* --- ADMIN LAYOUT --- */
.admin-layout { display: flex; height: 100vh; overflow: hidden; width: 100%; }
.admin-sidebar { width: 260px; background: var(--admin-sidebar-bg); color: #fff; display: flex; flex-direction: column; }
.sidebar-header { padding: 25px 20px; text-align: center; border-bottom: 1px solid #343a40; }
.sidebar-header h3 { font-weight: 900; }
.sidebar-header h3 span { color: var(--admin-accent); }
.sidebar-nav { flex: 1; padding: 20px 0; }
.sidebar-nav ul { list-style: none; padding: 0; }
.sidebar-nav a { display: flex; align-items: center; padding: 12px 25px; color: #c2c7d0; text-decoration: none; transition: 0.3s; }
.sidebar-nav a:hover { background: #343a40; color: #fff; }
.sidebar-nav a.router-link-active { background: var(--admin-accent); color: #fff; }
.sidebar-nav a .icon { margin-right: 15px; }
.sidebar-footer { padding: 20px; border-top: 1px solid #343a40; }
.admin-logout-btn { width: 100%; padding: 10px; background: transparent; border: 1px solid #444; color: #ccc; cursor: pointer; border-radius: 4px; }
.admin-logout-btn:hover { background: #e41e2b; border-color: #e41e2b; color: #fff; }

.admin-main-wrapper { flex: 1; display: flex; flex-direction: column; background: #f4f6f9; }
.admin-topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.topbar-left h2 { font-size: 1.1rem; color: #555; margin: 0; }
.admin-user-info { display: flex; align-items: center; gap: 12px; }
.admin-user-info img { width: 35px; height: 35px; border-radius: 50%; }
.admin-content { padding: 30px; overflow-y: auto; }

@media (max-width: 992px) {
  .full-container { padding-left: 20px; padding-right: 20px; }
  .search-bar { width: 250px; }
}
</style>
