# BỘ DỮ LIỆU ĐẦU VÀO CẦN THU THẬP ĐỂ XÂY DỰNG BÁO CÁO PHÁT TRIỂN PHẦN MỀM HƯỚNG DỊCH VỤ

> Mục đích: dùng như checklist/prompt đầu vào cho AI hoặc thành viên nhóm khi cần viết một báo cáo có cấu trúc tương tự báo cáo mẫu, nhưng nội dung phải phản ánh đúng project thực tế và đúng logic của source code.
>
> Nguyên tắc cốt lõi: **Code/Database/Route đang chạy là nguồn sự thật chính**. Không được suy diễn chức năng chỉ từ tên đề tài, README hoặc giao diện nếu source code không chứng minh.

---

# 0. QUY TẮC PHÂN TÍCH BẮT BUỘC

## 0.1. Thứ tự ưu tiên nguồn sự thật

Khi các nguồn mâu thuẫn, ưu tiên theo thứ tự:

1. Source code implementation đang được sử dụng
2. Route/API đang được đăng ký thực tế
3. Migration / schema / database hiện tại
4. Test code / Postman collection
5. Frontend đang gọi backend
6. Cấu hình runtime / environment
7. README / tài liệu kỹ thuật
8. Mô tả đề tài hoặc tài liệu cũ

Không lấy nội dung lý thuyết hoặc mô tả trong tài liệu cũ để thay thế implementation hiện tại.

## 0.2. Trạng thái chức năng

Mỗi chức năng/module phải được gắn một trong các trạng thái:

- `IMPLEMENTED`: đã có implementation đủ để sử dụng
- `PARTIAL`: có code nhưng chưa hoàn chỉnh
- `DESIGNED_ONLY`: mới có route/UI/placeholder/tài liệu, chưa có nghiệp vụ hoàn chỉnh
- `NOT_FOUND`: không tìm thấy trong project
- `NEEDS_RUNTIME_VERIFY`: source có nhưng cần chạy thật để xác nhận

## 0.3. Không tự tạo thành phần

Không tự thêm Actor, Use Case, API, Controller, Service, Repository, Entity, Table, DTO, External Service, WebSocket, Queue, Scheduler, Microservice, cổng thanh toán, công nghệ hoặc quy trình nghiệp vụ nếu project không có bằng chứng.

---

# 1. THÔNG TIN TỔNG QUAN ĐỀ TÀI

Đây là dữ liệu đầu vào để viết:
- Lời mở đầu
- Đặt vấn đề
- Tính cấp thiết
- Mục tiêu đề tài
- Phạm vi đề tài

| Dữ liệu | Nội dung cần có |
|---|---|
| Tên đề tài | Tên chính thức của project |
| Lĩnh vực | Ví dụ: thương mại điện tử, đặt phòng, quản lý sinh viên... |
| Bài toán thực tế | Project đang giải quyết vấn đề gì |
| Đối tượng sử dụng | Ai sử dụng hệ thống |
| Mục tiêu hệ thống | Hệ thống cho phép làm gì |
| Phạm vi | Những chức năng nào nằm trong đề tài |
| Ngoài phạm vi | Những phần không thực hiện |
| Vấn đề của cách làm cũ | Nếu có căn cứ thực tế |
| Giá trị hệ thống | Lợi ích cho người dùng/quản trị |
| Tình trạng project | Prototype / đồ án / production-like |

---

# 2. THÔNG TIN CÔNG NGHỆ

## 2.1. Backend

Cần lấy:
- Ngôn ngữ và phiên bản
- Framework và phiên bản
- Build tool / package manager
- ORM
- Security framework
- Validation framework
- Logging
- Test framework
- Server/runtime
- Port mặc định

| Thuộc tính | Giá trị thực tế | File chứng minh |
|---|---|---|
| Language | | |
| Framework | | |
| ORM | | |
| Security | | |
| Build Tool | | |
| Test Framework | | |
| Port | | |

