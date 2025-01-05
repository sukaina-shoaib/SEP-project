# project
# Hospital Receptionist management System 


# 1. Introduction:-
    The Hospital Receptionist Management System (HRMS) is designed to streamline the operations of a hospital, focusing on patient management, appointment scheduling, billing, and doctor management. This report outlines the structure, functionality, and components of the HMS developed using PHP, MySQL, and HTML/CSS.
    
# 3. System Overview The HRMS consists of several key components:
•	Patient Management: Allows for the registration of new patients and management of existing patients.
•	Appointment Scheduling: Facilitates booking, updating, and deleting appointments with doctors.
•	Billing System: Generates bills based on appointments and doctor fees.
•	Doctor Management: Displays a list of doctors along with their specialties and availability.

# 4. File Structure The system is organized into multiple files, each serving a specific purpose:
•	Database Setup:
•	data.php: Creates the database and necessary tables (Doctor, Patient, Appointment, Bill) if they do not exist.
•	Database Connection:
•	hospital_database.php: Establishes a connection to the MySQL database.
•	Patient Management:
•	appointment.php: Handles patient registration and appointment booking.
•	delete_appointment.php: Manages the deletion of appointments and associated bills.
•	form.php: Main interface for patient management, including forms for new and existing patients.
•	Billing:
•	bill.php: Displays the billing details for appointments.
•	Doctor Management:
•	doctor.php: Lists all doctors and their details.
•	User Authentication:
•	Styling and Scripts:
•	form.css: Contains styles for the forms and overall layout.
•	style.css: Styles for the login page and other components.

# 5. Key Features
•	Patient Registration: New patients can register by providing their details, which are stored in the database.
•	Appointment Booking: Patients can book appointments with doctors, and the system automatically calculates the billing amount based on the doctor's fee.
•	Bill Generation: After an appointment, a bill is generated and displayed to the patient.
•	Doctor List: A comprehensive list of doctors is available, showing their specialties and availability.
•	Responsive Design: The system is designed to be user-friendly and responsive across different devices.

# 6. Database Design The database consists of the following tables:
•	Doctor: Stores information about doctors, including their ID, name, specialty, availability, and fees.
•	Patient: Contains patient details such as ID, name, date of birth, contact number, and address.
•	Appointment: Links patients to doctors, storing appointment details like day and time.
•	Bill: Records billing information related to appointments.

# 7. Challenges Faced
•	Database Integration: Ensuring smooth interaction between the PHP scripts and the MySQL database required careful planning and testing.
•	User Interface Design: Creating a user-friendly interface that is both functional and visually appealing was a significant focus.
# 8. Conclusion :-
The Hospital Management System provides a comprehensive solution for managing hospital operations efficiently. It enhances patient experience through streamlined processes and effective management of appointments and billing. Future improvements could include adding features such as online appointment scheduling and patient feedback systems.
# 10. References
•	PHP Documentation: php.net
•	MySQL Documentation: mysql.com
•	HTML/CSS Resources: w3schools.com

# How to Run the Project Locally
Prerequisites
A local server environment (e.g., XAMPP, WAMP, or MAMP).
A web browser.
PHP (v7.4 or later) and MySQL installed.
Steps
Download the Project

Download the project files as a ZIP or clone the repository.
Set Up Local Server

Install and start XAMPP/WAMP/MAMP.
Ensure Apache and MySQL modules are running.
Place Files

Copy the project folder into the htdocs directory (for XAMPP) or the respective folder for your server.
Database Configuration

Open phpMyAdmin via your local server (e.g., http://localhost/phpmyadmin).
Create a database named hospital_management.
Import the data.php file to set up tables and initial data.
Modify Database Connection

Open hospital_database.php.
Ensure the database credentials match your server setup
Run the Project

Open a web browser.
Navigate to http://localhost/Hospital_Receptionist_Management_System/form.php.

