# API Documentation

## Base URL
```
http://localhost/qr-attendance-system/api/
```

## Authentication
All endpoints require authentication via session or API token.

## Response Format

### Success Response
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": {},
  "timestamp": "2024-01-01 12:00:00"
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error message",
  "errors": {},
  "timestamp": "2024-01-01 12:00:00"
}
```

## Endpoints

### Attendance Endpoints

#### Mark Attendance
```
POST /attendance/mark

Request Body:
{
  "student_id": 1,
  "class_id": 1,
  "qr_code_id": "QR_xyz"
}

Response:
{
  "status": "success",
  "message": "Attendance marked",
  "data": {
    "attendance_id": 123,
    "status": "present"
  }
}
```

#### Get Student Attendance
```
GET /attendance/student?student_id=1&class_id=1&start_date=2024-01-01&end_date=2024-01-31

Response:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "attendance_date": "2024-01-01",
      "status": "present",
      "time_in": "08:30:00",
      "time_out": "16:30:00"
    }
  ]
}
```

#### Get Class Attendance
```
GET /attendance/class?class_id=1&date=2024-01-01

Response:
{
  "status": "success",
  "data": [
    {
      "student_id": 1,
      "full_name": "John Doe",
      "status": "present",
      "time_in": "08:30:00"
    }
  ]
}
```

### QR Code Endpoints

#### Generate QR Code
```
POST /qrcode/generate

Request Body:
{
  "class_id": 1,
  "validity_minutes": 15
}

Response:
{
  "status": "success",
  "data": {
    "qr_id": 1,
    "qr_code_id": "QR_xyz",
    "session_id": "SESSION_xyz",
    "expires_at": "2024-01-01 12:15:00"
  }
}
```

#### Verify QR Code
```
GET /qrcode/verify?qr_code_id=QR_xyz

Response:
{
  "status": "success",
  "data": {
    "valid": true,
    "class_id": 1,
    "expires_at": "2024-01-01 12:15:00"
  }
}
```

### Report Endpoints

#### Generate Report
```
GET /reports/generate?type=monthly&student_id=1&class_id=1&month=01&year=2024

Response:
{
  "status": "success",
  "data": {
    "type": "monthly",
    "total_days": 20,
    "present_days": 18,
    "absent_days": 2,
    "attendance_percentage": 90.0
  }
}
```

#### Export Report
```
GET /reports/export?type=pdf&student_id=1&class_id=1

Response: PDF file download
```

### User Endpoints

#### Get User Profile
```
GET /users/profile

Response:
{
  "status": "success",
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@local",
    "full_name": "Administrator",
    "role": "admin"
  }
}
```

#### Update Profile
```
POST /users/profile/update

Request Body:
{
  "full_name": "New Name",
  "email": "new@email.com"
}

Response:
{
  "status": "success",
  "message": "Profile updated"
}
```

---

For complete API documentation, see API_DOCS.md