## 2.2. Frontend

Cần lấy:
- Framework/library thực tế
- CSR / SSR / server-rendered
- Router
- State management
- CSS/UI framework
- HTTP client
- Build tool
- Component structure
- Port
- Authentication handling

Không ghi React/Vue/Angular nếu frontend thực tế không dùng.

## 2.3. Database

Cần lấy:
- DBMS
- Version nếu xác định được
- ORM/query layer
- Database name
- Connection config
- Migration mechanism
- Seed data
- Transaction usage
- Index/constraint quan trọng

## 2.4. Công cụ hỗ trợ

Chỉ lấy những công cụ project thực sự sử dụng:
- IDE
- Git/GitHub/GitLab
- Postman
- Docker
- Swagger/OpenAPI
- CI/CD
- Database client
- Testing tools
- Deployment platform

---

# 3. KIẾN TRÚC VÀ PHONG CÁCH THIẾT KẾ

## 3.1. Kiểu kiến trúc thực tế

Xác định các kiểu phù hợp với code:
- Monolith
- Modular Monolith
- Client-Server
- Three-Tier
- Layered Architecture
- Microservices
- MVC
- RESTful API
- Server-rendered MVC

## 3.2. Các tầng/thành phần

Thu thập luồng thực tế:

```text
Client
→ Router
→ Middleware/Security
→ Controller
→ Service
→ Repository/ORM
→ Database
```

Nếu project không có `Repository`, phải ghi đúng như code, ví dụ:

```text
Controller
→ Service
→ Eloquent Model
→ Database
```

## 3.3. Luồng request tổng quát

Cần biết:
- Request bắt đầu ở đâu
- Authentication được kiểm tra ở đâu
- Validation ở đâu
- Business logic ở đâu
- Data access ở đâu
- Transaction ở đâu
- Response trả theo dạng nào

---

# 4. ACTOR / ROLE / QUYỀN

| Actor | Loại | Mô tả | Quyền | Route/Module | Bằng chứng |
|---|---|---|---|---|---|

Phân biệt:
- Actor người dùng
- Actor hệ thống ngoài
- Actor tự động như Scheduler/Cron/Worker nếu có

Không coi Controller/Service/Database là actor.

---

# 5. DANH SÁCH MODULE CHỨC NĂNG

| Module | Chức năng chính | Actor | Code chính | Trạng thái |
|---|---|---|---|---|

Tên module phải bám source thực tế.

---

# 6. DANH SÁCH CHỨC NĂNG CHI TIẾT

| ID | Chức năng | Actor | Module | Trigger | Kết quả | Trạng thái |
|---|---|---|---|---|---|---|

Mỗi chức năng phải truy được tới:
- Route
- Method
- Controller
- Service
- Model/Entity
- Table
- Frontend screen

---

# 7. YÊU CẦU PHI CHỨC NĂNG

## 7.1. Security
Thu thập:
- Authentication
- Authorization
- Role middleware
- JWT/session/OAuth
- Password hashing
- CSRF/CORS
- File upload validation
- API signature
- Secret handling
- Ownership check

## 7.2. Performance
Nếu có bằng chứng:
- Pagination
- Cache
- Index
- Query optimization
- Lock
- Transaction
- Timeout
- Async processing

Không tự ghi chỉ tiêu hiệu năng nếu chưa đo.

## 7.3. Reliability
- Transaction
- Rollback
- Idempotency
- Retry
- Exception handling
- Duplicate prevention
- Unique constraints

## 7.4. Usability
- Responsive
- Validation message
- Search/filter
- Pagination
- Accessibility nếu có

## 7.5. Maintainability / Scalability
- Module separation
- Service layer
- Interface
- Dependency injection
- Docker
- Environment config
- External services abstraction

---

# 8. DỮ LIỆU CHO SƠ ĐỒ PHÂN RÃ CHỨC NĂNG

Cần:
1. Tên hệ thống
2. Nhóm chức năng cấp 1
3. Chức năng con cấp 2
4. Chức năng con cấp 3 nếu thật sự cần
5. Trạng thái implementation

