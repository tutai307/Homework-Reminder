# 📚 Homework Reminder System
> **🚀 Ready for 2026:** Hệ thống đã sẵn sàng và tương thích hoàn toàn với chương trình GDPT 2018 áp dụng cho mọi khối lớp cấp 2 từ năm học 2025-2026.

Hệ thống quản lý thời khóa biểu và giao bài tập hàng ngày thông minh, hỗ trợ AI và thông báo cho phụ huynh qua Zalo.

## ✨ Tính năng nổi bật

### 🤖 AI Smart Entry & Planner (Nhập liệu & Lập kế hoạch)
- Tự động phân tách bài tập từ văn bản tự do với Pipeline 4 giai đoạn.
- **AI Study Plan:** Tự động đề xuất kế hoạch học tập tối ưu dựa trên lượng bài tập và deadline.
- Nhận diện môn học thông minh qua tên viết tắt và hỗ trợ môn học ghép (Sử/Địa).
- Tự động tính toán hạn nộp (Deadline) dựa trên thời khóa biểu thực tế.

### 📅 Quản lý bài tập & Preview
- **Nhóm theo môn học:** Giao diện tối ưu, gộp các tiết trùng môn để nhập liệu nhanh hơn.
- **Preview Modal:** Xem tổng hợp bài tập cần làm và đến hạn nộp cho các ngày trong tương lai.
- **Tách biệt hiển thị:** Phân biệt rõ giữa bài tập "được giao" và bài tập "đến hạn nộp".

### 💬 Kết nối Zalo & Public Portal
- **Zalo Message Template:** Tự động tạo tin nhắn báo cáo bài tập chuyên nghiệp, gộp môn học gọn gàng.
- **Public Portal:** Link chia sẻ công khai cho từng lớp học, không cần đăng nhập vẫn xem được bài tập.

### 🔐 Phân quyền & Quản lý
- Hỗ trợ vai trò: Admin, Giáo viên, Lớp trưởng/Lớp phó.
- Bảo toàn lịch sử: Chỉ cho phép sửa/xóa bài tập hiện tại và tương lai.

## 🛠 Công nghệ sử dụng
- **Framework:** Laravel 10+
- **Frontend:** Bootstrap 5, Select2, Flatpickr, SweetAlert2.
- **AI Integration:** OpenAI API (sử dụng API của OpenRouter)

## 🚀 Cài đặt nhanh
1. `composer install`
2. `npm install && npm run build`
3. `cp .env.example .env`
4. `php artisan key:generate`
5. `php artisan migrate --seed`
6. `php artisan serve`

---
*Phát triển bởi CBGV Nguyễn Thị Hường - lớp 7C - Trường TH và THCS Đông Mai*
