-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 12:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dentalworkclinic_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(150) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Booked','Checked-in','Waiting','With Doctor','Completed','Cancelled','No-show') DEFAULT 'Booked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `patient_name`, `doctor_id`, `department_id`, `appointment_date`, `appointment_time`, `reason`, `notes`, `status`, `created_at`, `updated_at`, `reminder_sent`) VALUES
(6, 1, 'Test Patient Updated', 1, 2, '2026-09-12', '10:30:00', '-', NULL, 'Booked', '2026-09-11 06:01:34', '2026-09-11 06:01:34', 0),
(11, 9, 'Gopal', 1, 2, '2026-09-11', '17:00:00', '', NULL, 'Booked', '2026-09-11 08:13:20', '2026-09-11 08:13:20', 0),
(12, 10, 'Sakshi', 1, 2, '2026-09-11', '15:00:00', '', NULL, 'Booked', '2026-09-11 10:35:21', '2026-09-11 10:35:21', 0),
(13, 10, 'Sakshi', 1, 2, '2026-09-12', '15:00:00', '-', NULL, 'Booked', '2026-09-11 10:36:25', '2026-09-11 10:36:25', 0);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `description`, `status`, `created_at`) VALUES
(1, 'General Dentistry', 'General dental consultation and treatment', 'active', '2026-09-07 09:53:46'),
(2, 'Orthodontics', 'Braces and teeth alignment treatment', 'active', '2026-09-07 09:53:46'),
(3, 'Endodontics', 'Root canal and related dental treatment', 'active', '2026-09-07 09:53:46'),
(4, 'Periodontics', 'Gum and periodontal treatment', 'active', '2026-09-07 09:53:46'),
(5, 'Prosthodontics', 'Dental crowns, bridges and dentures', 'active', '2026-09-07 09:53:46'),
(6, 'Pediatric Dentistry', 'Dental care for children', 'active', '2026-09-07 09:53:46'),
(7, 'Oral & Maxillofacial Surgery', 'Oral and dental surgical procedures', 'active', '2026-09-07 09:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `doctor_name` varchar(150) NOT NULL,
  `specialization` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `doctor_name`, `specialization`, `phone`, `email`, `status`, `created_at`) VALUES
(1, NULL, 2, 'Dr. Test Kumar', 'Orthodontics', NULL, NULL, 'active', '2026-09-09 11:07:43'),
(2, NULL, 1, 'Dr. Anil Sharma', 'General Dentistry', NULL, NULL, 'active', '2026-09-11 10:38:22'),
(3, NULL, 3, 'Dr. Priya Reddy', 'Endodontics', NULL, NULL, 'active', '2026-09-11 10:38:22'),
(4, NULL, 4, 'Dr. Suresh Patil', 'Periodontics', NULL, NULL, 'active', '2026-09-11 10:38:22'),
(5, NULL, 5, 'Dr. Neha Joshi', 'Prosthodontics', NULL, NULL, 'active', '2026-09-11 10:38:22'),
(6, NULL, 6, 'Dr. Kavya Rao', 'Pediatric Dentistry', NULL, NULL, 'active', '2026-09-11 10:38:22'),
(7, NULL, 7, 'Dr. Vikram Singh', 'Oral & Maxillofacial Surgery', NULL, NULL, 'active', '2026-09-11 10:38:22');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) DEFAULT 0.00,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `due_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('Unpaid','Partially Paid','Paid','Cancelled') DEFAULT 'Unpaid',
  `invoice_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `patient_id`, `appointment_id`, `invoice_number`, `total_amount`, `discount`, `net_amount`, `paid_amount`, `due_amount`, `status`, `invoice_date`, `created_at`) VALUES
