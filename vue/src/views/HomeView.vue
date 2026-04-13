<template>
  <div class="news-home-container">
    <div v-if="loading" class="news-loader">
      <div class="spinner"></div>
      <span>Đang tải tin tức...</span>
    </div>

    <div v-else-if="posts.length === 0" class="empty-news">
      <p>Không tìm thấy bài viết nào.</p>
    </div>

    <div v-else class="full-container">
      <!-- --- HERO SECTION (Featured + Latest Titles) --- -->
      <section v-if="featuredPost" class="hero-section">
        <div class="hero-left">
          <div class="featured-card">
            <div class="featured-img">
              <RouterLink :to="`/post/${featuredPost.id}`">
                <img :src="getImageUrl(featuredPost.media?.path)" :alt="featuredPost.title" />
              </RouterLink>
              <span v-if="featuredPost.menu" class="badge-cat">{{ featuredPost.menu.name }}</span>
            </div>
            <div class="featured-info">
              <RouterLink :to="`/post/${featuredPost.id}`" class="featured-title-link">
                <h1 class="featured-title">{{ featuredPost.title }}</h1>
              </RouterLink>
              <p class="featured-summary">{{ featuredPost.summary }}</p>
              <div class="featured-meta">
                <span>{{ formatDate(featuredPost.created_at) }}</span>
                <span class="dot"></span>
                <span>By <strong>{{ featuredPost.user?.name || 'Admin' }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <div class="hero-right">
          <h3 class="side-title">Tin mới cập nhật</h3>
          <div class="latest-titles-list">
            <div v-for="post in latestNewsTitles" :key="post.id" class="title-item">
              <RouterLink :to="`/post/${post.id}`" class="title-link">
                {{ post.title }}
              </RouterLink>
              <span class="title-date">{{ formatDate(post.created_at) }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- --- GRID SECTION --- -->
      <section class="grid-section" v-if="displayGridPosts.length > 0">
        <div class="section-divider">
          <h2>{{ selectedMenuName || (route.query.search ? `Kết quả tìm kiếm cho: "${route.query.search}"` : 'Tất cả tin tức') }}</h2>
        </div>

        <div class="news-grid-4">
          <article v-for="post in displayGridPosts" :key="post.id" class="grid-card">
            <div class="grid-img">
              <RouterLink :to="`/post/${post.id}`">
                <img :src="getImageUrl(post.media?.path)" :alt="post.title" />
              </RouterLink>
              <span v-if="post.menu" class="grid-badge">{{ post.menu.name }}</span>
            </div>
            <div class="grid-body">
              <RouterLink :to="`/post/${post.id}`" class="grid-title-link">
                <h3 class="grid-title">{{ post.title }}</h3>
              </RouterLink>
              <p class="grid-summary">{{ post.summary }}</p>
              <div class="grid-footer">
                <span class="grid-date">{{ formatDate(post.created_at) }}</span>
              </div>
            </div>
          </article>
        </div>
      </section>
      </div>
      </div>
      </template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const posts = ref([]);
const menus = ref([]);
const loading = ref(true);

const getImageUrl = (path) => {
  if (!path) return 'https://via.placeholder.com/800x450?text=NewsPortal';
  return `http://lumenapi.test/storage/${path}`;
};

const formatDate = (dateString) => {
  if (!dateString) return 'Vừa xong';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const fetchData = async () => {
  loading.value = true;
  try {
    const menuId = route.query.menu;
    const search = route.query.search;

    let postUrl = '/posts';
    if (menuId) {
      postUrl = `/posts/menu/${menuId}`;
    }

    const [postRes, menuRes] = await Promise.all([
      api.get(postUrl),
      api.get('/menus')
    ]);

    let filteredPosts = postRes.data;
    if (search) {
      const query = search.toLowerCase();
      filteredPosts = filteredPosts.filter(p =>
        p.title.toLowerCase().includes(query) ||
        (p.summary && p.summary.toLowerCase().includes(query))
      );
    }

    posts.value = filteredPosts;
    menus.value = menuRes.data;
  } catch (error) {
    console.error("Lỗi tải dữ liệu:", error);
  } finally {
    loading.value = false;
  }
};

watch(() => route.query, fetchData);

const featuredPost = computed(() => posts.value[0] || null);
const latestNewsTitles = computed(() => posts.value.slice(1, 6));
const displayGridPosts = computed(() => {
    // Hiển thị tất cả bài viết từ bài thứ 2 trở đi trong lưới
    // để đảm bảo phần này luôn có nội dung nếu có trên 1 bài viết.
    return posts.value.slice(1);
});

const selectedMenuName = computed(() => {
  if (!route.query.menu) return null;
  const menu = menus.value.find(m => m.id == route.query.menu);
  return menu ? menu.name : null;
});

onMounted(fetchData);
</script>

<style scoped>
.news-home-container {
  padding-bottom: 80px;
}

/* --- HERO SECTION --- */
.hero-section {
  display: grid;
  grid-template-columns: 3fr 1fr;
  gap: 20px;
  margin-bottom: 40px;
}

@media (max-width: 1024px) {
  .hero-section {
    grid-template-columns: 1fr;
  }
}

.featured-card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
}

.featured-img {
  position: relative;
  height: 450px;
}
.featured-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

@media (max-width: 768px) {
  .featured-img { height: 250px; }
}

.badge-cat {
  position: absolute;
  top: 20px;
  left: 20px;
  background: var(--primary-color, #e41e2b);
  color: #fff;
  padding: 5px 15px;
  border-radius: 4px;
  font-weight: 800;
  text-transform: uppercase;
  font-size: 0.75rem;
}

.featured-info {
  padding: 30px;
}
.featured-title {
  font-size: 2.5rem;
  font-weight: 900;
  line-height: 1.2;
  margin-bottom: 15px;
  color: #111;
}

@media (max-width: 768px) {
  .featured-title { font-size: 1.8rem; }
}

.featured-summary {
  color: #555;
  line-height: 1.6;
  margin-bottom: 20px;
  font-size: 1.1rem;
}

.featured-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.85rem;
  color: #888;
}
.dot {
  width: 4px;
  height: 4px;
  background: #ccc;
  border-radius: 50%;
}

.hero-right {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.side-title {
  font-size: 1.1rem;
  font-weight: 900;
  text-transform: uppercase;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 2px solid #f0f0f0;
}

.latest-titles-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.title-item {
  border-bottom: 1px solid #f5f5f5;
  padding-bottom: 10px;
}
.title-link {
  font-weight: 700;
  color: #222;
  text-decoration: none;
  font-size: 1rem;
  line-height: 1.4;
}
.title-link:hover { color: var(--primary-color, #e41e2b); }
.title-date { font-size: 0.75rem; color: #aaa; display: block; margin-top: 4px; }

/* --- GRID SECTION --- */
.section-divider {
  margin: 40px 0 20px;
  border-bottom: 2px solid #eee;
  padding-bottom: 10px;
}
.section-divider h2 {
  font-size: 1.5rem;
  font-weight: 900;
  text-transform: uppercase;
}

.news-grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.grid-card {
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0,0,0,0.03);
}

.grid-img {
  height: 180px;
  position: relative;
}
.grid-img img { width: 100%; height: 100%; object-fit: cover; }
.grid-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(0,0,0,0.7);
  color: #fff;
  font-size: 0.65rem;
  padding: 3px 8px;
  border-radius: 3px;
}

.grid-body { padding: 15px; }
.grid-title {
  font-size: 1rem;
  font-weight: 800;
  line-height: 1.4;
  margin-bottom: 8px;
  color: #111;
}

@media (max-width: 1200px) {
  .news-grid-4 { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
  .news-grid-4 { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 480px) {
  .news-grid-4 { grid-template-columns: 1fr; }
}

.news-loader { text-align: center; padding: 100px; }
.spinner {
  width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top-color: var(--primary-color, #e41e2b);
  border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty-news { text-align: center; padding: 150px 0; color: #999; }

</style>


