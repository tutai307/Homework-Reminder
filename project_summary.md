# Dự án: Homework-Reminder (Hệ thống Nhắc lịch Bài tập)

Tài liệu này tóm tắt phạm vi, cấu trúc và chức năng của dự án để AI (GPT/Claude) có thể nhanh chóng nắm bắt và hỗ trợ phát triển.

## 1. Mục tiêu hệ thống
Hệ thống giúp Giáo viên chủ nhiệm hoặc Ban cán sự lớp quản lý thời khóa biểu và giao bài tập hàng ngày cho học sinh. Đặc biệt hỗ trợ xuất mẫu tin nhắn (Zalo) để nhắc nhở phụ huynh/học sinh một cách chuyên nghiệp và đồng bộ.

## 2. Công nghệ sử dụng
- **Backend:** Laravel (PHP)
- **Frontend:** Blade Template, Bootstrap 5, Vanilla CSS
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

## 4. Các chức năng cốt lõi
### Quản lý Thời khóa biểu
- Thiết lập lịch học 6 ngày trong tuần (Thứ 2 - Thứ 7), mỗi ngày tối đa 10 tiết.
- Giao diện lưới (grid) trực quan để chọn môn học cho từng tiết.

### Quản lý Bài tập hàng ngày
- Giao diện lịch (Calendar) để xem bài tập theo ngày.
- Tự động gợi ý môn học dựa trên thời khóa biểu của ngày được chọn.
- Tự động tính toán `due_date` (hạn nộp) dựa trên tiết học tiếp theo của môn đó trong thời khóa biểu.

### Hệ thống Chia sẻ & Nhắc nhở
- **Trang Public Portal:** Mỗi lớp có một link chia sẻ công khai (`/p/{slug}`) để phụ huynh/học sinh xem mà không cần đăng nhập.
- **Copy Zalo Message:** Tính năng cực kỳ quan trọng, tạo ra chuỗi văn bản được format sẵn để gửi vào nhóm lớp:
    - Liệt kê bài tập cần làm cho ngày mai.
    - Nhắc nhở các bài tập của các môn có tiết ngày mai nhưng chưa đến hạn (nhắc làm sớm).
    - Đính kèm link Portal để xem chi tiết.

## 5. Phân quyền (Roles)
- **Admin:** Toàn quyền quản lý hệ thống, lớp học, môn học và người dùng.
- **Teacher:** Quản lý bài tập và thời khóa biểu của lớp học được phân công.
- **ClassMonitor / AcademicSubMonitor:** Có quyền giao bài tập và chỉnh sửa thời khóa biểu của lớp mình (tùy cấu hình logic).

## 6. Luồng hoạt động chính
1. Admin tạo Lớp học và Môn học.
2. Giáo viên/Lớp trưởng thiết lập Thời khóa biểu cho lớp.
3. Hàng ngày, người có quyền truy cập vào mục "Bài tập hàng ngày", chọn ngày và nhập nội dung bài tập cho các môn.
4. Nhấn "Copy Message" để lấy nội dung gửi lên nhóm Zalo lớp.
5. Phụ huynh nhấn vào link trong tin nhắn để xem chi tiết trên Portal.