(1, 1, NULL, 'INV-1001', 2500.00, 0.00, 2500.00, 1500.00, 1000.00, 'Partially Paid', '2026-09-10', '2026-09-10 06:38:45'),
(2, 4, NULL, 'INV-000002', 1500.00, 100.00, 1400.00, 1400.00, 0.00, 'Paid', '2026-09-10', '2026-09-10 09:39:08');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `record_type` varchar(100) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `appointment_id`, `record_type`, `title`, `description`, `file_path`, `record_date`, `created_at`) VALUES
(1, 1, 1, NULL, 'Consultation', 'Initial Checkup', 'Routine dental checkup, no major issues found.', NULL, '2026-09-10', '2026-09-10 06:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `patient_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'Appointment Reminder', 'Your appointment is scheduled for tomorrow.', 'reminder', 1, '2026-09-10 06:38:45'),
(2, 1, 'Payment Received', 'We received your payment of ₹1500.', 'billing', 1, '2026-09-10 06:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mrn` varchar(50) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `mrn`, `full_name`, `mobile`, `date_of_birth`, `gender`, `address`, `area`, `email`, `created_at`, `updated_at`) VALUES
(1, 1, 'SHVMS-MRN-000001', 'Test Patient Updated', '9876543210', '2000-01-01', '', 'Updated Test Address', 'Ring Road', 'patient@test.com', '2026-09-07 09:57:40', '2026-09-10 09:45:46'),
(4, 6, 'SVMSDH-000004', 'Test CurlPatient', '9812345670', '1995-05-05', 'Male', '', 'Ring Road', 'walkin_9812345670@no-email.local', '2026-09-10 09:37:11', '2026-09-10 09:37:11'),
(5, 9, 'SVMSDH-000005', 'Sakshi', '7498486711', '2004-06-08', 'Female', 'Kalaburagi City', 'Kalaburagi City', 'walkin_7498486711@no-email.local', '2026-09-11 06:05:15', '2026-09-11 06:05:15'),
(9, 13, 'SVMSDH-000006', 'Gopal', '7889877332', NULL, 'Male', '--', 'Kailash Nagar', 'tidkegopal62@gmail.com', '2026-09-11 08:13:20', '2026-09-11 08:13:20'),
(10, 14, 'SVMSDH-000007', 'Sakshi', '8080063117', '2026-09-12', 'Female', 'Aland Road', 'Aland Road', 'sakshic398@gmail.com', '2026-09-11 10:35:21', '2026-09-11 10:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','UPI','Card','Bank Transfer','Other') NOT NULL,
  `transaction_reference` varchar(150) DEFAULT NULL,
  `payment_date` datetime NOT NULL,
  `status` enum('Success','Pending','Failed','Refunded') DEFAULT 'Success',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `patient_id`, `amount`, `payment_method`, `transaction_reference`, `payment_date`, `status`, `notes`, `created_at`) VALUES
(1, 1, 1, 1500.00, 'UPI', 'TXN123456', '2026-09-10 12:08:45', 'Success', NULL, '2026-09-10 06:38:45'),
(2, 2, 4, 1400.00, 'Cash', '', '2026-09-10 11:39:24', 'Success', '', '2026-09-10 09:39:24');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `prescription_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `patient_id`, `doctor_id`, `appointment_id`, `prescription_date`, `notes`, `created_at`) VALUES
(1, 1, 1, NULL, '2026-09-10', 'Take medicines after food', '2026-09-10 06:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `medicine_name` varchar(200) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `prescription_items`
--

INSERT INTO `prescription_items` (`id`, `prescription_id`, `medicine_name`, `dosage`, `frequency`, `duration`, `instructions`) VALUES
(1, 1, 'Amoxicillin', '500mg', 'Twice a day', '5 days', 'Take after meals'),
(2, 1, 'Ibuprofen', '400mg', 'As needed', '3 days', 'For pain relief');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `referring_doctor_id` int(11) DEFAULT NULL,
  `referred_doctor_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `referral_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Accepted','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `patient_id`, `referring_doctor_id`, `referred_doctor_id`, `appointment_id`, `referral_date`, `reason`, `notes`, `status`, `created_at`) VALUES
(1, 1, 1, 1, NULL, '2026-09-10', 'Root Canal Treatment', NULL, 'Pending', '2026-09-10 06:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `treatment_name` varchar(200) NOT NULL,
  `tooth_number` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Planned','In Progress','Completed','Cancelled') DEFAULT 'Planned',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','receptionist','doctor','patient') NOT NULL DEFAULT 'patient',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'patient@test.com', '$2y$10$/mB/kqrnfrE6ETPhXO7ceuCxZfuGa2zHCNNmb1RERJ7/fIOiVaFcC', 'patient', 'active', '2026-09-07 09:57:40'),
(2, 'receptionist@123.com', '$2b$12$HWxIIrda7dXaO/Ee8FVK7eiCf3H15cCBBDJwqS.osGjudHo9fD3Ue', 'receptionist', 'active', '2026-09-09 10:55:47'),
(6, 'walkin_9812345670@no-email.local', '$2y$10$j9JDP02xZaOLU8yOPVlsYuQyCtUS/ZsTFiGbfQZo8QBDRXAos5yZG', 'patient', 'active', '2026-09-10 09:37:11'),
(9, 'walkin_7498486711@no-email.local', '$2y$10$Yjgrhi1JUmK60eh6ZuNIf.fYEnghVPfiU8H/WE9J6ZnuEr92ylsXC', 'patient', 'active', '2026-09-11 06:05:15'),
(13, 'tidkegopal62@gmail.com', '$2y$10$hHh1KA2tODHBi.6n6ueD8.94oDAEyJd33I1J/ZhXspAQRPsZ03HNe', 'patient', 'active', '2026-09-11 08:13:20'),
(14, 'sakshic398@gmail.com', '$2y$10$LlHqd5GVs0m2OGCjbsNdr.fDPpLCbgkxSPjC9VYQq0qyDj06Eqs2K', 'patient', 'active', '2026-09-11 10:35:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appointment_patient` (`patient_id`),
  ADD KEY `fk_appointment_doctor` (`doctor_id`),
  ADD KEY `fk_appointment_department` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_doctor_department` (`department_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `fk_invoice_patient` (`patient_id`),
  ADD KEY `fk_invoice_appointment` (`appointment_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_medical_patient` (`patient_id`),
  ADD KEY `fk_medical_doctor` (`doctor_id`),
  ADD KEY `fk_medical_appointment` (`appointment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_patient` (`patient_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `mrn` (`mrn`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_invoice` (`invoice_id`),
  ADD KEY `fk_payment_patient` (`patient_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prescription_patient` (`patient_id`),
  ADD KEY `fk_prescription_doctor` (`doctor_id`),
  ADD KEY `fk_prescription_appointment` (`appointment_id`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prescription_item` (`prescription_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_referral_patient` (`patient_id`),
  ADD KEY `fk_referring_doctor` (`referring_doctor_id`),
  ADD KEY `fk_referred_doctor` (`referred_doctor_id`),
  ADD KEY `fk_referral_appointment` (`appointment_id`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_treatment_patient` (`patient_id`),
  ADD KEY `fk_treatment_doctor` (`doctor_id`),
  ADD KEY `fk_treatment_appointment` (`appointment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `treatments`
--
ALTER TABLE `treatments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointment_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointment_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `fk_doctor_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_doctor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoice_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoice_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `fk_medical_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_medical_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_medical_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patient_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_prescription_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prescription_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prescription_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `fk_prescription_item` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `fk_referral_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_referral_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_referred_doctor` FOREIGN KEY (`referred_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_referring_doctor` FOREIGN KEY (`referring_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `treatments`
--
ALTER TABLE `treatments`
  ADD CONSTRAINT `fk_treatment_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_treatment_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_treatment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

