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

> [!IMPORTANT]
> **Cập nhật 2025:** Hệ thống đã được tối ưu hóa hoàn toàn cho chương trình giáo dục phổ thông 2018, hỗ trợ các môn học tích hợp (KHTN, Lịch sử & Địa lí) và các hoạt động giáo dục mới áp dụng đại trà từ năm học 2024-2025.

## 4. Các chức năng cốt lõi
### Quản lý Thời khóa biểu
- Thiết lập lịch học 6 ngày trong tuần (Thứ 2 - Thứ 7), mỗi ngày tối đa 10 tiết.
- Giao diện lưới (grid) trực quan để chọn môn học cho từng tiết.

### Quản lý Bài tập hàng ngày
- **Giao diện lịch (Calendar):** Xem bài tập theo ngày một cách trực quan.
- **Nhóm theo Môn học:** Tự động gộp các tiết học trùng môn để giáo viên chỉ cần nhập bài tập một lần cho mỗi môn.
- **AI Smart Entry (Nhập nhanh):** Tích hợp AI để phân tách đoạn văn bản tự do thành các mục bài tập dựa trên thời khóa biểu thực tế. Hiểu các từ viết tắt môn học chuẩn Việt Nam.
- **Thông minh hóa Deadline:** Tự động tính toán `due_date` và quy đổi ngôn ngữ tự nhiên (vd: "hạn tuần sau") thành ngày cụ thể.

### Hệ thống Chia sẻ & Nhắc nhở
- **Trang Public Portal:** Mỗi lớp có một link chia sẻ công khai (`/p/{slug}`) để xem bài tập.
- **Xem trước bài tập (Preview - New!):** Tính năng cho các ngày tương lai, hiển thị toàn bộ bài tập giao và bài tập đến hạn dưới dạng card chuyên nghiệp.
- **Copy Zalo Message:** Tạo tin nhắn được format sẵn. Tự động gộp bài tập theo môn học để tin nhắn ngắn gọn, chuyên nghiệp.

## 5. Các nâng cấp nổi bật (Phiên bản v1.2)
1. **Tách biệt hiển thị:** 
   - Trang chính: Chỉ hiện bài tập được giao mới trong ngày.
   - Modal Preview: Hiện tổng hợp bài tập giao + bài tập đến hạn (giúp học sinh biết mình cần làm gì).
2. **Quản lý xóa/sửa:** Cho phép xóa/sửa bài tập hôm nay và tương lai; khóa dữ liệu quá khứ để bảo toàn lịch sử.
3. **AI subject detection:** Nhận diện môn học thông minh qua tên viết tắt (vd: CN, AV, VL).

## 6. Luồng hoạt động chính
1. Admin tạo Lớp học và Môn học.
2. Giáo viên/Lớp trưởng thiết lập Thời khóa biểu.
3. Sử dụng **AI Parse** để nhập nhanh toàn bộ bài tập trong ngày từ một đoạn chat/ghi chú.
4. Click chuyển ngày để **Xem trước (Preview)** các bài tập cần làm cho các ngày sắp tới.
5. Nhấn "Copy Message" để gửi Zalo cho phụ huynh.
