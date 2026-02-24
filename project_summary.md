# Dự án: Homework-Reminder (Hệ thống Nhắc lịch Bài tập)

Tài liệu này tóm tắt phạm vi, cấu trúc và chức năng của dự án để AI (GPT/Claude) có thể nhanh chóng nắm bắt và hỗ trợ phát triển.

## 1. Mục tiêu hệ thống
Hệ thống giúp Giáo viên chủ nhiệm hoặc Ban cán sự lớp quản lý thời khóa biểu và giao bài tập hàng ngày cho học sinh. Đặc biệt hỗ trợ xuất mẫu tin nhắn (Zalo) để nhắc nhở phụ huynh/học sinh một cách chuyên nghiệp và đồng bộ.

## 2. Công nghệ sử dụng
- **Backend:** Laravel (PHP)
- **Frontend:** Blade Template, Bootstrap 5, Vanilla CSS
- **AI Integration:** OpenAI/OpenRouter (GPT-4o mini)
- **Thư viện chính:** 
    - Select2 (quản lý dropdown thông minh)
    - Flatpickr (chọn ngày tháng)
    - SweetAlert2 (thông báo popup)
    - Bootstrap Icons

## 3. Các thực thể chính (Models)
- `ClassModel`: Quản lý lớp học (tên lớp, năm học, mã chia sẻ public).
- `Subject`: Quản lý danh mục môn học.
- `Timetable`: Quản lý thời khóa biểu hàng tuần (weekday, period, subject_id, class_id).
- `Homework`: Quản lý đầu việc bài tập theo ngày (chứa ghi chú chung).
- `HomeworkItem`: Chi tiết bài tập cụ thể cho từng môn (homework_id, subject_id, content, due_date).
- `User`: Quản lý người dùng với các vai trò: `Admin`, `Teacher`, `ClassMonitor` (Lớp trưởng), `AcademicSubMonitor` (Lớp phó học tập).

> [!IMPORTANT]
> **Tối ưu Chương trình GDPT 2018:** Hệ thống hỗ trợ hoàn hảo các môn học tích hợp (KHTN, Lịch sử và Địa lí) và các hoạt động giáo dục mới (HĐTN, GDĐP).

## 4. Các chức năng cốt lõi (v2.x)
### Quản lý Thời khóa biểu
- Thiết lập lịch học 6 ngày trong tuần (Thứ 2 - Thứ 7), mỗi ngày tối đa 10 tiết.
- Giao diện lưới (grid) trực quan để chọn môn học cho từng tiết.

### AI Homework Parser (v2.1 - Pipeline 4 giai đoạn)
- **Phase 1: Clean & Normalize:** Chuẩn hóa tiếng Việt, xử lý i/y và loại bỏ dấu hoàn toàn để so khớp chính xác cao.
- **Phase 2: Local Subject Detection:** Sử dụng "Dynamic Subject Dictionary" để tìm môn học từ DB. Nếu không tìm thấy môn -> Không gọi AI (Tiết kiệm Quota).
- **Phase 3: AI Smart Extraction:** AI tự động trích xuất nội dung và quy đổi ngày nộp tương đối (vd: "mai", "thứ 6 tuần sau") thành ngày cụ thể (YYYY-MM-DD).
- **Phase 4: Validation & Grouping:** Gộp các bài tập cùng môn và gán vào đúng đối tượng Model.

### AI Study Plan (v2.2 - Gợi ý thông minh)
- Tự động phân tích danh sách bài tập (khi có >= 2 môn).
- Ưu tiên deadline gần, xen kẽ môn khó/dễ để tối ưu não bộ.
- Ước lượng thời gian học thực tế cho học sinh THCS/THPT.
- **Zalo Optimized:** Output dạng Plain Text (không markdown) để hiển thị đẹp nhất trên ứng dụng Zalo.

### Hệ thống Chia sẻ & Nhắc nhở
- **Landing Page v3:** Giao diện giới thiệu sản phẩm hiện đại, chuyên nghiệp, hỗ trợ dark mode và hiệu ứng glassmorphism.
- **Trang Public Portal:** Mỗi lớp có một link chia sẻ công khai (`/p/{slug}`) để xem bài tập.
- **Copy Zalo Message:** Tạo tin nhắn được format sẵn kèm Gợi ý học tập từ AI.

## 5. Các nâng cấp nổi bật (Phiên bản v2.x)
1. **Xử lý môn học ghép:** Tự động nhận diện và phân rã nội dung cho môn "Lịch sử và Địa lí" (Sử/Địa).
2. **Dynamic Target Date:** Tự động điều hướng tin nhắn Zalo sang ngày mai nếu ngày hiện tại được chọn.
3. **Optimized AI usage:** Cơ chế lọc môn học cục bộ trước khi gọi AI giúp giảm phí API đáng kể.

## 6. Luồng hoạt động chính
1. Admin tạo Lớp học và Môn học.
2. Giáo viên/Lớp trưởng thiết lập Thời khóa biểu.
3. Sử dụng **AI Parse** để nhập nhanh bài tập từ đoạn chat/ghi chú lộn xộn.
4. Nhấn "Copy Message" để gửi Zalo cho phụ huynh (bao gồm nội dung bài tập và kế hoạch học tập AI).
