# Emotional Regulation App

A comprehensive journaling application designed to help children and individuals effectively express and identify their emotions and their connections to thoughts and behaviors. The application features tools for both children and parents to track and manage emotional regulation through journal entries, emotion selection, and personalized coping strategies.

## Table of Contents
- [Overview](#overview)
- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Database Structure](#database-structure)
- [Development Sprints](#development-sprints)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Contact](#contact)
- [Acknowledgments](#acknowledgments)

## Overview

The Emotional Regulation App is a web-based platform that helps users identify, track, and manage their emotions through guided journaling, personalized coping strategies, and progress visualization. The application supports both individual users and parent-child relationships, offering an accessible platform for emotional wellness development.

### Purpose
- Help users recognize and identify their emotions
- Provide personalized coping strategies based on emotional states
- Track emotional patterns over time to enhance self-awareness
- Create a structured journaling system for emotional reflection
- Deliver visual progress tracking for emotional regulation development

## Installation

### Prerequisites
- Web server with PHP 7.0+ support (Apache recommended)
- MySQL 5.7+ or MariaDB 10.2+
- PHP extensions: mysqli, PDO

### Setup Steps
1. Clone the repository to your web server's document root
   ```
   git clone https://github.com/DakotaPennwest/Team-Workaholics-Capstone.git
   ```

2. Import the database schema
   ```
   mysql -u username -p database_name < emotionalRegulationApp.sql
   ```

3. Configure database connection
   - Edit the `db_connect.php` file with your database credentials:
   ```php
   $dsn = 'mysql:host=localhost;dbname=EmotionalRegulationApp';
   $username = 'your_username';
   $password = 'your_password';
   ```

4. Ensure the web server has read/write permissions for the application directory

5. Access the application through your web browser at your server URL

## Usage

1. Start the application in your web browser

2. **Test User Accounts**
   - **Child Test User Login:**
     - Username: `testUser`
     - Password: `password456`
   - **Parent Test User Login:**
     - Username: `testParentUser`
     - Password: `password123`

3. **Account Creation**
   - Choose to create a solo user account (for individuals 14+)
   - Or create a parent account with connected child accounts

4. **Journaling Process**
   - From the homepage, click "Begin Journal" to start the journaling process
   - Select an emotion that represents how you're feeling
   - Rate the intensity of the emotion on a scale of 1-5
   - Write your journal entry (optional prompts are available to help guide your writing)
   - Submit your journal to receive a personalized coping strategy

5. **Coping Strategies**
   - View your assigned coping strategy after completing a journal
   - Follow the illustrated steps to implement the strategy
   - Provide feedback on the strategy's effectiveness
   - Receive new strategies after every 5 journal entries

6. **Progress Tracking**
   - Access the "Progress" section to view reports on:
     - Emotion frequency and patterns
     - Strategy effectiveness
     - Journaling consistency

## Features

### User Account Management
- Three account types: Solo User, Parent User, Child User
- Secure login/logout functionality
- Password hashing for security
- Session management

### Emotion Tracking
- Selection from 21 predefined emotions (7 primary, 14 secondary)
- Emotion categories: Happy, Sad, Scared, Angry, Surprised, Disgusted, Calm
- Intensity rating on a 5-point scale
- Secondary emotions unlock after using primary emotions multiple times

### Journaling System
- Guided journaling with emotion-specific prompts
- Daily journal entries
- Historical journal review
- Journal entry export functionality

### Coping Strategies
- Six core coping strategies mapped to emotion categories:
  1. **Guided Visualization** (for Fear-based emotions)
  2. **Deep Breathing** (for Anger-based emotions)
  3. **Expressing Gratitude** (for Happiness/neutral emotions)
  4. **Positive Affirmations** (for Sadness-based emotions)
  5. **Grounding Techniques** (for Surprise-based emotions)
  6. **Cognitive Reframing** (for Disgust-based emotions)
- Strategy effectiveness feedback
- Strategy rotation based on journaling frequency

### Progress Tracking
- Emotion frequency visualization
- Strategy effectiveness reporting
- Emotional intensity tracking
- Historical data analysis

## Technology Stack

### Front-End
- HTML5
- CSS3 with custom frameworks
- JavaScript for interactivity
- SVG graphics for visual elements

### Back-End
- PHP 7+ for server-side processing
- MySQL for database management
- JSON for data exchange
- Session-based authentication

### Design Approach
- Cloud-based image resources
- RESTful API endpoints

## Database Structure

The application uses a relational database with the following key tables:

- **Parents**: Stores parent user information
- **Users**: Stores child or solo user information
- **Emotion**: Catalogs different emotions for selection
- **Coping_Strategy**: Stores emotional regulation strategies
- **Journal_Entry**: Records user journal entries
- **Assigned_Strategy**: Tracks which strategies are assigned to users
- **Strategy_Feedback**: Collects user feedback on strategies
- **Emotional_Strategy_Link**: Maps emotions to appropriate strategies

## Development Sprints

### Sprint 1: Account Management
- Child and Parent Account Creation/Login
- Design account creation forms
- Implement login functionality and error handling

### Sprint 2: Journal Entry Feature
- Develop UI for journal entry input
- Create journal entry prompts
- Implement save functionality for journal entry input
- Session management for journal data

### Sprint 3: Emotion Selection and Intensity Rating
- Interface for emotion selection by category
- Corresponding emotions relating to core emotions
- UI for emotion selections and intensity rating
- Save emotion data to corresponding journal entries

### Sprint 4: Coping Strategies Library and Feedback
- Curated list of coping strategies assigned to corresponding emotions
- Developed walkthroughs describing strategy use
- Interface for accessing strategies
- Feedback mechanism on usage and effectiveness of strategies

### Sprint 5: Report Generation
- Journal search functionality
- UI for report generation and output
- Functions to download user input

### Sprint 6: Final Debugging and Refinement
- Thorough testing of all features
- Bug fixing and issue resolution
- Verified feedback mechanisms
- User interface and experience adjustments

## Testing

- **End-to-End Testing**: Testing the complete application flow
- **Functional Testing**: Testing individual components and features
- **Security Testing**: Identifying and addressing security vulnerabilities
- **User Acceptance Testing (UAT)**: Testing with target users
- **Bug Fixing**: Addressing and resolving issues identified during testing
- **Stakeholder Communication**: Reporting progress and gathering feedback

## Troubleshooting

### Common Issues
- **Login Problems**: Check database credentials in `db_connect.php`
- **Journal Entry Failures**: Verify session variables are set correctly
- **Strategy Assignment Issues**: Check the database for mapping errors

## License

[Insert License Information Here]

## Contact

Team Workaholics
- GitHub: [https://github.com/DakotaPennwest/Team-Workaholics-Capstone](https://github.com/DakotaPennwest/Team-Workaholics-Capstone)
- [Insert Additional Contact Information]

## Acknowledgments

[Insert Acknowledgments Here]