```text
SYSTEM
├── Module A
│   ├── Function A1
│   ├── Function A2
│   └── Function A3
└── Module B
    ├── Function B1
    └── Function B2
```

---

# 9. DỮ LIỆU CHO USE CASE DIAGRAM

## 9.1. Use Case tổng quát
Cần:
- System boundary
- Actor
- Use Case
- Association

## 9.2. Use Case phân rã
Cần:
- Actor
- Chức năng
- `include`
- `extend`
- Generalization nếu có

Không dùng `include/extend` tùy ý.

## 9.3. Đặc tả Use Case

| Trường | Nội dung |
|---|---|
| ID | |
| Tên | |
| Mô tả | |
| Actor | |
| Trigger | |
| Tiền điều kiện | |
| Luồng chính | |
| Luồng thay thế | |
| Ngoại lệ | |
| Hậu điều kiện | |
| API liên quan | |
| Dữ liệu thay đổi | |

---

# 10. DỮ LIỆU CHO ACTIVITY DIAGRAM

Với mỗi nghiệp vụ cần:

```text
Tên nghiệp vụ
Actor bắt đầu
Trigger
Tiền điều kiện

Luồng:
1.
2.
3.

Decision:
- Nếu ...
- Nếu ...

Validation:
- ...

Ngoại lệ:
- ...

Hậu điều kiện:
- ...

Status thay đổi:
- ...

Table/Entity bị thay đổi:
- ...
```

---

# 11. DỮ LIỆU CHO SEQUENCE DIAGRAM

## 11.1. Participants

Chỉ dùng participant thực sự có trong code:

```text
Actor
Frontend
Router/API Client
Middleware
Controller
Service
Repository/ORM
Database
External Service
```

## 11.2. Call Flow

Cần truy chính xác:

```text
1. Actor thao tác
2. Frontend gửi request
3. Route nhận request
4. Middleware kiểm tra
5. Controller xử lý
6. Service xử lý nghiệp vụ
7. Data access query DB
8. External service nếu có
9. Data trả ngược
10. Response cho client
```

## 11.3. Alternative / Error Flow

Lấy từ code:
- Validation fail
- Unauthorized
- Forbidden
- Not found
- Conflict
- Business rule fail
- External API fail
- Database fail

---

# 12. DỮ LIỆU CHO CLASS DIAGRAM

Ưu tiên:
- Domain Entity
- Model
- Service
- Controller quan trọng
- Interface
- Enum
- Security class quan trọng

| Class | Loại | Field quan trọng | Method quan trọng | Extends/Implements |
|---|---|---|---|---|

Quan hệ cần xác định:
- Association
- Dependency
- Inheritance
- Implementation
- One-to-One
- One-to-Many
- Many-to-Many

---

# 13. DỮ LIỆU CHO ERD

Nguồn ưu tiên:
1. Migration
2. Database schema/dump
3. Entity/Model mapping
4. Query
5. Documentation

| Table | Column | Type | PK | FK | Nullable | Unique | Default |
|---|---|---|---|---|---|---|---|

Ngoài ra cần:
- Index
- Composite unique
- Cascade
- `nullOnDelete`
- Pivot table
- Soft delete
- Enum/status
- Timestamps

---

# 14. RELATIONAL SCHEMA

```text
TABLE_A(
    id PK,
    field_1,
    field_2,
    table_b_id FK -> TABLE_B.id
)
```

Dùng tên bảng/cột thật nếu báo cáo yêu cầu physical schema.

---

# 15. DỮ LIỆU CHO SYSTEM ARCHITECTURE

Cần thu thập:

## Client
- Browser
- Mobile
- Desktop
- External client

## Frontend
- Framework
- Router
- State
- UI
- HTTP communication

## Backend
- Framework
- Layer
- Module
- Security
- API

## Data Layer
- Database
- ORM
- Storage
- Cache

