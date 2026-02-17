# 📝 Backend Controllers Documentation
# เอกสารอธิบายการทำงานของ Controllers ทั้งหมดในระบบ IT Support Pro

> อัปเดตล่าสุด: 2026-02-16

---

## สารบัญ

1. [BaseCrudController](#1-basecrudcontroller---base-class-สำหรับ-crud)
2. [AuthController](#2-authcontroller---ระบบยืนยันตัวตน)
3. [UserController](#3-usercontroller---จัดการผู้ใช้)
4. [UserPermissionController](#4-userpermissioncontroller---สิทธิ์รายบุคคล)
5. [RoleController](#5-rolecontroller---จัดการ-role)
6. [RolePermissionController](#6-rolepermissioncontroller---สิทธิ์ตาม-role)
7. [IncidentController](#7-incidentcontroller---จัดการ-incident)
8. [AssetController](#8-assetcontroller---จัดการสินทรัพย์)
9. [AssetRequestController](#9-assetrequestcontroller---คำขอยืมสินทรัพย์)
10. [DashboardController](#10-dashboardcontroller---แดชบอร์ด)
11. [ActivityLogController](#11-activitylogcontroller---บันทึกกิจกรรม)
12. [BranchController](#12-branchcontroller---จัดการสาขา)
13. [DepartmentController](#13-departmentcontroller---จัดการแผนก)
14. [BusinessHourController](#14-businesshourcontroller---เวลาทำการ)
15. [HolidayController](#15-holidaycontroller---วันหยุด)
16. [IncidentTitleController](#16-incidenttitlecontroller---หัวข้อ-incident)
17. [KbArticleController](#17-kbarticlecontroller---knowledge-base)
18. [NotificationController](#18-notificationcontroller---การแจ้งเตือน)
19. [OrganizationNotificationController](#19-organizationnotificationcontroller---ตั้งค่าการแจ้งเตือนองค์กร)
20. [ProblemController](#20-problemcontroller---จัดการ-problem)
21. [ServiceCatalogItemController](#21-servicecatalogitemcontroller---รายการบริการ)
22. [ServiceRequestController](#22-servicerequestcontroller---คำขอบริการ)
23. [OtherRequestController](#23-otherrequestcontroller---คำขออื่นๆ)
24. [SlaController](#24-slacontroller---ตั้งค่า-sla)
25. [SlaCalculatorController](#25-slacalculatorcontroller---คำนวณ-sla)
26. [SatisfactionSurveyController](#26-satisfactionsurveycontroller---แบบสอบถามความพึงพอใจ)
27. [PmProjectController](#27-pmprojectcontroller---โครงการ-pm)
28. [PmScheduleController](#28-pmschedulecontroller---ตารางงาน-pm)
29. [SubContractorController](#29-subcontractorcontroller---ผู้รับเหมาช่วง)
30. [SystemSettingController](#30-systemsettingcontroller---ตั้งค่าระบบ)

---

## 1. BaseCrudController - Base Class สำหรับ CRUD

**ไฟล์:** `app/Http/Controllers/Api/BaseCrudController.php`  
**ประเภท:** Abstract Class (ไม่สามารถเรียกใช้โดยตรง)

เป็น Controller ต้นแบบที่ controllers อื่นๆ สืบทอด (extends) ไป ให้ฟังก์ชัน CRUD มาตรฐาน

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงข้อมูลทั้งหมด รองรับ pagination ด้วย `?per_page=20` |
| `store($request)` | POST | สร้างข้อมูลใหม่ พร้อม validate ตาม `$validationRules` |
| `show($id)` | GET | ดึงข้อมูลตาม ID |
| `update($request, $id)` | PUT | แก้ไขข้อมูล ใช้ `$updateValidationRules` ถ้ามี ไม่งั้นใช้ `$validationRules` |
| `destroy($id)` | DELETE | ลบข้อมูล (HTTP 204) |

**หมายเหตุ:** Controllers ที่ extends จาก BaseCrud สามารถ override แต่ละ method ได้ตามความจำเป็น

---

## 2. AuthController - ระบบยืนยันตัวตน

**ไฟล์:** `app/Http/Controllers/Api/AuthController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | Endpoint | คำอธิบาย |
|--------|------|----------|----------|
| `register($request)` | POST | `/api/register` | ลงทะเบียนผู้ใช้ใหม่ - validate ข้อมูล, สร้าง User, ออก token (Sanctum) |
| `login($request)` | POST | `/api/login` | เข้าสู่ระบบ - รองรับทั้ง email และ username, ออก token, แนบ permissions |
| `logout($request)` | POST | `/api/logout` | ออกจากระบบ - ลบ token ปัจจุบัน |
| `me($request)` | GET | `/api/me` | ดึงข้อมูลผู้ใช้ปัจจุบัน พร้อม branch, department, permissions |
| `updatePassword($request)` | PUT | `/api/password` | เปลี่ยนรหัสผ่าน - ต้องยืนยันรหัสผ่านปัจจุบันก่อน |
| `attachMergedPermissions($user)` | - | (private) | รวม permissions จาก role + user override แล้วแนบไปกับ user object |

**กลไก Permissions:**
- ดึง permissions จาก Role (base)
- ดึง permissions เฉพาะ User (override)
- รวมกัน: ถ้า user มี override → ใช้ค่า override, ไม่มี → ใช้ค่าจาก role

---

## 3. UserController - จัดการผู้ใช้

**ไฟล์:** `app/Http/Controllers/Api/UserController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงรายชื่อ users ทั้งหมด พร้อม branch/department, filter ตาม role/status ได้ |
| `show($id)` | GET | ดึงข้อมูล user + role permissions |
| `store($request)` | POST | สร้าง user ใหม่, hash password, broadcast event `UserUpdated` |
| `update($request, $id)` | PUT | แก้ไข user, unique check สำหรับ username/email (ยกเว้นตัวเอง), password เปลี่ยนได้เฉพาะเมื่อส่งมา |
| `getTechnicians()` | GET | ดึง users ที่มี role = Technician (id, name) |
| `attachRolePermissions($user)` | - | (private) แนบ role permissions ให้ user |

---

## 4. UserPermissionController - สิทธิ์รายบุคคล

**ไฟล์:** `app/Http/Controllers/Api/UserPermissionController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | Endpoint | คำอธิบาย |
|--------|------|----------|----------|
| `index($userId)` | GET | `/api/users/{userId}/permissions` | ดึง merged permissions (role + user override) ของ user |
| `update($request, $userId)` | PUT | `/api/users/{userId}/permissions` | บันทึก user-specific permissions (override role) |
| `reset($userId)` | POST | `/api/users/{userId}/permissions/reset` | รีเซ็ตกลับเป็นค่า role เดิม (ลบ override ทั้งหมด) |

---

## 5. RoleController - จัดการ Role

**ไฟล์:** `app/Http/Controllers/Api/RoleController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | Endpoint | คำอธิบาย |
|--------|------|----------|----------|
| `index()` | GET | `/api/roles` | ดึง roles ทั้งหมด พร้อมจำนวน permissions และจำนวน users ที่ใช้ |
| `show($role)` | GET | `/api/roles/{role}` | ดึงรายละเอียด role + permissions |
| `store($request)` | POST | `/api/roles` | สร้าง role ใหม่พร้อม permissions (ใช้ DB transaction) |
| `update($request, $role)` | PUT | `/api/roles/{role}` | แก้ไขชื่อ role (Default roles ห้ามเปลี่ยน name แต่เปลี่ยน display_name ได้) |
| `destroy($role)` | DELETE | `/api/roles/{role}` | ลบ role (ห้ามลบ default roles, ห้ามลบถ้ามี user ใช้อยู่) |

**Default Roles:** Admin, Technician, Helpdesk, Purchase, User

---

## 6. RolePermissionController - สิทธิ์ตาม Role

**ไฟล์:** `app/Http/Controllers/Api/RolePermissionController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | Endpoint | คำอธิบาย |
|--------|------|----------|----------|
| `index($roleId)` | GET | `/api/roles/{roleId}/permissions` | ดึง permissions ของ role (จับคู่กับ menus ทั้งหมด) |
| `update($request, $roleId)` | PUT | `/api/roles/{roleId}/permissions` | อัปเดต permissions ของ role (upsert ทีละ menu) |
| `resetToDefault($roleId)` | POST | `/api/roles/{roleId}/permissions/reset-default` | รีเซ็ต permissions กลับค่า default จาก seeder |
| `seedDefaultPermissions($role)` | - | (private) | กำหนด permissions default ตาม role name |

---

## 7. IncidentController - จัดการ Incident

**ไฟล์:** `app/Http/Controllers/Api/IncidentController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึง incidents ทั้งหมด พร้อม requester/assignee, filter ตาม status/priority/category/search, รองรับ limit |
| `show($id)` | GET | ดึงรายละเอียด incident + relationships (requester, assignee, asset, subContractors) |
| `store($request)` | POST | สร้าง incident ใหม่ - map ข้อมูลจาก frontend, คำนวณ SLA (response_time, resolution_time, sla_due_at), เปลี่ยนสถานะ asset เป็น Maintenance, ส่ง notification |
| `update($request, $id)` | PUT | อัปเดต incident - จัดการเปลี่ยนสถานะ (Open→In Progress→Resolved→Closed), บันทึก repair details, คืนสถานะ asset, สร้าง MaintenanceHistory, ส่ง email/notification |
| `mapRequestData($request)` | - | (private) แปลงชื่อ field จาก frontend ให้ตรงกับ backend |

**Flow สำคัญ:**
1. **สร้าง Incident** → คำนวณ SLA → เปลี่ยน asset เป็น Maintenance → แจ้งเตือน
2. **เปลี่ยนเป็น Resolved** → บันทึก resolved_at → ส่ง notification ให้ผู้แจ้ง
3. **เปลี่ยนเป็น Closed** → คืนสถานะ asset → สร้าง MaintenanceHistory

---

## 8. AssetController - จัดการสินทรัพย์

**ไฟล์:** `app/Http/Controllers/Api/AssetController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึง assets ทั้งหมด พร้อม branch, คำนวณ available_quantity จาก serial statuses |
| `show($id)` | GET | ดึงรายละเอียด asset + maintenance history, borrowing history, serial statuses |
| `update($request, $id)` | PUT | อัปเดต asset + sync serial statuses (ถ้ามี) |
| `bulkStore($request)` | POST | สร้าง assets หลายรายการพร้อมกัน (Bulk Create) พร้อมสร้าง serial statuses อัตโนมัติ |
| `checkSerialNumbers($request)` | POST | ตรวจสอบว่า serial numbers ซ้ำหรือไม่ (ใช้ validate ก่อน submit) |
| `maintenanceHistory($id)` | GET | ดึงประวัติการซ่อมของ asset |
| `borrowingHistory($id)` | GET | ดึงประวัติการยืมของ asset |
| `statistics()` | GET | สถิติ assets (total, available, in_use, maintenance, on_loan, retired) |

---

## 9. AssetRequestController - คำขอยืมสินทรัพย์

**ไฟล์:** `app/Http/Controllers/Api/AssetRequestController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงคำขอยืมทั้งหมด พร้อม requester/asset/approvedBy, filter สถานะ |
| `show($id)` | GET | ดึงรายละเอียดคำขอ |
| `store($request)` | POST | สร้างคำขอยืมใหม่ |
| `update($request, $id)` | PUT | อัปเดตคำขอ + จัดการ flow (อนุมัติ/ปฏิเสธ/ยืม/คืน) |
| `approve($assetRequest)` | POST | อนุมัติคำขอ → เปลี่ยนสถานะ asset เป็น On Loan, ลด available_quantity |
| `reject($assetRequest)` | POST | ปฏิเสธคำขอ |
| `myRequests()` | GET | ดึงคำขอของ user ปัจจุบัน |
| `statistics()` | GET | สถิติคำขอ (pending, approved, rejected, returned) |

**Flow การยืม-คืน:**
1. **ยืม (Approve):** สร้าง BorrowingHistory → อัปเดต serial status → เปลี่ยน asset status → broadcast event
2. **คืน (Return):** อัปเดต BorrowingHistory → คืน serial status → เปลี่ยน asset status กลับ

---

## 10. DashboardController - แดชบอร์ด

**ไฟล์:** `app/Http/Controllers/Api/DashboardController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `overview()` | GET | ภาพรวมทั้งระบบ - จำนวน incidents, assets, users, problems, requests |
| `incidentsTrend($request)` | GET | แนวโน้ม incidents ตามช่วงเวลา (default 30 วัน) |
| `incidentsByCategory()` | GET | จำนวน incidents แยกตามหมวดหมู่ |
| `incidentsByPriority()` | GET | จำนวน incidents แยกตามความสำคัญ |
| `topTechnicians($request)` | GET | อันดับช่างที่ resolve incidents มากที่สุด |
| `recentIncidents($request)` | GET | Incidents ล่าสุด |
| `slaCompliance()` | GET | อัตราการปฏิบัติตาม SLA (met / breached / compliance_rate) |

---

## 11. ActivityLogController - บันทึกกิจกรรม

**ไฟล์:** `app/Http/Controllers/Api/ActivityLogController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึง activity logs พร้อม user, filter ตาม action/module/user/severity/date range, pagination |
| `store($request)` | POST | บันทึก log ใหม่ พร้อมเพิ่มข้อมูลอัตโนมัติ (IP, User Agent, severity, event_type) |
| `statistics($request)` | GET | สถิติ logs (total, today, security events, errors, by action, by module) |
| `securityLogs($request)` | GET | ดึง security logs (login, logout, password_change, etc.) |
| `errorLogs($request)` | GET | ดึง error logs |
| `parseUserAgent($ua)` | - | (private) แยกข้อมูล browser/OS จาก User Agent string |
| `getSeverityByAction($action)` | - | (private) กำหนด severity ตามประเภท action |
| `getEventTypeByAction($action)` | - | (private) กำหนด event type ตาม action |

---

## 12. BranchController - จัดการสาขา

**ไฟล์:** `app/Http/Controllers/Api/BranchController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `store($request)` | POST | สร้างสาขาใหม่ + broadcast event `BranchUpdated` |
| `update($request, $id)` | PUT | แก้ไขสาขา + broadcast event |
| `destroy($id)` | DELETE | ลบสาขา + broadcast event |

**หมายเหตุ:** `index` และ `show` ใช้จาก BaseCrudController โดยตรง

---

## 13. DepartmentController - จัดการแผนก

**ไฟล์:** `app/Http/Controllers/Api/DepartmentController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `store($request)` | POST | สร้างแผนกใหม่ + load branch + broadcast event `DepartmentUpdated` |
| `update($request, $id)` | PUT | แก้ไขแผนก + broadcast event |
| `destroy($id)` | DELETE | ลบแผนก + broadcast event |

---

## 14. BusinessHourController - เวลาทำการ

**ไฟล์:** `app/Http/Controllers/Api/BusinessHourController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `update($request, $id)` | PUT | แก้ไขเวลาทำการ พร้อม normalize เวลา (แปลงเป็น H:i), ล้างเวลาถ้าวันหยุด |
| `isOpen()` | GET | ตรวจสอบว่าตอนนี้อยู่ในเวลาทำการหรือไม่ |
| `getByDay($day)` | GET | ดึงเวลาทำการของวัน (0=อาทิตย์ - 6=เสาร์) |
| `bulkUpdate($request)` | PUT | อัปเดตเวลาทำการทุกวันพร้อมกัน (updateOrCreate) |

---

## 15. HolidayController - วันหยุด

**ไฟล์:** `app/Http/Controllers/Api/HolidayController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงวันหยุดทั้งหมด + filter type/user/date range |
| `types()` | GET | ดึงประเภทวันหยุดทั้งหมด (วันหยุดราชการ, ลาป่วย, ลาพักร้อน, etc.) |
| `forSlaCalculation($request)` | GET | ดึงวันหยุดสำหรับใช้คำนวณ SLA (affects_all หรือ เฉพาะ user) |

---

## 16. IncidentTitleController - หัวข้อ Incident

**ไฟล์:** `app/Http/Controllers/Api/IncidentTitleController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `all()` | GET | ดึงหัวข้อ incident ที่ active ทั้งหมด (จัดตาม category, title) |
| `categories()` | GET | ดึง categories ทั้งหมด (distinct) |
| `byCategory($category)` | GET | ดึงหัวข้อตาม category ที่ active |
| `toggle($id)` | PATCH | สลับสถานะ active/inactive |

**ข้อมูลที่เก็บ:** title, category, priority, response_time, resolution_time (ใช้สำหรับ SLA อัตโนมัติ)

---

## 17. KbArticleController - Knowledge Base

**ไฟล์:** `app/Http/Controllers/Api/KbArticleController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `show($id)` | GET | ดึงบทความ + เพิ่ม view count อัตโนมัติ |
| `popular()` | GET | ดึง 10 บทความยอดนิยม (เรียงตาม views) |
| `recent()` | GET | ดึง 10 บทความล่าสุด |
| `categories()` | GET | ดึง categories ทั้งหมด (distinct) |
| `helpful($id)` | POST | กด "มีประโยชน์" → +1 helpful |
| `notHelpful($id)` | POST | กด "ไม่มีประโยชน์" → +1 not_helpful |

---

## 18. NotificationController - การแจ้งเตือน

**ไฟล์:** `app/Http/Controllers/Api/NotificationController.php`  
**สืบทอดจาก:** BaseCrudController

ใช้ CRUD มาตรฐานจาก BaseCrudController ทั้งหมด ไม่มี method เพิ่มเติม

**Validation:** user_id, type, message, read (boolean)

---

## 19. OrganizationNotificationController - ตั้งค่าการแจ้งเตือนองค์กร

**ไฟล์:** `app/Http/Controllers/Api/OrganizationNotificationController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `initialize($request)` | POST | สร้างการตั้งค่าแจ้งเตือนครั้งแรกตาม event types |
| `index()` | GET | ดึงการตั้งค่าการแจ้งเตือนทั้งหมด (จัดกลุ่มตาม event_type) |
| `update($request, $id)` | PUT | อัปเดตการตั้งค่า (เปิด/ปิด channel, ตั้งค่า credentials) |
| `testNotification($request, $id, $channel)` | POST | ทดสอบส่งแจ้งเตือนตาม channel ที่เลือก |
| `testEmail($notification)` | - | (private) ทดสอบส่งอีเมล (ใช้ SystemSetting สำหรับ SMTP config) |
| `testTelegram($notification)` | - | (private) ทดสอบส่ง Telegram (ใช้ Bot API) |
| `testLine($notification)` | - | (private) ทดสอบส่ง LINE Notify |

---

## 20. ProblemController - จัดการ Problem

**ไฟล์:** `app/Http/Controllers/Api/ProblemController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึง problems พร้อม assignedTo/incidents, filter status/priority |
| `show($id)` | GET | ดึงรายละเอียด problem + relationships |
| `store($request)` | POST | สร้าง problem ใหม่ + sync related incidents |
| `update($request, $id)` | PUT | อัปเดต problem + sync related incidents |

**สถานะ:** Open, Investigating, Known Error, Resolved, Closed

---

## 21. ServiceCatalogItemController - รายการบริการ

**ไฟล์:** `app/Http/Controllers/Api/ServiceCatalogItemController.php`  
**สืบทอดจาก:** BaseCrudController

ใช้ CRUD มาตรฐานจาก BaseCrudController ทั้งหมด ไม่มี method เพิ่มเติม

**Validation:** name, description, category, sla, cost, icon, estimated_time

---

## 22. ServiceRequestController - คำขอบริการ

**ไฟล์:** `app/Http/Controllers/Api/ServiceRequestController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงคำขอบริการ พร้อม service/requester/approvedBy |
| `show($id)` | GET | ดึงรายละเอียดคำขอ |
| `approve($request, $id)` | POST | อนุมัติคำขอ → บันทึก approved_by_id + approved_at |
| `reject($request, $id)` | POST | ปฏิเสธคำขอ → บันทึกเหตุผล |
| `startProgress($id)` | POST | เริ่มดำเนินการ → สถานะ In Progress |
| `complete($id)` | POST | เสร็จสิ้น → สถานะ Completed + บันทึก completion_date |

**สถานะ:** Pending → Approved → In Progress → Completed / Rejected

---

## 23. OtherRequestController - คำขออื่นๆ

**ไฟล์:** `app/Http/Controllers/Api/OtherRequestController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงคำขอพร้อม relationships, filter สถานะ, support limit |
| `store($request)` | POST | สร้างคำขอใหม่ |
| `approve($id)` | POST | อนุมัติ → แจ้ง notification |
| `reject($request, $id)` | POST | ปฏิเสธ + เหตุผล → แจ้ง notification |
| `complete($request, $id)` | POST | จัดหาเรียบร้อย + ใส่ serial numbers |
| `receive($id)` | POST | รับของแล้ว → assign serial numbers ให้ asset, อัปเดต quantity, สร้าง BorrowingHistory |

**Flow:** Pending → Approved → Completed (จัดหาแล้ว) → Received (รับของแล้ว)

---

## 24. SlaController - ตั้งค่า SLA

**ไฟล์:** `app/Http/Controllers/Api/SlaController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `all()` | GET | ดึง SLA ทั้งหมด |
| `getByPriority($priority)` | GET | ดึง SLA ตามระดับ priority (เฉพาะที่ active) |

**Validation:** name, priority, response_time (นาที), resolution_time (นาที), description, is_active

---

## 25. SlaCalculatorController - คำนวณ SLA

**ไฟล์:** `app/Http/Controllers/Api/SlaCalculatorController.php`  
**สืบทอดจาก:** Controller  
**ใช้ Service:** `SlaCalculatorService`

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `calculateForIncident($request, $id)` | GET | คำนวณสถานะ SLA สำหรับ incident เฉพาะ (elapsed, remaining, status) |
| `calculate($request)` | POST | คำนวณ SLA ตาม parameters ที่กำหนดเอง |
| `calculateBusinessMinutes($request)` | POST | คำนวณนาทีทำการระหว่างสองเวลา |
| `getDeadline($request)` | POST | คำนวณ deadline ของ SLA |
| `isWithinBusinessHours($request)` | GET | ตรวจสอบว่าเวลาที่กำหนดอยู่ในเวลาทำการหรือไม่ |
| `getOpenIncidentsSlaStatus()` | GET | สรุปสถานะ SLA ของ incidents ที่เปิดอยู่ทั้งหมด |

---

## 26. SatisfactionSurveyController - แบบสอบถามความพึงพอใจ

**ไฟล์:** `app/Http/Controllers/Api/SatisfactionSurveyController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `store($request)` | POST | บันทึกแบบสอบถาม + auto-close incident + คืนสถานะ asset + สร้าง MaintenanceHistory |
| `pending()` | GET | ดึง incidents ที่ resolved แต่ยังไม่ได้ทำแบบสอบถาม (ของ user ปัจจุบัน) |
| `index($request)` | GET | ดึงแบบสอบถามทั้งหมด พร้อม respondent/incident |
| `getByTicketId($ticketId)` | GET | ดึงแบบสอบถามตาม ticket ID |
| `checkTicket($ticketId)` | GET | ตรวจสอบว่า ticket ได้ทำแบบสอบถามแล้วหรือยัง |
| `statistics()` | GET | สถิติ (total, average rating, rating distribution 1-5) |

**Flow สำคัญ (store):**
1. บันทึกแบบสอบถาม
2. เปลี่ยน incident สถานะเป็น Closed
3. คืนสถานะ asset เป็นค่าก่อนหน้า
4. สร้าง MaintenanceHistory
5. Broadcast events

---

## 27. PmProjectController - โครงการ PM

**ไฟล์:** `app/Http/Controllers/Api/PmProjectController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงโครงการ PM ทั้งหมด + filter status/organization/search + คำนวณ stats |
| `store($request)` | POST | สร้างโครงการใหม่ + auto-generate project_code + upload ไฟล์ (contract, TOR) |
| `show($pmProject)` | GET | ดึงรายละเอียดโครงการ + manager |
| `update($request, $pmProject)` | PUT | แก้ไขโครงการ + จัดการ file upload (ลบไฟล์เก่า) |
| `destroy($pmProject)` | DELETE | ลบโครงการ + ลบไฟล์ที่เกี่ยวข้อง |

**สถานะ:** Planning, In Progress, Completed, Cancelled

---

## 28. PmScheduleController - ตารางงาน PM

**ไฟล์:** `app/Http/Controllers/Api/PmScheduleController.php`  
**สืบทอดจาก:** Controller

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `index($request)` | GET | ดึงตารางวล PM + filter project/status/date range/search + stats |
| `store($request)` | POST | สร้างตาราง PM ใหม่ + สร้าง default checklist + คำนวณ completion |
| `show($pmSchedule)` | GET | ดึงรายละเอียดตาราง + checklist items + asset + technician |
| `update($request, $pmSchedule)` | PUT | แก้ไขตาราง + sync checklist items + คำนวณ completion |
| `destroy($pmSchedule)` | DELETE | ลบตาราง + checklist items ที่เกี่ยวข้อง |
| `execute($request, $pmSchedule)` | POST | ดำเนินการ PM - อัปเดต checklist, บันทึกผลลัพธ์, เปลี่ยนสถานะ |
| `statistics($request)` | GET | สถิติ PM (total, scheduled, completed, overdue, compliance rate) |
| `createDefaultChecklist($pmSchedule)` | - | (private) สร้าง checklist default ตามประเภท asset |
| `updateOverdueSchedules()` | - | อัปเดตตารางที่เลยกำหนด → สถานะ Overdue |

---

## 29. SubContractorController - ผู้รับเหมาช่วง

**ไฟล์:** `app/Http/Controllers/Api/SubContractorController.php`  
**สืบทอดจาก:** BaseCrudController

ใช้ CRUD มาตรฐานจาก BaseCrudController ทั้งหมด ไม่มี method เพิ่มเติม

**Validation:** name, company, email, phone, specialty, province, bank_name, bank_account_name, bank_account_number, status

---

## 30. SystemSettingController - ตั้งค่าระบบ

**ไฟล์:** `app/Http/Controllers/Api/SystemSettingController.php`  
**สืบทอดจาก:** BaseCrudController

| Method | HTTP | คำอธิบาย |
|--------|------|----------|
| `testEmail($request)` | POST | ทดสอบการส่งอีเมล - ดึง config จาก DB → set ค่า SMTP → ส่ง test email |

**Validation:** category, key, value, description

---

## สรุปความสัมพันธ์ระหว่าง Controllers

```
BaseCrudController (Abstract)
├── UserController
├── AssetController
├── AssetRequestController
├── IncidentController
├── ActivityLogController
├── BranchController
├── DepartmentController
├── BusinessHourController
├── HolidayController
├── IncidentTitleController
├── KbArticleController
├── NotificationController
├── OtherRequestController
├── ProblemController
├── SatisfactionSurveyController
├── ServiceCatalogItemController
├── ServiceRequestController
├── SlaController
├── SubContractorController
└── SystemSettingController

Controller (Laravel Base)
├── AuthController
├── DashboardController
├── RoleController
├── RolePermissionController
├── UserPermissionController
├── OrganizationNotificationController
├── PmProjectController
├── PmScheduleController
└── SlaCalculatorController
```
