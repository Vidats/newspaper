<template>
  <div class="post-detail-page">
    <div v-if="loading" class="post-loader">
      <div class="spinner"></div>
      <p>Đang tải bài viết...</p>
    </div>

    <div v-else-if="!post" class="post-not-found">
      <h2>404</h2>
      <p>Xin lỗi, bài viết này không tồn tại hoặc đã bị xóa.</p>
      <RouterLink to="/" class="btn-back">Quay về Trang chủ</RouterLink>
    </div>

    <div v-else class="full-container">
      <!-- --- BREADCRUMBS --- -->
      <nav class="breadcrumb">
        <RouterLink to="/">Trang chủ</RouterLink>
        <span class="sep">/</span>
        <RouterLink v-if="post.menu" :to="{ path: '/', query: { menu: post.menu.id } }">
          {{ post.menu.name }}
        </RouterLink>
        <span class="sep">/</span>
        <span class="current">{{ post.title }}</span>
      </nav>

      <div class="post-layout">
        <!-- --- MAIN CONTENT --- -->
        <main class="post-main">
          <header class="post-header">
            <h1 class="post-title">{{ post.title }}</h1>
            <div class="post-meta">
              <div class="meta-left">
                <span class="meta-author">
                  By <strong>{{ post.user?.name || 'Admin' }}</strong>
                </span>
                <span class="meta-date">📅 {{ formatDate(post.created_at) }}</span>
              </div>
              <div class="meta-right">
                <button @click="sharePost" class="btn-share">Chia sẻ</button>
              </div>
            </div>
          </header>

          <div class="post-summary" v-if="post.summary">
             {{ post.summary }}
          </div>

          <div class="post-featured-img" v-if="post.media">
            <img :src="getImageUrl(post.media.path)" :alt="post.title" />
            <p class="img-caption" v-if="post.media.name">{{ post.media.name }}</p>
          </div>

          <div class="post-content" v-html="post.content"></div>

          <footer class="post-footer-tags">
             <div class="tags" v-if="post.menu">
                <span class="tag-label">Chuyên mục:</span>
                <RouterLink :to="{ path: '/', query: { menu: post.menu.id } }" class="tag-link">
                   #{{ post.menu.name }}
                </RouterLink>
             </div>
          </footer>
        </main>

        <!-- --- SIDEBAR --- -->
        <aside class="post-sidebar">
          <div class="sidebar-box">
             <h3 class="side-title">Tin cùng chuyên mục</h3>
             <div class="related-list" v-if="relatedPosts.length > 0">
                <div v-for="related in relatedPosts" :key="related.id" class="related-item">
                   <RouterLink :to="`/post/${related.slug}`" class="related-link">
                      {{ related.title }}
                   </RouterLink>
                   <span class="related-date">{{ formatDate(related.created_at) }}</span>
                </div>
             </div>
             <p v-else class="no-data">Không có bài viết liên quan.</p>
          </div>

          <div class="sidebar-box banner-promo">
             <h4>NEWSPORTAL</h4>
             <p>Cập nhật tin tức 24/7</p>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const post = ref(null);
const relatedPosts = ref([]);
const loading = ref(true);

const getImageUrl = (path) => {
  if (!path) return '';
  return `http://lumenapi.test/storage/${path}`;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const fetchPostDetail = async () => {
  loading.value = true;
  try {
    const slug = route.params.slug;
    const res = await api.get(`/posts/${slug}`);
    post.value = res.data;

    // Sau khi lấy bài viết, lấy thêm các bài viết cùng chuyên mục
    if (post.value.menu_id) {
       const relatedRes = await api.get(`/posts/menu/${post.value.menu_id}`);
       // Lọc bỏ bài hiện tại và lấy tối đa 5 bài
       relatedPosts.value = relatedRes.data
          .filter(p => p.slug != slug)
          .slice(0, 5);
    }
  } catch (error) {
    console.error("Lỗi tải chi tiết bài viết:", error);
    post.value = null;
  } finally {
    loading.value = false;
  }
};

const sharePost = () => {
  alert("Tính năng chia sẻ đang được phát triển!");
};

// Theo dõi thay đổi Slug trên URL (ví dụ khi nhấn vào bài viết liên quan)
watch(() => route.params.slug, fetchPostDetail);

onMounted(fetchPostDetail);
</script>

<style scoped>
.post-detail-page {
  padding: 40px 0 80px;
  background: #fdfdfd;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.85rem;
  color: #888;
  margin-bottom: 30px;
}
.breadcrumb a { text-decoration: none; color: #666; transition: 0.2s; }
.breadcrumb a:hover { color: var(--primary-color); }
.breadcrumb .current { color: #aaa; font-weight: 500; }

.post-layout {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 50px;
}

.post-main {
  background: #fff;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.02);
}

.post-title {
  font-size: 2.8rem;
  font-weight: 900;
  line-height: 1.2;
  margin-bottom: 25px;
  color: #111;
  letter-spacing: -1px;
}

.post-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 25px;
  margin-bottom: 30px;
  border-bottom: 1px solid #eee;
}
.meta-left { display: flex; gap: 20px; color: #777; font-size: 0.9rem; }
.btn-share {
  background: #f0f2f5;
  border: none;
  padding: 8px 20px;
  border-radius: 20px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}
.btn-share:hover { background: #e4e6eb; }

.post-summary {
  font-size: 1.25rem;
  font-weight: 700;
  color: #444;
  line-height: 1.6;
  margin-bottom: 35px;
  border-left: 5px solid var(--primary-color);
  padding-left: 25px;
}

.post-featured-img { margin-bottom: 40px; }
.post-featured-img img { width: 100%; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.img-caption { text-align: center; color: #888; font-size: 0.85rem; font-style: italic; margin-top: 10px; }

.post-content {
  font-size: 1.15rem;
  line-height: 1.8;
  color: #333;
}
.post-content :deep(p) { margin-bottom: 25px; }
.post-content :deep(img) { max-width: 100%; border-radius: 8px; margin: 20px 0; }

.post-footer-tags { margin-top: 50px; padding-top: 30px; border-top: 1px solid #eee; }
.tag-label { font-weight: 700; margin-right: 10px; }
.tag-link { color: var(--primary-color); text-decoration: none; font-weight: 600; }

/* SIDEBAR */
.sidebar-box {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.02);
  margin-bottom: 30px;
}
.side-title {
  font-size: 1.1rem;
  font-weight: 900;
  text-transform: uppercase;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #eee;
}

.related-item { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f9f9f9; }
.related-link { text-decoration: none; color: #222; font-weight: 700; font-size: 0.95rem; line-height: 1.4; display: block; }
.related-link:hover { color: var(--primary-color); }
.related-date { font-size: 0.75rem; color: #aaa; }

.banner-promo {
  background: linear-gradient(135deg, var(--primary-color) 0%, #ff4b5c 100%);
  color: #fff;
  text-align: center;
  padding: 40px 20px;
}
.banner-promo h4 { font-size: 1.5rem; font-weight: 900; margin-bottom: 10px; }

/* LOADING & 404 */
.post-loader, .post-not-found { text-align: center; padding: 100px 0; }
.post-not-found h2 { font-size: 5rem; color: #eee; margin-bottom: 20px; }
.btn-back { display: inline-block; margin-top: 20px; color: var(--primary-color); font-weight: 700; text-decoration: none; }

@media (max-width: 992px) {
  .post-layout { grid-template-columns: 1fr; }
  .post-main { padding: 25px; }
  .post-title { font-size: 2rem; }
}
</style>