## External Systems
- OAuth
- Email
- Shipping
- Payment
- AI
- Maps
- Cloud storage
- Other API

## Infrastructure
- Nginx
- Docker
- Reverse proxy
- Application server
- DB container
- Volume

---

# 16. DỮ LIỆU CHO API ENDPOINTS

| Method | Endpoint | Actor/Role | Auth | Controller | Service | Request | Response | Status |
|---|---|---|---|---|---|---|---|---|

Nên bổ sung:
- Path variable
- Query parameter
- Request body
- Multipart
- Validation
- Response structure
- HTTP status
- Error response
- External service liên quan

---

# 17. CẤU TRÚC BACKEND

Cung cấp cây thư mục thực tế và mô tả vai trò.

| Folder/Package | Vai trò | Class tiêu biểu |
|---|---|---|

Không ép project phải có `repository`, `dto`, `service` nếu thực tế không có.

---

# 18. CẤU TRÚC FRONTEND

Cần:
- Trang chính
- Component chính
- API client
- Router
- Auth guard
- State
- Form
- Validation
- Upload
- Realtime/polling nếu có

---

# 19. MAPPING FRONTEND ↔ BACKEND ↔ DATABASE

| Chức năng | Màn hình FE | Route/API | Controller | Service | Model/Entity | Table |
|---|---|---|---|---|---|---|

Đây là bảng gốc để kiểm tra logic xuyên suốt báo cáo.

---

# 20. DỮ LIỆU MÔI TRƯỜNG PHÁT TRIỂN

| Hạng mục | Thông tin |
|---|---|
| OS | |
| JDK/PHP/Node/Python | |
| Backend framework | |
| Frontend framework | |
| DBMS | |
| IDE | |
| Build tool | |
| Package manager | |
| Git | |
| Postman | |
| Docker | |
| Browser | |

Ngoài ra:
- Port backend
- Port frontend
- Port database
- Environment variables
- Cách chạy project
- Build command
- Migration command
- Seed command
- Test command

---

# 21. DỮ LIỆU TRIỂN KHAI BACKEND

Cần mô tả từ code:
- Entry point
- Route/controller registration
- Dependency injection
- Security
- Service layer
- Data access
- Transaction
- Exception handling
- File upload
- External API
- Scheduler
- Queue

---

# 22. DỮ LIỆU TRIỂN KHAI FRONTEND

Cần:
- Cách tổ chức UI
- Page chính
- Component reuse
- Form handling
- API calls
- Authentication
- Error handling
- State
- Responsive
- Navigation
- Build process

---

# 23. ẢNH GIAO DIỆN CẦN THU THẬP

Nên thu:
- Đăng nhập
- Đăng ký
- Trang chính
- Trang nghiệp vụ chính
- Trang chi tiết
- Trang user
- Trang staff
- Trang admin
- Dashboard
- Form/popup quan trọng

| Hình | Màn hình | Actor | Chức năng | Route |
|---|---|---|---|---|

---

# 24. DỮ LIỆU KIỂM THỬ API

| ID | API | Input | Điều kiện | Expected Result |
|---|---|---|---|---|

Nên có:
- Happy path
- Validation fail
- Unauthorized
- Forbidden
- Not found
- Duplicate/conflict
- Business rule fail

Nếu có:
- Postman collection
- Postman environment
- Base URL
- Token variables
- Test data variables

---

# 25. DỮ LIỆU KIỂM THỬ CHỨC NĂNG

| Test | Actor | Bước thực hiện | Input | Expected |
|---|---|---|---|---|

Nếu source có automated test, thu:
- Test class
- Test case
- Kết quả chạy
- Pass/fail
- Lỗi còn tồn tại

Không viết "tất cả test pass" nếu chưa chạy.

---

# 26. DỮ LIỆU ĐÁNH GIÁ HỆ THỐNG

## 26.1. Chức năng đã hoàn thành
Danh sách `IMPLEMENTED`.

