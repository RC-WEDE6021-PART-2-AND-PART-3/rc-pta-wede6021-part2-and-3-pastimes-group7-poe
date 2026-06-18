Pastimes – Online Second-Hand Clothing Store
WEDE6021 Portfolio of Evidence (POE)
Student Information
Student Name: Mampe Shaan Motsholane & Keletso Hope Thamaga
Student Number: ST10437865 & ST10456948
Group Name: GROUP 7
Module: WEDE6021 – Web Development (Intermediate)
Project Name: Pastimes

Project Overview
Pastimes is an online second-hand clothing store developed using PHP, MySQL, HTML5, CSS, and XAMPP. The application enables users to buy and sell pre-owned branded clothing through a secure and user-friendly web platform.
The system includes customer registration and login, administrator verification, shopping cart management, seller requests, messaging functionality, and delivery detail management.

System Requirements
The following software is required to run the application:
* XAMPP
* PHP 8.0 or higher
* MySQL
* phpMyAdmin
* Google Chrome, Microsoft Edge, or Mozilla Firefox

Installation Instructions
Step 1: Install XAMPP
Download and install XAMPP.
Step 2: Copy Project Files
Copy the Pastimes project folder into:
C:\xampp\htdocs\
Step 3: Start XAMPP Services
Open XAMPP Control Panel and start:
* Apache
* MySQL
Step 4: Create Database
Open phpMyAdmin:
http://localhost/phpmyadmin
Create a database named:
ClothingStore
Step 5: Import Database
Import the SQL file:
myClothingStore.sql
This file creates all required database tables.
Step 6: Load Sample Data
The project includes:
* userData.txt
* adminData.txt
* clothesData.txt
Run:
* createTable.php
or
* loadClothingStore.php
to populate the database.
Step 7: Launch Application
Open a browser and navigate to:
http://localhost/Pastimes

Features Implemented
User Registration
Users can register by entering:
* Name
* Email Address
* Username
* Password
Passwords are encrypted before storage in the database.

User Login
Users can:
* Login using registered credentials
* Access the system only after administrator approval

Shopping Cart
Customers can:
* Add products to cart
* Remove products from cart
* Update quantities
* Continue shopping
* Checkout selected products

Delivery Details
Customers can:
* Enter delivery addresses
* Save courier information

Messaging System
Customers can:
* Send messages
* Receive messages
* Communicate with sellers and administrators

Seller Requests
Sellers can:
* Request to sell clothing items
* Upload product images
* Add descriptions
* Specify clothing brands

Administrator Features
Administrators can:
* Login securely
* Verify new users
* Add users
* Update users
* Delete users
* Add clothing items
* Update clothing items
* Delete clothing items
* Review seller requests
* Communicate with buyers and sellers

Database Tables
tblUser
Stores customer information.
FieldDescriptionidPrimary KeynameCustomer NameemailEmail AddressusernameUsernamepasswordHashed PasswordstatusPending / Approved
tblAdmin
Stores administrator details.
FieldDescriptionidPrimary KeyemailAdministrator EmailpasswordHashed Password
tblClothes
Stores clothing products.
FieldDescriptionidPrimary KeynameProduct NamebrandProduct BranddescriptionProduct DescriptionpriceProduct PriceimageProduct Image
tblMessages
Stores communication between users.

tblDelivery
Stores customer delivery information.

sell_requests
Stores clothing sale requests submitted by sellers.

Folder Structure
Pastimes/
??? DBConn.php
??? createTable.php
??? loadClothingStore.php
??? login.php
??? register.php
??? admin.php
??? shop.php
??? cart.php
??? checkout.php
??? sell_request.php
??? message.php
??? delivery.php
?
??? css/
? ??? style.css
?
??? images/
? ??? product images
?
??? database/
? ??? myClothingStore.sql
? ??? userData.txt
? ??? adminData.txt
? ??? clothesData.txt
?
??? documentation/
??? README.md

Default Administrator Account
Example Administrator Login:
Email: admin@pastimes.co.za
Password: admin123
Note: Administrator credentials should be changed before deployment.

Security Features
The application includes:
* Password hashing
* Session management
* Input validation
* Required form fields
* User verification
* Access control

Known Limitations
Current limitations include:
* No online payment gateway
* No email notification system
* Limited mobile responsiveness
* No advanced product filtering

Future Enhancements
Future versions may include:
* Online payments
* Product search functionality
* Email notifications
* Order tracking
* Mobile application integration


