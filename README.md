# 🎬 Online Streaming Video Platform

A PHP + MySQL powered web application for uploading, viewing, and categorizing online videos.  
Supports multiple categories like Sports, Gaming, News, and Cartoons, along with user accounts, likes, and profiles.

---

## 📌 Features

- **User Accounts**
  - Registration & Login
  - Profile editing
  - Logout functionality

- **Video Uploading**
  - Upload videos by category (sports, gaming, news, cartoons)
  - Support for multiple upload forms
  - View uploaded videos in a public gallery

- **Video Interaction**
  - Like videos
  - View video details
  - Live streaming section

- **Categories**
  - Sports
  - Gaming
  - News
  - Cartoons

- **Search & Browsing**
  - Search videos by title
  - Filter by category

- **Admin Panel**
  - Manage videos
  - Manage users
  - Delete inappropriate content

---

## 🛠 Tech Stack

- **Frontend:** HTML, CSS
- **Backend:** PHP
- **Database:** MySQL
- **Web Server:** Apache (XAMPP, WAMP, or similar)
- **Media Storage:** Local uploads directory

---
onlinestreaming/
├── addvideo.php # Form to add new video
├── admin.php # Admin dashboard
├── cartoon.php # Cartoon video listing
├── db.php # Database connection file
├── delete_video.php # Delete video handler
├── edit_profile.php # Edit user profile page
├── gaming.php # Gaming video listing
├── get_user_details.php # Fetch user details via AJAX
├── home.php # Homepage (logged-in view)
├── img/ # Images used in the site
├── index.html # Landing page
├── index.php # Main login/registration entry point
├── like_handler.php # Backend like system
├── like_video.php # Like action
├── live.php # Live streaming section
├── login.php # Login processing
├── logout.php # Logout processing
├── news.php # News video listing
├── profile.php # User profile page
├── pstyle.css # Profile page styles
├── registration.php # Registration form
├── regstyle.css # Registration form styles
├── search.php # Search functionality
├── sports.php # Sports video listing
├── sss.txt # Misc notes or test data
├── up.php # General upload form
├── upload.php # Upload handler
├── uploadc.php # Upload cartoons
├── uploaded_videos.php # Show all uploaded videos
├── uploadg.php # Upload gaming videos
├── uploadn.php # Upload news videos
├── uploads/ # Uploaded video storage
├── uploads.php # Upload handler for main videos
├── users.php # User management
├── userstyle.css # User dashboard styles
├── video.php # Single video view page
├── vstyle.css # Video styles

---

## ⚙️ Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/onlinestreamingvideo.git
   cd onlinestreamingvideo/4228
Set Up Web Server

Place the onlinestreaming folder inside your server's root directory:

XAMPP: htdocs/

WAMP: www/

Start Apache and MySQL.

Create Database

Open phpMyAdmin at http://localhost/phpmyadmin

Create a new database, e.g., video_streaming

Import the provided .sql file if available, or manually create tables based on db.php connection settings.

Configure Database Connection

Open db.php and set your database credentials:

php
Copy
Edit
$conn = mysqli_connect("localhost", "root", "", "video_streaming");
Access the App

Go to http://localhost/4228/ in your browser.

🚀 Usage
Register/Login → Create an account

Upload Videos → Choose category & upload

Like & View → Interact with videos

Search → Find videos quickly

Admin → Manage content & users