## 26.2. Chức năng chưa hoàn thành
- PARTIAL
- DESIGNED_ONLY
- NOT_FOUND

## 26.3. Ưu điểm
Chỉ đánh giá có bằng chứng:
- Phân quyền
- Module hóa
- Transaction
- Validation
- Integration
- Responsive
- Test coverage
- Docker
- Logging
- Error handling

## 26.4. Hạn chế
Thu từ:
- Bug
- Test failure
- Security issue
- Placeholder
- Hard-coded config
- Missing feature
- Performance issue
- Inconsistent route
- Missing validation
- Missing documentation

## 26.5. Hướng phát triển
Phải xuất phát từ hạn chế thực tế.

---

# 27. DỮ LIỆU CHO KẾT LUẬN

Tổng hợp:
- Mục tiêu ban đầu
- Những gì đã triển khai
- Kiến trúc đã áp dụng
- Công nghệ chính
- Kết quả kiểm thử
- Giá trị đạt được
- Hạn chế
- Hướng mở rộng

Kết luận không được giới thiệu chức năng mới chưa xuất hiện trong Chương 2/3.

---

# 28. TÀI LIỆU THAM KHẢO

## 28.1. Tài liệu lý thuyết
- Official documentation
- Sách
- Bài báo
- Tài liệu framework

## 28.2. Tài liệu kỹ thuật project
- API docs
- External service docs
- Payment docs
- Deployment docs

Ưu tiên official documentation.

---

# 29. TRACEABILITY MATRIX BẮT BUỘC

| Business Function | Actor | Use Case | Activity | Sequence | API | Controller | Service | Model/Entity | Table | UI |
|---|---|---|---|---|---|---|---|---|---|---|

Mục đích:

```text
Nghiệp vụ
→ Actor
→ Use Case
→ Workflow
→ API
→ Code
→ Database
→ UI
```

---

# 30. BẢNG KIỂM TRA TÍNH NHẤT QUÁN

- [ ] Actor khớp role/middleware
- [ ] Use Case khớp chức năng thực tế
- [ ] Activity khớp business logic
- [ ] Sequence khớp call flow source code
- [ ] Class Diagram khớp class/model
- [ ] ERD khớp migration/schema
- [ ] API table khớp route
- [ ] Frontend khớp API đang gọi
- [ ] Screenshot khớp chức năng đang có
- [ ] Test khớp endpoint
- [ ] Kết luận không thêm chức năng mới
- [ ] Hướng phát triển không bị viết thành chức năng đã hoàn thành

---

# 31. FILE/FOLDER NÊN CUNG CẤP CHO AI PHÂN TÍCH

```text
project/
├── README
├── dependency files
│   ├── pom.xml / build.gradle
│   ├── package.json
│   ├── composer.json
│   └── requirements.txt
├── backend source
├── frontend source
├── routes
├── controllers
├── services
├── repositories / models
├── entities
├── dto / requests / responses
├── security / middleware
├── config
├── migrations
├── schema.sql
├── seeders
├── tests
├── Dockerfile
├── compose.yaml
├── .env.example
├── Postman collection
└── screenshots
```

---

# 32. OUTPUT BÓC TÁCH NÊN CÓ TRƯỚC KHI VIẾT BÁO CÁO

1. `01_project_overview.md`
2. `02_technology_stack.md`
3. `03_actors_roles.md`
4. `04_functional_modules.md`
5. `05_business_rules.md`
6. `06_use_cases.md`
7. `07_activity_flows.md`
8. `08_sequence_flows.md`
9. `09_class_domain_model.md`
10. `10_erd_database.md`
11. `11_api_catalog.md`
12. `12_system_architecture.md`
13. `13_frontend_backend_mapping.md`
14. `14_runtime_environment.md`
15. `15_ui_screens.md`
16. `16_testing.md`
17. `17_issues_limitations.md`
18. `18_traceability_matrix.md`

---

# 33. ÁNH XẠ DỮ LIỆU ĐẦU VÀO → CẤU TRÚC BÁO CÁO

