<template>
  <div class="admin-page">
    <div class="page-header">
      <h1 class="page-title">Quản lý Chuyên mục (Menu)</h1>
      <button v-if="!showForm" @click="showForm = true" class="btn-primary">+ Thêm chuyên mục mới</button>
    </div>

    <!-- --- FORM CARD --- -->
    <div v-if="showForm || isEditing" class="admin-card mb-4">
      <div class="card-header">
        <h3 class="card-title">{{ isEditing ? 'Cập nhật chuyên mục' : 'Thêm chuyên mục mới' }}</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="saveMenu" class="admin-form">
          <div class="form-row">
            <div class="form-group col-6">
              <label>Tên chuyên mục</label>
              <input type="text" v-model="form.name" placeholder="Ví dụ: Thời sự, Thể thao..." required />
            </div>
            <div class="form-group col-6">
              <label>Mô tả ngắn</label>
              <input type="text" v-model="form.description" placeholder="Mô tả về chuyên mục này..." />
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-success">{{ isEditing ? 'Cập nhật' : 'Lưu lại' }}</button>
            <button type="button" class="btn-secondary" @click="cancelEdit">Hủy bỏ</button>
          </div>
        </form>
      </div>
    </div>

    <!-- --- TABLE CARD --- -->
    <div class="admin-card">
      <div class="card-header">
        <h3 class="card-title">Danh sách chuyên mục</h3>
      </div>
      <div class="card-body p-0">
        <table class="admin-table">
          <thead>
            <tr>
              <th width="80">ID</th>
              <th>Tên chuyên mục</th>
              <th>Mô tả</th>
              <th width="150" class="text-right">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="menu in menus" :key="menu.id">
              <td>#{{ menu.id }}</td>
              <td><strong>{{ menu.name }}</strong></td>
              <td>{{ menu.description || '---' }}</td>
              <td class="text-right">
                <div class="action-group">
                  <button class="btn-icon btn-edit" @click="editMenu(menu)" title="Sửa">✏️</button>
                  <button class="btn-icon btn-delete" @click="deleteMenu(menu.id)" title="Xóa">🗑️</button>
                </div>
              </td>
            </tr>
            <tr v-if="menus.length === 0">
              <td colspan="4" class="text-center py-4">Chưa có chuyên mục nào.</td>
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

const menus = ref([]);
const showForm = ref(false);
const isEditing = ref(false);
const currentId = ref(null);

const form = reactive({ name: '', description: '' });

const fetchMenus = async () => {
  try {
    const res = await api.get('/menus');
    menus.value = res.data;
  } catch (error) {
    console.error("Lỗi lấy danh sách menu:", error);
  }
};

const saveMenu = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/menus/${currentId.value}`, form);
    } else {
      await api.post('/menus', form);
    }
    cancelEdit();
    fetchMenus();
  } catch (error) {
    alert("Có lỗi xảy ra khi lưu chuyên mục!");
  }
};

const editMenu = (menu) => {
  isEditing.value = true;
  showForm.value = true;
  currentId.value = menu.id;
  form.name = menu.name;
  form.description = menu.description;
};

const deleteMenu = async (id) => {
  if (confirm('Bạn có chắc muốn xóa chuyên mục này?')) {
    try {
      await api.delete(`/menus/${id}`);
      fetchMenus();
    } catch (error) {
      console.error("Lỗi xóa menu:", error);
    }
  }
};

const cancelEdit = () => {
  showForm.value = false;
  isEditing.value = false;
  currentId.value = null;
  form.name = '';
  form.description = '';
};

onMounted(fetchMenus);
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
.form-group input:focus { border-color: var(--admin-accent, #007bff); }
.form-actions { display: flex; gap: 10px; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th { background: #f8fafc; padding: 15px 25px; text-align: left; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #edf2f7; }
.admin-table td { padding: 15px 25px; border-bottom: 1px solid #edf2f7; color: #4a5568; }
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
