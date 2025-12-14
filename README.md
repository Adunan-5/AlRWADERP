# AlRWAD ERP — HR Outsourcing, Payroll & Accounting System (CodeIgniter 3)

AlRWAD ERP is a fully custom-built enterprise resource planning system developed using CodeIgniter 3.  
The application manages manpower outsourcing, employee onboarding, project assignment, timesheet-driven billing, payroll automation, and complete accounting workflows.

This repository contains a sanitized version of the application structure and documentation.  
All sensitive configurations, client data, and uploaded files have been removed.

---

## 🚀 Project Overview

AlRWAD ERP enables organizations to manage large outsourced workforces across different employee categories.  
The system automates:

- Employee onboarding  
- Customer project creation  
- Manpower assignment  
- Timesheet import and approval  
- Payroll calculations for different salary models  
- Financial accounting entries  
- HR document tracking and expiry alerts  

---

# 🧩 Key Modules & Features

## 👨‍🏭 1. Employee Management

Supports multiple employee types:

- **Own Employees**  
- **Supplier Employees**  
- **Freelancers**

Each type has different rules for:

- Salary structure  
- Payment cycles  
- Cost allocations  
- Document requirements  

Employees can also be categorized by **skills/professions** such as Electrician, Pipefitter, Manager, Accountant, etc.

---

## 🏗 2. Project & Manpower Assignment

- Create projects for each customer  
- Assign employees with custom hourly/weekly/monthly rates  
- Specify working periods (start/end dates)  
- Manage manpower distribution for long and short-term contracts  

---

## 📥 3. Timesheet Management

- Import monthly timesheets from **Excel**  
- Manual entry supported  
- Project-wise and employee-wise breakdown  
- Validation for missing or conflicting entries  

Timesheets feed directly into the **payroll and invoice generation process**.

---

## 💸 4. Payroll Engine (Highly Configurable)

The payroll system supports multiple salary structures:

- OWN_BASIC_DIRECT  
- OWN_BASIC_INDIRECT  
- OWN_BASIC_2.10  
- OWN_%  
- Supplier-based salary models  
- Freelancer payment logic  

### Payroll Workflow:
1. Generate payroll  
2. Add/Deduct allowances & adjustments  
3. Submit for approval  
4. Approve/Reject  
5. Process payment  
6. Email payslip to employee  

---

## 🧮 5. Full Accounting System

Built-in accounting features:

- Chart of Accounts (COA)  
- Journal entries  
- Bank accounts & transactions  
- Budgeting  
- Profit & loss  
- Balance sheet  
- Financial registers  
- Customer billing  
- Supplier payments  

### Automated accounting entries include:
- Employee payroll  
- Customer invoices  
- Supplier bills  
- Payments  
- Adjustments  

All accounting operations are **company-specific** with multi-company separation.

---

## 📊 6. HR Document Expiry Alerts

Automatic alerts for upcoming document expiries:

- Passport  
- Residency/ID  
- License  
- Work permits  

Filters available:

- Expiring in **1 month**  
- Expiring in **3 months**  
- Expiring in **6 months**

This helps HR avoid compliance issues.

---

## 🔐 7. Role-Based Access Control

- Create roles with granular permissions  
- Assign capabilities to roles  
- Extend or restrict capabilities for individual users  
- Module-level access control for HR, Payroll, Accounts, Projects, etc.

---

# 🧱 Sanitized Architecture Overview
rwaderp/
├─ application/
│ ├─ controllers/
│ ├─ models/
│ ├─ views/
│ ├─ libraries/
│ ├─ helpers/
│ ├─ language/
│ ├─ config/ (database.php ignored)
│ ├─ logs/ (ignored)
│ └─ third_party/
├─ modules/ (feature modules)
├─ assets/
├─ uploads/ (ignored)
├─ vendor/ (ignored)
├─ temp/
└─ index.php
---

# 👨‍💻 My Responsibilities

- Designed complete ERP architecture  
- Built employee onboarding module  
- Developed project allocation and manpower assignment logic  
- Implemented Excel-based timesheet import  
- Designed and developed a multi-category payroll engine  
- Built full accounting module including COA, journals, reports, and bank operations  
- Implemented HR document expiry monitoring system  
- Designed role-based access control with extendable permissions  
- Integrated automatic accounting entries for operational workflows  
- Developed quotation and invoice modules for manpower outsourcing  

---

# ⚠️ Disclaimer

This repository contains **documentation and sanitized structural files only**.  
Actual client data, sensitive configurations, and proprietary attachments are excluded.
