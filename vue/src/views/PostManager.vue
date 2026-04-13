<template>
  <div class="admin-page">
    <div class="page-header">
      <h1 class="page-title">Quản lý Bài viết</h1>
      <button v-if="!showForm" @click="showForm = true" class="btn-primary">+ Thêm bài viết mới</button>
    </div>

    <!-- --- FORM CARD --- -->
    <div v-if="showForm || isEditing" class="admin-card form-card mb-4">
      <div class="card-header">
        <h3 class="card-title">{{ isEditing ? 'Chỉnh sửa bài viết' : 'Đăng bài viết mới' }}</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="savePost" class="admin-form">
          <div class="form-row">
            <div class="form-group col-12">
              <label>Tiêu đề bài viết</label>
              <input type="text" v-model="form.title" placeholder="Nhập tiêu đề hấp dẫn..." required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-12">
              <label>Tóm tắt ngắn</label>
              <textarea v-model="form.summary" placeholder="Mô tả ngắn gọn nội dung bài viết..." rows="2"></textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-12">
              <label>Nội dung chi tiết</label>
              <textarea v-model="form.content" placeholder="Viết nội dung bài viết ở đây..." required rows="8"></textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              <label>Chuyên mục</label>
              <select v-model="form.menu_id" required>
                <option value="" disabled>-- Chọn chuyên mục --</option>
                <option v-for="menu in menus" :key="menu.id" :value="menu.id">
                  {{ menu.name }}
                </option>
              </select>
            </div>
            <div class="form-group col-6">
              <label>Hình ảnh đại diện</label>
              <select v-model="form.media_id">
                <option value="">-- Không có ảnh --</option>
                <option v-for="media in medias" :key="media.id" :value="media.id">
                  {{ media.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-success">{{ isEditing ? 'Cập nhật' : 'Lưu bài viết' }}</button>
            <button type="button" class="btn-secondary" @click="cancelEdit">Hủy bỏ</button>
          </div>
        </form>
      </div>
    </div>

    <!-- --- TABLE CARD --- -->
    <div class="admin-card">
      <div class="card-header">
        <h3 class="card-title">Danh sách bài viết</h3>
        <div class="card-tools">
          <input type="text" placeholder="Tìm kiếm..." class="table-search" />
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th width="50">ID</th>
                <th width="100">Ảnh</th>
                <th>Thông tin bài viết</th>
                <th>Chuyên mục</th>
                <th>Tác giả</th>
                <th width="150" class="text-right">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="post in posts" :key="post.id">
                <td>#{{ post.id }}</td>
                <td>
                  <div class="table-img">
                    <img v-if="post.media" :src="getImageUrl(post.media.path)" alt="thumb" />
                    <span v-else class="no-img">No Img</span>
                  </div>
                </td>
                <td>
                  <div class="post-info">
                    <h5 class="table-post-title">{{ post.title }}</h5>
                    <p class="table-post-summary">{{ post.summary }}</p>
                  </div>
                </td>
                <td><span class="admin-badge">{{ post.menu?.name || 'Chưa phân loại' }}</span></td>
                <td>{{ post.user?.name || 'Admin' }}</td>
                <td class="text-right">
                  <div class="action-group">
                    <button class="btn-icon btn-edit" @click="editPost(post)" title="Sửa">✏️</button>
                    <button class="btn-icon btn-delete" @click="deletePost(post.id)" title="Xóa">🗑️</button>
                  </div>
                </td>
              </tr>
              <tr v-if="posts.length === 0">
                <td colspan="6" class="text-center py-4">Chưa có bài viết nào.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../services/api';

const posts = ref([]);
const menus = ref([]);
const medias = ref([]);
const showForm = ref(false);
const isEditing = ref(false);
const currentId = ref(null);

const form = reactive({
  title: '',
  summary: '',
  content: '',
  menu_id: '',
  media_id: ''
});

const getImageUrl = (path) => {
  if (!path) return '';
  return `http://lumenapi.test/storage/${path}`;
};

const fetchData = async () => {
  try {
    const [postRes, menuRes, mediaRes] = await Promise.all([
      api.get('/posts'),
      api.get('/menus'),
      api.get('/media')
    ]);
    posts.value = postRes.data;
    menus.value = menuRes.data;
    medias.value = mediaRes.data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu:", error);
  }
};

const savePost = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/posts/${currentId.value}`, form);
    } else {
      await api.post('/posts', form);
    }
    cancelEdit();
    fetchData();
  } catch (error) {
    alert("Có lỗi xảy ra khi lưu bài viết!");
  }
};

const editPost = (post) => {
  isEditing.value = true;
  showForm.value = true;
  currentId.value = post.id;
  form.title = post.title;
  form.summary = post.summary || '';
  form.content = post.content;
  form.menu_id = post.menu_id;
  form.media_id = post.media_id || '';
};

const deletePost = async (id) => {
  if (confirm('Bạn chắc chắn muốn xóa bài viết này?')) {
    try {
      await api.delete(`/posts/${id}`);
      fetchData();
    } catch (error) {
      console.error("Lỗi khi xóa bài viết:", error);
    }
  }
};

const cancelEdit = () => {
  showForm.value = false;
  isEditing.value = false;
  currentId.value = null;
  form.title = '';
  form.summary = '';
  form.content = '';
  form.menu_id = '';
  form.media_id = '';
};

onMounted(fetchData);
</script>

<style scoped>
.admin-page {
  animation: fadeIn 0.5s ease;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
  margin: 0;
}

/* --- ADMIN CARDS --- */
.admin-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  border: 1px solid #edf2f7;
  overflow: hidden;
}

.card-header {
  padding: 20px 25px;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafafa;
}

.card-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #4a5568;
}

.card-body {
  padding: 25px;
}

.p-0 { padding: 0 !important; }
.mb-4 { margin-bottom: 1.5rem; }

/* --- FORM ELEMENTS --- */
.admin-form .form-row {
  display: flex;
  flex-wrap: wrap;
  margin: 0 -10px 15px;
}

.form-group {
  padding: 0 10px;
  margin-bottom: 15px;
}

.col-12 { width: 100%; }
.col-6 { width: 50%; }

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #4a5568;
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px 15px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #fff;
  transition: all 0.3s;
  outline: none;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

/* --- TABLES --- */
.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th {
  background: #f8fafc;
  padding: 15px 25px;
  text-align: left;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  border-bottom: 2px solid #edf2f7;
}

.admin-table td {
  padding: 15px 25px;
  border-bottom: 1px solid #edf2f7;
  vertical-align: middle;
  color: #4a5568;
}

.admin-table tr:hover {
  background: #f1f5f9;
}

.table-img img {
  width: 80px;
  height: 50px;
  object-fit: cover;
  border-radius: 6px;
}

.no-img {
  width: 80px;
  height: 50px;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: #94a3b8;
  border-radius: 6px;
}

.table-post-title {
  margin: 0 0 5px 0;
  font-weight: 700;
  color: #1a202c;
}

.table-post-summary {
  margin: 0;
  font-size: 0.85rem;
  color: #718096;
  max-width: 400px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.admin-badge {
  background: #ebf8ff;
  color: #2b6cb0;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* --- BUTTONS --- */
.btn-primary { background: var(--admin-accent, #007bff); color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-success { background: #38a169; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-secondary { background: #edf2f7; color: #4a5568; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-icon { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 5px; border-radius: 4px; transition: background 0.2s; }
.btn-edit:hover { background: #feebc8; }
.btn-delete:hover { background: #fed7d7; }

.text-right { text-align: right; }
.text-center { text-align: center; }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .col-6 { width: 100%; }
}
</style>
