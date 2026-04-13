<template>
  <div class="post-container">
    <h1>Danh sách bài viết</h1>

    <!-- Hiển thị lúc đang tải dữ liệu -->
    <p v-if="loading">Đang tải dữ liệu...</p>

    <!-- Báo lỗi nếu API sập -->
    <p v-if="error" style="color: red;">{{ error }}</p>

    <!-- Render danh sách bài viết -->
    <ul v-if="!loading && !error">
      <li v-for="post in posts" :key="post.id" class="post-item">
        <h2>{{ post.title }}</h2>
        <p><strong>Danh mục:</strong> {{ post.menu ? post.menu.name : 'Không có' }}</p>
        <p><strong>Tác giả:</strong> {{ post.user ? post.user.name : 'Ẩn danh' }}</p>
        <p><strong>Tóm tắt:</strong> {{ post.summary }}</p>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// Bỏ import axios gốc đi và import file api của bạn vào
// (Nhớ điều chỉnh đường dẫn '../services/api' cho khớp với cấu trúc thư mục của bạn nhé)
import api from '../services/api';

// Khai báo các biến trạng thái
const posts = ref([]);
const loading = ref(true);
const error = ref(null);

// Hàm gọi API
const fetchPosts = async () => {
  try {
    // Bây giờ chỉ cần gọi '/posts' là nó tự hiểu thành http://lumenapi.test/api/posts
    const response = await api.get('/posts');
    posts.value = response.data;
  } catch (err) {
    error.value = 'Không thể kết nối đến máy chủ Lumen. Hãy kiểm tra lại backend!';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// Gọi hàm ngay khi component vừa được mount lên giao diện
onMounted(() => {
  fetchPosts();
});
</script>

<style scoped>
/* Thêm một chút CSS cho dễ nhìn */
.post-container {
  max-width: 800px;
  margin: 0 auto;
  font-family: Arial, sans-serif;
}
.post-item {
  border: 1px solid #ddd;
  padding: 15px;
  margin-bottom: 15px;
  border-radius: 8px;
  list-style: none;
}
</style>
