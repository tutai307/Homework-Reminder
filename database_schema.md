# Database Schema - Homework Reminder System

## Bảng và Quan hệ

### 1. `classes` - Lớp học
| Field | Type | Description |
|-------|------|-------------|
| id | bigint (PK) | ID lớp học |
| name | string(50) | Tên lớp (VD: "10A1", "11B2") |
| school_year | string(20) | Năm học (VD: "2024-2025") |
| description | text (nullable) | Mô tả lớp |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

### 2. `subjects` - Môn học
| Field | Type | Description |
|-------|------|-------------|
| id | bigint (PK) | ID môn học |
| name | string(100) | Tên môn (VD: "Toán", "Văn", "Anh") |
| code | string(20) (unique) | Mã môn (VD: "MATH", "LIT") |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

### 3. `timetables` - Thời khóa biểu
| Field | Type | Description |
|-------|------|-------------|
| id | bigint (PK) | ID thời khóa biểu |
| class_id | bigint (FK) | ID lớp học |
| weekday | tinyint | Thứ trong tuần (1=Thứ 2, 2=Thứ 3, ..., 7=Chủ nhật) |
| subject_id | bigint (FK) | ID môn học |
| period | tinyint | Tiết học (1, 2, 3, ...) |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

**Indexes:**
- Unique: `class_id`, `weekday`, `period`

### 4. `homework` - Bài tập hàng ngày
| Field | Type | Description |
|-------|------|-------------|
| id | bigint (PK) | ID bài tập |
| class_id | bigint (FK) | ID lớp học |
| date | date | Ngày bài tập |
| notes | text (nullable) | Ghi chú chung |
| created_by | bigint (FK users) | Người tạo |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

**Indexes:**
- Index: `class_id`, `date`
- Unique: `class_id`, `date`

### 5. `homework_items` - Chi tiết bài tập
| Field | Type | Description |
|-------|------|-------------|
| id | bigint (PK) | ID mục bài tập |
| homework_id | bigint (FK) | ID bài tập (homework) |
| subject_id | bigint (FK) | ID môn học |
| content | text | Nội dung bài tập |
| due_date | date (nullable) | Hạn nộp (nếu khác ngày chính) |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

**Indexes:**
- Index: `homework_id`, `subject_id`

### 6. `tests` - Kiểm tra
| Field | Type | Description |
|-------|------|-------------|
| id | bigint (PK) | ID kiểm tra |
| class_id | bigint (FK) | ID lớp học |
| subject_id | bigint (FK) | ID môn học |
| date | date | Ngày kiểm tra |
| type | enum | Loại: 'oral' (miệng), '15min' (15 phút), '45min' (45 phút) |
| content | text (nullable) | Nội dung/đề bài |
| created_by | bigint (FK users) | Người tạo |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

**Indexes:**
- Index: `class_id`, `date`, `type`

### 7. `users` - Người dùng (Laravel default)
| Field | Type | Description |
|-------|------|-------------|
| id | bigint (PK) | ID người dùng |
| name | string(255) | Tên người dùng |
| email | string(255) (unique) | Email |
| password | string(255) | Mật khẩu |
| role | enum | Vai trò: 'admin', 'teacher' |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

---

## Quan hệ (Relationships)

### Classes
- `hasMany` Timetables
- `hasMany` Homework
- `hasMany` Tests

### Subjects
- `hasMany` Timetables
- `hasMany` HomeworkItems
- `hasMany` Tests

### Timetables
- `belongsTo` Class
- `belongsTo` Subject

### Homework
- `belongsTo` Class
- `belongsTo` User (created_by)
- `hasMany` HomeworkItems

### HomeworkItems
- `belongsTo` Homework
- `belongsTo` Subject

### Tests
- `belongsTo` Class
- `belongsTo` Subject
- `belongsTo` User (created_by)

### Users
- `hasMany` Homework (created_by)
- `hasMany` Tests (created_by)

---

## Ghi chú thiết kế

1. **Timetable**: Một lớp có thể có nhiều môn trong một thứ, phân biệt bằng `period` (tiết học)
2. **Homework**: Mỗi lớp chỉ có một bản ghi bài tập cho mỗi ngày, chi tiết được lưu trong `homework_items`
3. **Tests**: Tách riêng khỏi homework để quản lý dễ dàng hơn
4. **Subjects**: Bảng riêng để dễ quản lý và mở rộng
5. **Date fields**: Sử dụng `date` type cho các trường ngày (không cần giờ)

