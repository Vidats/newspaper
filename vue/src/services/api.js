import axios from 'axios';

const api = axios.create({
  baseURL: 'http://lumenapi.test/api',
});

// THÊM INTERCEPTOR: Tự động đính kèm Token vào mọi Request
api.interceptors.request.use((config) => {
  // Lấy token từ kho lưu trữ của trình duyệt
  const token = localStorage.getItem('token');

  // Nếu có token, gắn nó vào Header Authorization
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
}, (error) => {
  return Promise.reject(error);
});

// THÊM INTERCEPTOR: Xử lý lỗi hệ thống (Ví dụ: Token hết hạn)
api.interceptors.response.use((response) => {
  return response;
}, (error) => {
  // Nếu server trả về 401 (Unauthorized)
  if (error.response && error.response.status === 401) {
    console.warn("Token không hợp lệ hoặc đã hết hạn. Đang đăng xuất...");
    localStorage.removeItem('token');
    localStorage.removeItem('user');

    // Nếu đang ở các trang quản trị, hãy đá người dùng về trang chủ
    if (window.location.pathname.startsWith('/menu') ||
        window.location.pathname.startsWith('/media') ||
        window.location.pathname.startsWith('/post')) {
      window.location.href = '/';
    }
  }
  return Promise.reject(error);
});

export default api;
