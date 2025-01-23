CREATE DATABASE IF NOT EXISTS EmotionalRegulationApp;
USE EmotionalRegulationApp;

-- Drop tables
DROP TABLE Emotional_Strategy_Link;
DROP TABLE Strategy_Feedback;
DROP TABLE Assigned_Strategy;
DROP TABLE Journal_Entry;
DROP TABLE Coping_Strategy;
DROP TABLE Emotion;
DROP TABLE Users;
DROP TABLE Parents;

-- Create Parents table
CREATE TABLE Parents (
    parent_user_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_fname VARCHAR(50), -- Parent's first name
	parent_username VARCHAR(50) UNIQUE,
    parent_password VARCHAR(255),
    parent_email VARCHAR(255) UNIQUE
);

-- Create Users table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_user_id INT NOT NULL,
    user_fname VARCHAR(50), -- User's first name
    user_username VARCHAR(50) UNIQUE,
    user_dob DATE,
    user_password VARCHAR(255),
    consent_status BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (parent_user_id) REFERENCES Parents(parent_user_id)
);

-- Create Emotion table
CREATE TABLE Emotion (
    emotion_id INT AUTO_INCREMENT PRIMARY KEY,
    emotion_name VARCHAR(50) UNIQUE,
    emotion_core_category VARCHAR(50),
    emotion_type VARCHAR(50)
);

-- Create Coping_Strategy table
CREATE TABLE Coping_Strategy (
    strategy_id INT AUTO_INCREMENT PRIMARY KEY,
    strategy_name VARCHAR(100) UNIQUE,
    strategy_descript TEXT,
    strategy_steps TEXT
);





-- Create Journal_Entry table
CREATE TABLE Journal_Entry (
    journal_entry_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    emotion_id INT,
    strategy_id INT NOT NULL,
    journal_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    journal_content TEXT,
    emotional_intensity_rating INT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (emotion_id) REFERENCES Emotion(emotion_id),
    FOREIGN KEY (strategy_id) REFERENCES Coping_Strategy(strategy_id)
);

-- Create Assigned_Strategy table
CREATE TABLE Assigned_Strategy (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    strategy_id INT,
-- This should automatically store the current date/time when a new row is made.
    assigned_start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assignment_end_date DATE,
    is_current BOOLEAN,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (strategy_id) REFERENCES Coping_Strategy(strategy_id)
);

-- Create Strategy_Feedback table
CREATE TABLE Strategy_Feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT,
-- This should automatically store the current date/time when a new row is made.
    feedback_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rating INT CHECK (rating BETWEEN 1 AND 5), -- Ensures ratings are within 1-5
    comments TEXT, -- Allows detailed user feedback
    recommendation VARCHAR(255), -- Optional recommendations or notes
    FOREIGN KEY (assignment_id) REFERENCES Assigned_Strategy(assignment_id)
);

-- Create Emotional_Strategy_Link table
CREATE TABLE Emotional_Strategy_Link (
    link_id INT AUTO_INCREMENT PRIMARY KEY,
    emotion_id INT,
    strategy_id INT,
    FOREIGN KEY (emotion_id) REFERENCES Emotion(emotion_id),
    FOREIGN KEY (strategy_id) REFERENCES Coping_Strategy(strategy_id)
);

-- Insert test parent user values
INSERT INTO Parents (parent_fname, parent_username, parent_password, parent_email)
VALUES ('John Doe', 'john.doe', 'password123', 'john.doe@example.com');


-- Insert test user values
INSERT INTO Users (parent_user_id, user_fname, user_username, user_dob, user_password, consent_status)
VALUES (1, 'Jane Doe', 'jane.doe', '2000-01-01', 'password456', TRUE);