| Phần báo cáo | Dữ liệu bắt buộc |
|---|---|
| Lời mở đầu | Tổng quan đề tài, bài toán, mục tiêu |
| 1.1 Đặt vấn đề | Bối cảnh, vấn đề thực tế |
| 1.2 Kiến trúc/SOA/REST | Kiến trúc thực tế, API style |
| 1.3 Backend framework | Stack backend |
| 1.4 Frontend framework | Stack frontend |
| 1.5 Công cụ | IDE, Postman, Git, Docker... |
| 2.1 Yêu cầu nghiệp vụ | Actor, module, chức năng, rule |
| 2.1.1 Mục tiêu chức năng | Functional catalog |
| 2.1.2 Actor & nhu cầu | Actor/Role matrix |
| 2.1.3 NFR | Security, performance, reliability... |
| 2.2 Use Case | Actors + Functions + Relations |
| 2.2 Activity | Workflow + Decision + Status |
| 2.2 Sequence | Call flow FE→BE→DB |
| 2.2 ERD | Schema + FK + cardinality |
| 2.2 Class | Model/Class/Service relationships |
| 2.3 CSDL | Table/column/constraint |
| 2.4 Kiến trúc | Client/Frontend/Backend/DB/External |
| 2.5 API | Route catalog |
| 3.1 Môi trường | Version/tool/port/config |
| 3.2 Backend | Backend structure + flow |
| 3.3 Frontend | Frontend structure + workflow |
| 3.3 Giao diện | Screenshots + route + chức năng |
| 3.4 Kiểm thử | Testcase + Postman + automated test |
| 3.4 Đánh giá | Implemented/limitations/future |
| Kết luận | Mục tiêu ↔ kết quả thực tế |
| Tài liệu tham khảo | Official docs + source references |

---

# 34. LOGIC BẮT BUỘC CỦA BÁO CÁO

Báo cáo cuối phải giữ chuỗi logic:

```text
Mô tả nghiệp vụ
      ↓
Use Case
      ↓
Activity
      ↓
Sequence
      ↓
API
      ↓
Controller
      ↓
Service
      ↓
Model / Repository
      ↓
Database
```

Nếu project không có một layer nào thì bỏ layer đó.

Ví dụ:

```text
UI
→ Route
→ Controller
→ Eloquent Model
→ Database
```

là hoàn toàn hợp lệ nếu đó là kiến trúc thực tế.

**Không được ép project vào kiến trúc của báo cáo mẫu. Phải dùng cấu trúc báo cáo mẫu nhưng nội dung kỹ thuật phải đi theo code thực tế.**

---

# 35. TIÊU CHÍ XÁC NHẬN ĐỦ DỮ LIỆU ĐỂ VIẾT BÁO CÁO

Chỉ bắt đầu viết báo cáo hoàn chỉnh khi đã trả lời được:

- Hệ thống giải quyết bài toán gì?
- Ai sử dụng?
- Có những module nào?
- Mỗi actor làm được gì?
- Business rule quan trọng là gì?
- API nào phục vụ từng chức năng?
- Request chạy qua class nào?
- Dữ liệu được lưu ở bảng nào?
- Những trạng thái nghiệp vụ nào tồn tại?
- Hệ thống tích hợp dịch vụ ngoài nào?
- Kiến trúc frontend/backend thực tế là gì?
- CSDL thực tế gồm những bảng nào?
- Có thể vẽ Use Case/Activity/Sequence/Class/ERD dựa trên source không?
- Môi trường chạy project là gì?
- Những màn hình nào đã có?
- API/chức năng đã được test như thế nào?
- Phần nào đã hoàn thành?
- Phần nào còn lỗi/chưa hoàn thành?
- Có thể truy một chức năng từ UI → API → code → DB không?

Nếu còn nhiều câu trả lời là `UNKNOWN`, chưa nên viết phần đó như một kết luận chắc chắn.
