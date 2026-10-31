# Student Feedback System Diagrams

These Mermaid diagrams can be pasted into Mermaid Live Editor, GitHub Markdown, or documentation tools that support Mermaid.

## ER Diagram

```mermaid
erDiagram
    USERS ||--o| ADMIN : has
    USERS ||--o| STUDENTS : has
    USERS ||--o| FACULTY : has
    CLASSES ||--o{ STUDENTS : contains
    CLASSES ||--o{ SUBJECTS : offers
    FACULTY ||--o{ FACULTY_SUBJECTS : teaches
    SUBJECTS ||--o{ FACULTY_SUBJECTS : assigned
    STUDENTS ||--o{ FEEDBACK : submits
    FACULTY_SUBJECTS ||--o{ FEEDBACK : receives
    FEEDBACK ||--o{ FEEDBACK_SCORES : contains
    FEEDBACK_CRITERIA ||--o{ FEEDBACK_SCORES : measures

    USERS {
        int id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        enum status
    }
    CLASSES {
        int id PK
        varchar class_name
        tinyint semester
        varchar academic_year
    }
    STUDENTS {
        int id PK
        int user_id FK
        int class_id FK
        varchar roll_no UK
        varchar full_name
    }
    FACULTY {
        int id PK
        int user_id FK
        varchar employee_no UK
        varchar full_name
    }
    SUBJECTS {
        int id PK
        int class_id FK
        varchar subject_code UK
        varchar subject_name
    }
    FACULTY_SUBJECTS {
        int id PK
        int faculty_id FK
        int subject_id FK
    }
    FEEDBACK {
        int id PK
        int student_id FK
        int faculty_subject_id FK
        tinyint rating
        text comments
    }
    FEEDBACK_CRITERIA {
        int id PK
        varchar criteria_name
        varchar description
        tinyint display_order
        enum status
    }
    FEEDBACK_SCORES {
        int id PK
        int feedback_id FK
        int criteria_id FK
        tinyint score
    }
```

## DFD Level 0

```mermaid
flowchart LR
    Student["Student"] -->|"Login, feedback"| System["Student Feedback System"]
    Admin["Admin"] -->|"Manage master data, reports"| System
    Faculty["Faculty"] -->|"View results"| System
    System -->|"Authentication, CRUD, reports"| Database[("MySQL Database")]
    System -->|"Feedback status"| Student
    System -->|"Analytics"| Admin
    System -->|"Ratings and comments"| Faculty
```

## DFD Level 1

```mermaid
flowchart LR
    U["Users"] --> A["1.0 Authenticate User"]
    A --> S["Session and Role Control"]
    S --> B["2.0 Manage Students, Faculty, Classes, Subjects"]
    S --> C["3.0 Submit Feedback"]
    S --> D["4.0 Generate Reports"]
    B --> DB[("Master Tables")]
    C --> FB[("Feedback Table")]
    FB --> D
    DB --> C
    DB --> D
    D --> R["Tables and Charts"]
```

## Use Case Diagram

```mermaid
flowchart TB
    Admin["Admin"] --> UC1["Login"]
    Admin --> UC2["Manage Students"]
    Admin --> UC3["Manage Faculty"]
    Admin --> UC4["Create Classes"]
    Admin --> UC5["Manage Subjects"]
    Admin --> UC6["Assign Faculty"]
    Admin --> UC7["View Reports"]
    Student["Student"] --> UC1
    Student --> UC8["View Assigned Subjects"]
    Student --> UC9["Submit Feedback"]
    Faculty["Faculty"] --> UC1
    Faculty --> UC10["View Average Rating"]
    Faculty --> UC11["View Comments"]
```

## System Architecture Diagram

```mermaid
flowchart TB
    Browser["Browser: Bootstrap 5, JavaScript, Chart.js"] --> Apache["Apache via XAMPP/WAMP/LAMP"]
    Apache --> PHP["Core PHP Application"]
    PHP --> Auth["Session Auth and RBAC"]
    PHP --> Modules["Admin, Student, Faculty Modules"]
    Modules --> PDO["PDO Prepared Statements"]
    PDO --> MySQL[("MySQL: student_feedback_system")]
```
