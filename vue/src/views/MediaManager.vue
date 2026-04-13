<template>
  <div class="admin-page">
    <div class="page-header">
      <h1 class="page-title">Quản lý Thư viện Media</h1>
      <button v-if="!showForm" @click="showForm = true" class="btn-primary">+ Tải lên file mới</button>
    </div>

    <!-- --- UPLOAD CARD --- -->
    <div v-if="showForm || isEditing" class="admin-card mb-4">
      <div class="card-header">
        <h3 class="card-title">{{ isEditing ? 'Cập nhật thông tin file' : 'Tải lên Media mới' }}</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="saveMedia" class="admin-form">
          <div class="form-row">
            <div class="form-group col-6">
              <label>Tên hiển thị</label>
              <input type="text" v-model="form.name" placeholder="Nhập tên gợi nhớ..." required />
            </div>
            <div class="form-group col-6">
              <label>Chọn tệp tin {{ isEditing ? '(Để trống nếu không muốn thay đổi file)' : '' }}</label>
              <input type="file" ref="fileInputDOM" @change="handleFileChange" class="file-input" />
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-success" :disabled="isLoading">
              {{ isLoading ? 'Đang xử lý...' : (isEditing ? 'Cập nhật' : 'Tải lên ngay') }}
            </button>
            <button type="button" class="btn-secondary" @click="cancelEdit">Hủy bỏ</button>
          </div>
        </form>
      </div>
    </div>

    <!-- --- MEDIA GRID / TABLE --- -->
    <div class="admin-card">
      <div class="card-header">
        <h3 class="card-title">Tất cả Media</h3>
      </div>
      <div class="card-body p-0">
        <table class="admin-table">
          <thead>
            <tr>
              <th width="80">ID</th>
              <th width="120">Xem trước</th>
              <th>Tên hiển thị</th>
              <th>Thông tin tệp</th>
              <th width="150" class="text-right">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="media in medias" :key="media.id">
              <td>#{{ media.id }}</td>
              <td>
                <div class="table-img-preview">
                  <img v-if="media.type?.includes('image')" :src="getImageUrl(media.path)" alt="preview" />
                  <div v-else class="file-icon">📄</div>
                </div>
              </td>
              <td><strong>{{ media.name }}</strong></td>
              <td>
                <div class="file-meta">
                  <span>📁 {{ media.path }}</span>
                  <span>🏷️ {{ media.type }}</span>
                </div>
              </td>
              <td class="text-right">
                <div class="action-group">
                  <button class="btn-icon btn-edit" @click="editMedia(media)" title="Sửa">✏️</button>
                  <button class="btn-icon btn-delete" @click="deleteMedia(media.id)" title="Xóa">🗑️</button>
                </div>
              </td>
            </tr>
            <tr v-if="medias.length === 0">
              <td colspan="5" class="text-center py-4">Chưa có dữ liệu media.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../services/api';

const medias = ref([]);
const selectedFile = ref(null);
const isLoading = ref(false);
const showForm = ref(false);
const isEditing = ref(false);
const currentId = ref(null);
const fileInputDOM = ref(null);

const form = reactive({ name: '' });

const getImageUrl = (path) => {
  if (!path) return '';
  return `http://lumenapi.test/storage/${path}`;
};

const fetchMedia = async () => {
  try {
    const res = await api.get('/media');
    medias.value = res.data;
  } catch (error) {
    console.error("Lỗi lấy danh sách media:", error);
  }
};

const handleFileChange = (event) => {
  selectedFile.value = event.target.files[0];
  if (!isEditing.value && selectedFile.value && !form.name) {
    form.name = selectedFile.value.name.split('.').slice(0, -1).join('.');
  }
};

const saveMedia = async () => {
  if (!isEditing.value && !selectedFile.value) {
    alert("Vui lòng chọn một file!");
    return;
  }

  isLoading.value = true;
  const formData = new FormData();
  formData.append('name', form.name);
  if (selectedFile.value) {
    formData.append('file', selectedFile.value);
  }

  try {
    if (isEditing.value) {
      formData.append('_method', 'PUT');
      await api.post(`/media/${currentId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      await api.post('/media', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }
    cancelEdit();
    fetchMedia();
  } catch (error) {
    alert("Có lỗi xảy ra khi lưu media!");
  } finally {
    isLoading.value = false;
  }
};

const editMedia = (media) => {
  isEditing.value = true;
  showForm.value = true;
  currentId.value = media.id;
  form.name = media.name;
};

const deleteMedia = async (id) => {
  if (confirm('Xóa file này vĩnh viễn?')) {
    try {
      await api.delete(`/media/${id}`);
      fetchMedia();
    } catch (error) {
      console.error("Lỗi xóa media:", error);
    }
  }
};

const cancelEdit = () => {
  showForm.value = false;
  isEditing.value = false;
  currentId.value = null;
  form.name = '';
  selectedFile.value = null;
  if (fileInputDOM.value) fileInputDOM.value.value = '';
};

onMounted(fetchMedia);
</script>

<style scoped>
.admin-page { animation: fadeIn 0.5s ease; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #333; margin: 0; }
.admin-card { background: #fff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #edf2f7; overflow: hidden; }
.card-header { padding: 20px 25px; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center; background: #fafafa; }
.card-title { margin: 0; font-size: 1.1rem; font-weight: 700; color: #4a5568; }
.card-body { padding: 25px; }
.p-0 { padding: 0 !important; }
.mb-4 { margin-bottom: 1.5rem; }
.admin-form .form-row { display: flex; flex-wrap: wrap; margin: 0 -10px 15px; }
.form-group { padding: 0 10px; margin-bottom: 15px; }
.col-6 { width: 50%; }
.form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #4a5568; font-size: 0.9rem; }
.form-group input { width: 100%; padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 6px; outline: none; }
.file-input { padding: 7px !important; background: #f8fafc !important; }
.form-actions { display: flex; gap: 10px; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th { background: #f8fafc; padding: 15px 25px; text-align: left; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #edf2f7; }
.admin-table td { padding: 15px 25px; border-bottom: 1px solid #edf2f7; color: #4a5568; vertical-align: middle; }
.table-img-preview img { width: 100px; height: 60px; object-fit: cover; border-radius: 6px; }
.file-icon { width: 100px; height: 60px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 2rem; border-radius: 6px; }
.file-meta { display: flex; flex-direction: column; gap: 5px; font-size: 0.85rem; color: #718096; }
.btn-primary { background: var(--admin-accent, #007bff); color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-success { background: #38a169; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-secondary { background: #edf2f7; color: #4a5568; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-icon { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 5px; border-radius: 4px; }
.btn-edit:hover { background: #feebc8; }
.btn-delete:hover { background: #fed7d7; }
.text-right { text-align: right; }
.text-center { text-align: center; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
