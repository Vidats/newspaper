<template>
  <div class="auth-wrapper">
    <div class="auth-box">
      <h2>{{ isLoginMode ? 'Đăng Nhập Quản Trị' : 'Đăng Ký Tài Khoản' }}</h2>

      <form @submit.prevent="handleSubmit">
        <!-- Ô Họ Tên: Chỉ hiện khi ở chế độ Đăng Ký -->
        <div class="form-group" v-if="!isLoginMode">
          <label>Họ và Tên</label>
          <input type="text" v-model="form.name" placeholder="Nguyễn Văn A" required />
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" v-model="form.email" placeholder="admin@example.com" required />
        </div>

        <div class="form-group">
          <label>Mật khẩu</label>
          <input type="password" v-model="form.password" placeholder="••••••••" required minlength="6" />
        </div>

        <button type="submit" class="btn-submit" :disabled="loading">
          {{ loading ? 'Đang xử lý...' : (isLoginMode ? 'Đăng Nhập' : 'Đăng Ký') }}
        </button>
      </form>

      <!-- Nút chuyển đổi qua lại giữa Đăng nhập và Đăng ký -->
      <p class="toggle-text" @click="toggleMode">
        {{ isLoginMode ? 'Chưa có tài khoản? Đăng ký ngay' : 'Đã có tài khoản? Đăng nhập' }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const isLoginMode = ref(true); // Mặc định mở lên là form Đăng nhập
const loading = ref(false);

const form = reactive({
  name: '',
  email: '',
  password: ''
});

// Chuyển chế độ form
const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value;
  // Xóa trắng form khi chuyển đổi
  form.name = '';
  form.password = '';
};

// Xử lý khi bấm nút Submit
const handleSubmit = async () => {
  loading.value = true;

  try {
    if (isLoginMode.value) {
      // 1. CHẾ ĐỘ ĐĂNG NHẬP
      const res = await api.post('/login', {
        email: form.email,
        password: form.password
      });

      // Thành công: Lưu token vào kho của trình duyệt
      localStorage.setItem('token', res.data.token);
      localStorage.setItem('user', JSON.stringify(res.data.user)); // Lưu thêm thông tin user cho tiện

      alert('Đăng nhập thành công!');
      router.push('/post'); // Đẩy người dùng vào trang Admin

    } else {
      // 2. CHẾ ĐỘ ĐĂNG KÝ
      await api.post('/register', form);
      alert('Đăng ký thành công! Vui lòng đăng nhập.');
      isLoginMode.value = true; // Chuyển form về lại đăng nhập
      form.password = ''; // Xóa mật khẩu cho an toàn
    }
  } catch (error) {
    // Bắt lỗi (sai pass, email đã tồn tại...)
    const errorMsg = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng kiểm tra lại!';
    alert(errorMsg);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f4f6f9;
}
.auth-box {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 400px;
}
.auth-box h2 {
  text-align: center;
  margin-top: 0;
  margin-bottom: 25px;
  color: #333;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #555;
}
.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
}
.btn-submit {
  width: 100%;
  padding: 12px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  margin-top: 10px;
}
.btn-submit:disabled {
  background-color: #999;
  cursor: not-allowed;
}
.toggle-text {
  text-align: center;
  margin-top: 20px;
  color: #007bff;
  cursor: pointer;
  font-size: 0.9em;
}
.toggle-text:hover {
  text-decoration: underline;
}
</style>
