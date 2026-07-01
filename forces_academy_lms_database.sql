CREATE DATABASE IF NOT EXISTS forces_academy_lms;
USE forces_academy_lms;

CREATE TABLE IF NOT EXISTS students (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    class VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS courses (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    teacher_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notices (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    posted_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS course_materials (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    course_id INT(11) NOT NULL,
    material_title VARCHAR(150) NOT NULL,
    material_type VARCHAR(50) NOT NULL DEFAULT 'Notes',
    description TEXT,
    resource_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_materials_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

INSERT INTO admins (username, password, email)
VALUES ('admin', '$2y$12$TWZDeWXNPp1x1UAa5qUbqu1vtBi9uVe97.D6jQLptHqXDtfwvvYQq', 'admin@forcesacademy.com')
ON DUPLICATE KEY UPDATE
password = VALUES(password),
email = VALUES(email);


INSERT INTO courses (course_name,description,teacher_name) VALUES
('Web Development','HTML CSS JavaScript PHP and MySQL','Sir Ahmed'),
('Database Systems','Relational databases SQL normalization','Sir Hamza'),
('Data Structures','Arrays Linked Lists Stacks Queues Trees','Maam Ayesha'),
('Software Engineering','SDLC UML Agile Testing','Sir Bilal'),
('Artificial Intelligence','ML basics search and expert systems','Dr. Hassan'),
('Cyber Security','Network security cryptography ethical hacking','Sir Usman');

INSERT INTO notices(title,content,posted_by) VALUES
('Welcome','Welcome to Forces Academy LMS.','Admin');

INSERT INTO course_materials(course_id,material_title,material_type,description,resource_link) VALUES
(1,'HTML Notes','PDF','Introduction to HTML','#'),
(2,'SQL Practice','PDF','Basic SQL Queries','#'),
(3,'DSA Slides','PPT','Stacks and Queues','#');
