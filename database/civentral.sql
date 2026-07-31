-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2026 at 10:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `civentral`
--

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `action_id` int(11) NOT NULL,
  `action_name` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive','Archived') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`action_id`, `action_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'View', NULL, 'Active', '2026-07-28 14:24:09', '2026-07-28 14:24:27'),
(2, 'Create', NULL, 'Active', '2026-07-28 14:24:09', '2026-07-28 14:24:09'),
(3, 'Edit', NULL, 'Active', '2026-07-28 14:24:09', '2026-07-28 14:24:09'),
(4, 'Delete', NULL, 'Active', '2026-07-28 14:24:09', '2026-07-28 14:24:09'),
(5, 'Export', NULL, 'Active', '2026-07-28 14:24:09', '2026-07-28 14:24:09'),
(6, 'Approve', NULL, 'Active', '2026-07-28 14:24:09', '2026-07-28 14:24:09'),
(7, 'Archive', NULL, 'Active', '2026-07-31 01:10:50', '2026-07-31 01:10:50'),
(8, 'Restore', NULL, 'Active', '2026-07-31 01:10:50', '2026-07-31 01:10:50'),
(9, 'Reject', NULL, 'Active', '2026-07-31 01:10:50', '2026-07-31 01:10:50');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `audit_id` bigint(20) NOT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `target_table` varchar(100) DEFAULT NULL,
  `target_id` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_uri` varchar(255) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `status` enum('Success','Failed') DEFAULT 'Success',
  `context_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`audit_id`, `actor_user_id`, `session_id`, `department_id`, `module_id`, `resource_id`, `action`, `target_table`, `target_id`, `description`, `ip_address`, `request_method`, `request_uri`, `browser`, `operating_system`, `status`, `context_json`, `created_at`) VALUES
(1, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to a***n@civentral.gov.ph', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-25 17:04:28'),
(2, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-25 17:04:50'),
(3, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to a***n@civentral.gov.ph', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 02:22:02'),
(4, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 02:22:13'),
(5, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to a***n@civentral.gov.ph', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 04:50:28'),
(6, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 04:50:44'),
(7, 1, NULL, NULL, NULL, NULL, 'Change Password', 'users', '1', 'User successfully updated their account password.', '::1', 'POST', '/civentral/api/employee/change-password.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 04:53:26'),
(8, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to a***n@civentral.gov.ph', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 04:56:31'),
(9, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 04:56:44'),
(10, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:04:48'),
(11, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:05:13'),
(12, 1, NULL, NULL, NULL, NULL, 'Change Password', 'users', '1', 'User successfully updated their account password.', '::1', 'POST', '/civentral/api/employee/change-password.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:05:47'),
(13, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:15:33'),
(14, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:15:41'),
(15, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '4', 'Created department \"Education & Scholarship\" (ESMS)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:17:15'),
(16, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '5', 'Created department \"Citizenship Information  & Engagement\" (CIE)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:18:44'),
(17, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '6', 'Created department \"Permits & Licensing Management\" (PLM)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:19:15'),
(18, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '7', 'Created department \"Social Services Management\" (SSM)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:19:30'),
(19, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '8', 'Created department \"Health & Sanitation Management\" (HSM)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:19:45'),
(20, 1, NULL, NULL, NULL, NULL, 'Update Department', 'departments', '8', 'Updated department ID 8', '::1', 'PUT', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:20:01'),
(21, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '9', 'Created department \"Disaster Risk Reduction & Emergency Response\" (DRRM)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:20:28'),
(22, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '10', 'Created department \"Urban Planning Zoning & Housing\" (UPZH)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:20:53'),
(23, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '11', 'Created department \"Revenue Collection & Treasury Services\" (RCTS)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:21:13'),
(24, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '12', 'Created department \"Transport & Mobility Management\" (TMM)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:21:30'),
(25, 1, NULL, NULL, NULL, NULL, 'Create Department', 'departments', '13', 'Created department \"Public Assets & Facilities Management\" (PAFM)', '::1', 'POST', '/civentral/api/employee/departments.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:21:48'),
(26, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:31:05'),
(27, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:31:46'),
(28, 1, NULL, NULL, NULL, NULL, 'Create Role', 'roles', '4', 'Created role \"Eudcation Scholarship Adminmistrator\" (ESA)', '::1', 'POST', '/civentral/api/employee/roles.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:34:59'),
(29, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:50:30'),
(30, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:51:07'),
(31, 1, NULL, NULL, NULL, NULL, 'Create User Account', 'users', '2', 'Created user account for John Laurence Gilbuena (ESA-2026-001)', '::1', 'POST', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 06:54:42'),
(32, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:02:58'),
(33, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:03:28'),
(34, 1, NULL, NULL, NULL, NULL, 'Create User Account', 'users', '3', 'Created user account for John Laurence Gilbuena (ESA-2026-001)', '::1', 'POST', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:04:33'),
(35, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:05:56'),
(36, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:06:16'),
(37, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:19:06'),
(38, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:19:21'),
(39, 1, NULL, NULL, NULL, NULL, 'Create Module', 'modules', '4', 'Created module \"User & Account Management\"', '::1', 'POST', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:35:13'),
(40, 1, NULL, NULL, NULL, NULL, 'Create Module', 'modules', '5', 'Created module \"Role & Permission Management\"', '::1', 'POST', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:35:35'),
(41, 1, NULL, NULL, NULL, NULL, 'Create Module', 'modules', '6', 'Created module \"Department Management\"', '::1', 'POST', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:35:58'),
(42, 1, NULL, NULL, NULL, NULL, 'Create Module', 'modules', '7', 'Created module \"Citizen Management\"', '::1', 'POST', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:36:12'),
(43, 1, NULL, NULL, NULL, NULL, 'Create Module', 'modules', '8', 'Created module \"Audit Logs System\"', '::1', 'POST', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:36:25'),
(44, 1, NULL, NULL, NULL, NULL, 'Create Resource', 'resources', '6', 'Created resource \"Users Account\" under module ID 4', '::1', 'POST', '/civentral/api/employee/resources.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:54:37'),
(45, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:55:00'),
(46, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 07:55:07'),
(47, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:04:01'),
(48, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:04:08'),
(49, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '1', 'Updated permissions matrix for role \"Super Administrator\" (6 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:05:00'),
(50, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:06:57'),
(51, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:07:10'),
(52, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (1 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:07:21'),
(53, 1, NULL, NULL, NULL, NULL, 'Update Access Control Matrix', 'roles', '4', 'Updated department access boundary for role \"Eudcation Scholarship Adminmistrator\" (Global Access: NO)', '::1', 'POST', '/civentral/api/employee/access-control.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:09:31'),
(54, 1, NULL, NULL, NULL, NULL, 'Update Access Control Matrix', 'roles', '4', 'Updated department access boundary for role \"Eudcation Scholarship Adminmistrator\" (Global Access: NO)', '::1', 'POST', '/civentral/api/employee/access-control.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:09:41'),
(55, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (2 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:13:03'),
(56, 1, NULL, NULL, NULL, NULL, 'Create Resource', 'resources', '13', 'Created resource \"dsdsada\" under module ID 9', '::1', 'POST', '/civentral/api/employee/resources.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:15:57'),
(57, 1, NULL, NULL, NULL, NULL, 'Update Resource', 'resources', '7', 'Updated resource ID 7', '::1', 'PUT', '/civentral/api/employee/resources.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:16:48'),
(58, 1, NULL, NULL, NULL, NULL, 'Update Resource', 'resources', '7', 'Updated resource ID 7', '::1', 'PUT', '/civentral/api/employee/resources.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:17:02'),
(59, 1, NULL, NULL, NULL, NULL, 'Update Module', 'modules', '9', 'Updated module ID 9', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:17:11'),
(60, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (3 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:17:56'),
(61, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (2 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:17:59'),
(62, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:24:55'),
(63, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:25:09'),
(64, 1, NULL, NULL, NULL, NULL, 'Create Role', 'roles', '5', 'Created role \"Scholarship Coordinator\" (SC)', '::1', 'POST', '/civentral/api/employee/roles.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:26:25'),
(65, 1, NULL, NULL, NULL, NULL, 'Create Role', 'roles', '6', 'Created role \"Health Sanitation Administrator\" (HSA)', '::1', 'POST', '/civentral/api/employee/roles.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 08:29:40'),
(66, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:03:27'),
(67, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:03:32'),
(68, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:04:12'),
(69, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:04:25'),
(70, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:06:51'),
(71, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:07:06'),
(72, 3, NULL, NULL, NULL, NULL, 'Change Password', 'users', '3', 'User successfully updated their account password.', '::1', 'POST', '/civentral/api/employee/change-password.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:07:41'),
(73, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:35:34'),
(74, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:35:46'),
(75, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:50:22'),
(76, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 09:50:36'),
(77, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:00:50'),
(78, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:01:02'),
(79, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:11:10'),
(80, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:11:15'),
(81, 3, NULL, NULL, NULL, NULL, 'Create User Account', 'users', '4', 'Created user account for Mae Basco (SC-2026-001)', '::1', 'POST', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:13:09'),
(82, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:13:57'),
(83, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:14:28'),
(84, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:15:38'),
(85, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (12 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:17:21'),
(86, 1, NULL, NULL, NULL, NULL, 'Create Resource', 'resources', '21', 'Created resource \"Account Status\" under module ID 4', '::1', 'POST', '/civentral/api/employee/resources.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:18:58'),
(87, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '1', 'Updated permissions matrix for role \"Super Administrator\" (18 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:19:11'),
(88, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:24:07'),
(89, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:24:22'),
(90, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (0 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:24:36'),
(91, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (1 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:24:44'),
(92, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:30:38'),
(93, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:30:50'),
(94, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:31:00'),
(95, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:31:07'),
(96, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (0 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:31:39'),
(97, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (1 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:31:50'),
(98, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:32:26'),
(99, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:32:26'),
(100, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (2 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:35:34'),
(101, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (3 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:35:43'),
(102, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (4 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:35:52'),
(103, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (5 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:36:09'),
(104, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (6 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:36:17'),
(105, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (2 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:36:32'),
(106, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (1 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:39:12'),
(107, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (2 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:39:29'),
(108, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (3 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:39:40'),
(109, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (4 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:39:52'),
(110, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:43:14'),
(111, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:43:22'),
(112, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (6 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:47:45'),
(113, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (5 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:48:54'),
(114, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:59:00'),
(115, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 10:59:05'),
(116, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:00:03'),
(117, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:00:10'),
(118, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (5 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:00:21'),
(119, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (27 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:04:21'),
(120, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (7 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:04:39'),
(121, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (25 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:08:02'),
(122, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:09:03'),
(123, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:09:29'),
(124, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (12 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:10:21'),
(125, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:26:42'),
(126, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:26:54'),
(127, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (13 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:28:52'),
(128, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (21 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:32:03'),
(129, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:32:35'),
(130, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 11:32:44'),
(131, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 13:41:31'),
(132, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 14:19:32'),
(133, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 14:19:44'),
(134, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 14:34:46'),
(135, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 14:34:51'),
(136, 3, NULL, NULL, NULL, NULL, 'Update Profile', 'users', '3', 'User updated their personal profile details.', '::1', 'POST', '/civentral/api/employee/profile.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 14:35:28'),
(137, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 14:43:32'),
(138, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 14:43:32'),
(139, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-26 18:28:03'),
(140, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 02:52:17'),
(141, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 02:52:41'),
(142, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 03:56:05'),
(143, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 03:56:55'),
(144, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 06:01:45'),
(145, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 06:02:01');
INSERT INTO `audit_logs` (`audit_id`, `actor_user_id`, `session_id`, `department_id`, `module_id`, `resource_id`, `action`, `target_table`, `target_id`, `description`, `ip_address`, `request_method`, `request_uri`, `browser`, `operating_system`, `status`, `context_json`, `created_at`) VALUES
(146, 1, NULL, NULL, NULL, NULL, 'Update Profile', 'users', '1', 'User updated their personal profile details.', '::1', 'POST', '/civentral/api/employee/profile.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 06:43:43'),
(147, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 13:33:22'),
(148, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 13:33:40'),
(149, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 05:22:58'),
(150, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 05:23:24'),
(151, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 05:58:51'),
(152, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 05:59:11'),
(153, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:00:15'),
(154, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:00:35'),
(155, 1, NULL, NULL, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (47 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:01:05'),
(156, 3, NULL, NULL, NULL, NULL, 'Create Module', 'modules', '11', 'Created module \"Scholarship Application\"', '::1', 'POST', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:01:27'),
(157, 3, NULL, NULL, NULL, NULL, 'Create Resource', 'resources', '37', 'Created resource \"Scholarship Applicant\" under module ID 11', '::1', 'POST', '/civentral/api/employee/resources.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:02:16'),
(158, 1, NULL, NULL, NULL, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:18:38'),
(159, 1, NULL, NULL, NULL, NULL, 'Update Resource', 'resources', '37', 'Updated resource ID 37', '::1', 'PATCH', '/civentral/api/employee/resources.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:19:37'),
(160, 3, NULL, NULL, NULL, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:26:49'),
(161, 3, NULL, NULL, NULL, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PUT', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:27:02'),
(162, 3, NULL, NULL, NULL, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:27:27'),
(163, 3, NULL, NULL, NULL, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PUT', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:32:05'),
(164, 3, NULL, NULL, NULL, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:32:09'),
(165, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:34:16'),
(166, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:34:36'),
(167, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:38:51'),
(168, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:38:51'),
(169, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:39:26'),
(170, 3, NULL, NULL, NULL, NULL, 'Update User Account', 'users', '4', 'Updated user account ID 4 (SC-2026-001)', '::1', 'PUT', '/civentral/api/employee/users.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 06:39:26'),
(171, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-28 07:59:56'),
(172, 3, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 03:52:11'),
(173, 3, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 03:52:54'),
(174, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 10:22:14'),
(175, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 10:22:37'),
(176, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 10:23:01'),
(177, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 10:23:16'),
(178, 1, NULL, NULL, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 10:34:13'),
(179, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-30 10:34:35'),
(180, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 05:57:53'),
(181, 1, NULL, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 05:58:33'),
(182, 1, NULL, 4, NULL, NULL, 'Update Department', 'departments', '4', 'Updated department ID 4', '::1', 'PUT', '/civentral/api/employee/departments.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 06:12:47'),
(183, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 12:49:23'),
(184, 1, NULL, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 12:49:58'),
(185, 1, NULL, 1, NULL, NULL, 'Update Department', 'departments', '1', 'Update Department on departments #1: department_head_user_id: \"null\" → \"1\"', '::1', 'PUT', '/civentral/api/employee/departments.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"department_id\":1,\"department_code\":\"IT\",\"department_name\":\"Information Technology Department\",\"department_head_user_id\":null,\"description\":\"IT Systems and Infrastructure\",\"status\":\"Active\",\"created_at\":\"2026-07-25 21:51:44\",\"updated_at\":\"2026-07-25 21:51:44\"},\"new_data\":{\"department_id\":1,\"department_code\":\"IT\",\"department_name\":\"Information Technology Department\",\"department_head_user_id\":1,\"description\":\"IT Systems and Infrastructure\",\"status\":\"Active\",\"created_at\":\"2026-07-25 21:51:44\",\"updated_at\":\"2026-07-30 20:50:27\"},\"changes\":{\"department_head_user_id\":{\"old\":null,\"new\":1}}}', '2026-07-30 12:50:27'),
(186, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 14:26:36'),
(187, 1, NULL, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 14:27:28'),
(188, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 15:15:30'),
(189, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 15:33:19'),
(190, 1, 53, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 15:33:55'),
(191, 1, 53, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 16:09:05'),
(192, 1, NULL, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 16:09:32'),
(193, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 16:47:36'),
(194, 1, 55, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-30 16:48:36'),
(195, 3, NULL, 4, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Browser', 'Desktop OS', 'Success', NULL, '2026-07-30 17:19:45'),
(196, 3, NULL, 4, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Browser', 'Desktop OS', 'Success', NULL, '2026-07-30 17:25:55'),
(197, 3, 56, 4, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Browser', 'Desktop OS', 'Success', NULL, '2026-07-30 17:27:01'),
(198, 1, NULL, 1, NULL, NULL, 'Create Department', 'departments', '99', 'Test Department Created by script', '127.0.0.1', 'POST', '', 'Unknown', 'Unknown', 'Success', NULL, '2026-07-30 17:40:00'),
(199, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 04:04:09'),
(200, 1, NULL, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 04:04:28'),
(201, 3, NULL, 4, NULL, NULL, 'Initiate 2FA Login', 'users', '3', 'OTP code generated and sent to s**************p@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 04:04:57'),
(202, 3, NULL, 4, NULL, NULL, '2FA Verification Success', 'users', '3', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 04:05:02'),
(203, 3, NULL, 4, NULL, NULL, 'Create Role', 'roles', '7', 'Create Role on roles #7', '::1', 'POST', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":null,\"new_data\":{\"role_id\":7,\"role_name\":\"Scholarship Screening Committee\",\"role_prefix\":\"SSC\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 12:07:22\",\"updated_at\":\"2026-07-31 12:07:22\",\"department_id\":4},\"changes\":null}', '2026-07-31 04:07:22'),
(204, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 04:08:42'),
(205, 1, NULL, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 04:09:20'),
(206, 3, NULL, 4, NULL, NULL, 'Create Role', 'roles', '8', 'Create Role on roles #8', '::1', 'POST', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":null,\"new_data\":{\"role_id\":8,\"role_name\":\"Section Head\",\"role_prefix\":\"SH\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"head\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 12:17:14\",\"updated_at\":\"2026-07-31 12:17:14\",\"department_id\":4},\"changes\":null}', '2026-07-31 04:17:14'),
(207, 3, NULL, 4, NULL, NULL, 'Test Action', 'roles', '99', 'Testing audit logger dispatch', '127.0.0.1', 'POST', '', 'Unknown', 'Unknown', 'Success', NULL, '2026-07-31 04:25:56'),
(209, 3, NULL, 4, NULL, NULL, 'Create Role', 'roles', '9', 'Create Role on roles #9', '::1', 'POST', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":null,\"new_data\":{\"role_id\":9,\"role_name\":\"Administrative Assistant\",\"role_prefix\":\"AA\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 12:29:00\",\"updated_at\":\"2026-07-31 12:29:00\",\"department_id\":4},\"changes\":null}', '2026-07-31 04:29:00'),
(212, 3, NULL, 4, NULL, NULL, 'Create Role', 'roles', '10', 'Create Role on roles #10', '::1', 'POST', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":null,\"new_data\":{\"role_id\":10,\"role_name\":\"Payroll Officer\",\"role_prefix\":\"PO\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 12:54:47\",\"updated_at\":\"2026-07-31 12:54:47\",\"department_id\":4},\"changes\":null}', '2026-07-31 04:54:47'),
(215, 3, NULL, 4, NULL, NULL, 'Create Role', 'roles', '11', 'Create Role on roles #11', '::1', 'POST', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":null,\"new_data\":{\"role_id\":11,\"role_name\":\"Budget Officer\",\"role_prefix\":\"BO\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 13:02:52\",\"updated_at\":\"2026-07-31 13:02:52\",\"department_id\":4},\"changes\":null}', '2026-07-31 05:02:52'),
(216, 1, NULL, 1, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '4', 'Updated permissions matrix for role \"Eudcation Scholarship Adminmistrator\" (50 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 05:03:56'),
(218, 3, NULL, 4, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '7', 'Updated permissions matrix for role \"Scholarship Screening Committee\" (0 permissions granted)', '127.0.0.1', 'POST', '', 'Unknown', 'Unknown', 'Success', NULL, '2026-07-31 05:42:49'),
(219, 3, NULL, 4, 11, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PUT', '/civentral/api/employee/modules.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 05:58:00'),
(220, 3, NULL, 4, NULL, 37, 'Update Resource', 'resources', '37', 'Updated resource ID 37', '::1', 'PUT', '/civentral/api/employee/resources.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 05:58:23'),
(221, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:12:52'),
(222, 1, NULL, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:13:12'),
(223, 1, NULL, 1, NULL, NULL, 'Create User Account', 'users', '5', 'Create User Account on users #5', '::1', 'POST', '/civentral/api/employee/users.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":null,\"new_data\":{\"user_id\":5,\"employee_id\":\"HSA-2026-001\",\"role_id\":6,\"position_id\":6,\"first_name\":\"Andrie\",\"middle_name\":null,\"last_name\":\"Suruiz\",\"mobile_number\":\"09205941912\",\"profile_picture\":\"default-avatar.png\",\"email\":\"suruizjoshua72@gmail.com\",\"password\":\"$2y$10$KEEha0FBHyWUtQ/o8tnCbOT0zHezauRlIBy4SZeQ9DK.D2w1OpGZS\",\"status\":\"Active\",\"failed_attempts\":0,\"last_login\":null,\"created_at\":\"2026-07-31 14:17:31\",\"updated_at\":\"2026-07-31 14:17:31\",\"is_first_login\":1,\"email_verified\":1,\"mobile_verified\":1,\"password_changed_at\":null,\"last_password_reset\":null},\"changes\":null}', '2026-07-31 06:17:31'),
(224, 5, NULL, 8, NULL, NULL, 'Initiate 2FA Login', 'users', '5', 'OTP code generated and sent to s************2@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:18:21'),
(225, 5, 61, 8, NULL, NULL, '2FA Verification Success', 'users', '5', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:18:42'),
(226, 1, NULL, 1, NULL, NULL, 'Initiate 2FA Login', 'users', '1', 'OTP code generated and sent to s**********e@gmail.com', '::1', 'POST', '/civentral/api/employee/login.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:19:11'),
(227, 1, 62, 1, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:19:37'),
(228, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '6', 'Updated permissions matrix for role \"Health Sanitation Administrator\" (29 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:20:57'),
(229, 1, 62, 1, NULL, NULL, 'Update Access Control Matrix', 'roles', '6', 'Updated department access boundary for role \"Health Sanitation Administrator\" (Global Access: NO)', '::1', 'POST', '/civentral/api/employee/access-control.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:23:46'),
(230, 5, 61, 8, NULL, NULL, 'Create Role', 'roles', '12', 'Create Role on roles #12', '::1', 'POST', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":null,\"new_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 14:27:21\",\"department_id\":8},\"changes\":null}', '2026-07-31 06:27:21'),
(231, 5, 61, 8, 13, NULL, 'Create Module', 'modules', '13', 'Created module \"Health Service\"', '::1', 'POST', '/civentral/api/employee/modules.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:36:33'),
(232, 1, 62, 1, NULL, NULL, 'Update Access Control Matrix', 'roles', '12', 'Updated department access boundary for role \"Doctor\" (Global Access: NO)', '::1', 'POST', '/civentral/api/employee/access-control.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:43:49'),
(233, 1, 62, 1, NULL, NULL, 'Update Access Control Matrix', 'roles', '6', 'Updated department access boundary for role \"Health Sanitation Administrator\" (Global Access: NO)', '::1', 'POST', '/civentral/api/employee/access-control.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:43:56'),
(234, 5, 61, 8, NULL, NULL, 'Update Role Permissions Matrix', 'roles', '12', 'Updated permissions matrix for role \"Doctor\" (0 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 06:55:17'),
(235, 1, 62, 1, 11, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 07:00:37'),
(236, 1, 62, 1, NULL, 37, 'Update Resource', 'resources', '37', 'Updated resource ID 37', '::1', 'PATCH', '/civentral/api/employee/resources.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 07:02:02'),
(237, 1, 62, 1, NULL, 37, 'Update Resource', 'resources', '37', 'Updated resource ID 37', '::1', 'PATCH', '/civentral/api/employee/resources.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 07:02:13'),
(238, 1, 62, 1, 11, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 07:03:34'),
(239, 1, 62, 1, 11, NULL, 'Update Module', 'modules', '11', 'Updated module ID 11', '::1', 'PATCH', '/civentral/api/employee/modules.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 07:05:06'),
(240, 1, NULL, 1, NULL, NULL, 'Update Module', 'modules', '13', 'Updated description for module 13', '127.0.0.1', 'POST', '', 'Unknown', 'Unknown', 'Success', '{\"old_data\":{\"module_id\":13,\"module_name\":\"Health Service\",\"description\":\"test\",\"status\":\"Active\",\"created_at\":\"2026-07-31 14:36:33\",\"updated_at\":\"2026-07-31 14:36:33\"},\"new_data\":{\"module_id\":13,\"module_name\":\"Health Service\",\"description\":\"Test mutation audit logging\",\"status\":\"Active\",\"created_at\":\"2026-07-31 14:36:33\",\"updated_at\":\"2026-07-31 15:30:17\"},\"changes\":{\"description\":{\"old\":\"test\",\"new\":\"Test mutation audit logging\"}}}', '2026-07-31 07:30:17'),
(241, 1, 62, 1, NULL, NULL, 'Update Role', 'roles', '12', 'Update Role on roles #12: status: \"Active\" → \"Inactive\"', '::1', 'PATCH', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 14:43:49\",\"department_id\":8},\"new_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Inactive\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 15:35:37\",\"department_id\":8},\"changes\":{\"status\":{\"old\":\"Active\",\"new\":\"Inactive\"}}}', '2026-07-31 07:35:37'),
(242, 1, 62, 1, NULL, NULL, 'Update Role', 'roles', '12', 'Update Role on roles #12: status: \"Inactive\" → \"Active\"', '::1', 'PATCH', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Inactive\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 15:35:37\",\"department_id\":8},\"new_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 15:36:08\",\"department_id\":8},\"changes\":{\"status\":{\"old\":\"Inactive\",\"new\":\"Active\"}}}', '2026-07-31 07:36:08'),
(243, 1, 62, 1, NULL, NULL, 'Update Access Control Matrix', 'roles', '5', 'Updated department access boundary for role \"Scholarship Coordinator\" (Global Access: NO)', '::1', 'POST', '/civentral/api/employee/access-control.php', 'Chrome', 'Windows', 'Success', NULL, '2026-07-31 07:37:25'),
(244, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '1', 'Updated permissions matrix for role \"Super Administrator\" (67 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":1,\"role_name\":\"Super Administrator\",\"granted_permission_count\":70,\"permission_ids\":[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88]},\"new_data\":{\"role_id\":1,\"role_name\":\"Super Administrator\",\"granted_permission_count\":67,\"permission_ids\":[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,67,69,71,72,73,74,75,76,86,87,88,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":70,\"new\":67}}}', '2026-07-31 07:38:14'),
(245, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '6', 'Updated permissions matrix for role \"Health Sanitation Administrator\" (39 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":38,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,67,69,77,78,79,80,81,82,83,84,85]},\"new_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":39,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,69,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":38,\"new\":39}}}', '2026-07-31 07:38:47'),
(246, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '1', 'Updated permissions matrix for role \"Super Administrator\" (68 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":1,\"role_name\":\"Super Administrator\",\"granted_permission_count\":67,\"permission_ids\":[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,67,69,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88]},\"new_data\":{\"role_id\":1,\"role_name\":\"Super Administrator\",\"granted_permission_count\":68,\"permission_ids\":[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,69,71,72,73,74,75,76,86,87,88,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":67,\"new\":68}}}', '2026-07-31 07:40:18'),
(247, 1, NULL, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '4', 'Updated permissions matrix for role \"Department Admin\"', '127.0.0.1', 'POST', '', 'Unknown', 'Unknown', 'Success', '{\"old_data\":{\"role_id\":4,\"permission_ids\":[10,11,12]},\"new_data\":{\"role_id\":4,\"permission_ids\":[10,11,12,15]},\"changes\":{\"permission_ids\":{\"old\":\"[10,11,12]\",\"new\":\"[10,11,12,15]\"}}}', '2026-07-31 07:40:48'),
(248, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '6', 'Updated permissions matrix for role \"Health Sanitation Administrator\" (40 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":39,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,69,77,78,79,80,81,82,83,84,85]},\"new_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":40,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":39,\"new\":40},\"permission_ids\":{\"old\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,69,77,78,79,80,81,82,83,84,85]\",\"new\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,77,78,79,80,81,82,83,84,85]\"}}}', '2026-07-31 07:42:11'),
(249, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '6', 'Updated permissions matrix for role \"Health Sanitation Administrator\" (38 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":40,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,77,78,79,80,81,82,83,84,85]},\"new_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":38,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,67,69,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":40,\"new\":38},\"permission_ids\":{\"old\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,77,78,79,80,81,82,83,84,85]\",\"new\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,67,69,77,78,79,80,81,82,83,84,85]\"}}}', '2026-07-31 07:42:30'),
(250, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '6', 'Updated permissions matrix for role \"Health Sanitation Administrator\" (41 permissions granted)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":38,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,67,69,77,78,79,80,81,82,83,84,85]},\"new_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":41,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,70,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":38,\"new\":41},\"permission_ids\":{\"old\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,67,69,77,78,79,80,81,82,83,84,85]\",\"new\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,70,77,78,79,80,81,82,83,84,85]\"}}}', '2026-07-31 07:43:49'),
(251, 5, 61, 8, NULL, NULL, 'Update Role', 'roles', '12', 'Update Role on roles #12: status: \"Active\" → \"Inactive\"', '::1', 'PATCH', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 15:36:08\",\"department_id\":8},\"new_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Inactive\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 15:44:54\",\"department_id\":8},\"changes\":{\"status\":{\"old\":\"Active\",\"new\":\"Inactive\"}}}', '2026-07-31 07:44:54'),
(252, 5, 61, 8, NULL, NULL, 'Update Role', 'roles', '12', 'Update Role on roles #12: status: \"Inactive\" → \"Active\"', '::1', 'PATCH', '/civentral/api/employee/roles.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Inactive\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 15:44:54\",\"department_id\":8},\"new_data\":{\"role_id\":12,\"role_name\":\"Doctor\",\"role_prefix\":\"DOCT\",\"is_global_access\":0,\"is_superadmin\":0,\"description\":\"test\",\"status\":\"Active\",\"is_system_role\":0,\"created_at\":\"2026-07-31 14:27:21\",\"updated_at\":\"2026-07-31 15:45:17\",\"department_id\":8},\"changes\":{\"status\":{\"old\":\"Inactive\",\"new\":\"Active\"}}}', '2026-07-31 07:45:17'),
(253, 1, NULL, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '4', 'Granted updated access permissions to role: Health Sanitation Administrator (12 permissions active)', '127.0.0.1', 'POST', '', 'Unknown', 'Unknown', 'Success', '{\"old_data\":{\"role_id\":4,\"permission_ids\":[10,11]},\"new_data\":{\"role_id\":4,\"permission_ids\":[10,11,12]},\"changes\":{\"permission_ids\":{\"old\":\"[10,11]\",\"new\":\"[10,11,12]\"}}}', '2026-07-31 07:59:32'),
(254, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '1', 'Granted updated access permissions to role: Super Administrator (70 permissions active)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":1,\"role_name\":\"Super Administrator\",\"granted_permission_count\":68,\"permission_ids\":[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,69,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88]},\"new_data\":{\"role_id\":1,\"role_name\":\"Super Administrator\",\"granted_permission_count\":70,\"permission_ids\":[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,86,87,88,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":68,\"new\":70},\"permission_ids\":{\"old\":\"[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,69,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88]\",\"new\":\"[1,2,3,4,5,6,13,14,15,16,17,18,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,86,87,88,77,78,79,80,81,82,83,84,85]\"}}}', '2026-07-31 08:01:02'),
(255, 1, 62, 1, NULL, NULL, 'Update Role Permissions Matrix', 'role_permissions', '6', 'Granted updated access permissions to role: Health Sanitation Administrator (38 permissions active)', '::1', 'POST', '/civentral/api/employee/permissions.php', 'Chrome', 'Windows', 'Success', '{\"old_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":41,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,70,77,78,79,80,81,82,83,84,85]},\"new_data\":{\"role_id\":6,\"role_name\":\"Health Sanitation Administrator\",\"granted_permission_count\":38,\"permission_ids\":[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,67,69,77,78,79,80,81,82,83,84,85]},\"changes\":{\"granted_permission_count\":{\"old\":41,\"new\":38},\"permission_ids\":{\"old\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,66,67,68,69,70,77,78,79,80,81,82,83,84,85]\",\"new\":\"[1,2,3,13,15,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,51,55,61,65,67,69,77,78,79,80,81,82,83,84,85]\"}}}', '2026-07-31 08:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `citizen_login_history`
--

CREATE TABLE `citizen_login_history` (
  `login_id` bigint(20) UNSIGNED NOT NULL,
  `citizen_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `login_status` enum('Success','Failed') NOT NULL,
  `failure_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `citizen_otps`
--

CREATE TABLE `citizen_otps` (
  `otp_id` bigint(20) UNSIGNED NOT NULL,
  `citizen_user_id` bigint(20) UNSIGNED NOT NULL,
  `otp_code` char(6) NOT NULL,
  `purpose` enum('Registration','Login','Password Reset') NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `citizen_password_resets`
--

CREATE TABLE `citizen_password_resets` (
  `reset_id` bigint(20) UNSIGNED NOT NULL,
  `citizen_user_id` bigint(20) UNSIGNED NOT NULL,
  `reset_token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `citizen_sessions`
--

CREATE TABLE `citizen_sessions` (
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `citizen_user_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `refresh_token_hash` char(64) NOT NULL,
  `push_token` varchar(255) DEFAULT NULL,
  `platform` enum('Android','iOS','Web') NOT NULL,
  `login_ip` varchar(45) DEFAULT NULL,
  `is_revoked` tinyint(1) NOT NULL DEFAULT 0,
  `last_active_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `citizen_users`
--

CREATE TABLE `citizen_users` (
  `citizen_user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `has_no_middle_name` tinyint(1) NOT NULL DEFAULT 0,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Pending','Active','Inactive','Locked','Archived') NOT NULL DEFAULT 'Pending',
  `registry_completed` tinyint(1) NOT NULL DEFAULT 0,
  `failed_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `biometric_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `citizen_users`
--

INSERT INTO `citizen_users` (`citizen_user_id`, `first_name`, `middle_name`, `has_no_middle_name`, `last_name`, `suffix`, `email`, `mobile_number`, `password`, `status`, `registry_completed`, `failed_attempts`, `last_login`, `biometric_enabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Juan', NULL, 0, 'Dela Cruz', NULL, 'juan@example.com', NULL, 'placeholder_hash', 'Active', 0, 0, NULL, 0, '2026-07-27 03:54:40', '2026-07-27 03:54:40', NULL),
(2, 'Maria', NULL, 0, 'Clara', NULL, 'maria.clara@example.com', NULL, 'placeholder_hash', 'Inactive', 0, 0, NULL, 0, '2026-07-25 03:55:31', '2026-07-27 03:55:31', NULL),
(3, 'Jose', NULL, 0, 'Rizal', NULL, 'j.rizal@example.com', NULL, 'placeholder_hash', 'Locked', 0, 0, NULL, 0, '2026-07-22 03:55:31', '2026-07-27 03:55:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(150) NOT NULL,
  `department_head_user_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_code`, `department_name`, `department_head_user_id`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'IT', 'Information Technology Department', 1, 'IT Systems and Infrastructure', 'Active', '2026-07-25 13:51:44', '2026-07-30 12:50:27'),
(4, 'ESMS', 'Education & Scholarship', 3, 'test', 'Active', '2026-07-26 00:17:15', '2026-07-30 06:12:47'),
(5, 'CIE', 'Citizenship Information  & Engagement', NULL, 'TEST', 'Active', '2026-07-26 00:18:44', '2026-07-26 00:18:44'),
(6, 'PLM', 'Permits & Licensing Management', NULL, 'test', 'Active', '2026-07-26 00:19:15', '2026-07-26 00:19:15'),
(7, 'SSM', 'Social Services Management', NULL, 'TEST', 'Active', '2026-07-26 00:19:30', '2026-07-26 00:19:30'),
(8, 'HSM', 'Health & Sanitation Management', NULL, 'TEST', 'Active', '2026-07-26 00:19:45', '2026-07-26 00:20:01'),
(9, 'DRRM', 'Disaster Risk Reduction & Emergency Response', NULL, 'TEST', 'Active', '2026-07-26 00:20:28', '2026-07-26 00:20:28'),
(10, 'UPZH', 'Urban Planning Zoning & Housing', NULL, 'test', 'Active', '2026-07-26 00:20:53', '2026-07-26 00:20:53'),
(11, 'RCTS', 'Revenue Collection & Treasury Services', NULL, 'TEST', 'Active', '2026-07-26 00:21:13', '2026-07-26 00:21:13'),
(12, 'TMM', 'Transport & Mobility Management', NULL, 'TEST', 'Active', '2026-07-26 00:21:30', '2026-07-26 00:21:30'),
(13, 'PAFM', 'Public Assets & Facilities Management', NULL, 'TEST', 'Active', '2026-07-26 00:21:48', '2026-07-26 00:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `login_status` enum('Success','Failed') DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `failure_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`login_id`, `user_id`, `session_id`, `login_time`, `logout_time`, `ip_address`, `device_info`, `login_status`, `browser`, `operating_system`, `failure_reason`) VALUES
(1, 1, NULL, '2026-07-25 16:00:26', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(2, 1, NULL, '2026-07-25 16:00:49', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (2/3)'),
(3, 1, NULL, '2026-07-25 16:03:33', '2026-07-25 16:24:29', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(4, 1, NULL, '2026-07-25 16:25:19', '2026-07-25 16:42:26', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(5, 1, NULL, '2026-07-25 16:43:11', '2026-07-25 16:56:53', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(6, 1, NULL, '2026-07-25 19:04:50', '2026-07-25 19:10:03', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(7, 1, NULL, '2026-07-26 04:22:13', '2026-07-26 08:01:31', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(8, 1, NULL, '2026-07-26 06:50:44', '2026-07-26 06:56:17', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(9, 1, NULL, '2026-07-26 06:56:44', '2026-07-26 07:44:55', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(10, 1, NULL, '2026-07-26 08:01:34', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(11, 1, NULL, '2026-07-26 08:01:48', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (2/3)'),
(12, 1, NULL, '2026-07-26 08:01:55', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Account locked due to 3 consecutive authorization failures'),
(13, 1, NULL, '2026-07-26 08:03:12', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(14, 1, NULL, '2026-07-26 08:03:44', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (2/3)'),
(15, 1, NULL, '2026-07-26 08:03:51', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Account locked due to 3 consecutive authorization failures'),
(16, 1, NULL, '2026-07-26 08:05:13', '2026-07-26 08:14:44', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(17, 1, NULL, '2026-07-26 08:15:41', '2026-07-26 08:30:38', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(18, 1, NULL, '2026-07-26 08:30:48', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(19, 1, NULL, '2026-07-26 08:31:46', '2026-07-26 08:50:16', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(20, 1, NULL, '2026-07-26 08:51:07', '2026-07-26 08:55:42', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(21, NULL, NULL, '2026-07-26 08:57:46', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(22, NULL, NULL, '2026-07-26 09:00:14', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (2/3)'),
(23, 1, NULL, '2026-07-26 09:03:28', '2026-07-26 09:17:50', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(24, 3, NULL, '2026-07-26 09:06:16', '2026-07-26 09:58:55', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(25, 1, NULL, '2026-07-26 09:19:21', '2026-07-26 09:54:41', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(26, 1, NULL, '2026-07-26 09:55:07', '2026-07-26 10:03:27', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(27, 1, NULL, '2026-07-26 10:04:08', '2026-07-26 10:24:38', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(28, 3, NULL, '2026-07-26 10:06:25', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(29, 3, NULL, '2026-07-26 10:07:10', '2026-07-26 11:02:44', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(30, 1, NULL, '2026-07-26 10:25:09', '2026-07-26 10:47:41', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(31, 1, NULL, '2026-07-26 11:03:32', '2026-07-26 11:03:33', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(32, 1, NULL, '2026-07-26 11:04:25', '2026-07-26 11:50:07', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(33, 3, NULL, '2026-07-26 11:05:05', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(34, 3, NULL, '2026-07-26 11:05:12', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (2/3)'),
(35, 3, NULL, '2026-07-26 11:05:13', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Account locked due to 3 consecutive authorization failures'),
(36, 3, NULL, '2026-07-26 11:07:06', '2026-07-26 11:34:59', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(37, 3, NULL, '2026-07-26 11:35:19', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(38, 3, NULL, '2026-07-26 11:35:46', '2026-07-26 11:47:30', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(39, 1, NULL, '2026-07-26 11:50:36', '2026-07-26 12:00:11', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(40, 3, NULL, '2026-07-26 12:00:35', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(41, 3, NULL, '2026-07-26 12:01:02', '2026-07-26 12:10:41', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(42, 3, NULL, '2026-07-26 12:10:57', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(43, 3, NULL, '2026-07-26 12:11:15', '2026-07-26 12:23:40', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(44, 1, NULL, '2026-07-26 12:15:38', '2026-07-26 12:30:36', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(45, 3, NULL, '2026-07-26 12:24:22', '2026-07-26 12:30:19', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(46, 3, NULL, '2026-07-26 12:31:00', '2026-07-26 12:58:41', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(47, 1, NULL, '2026-07-26 12:31:07', '2026-07-26 12:42:51', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(48, 1, NULL, '2026-07-26 12:43:22', '2026-07-26 12:58:46', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(49, 1, NULL, '2026-07-26 12:59:05', '2026-07-26 13:19:18', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(50, 3, NULL, '2026-07-26 13:00:10', '2026-07-26 13:08:35', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(51, 3, NULL, '2026-07-26 13:09:29', '2026-07-26 13:32:11', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(52, 1, NULL, '2026-07-26 13:26:54', '2026-07-26 15:21:35', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(53, 3, NULL, '2026-07-26 13:32:44', '2026-07-26 14:21:55', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(54, 3, NULL, '2026-07-26 15:41:18', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(55, 3, NULL, '2026-07-26 16:19:44', '2026-07-26 16:26:58', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(56, 3, NULL, '2026-07-26 16:34:51', '2026-07-26 20:27:51', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(57, 1, NULL, '2026-07-27 04:52:05', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(58, 1, NULL, '2026-07-27 04:52:41', '2026-07-27 05:42:53', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(59, 1, NULL, '2026-07-27 05:56:55', '2026-07-27 06:41:52', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(60, 1, NULL, '2026-07-27 08:02:01', '2026-07-27 09:15:54', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(61, 1, NULL, '2026-07-27 15:33:40', '2026-07-27 15:59:29', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(62, NULL, NULL, '2026-07-28 07:20:37', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid username or password'),
(63, NULL, NULL, '2026-07-28 07:20:59', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid username or password'),
(64, NULL, NULL, '2026-07-28 07:21:00', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid username or password'),
(65, NULL, NULL, '2026-07-28 07:21:03', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid username or password'),
(66, NULL, NULL, '2026-07-28 07:21:21', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid username or password'),
(67, 3, 42, '2026-07-28 07:23:24', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(68, 3, NULL, '2026-07-28 07:59:11', '2026-07-28 09:59:18', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(69, 1, 44, '2026-07-28 08:00:35', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(70, 1, NULL, '2026-07-28 08:34:36', '2026-07-28 09:59:19', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(71, 3, 46, '2026-07-30 05:52:54', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(72, 1, NULL, '2026-07-30 12:22:37', '2026-07-30 12:22:38', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(73, 1, NULL, '2026-07-30 12:22:48', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(74, 1, NULL, '2026-07-30 12:23:16', '2026-07-30 12:33:59', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(75, 1, NULL, '2026-07-30 12:34:35', '2026-07-30 13:38:44', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(76, 1, NULL, '2026-07-30 13:58:33', '2026-07-30 20:49:02', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(77, 1, NULL, '2026-07-30 20:49:12', NULL, '::1', NULL, 'Failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Invalid password attempt (1/3)'),
(78, 1, NULL, '2026-07-30 20:49:58', '2026-07-30 22:26:18', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(79, 1, NULL, '2026-07-30 22:27:28', '2026-07-30 23:15:17', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(80, 1, 53, '2026-07-30 23:33:55', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(81, 1, NULL, '2026-07-31 00:09:32', '2026-07-31 00:35:44', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(82, 1, 55, '2026-07-31 00:48:36', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(83, NULL, NULL, '2026-07-31 01:18:27', NULL, '::1', NULL, 'Failed', 'PostmanRuntime/7.39.1', NULL, 'Invalid username or password'),
(84, 3, NULL, '2026-07-31 01:19:30', NULL, '::1', NULL, 'Failed', 'PostmanRuntime/7.39.1', NULL, 'Invalid password attempt (1/3)'),
(85, 3, 56, '2026-07-31 01:27:01', NULL, '::1', NULL, 'Success', 'PostmanRuntime/7.39.1', NULL, NULL),
(86, 1, NULL, '2026-07-31 12:04:28', '2026-07-31 12:04:38', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(87, 3, NULL, '2026-07-31 12:05:02', '2026-07-31 14:18:53', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL),
(88, 1, NULL, '2026-07-31 12:09:20', '2026-07-31 13:58:42', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Sa', NULL, NULL),
(89, 1, NULL, '2026-07-31 14:13:12', '2026-07-31 14:17:52', '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Sa', NULL, NULL),
(90, 5, 61, '2026-07-31 14:18:42', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Sa', NULL, NULL),
(91, 1, 62, '2026-07-31 14:19:37', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Archived') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`module_id`, `module_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(4, 'User & Account Management', 'test', 'Active', '2026-07-26 01:35:13', '2026-07-28 06:24:27'),
(5, 'Role & Permission Management', 'test', 'Active', '2026-07-26 01:35:35', '2026-07-26 01:35:35'),
(6, 'Department Management', 'test', 'Active', '2026-07-26 01:35:58', '2026-07-26 01:35:58'),
(7, 'Citizen Management', '', 'Active', '2026-07-26 01:36:12', '2026-07-26 01:36:12'),
(8, 'Audit Logs System', '', 'Active', '2026-07-26 01:36:25', '2026-07-26 01:36:25'),
(11, 'Scholarship Application', 'test', 'Archived', '2026-07-28 00:01:27', '2026-07-31 07:08:25'),
(13, 'Health Service', 'Test mutation audit logging', 'Active', '2026-07-31 06:36:33', '2026-07-31 07:30:17');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(20) NOT NULL,
  `audit_id` bigint(20) DEFAULT NULL,
  `action_id` int(11) NOT NULL,
  `actor_user_id` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('Low','Normal','High','Critical') DEFAULT 'Normal',
  `notification_status` enum('Unread','Read','Archived') DEFAULT 'Unread',
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `audit_id`, `action_id`, `actor_user_id`, `recipient_user_id`, `department_id`, `title`, `message`, `priority`, `notification_status`, `read_at`, `created_at`) VALUES
(1, 198, 2, 1, 1, 1, 'Create Department — Department', 'Test Department Created by script', 'High', 'Read', '2026-07-31 12:09:39', '2026-07-30 17:40:00'),
(3, 203, 2, 3, 1, 4, 'Create Role — Role', 'Create Role on roles #7', 'High', 'Read', '2026-07-31 12:16:14', '2026-07-31 04:14:54'),
(4, 203, 2, 3, 3, 4, 'Create Role — Role', 'Create Role on roles #7', 'High', 'Read', '2026-07-31 12:27:03', '2026-07-31 04:14:54'),
(5, 203, 2, 3, 4, 4, 'Create Role — Role', 'Create Role on roles #7', 'High', 'Unread', NULL, '2026-07-31 04:14:54'),
(6, 206, 2, 3, 1, 4, 'Create Role — Role', 'Create Role on roles #8', 'High', 'Read', '2026-07-31 12:34:29', '2026-07-31 04:27:12'),
(7, 206, 2, 3, 4, 4, 'Create Role — Role', 'Create Role on roles #8', 'High', 'Unread', NULL, '2026-07-31 04:27:12'),
(10, 209, 2, 3, 1, 4, 'Create Role — Role', 'Create Role on roles #9', 'High', 'Read', '2026-07-31 12:34:46', '2026-07-31 04:29:00'),
(11, 209, 2, 3, 4, 4, 'Create Role — Role', 'Create Role on roles #9', 'High', 'Unread', NULL, '2026-07-31 04:29:00'),
(16, 212, 2, 3, 3, 4, 'Create Role — Role', 'Create Role on roles #10', 'High', 'Read', '2026-07-31 12:55:56', '2026-07-31 04:54:47'),
(17, 212, 2, 3, 1, 4, 'Create Role — Role', 'Create Role on roles #10', 'High', 'Read', '2026-07-31 12:55:36', '2026-07-31 04:54:47'),
(20, 215, 2, 3, 1, 4, 'Create Role — Role', 'Create Role on roles #11', 'High', 'Read', '2026-07-31 13:03:17', '2026-07-31 05:02:52'),
(22, 218, 3, 3, 1, 4, 'Update Role Permissions Matrix — Role', 'John Laurence Gilbuena: Updated permissions matrix for role \"Scholarship Screening Committee\" (0 permissions granted)', 'Normal', 'Read', '2026-07-31 14:13:33', '2026-07-31 05:42:49'),
(23, 219, 3, 3, 1, 4, 'Update Module — Module', 'John Laurence Gilbuena: Updated module ID 11', 'Normal', 'Read', '2026-07-31 14:14:43', '2026-07-31 05:58:00'),
(24, 220, 3, 3, 1, 4, 'Update Resource — Resource', 'John Laurence Gilbuena: Updated resource ID 37', 'Normal', 'Read', '2026-07-31 14:14:50', '2026-07-31 05:58:23'),
(25, 223, 2, 1, 5, 1, 'Create User Account — User', 'Joshua Suruiz: Create User Account on users #5', 'High', 'Read', '2026-07-31 15:00:02', '2026-07-31 06:17:31'),
(26, 230, 2, 5, 1, 8, 'Create Role — Role', 'Andrie Suruiz: Create Role on roles #12', 'High', 'Read', '2026-07-31 15:06:14', '2026-07-31 06:27:21'),
(27, 231, 2, 5, 1, 8, 'Create Module — Module', 'Andrie Suruiz: Created module \"Health Service\"', 'High', 'Read', '2026-07-31 15:06:14', '2026-07-31 06:36:33'),
(28, 234, 3, 5, 1, 8, 'Update Role Permissions Matrix — Role', 'Andrie Suruiz: Updated permissions matrix for role \"Doctor\" (0 permissions granted)', 'Normal', 'Read', '2026-07-31 14:59:47', '2026-07-31 06:55:17'),
(29, 251, 3, 5, 1, 8, 'Update Role — Role', 'Andrie Suruiz: Update Role on roles #12: status: \"Active\" → \"Inactive\"', 'Normal', 'Read', '2026-07-31 15:45:09', '2026-07-31 07:44:54'),
(30, 252, 3, 5, 1, 8, 'Update Role — Role', 'Andrie Suruiz: Update Role on roles #12: status: \"Inactive\" → \"Active\"', 'Normal', 'Read', '2026-07-31 16:02:11', '2026-07-31 07:45:17'),
(31, 253, 3, 1, 3, 1, 'Update Role Permissions Matrix — Role Permission', 'Joshua Suruiz: Granted updated access permissions to role: Health Sanitation Administrator (12 permissions active)', 'Normal', 'Unread', NULL, '2026-07-31 07:59:32'),
(32, 255, 3, 1, 5, 1, 'Update Role Permissions Matrix — Role Permission', 'Joshua Suruiz: Granted updated access permissions to role: Health Sanitation Administrator (38 permissions active)', 'Normal', 'Read', '2026-07-31 16:01:45', '2026-07-31 08:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `permission_key` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `resource_id`, `action_id`, `permission_key`, `created_at`, `status`) VALUES
(1, 6, 1, 'res_6_act_1', '2026-07-26 02:05:00', 'Active'),
(2, 6, 2, 'res_6_act_2', '2026-07-26 02:05:00', 'Active'),
(3, 6, 3, 'res_6_act_3', '2026-07-26 02:05:00', 'Active'),
(4, 6, 4, 'res_6_act_4', '2026-07-26 02:05:00', 'Active'),
(5, 6, 5, 'res_6_act_5', '2026-07-26 02:05:00', 'Active'),
(6, 6, 6, 'res_6_act_6', '2026-07-26 02:05:00', 'Active'),
(13, 21, 1, 'res_21_act_1', '2026-07-26 04:19:11', 'Active'),
(14, 21, 2, 'res_21_act_2', '2026-07-26 04:19:11', 'Active'),
(15, 21, 3, 'res_21_act_3', '2026-07-26 04:19:11', 'Active'),
(16, 21, 4, 'res_21_act_4', '2026-07-26 04:19:11', 'Active'),
(17, 21, 5, 'res_21_act_5', '2026-07-26 04:19:11', 'Active'),
(18, 21, 6, 'res_21_act_6', '2026-07-26 04:19:11', 'Active'),
(31, 25, 1, 'roles:view', '2026-07-26 10:55:43', 'Active'),
(32, 25, 2, 'roles:create', '2026-07-26 10:55:43', 'Active'),
(33, 25, 3, 'roles:edit', '2026-07-26 10:55:43', 'Active'),
(34, 25, 4, 'roles:delete', '2026-07-26 10:55:43', 'Active'),
(35, 26, 1, 'module management:view', '2026-07-26 10:55:43', 'Active'),
(36, 26, 2, 'module management:create', '2026-07-26 10:55:43', 'Active'),
(37, 26, 3, 'module management:edit', '2026-07-26 10:55:43', 'Active'),
(38, 26, 4, 'module management:delete', '2026-07-26 10:55:43', 'Active'),
(39, 27, 1, 'resource management:view', '2026-07-26 10:55:43', 'Active'),
(40, 27, 2, 'resource management:create', '2026-07-26 10:55:43', 'Active'),
(41, 27, 3, 'resource management:edit', '2026-07-26 10:55:43', 'Active'),
(42, 27, 4, 'resource management:delete', '2026-07-26 10:55:43', 'Active'),
(43, 28, 1, 'action management:view', '2026-07-26 10:55:43', 'Active'),
(44, 28, 2, 'action management:create', '2026-07-26 10:55:43', 'Active'),
(45, 28, 3, 'action management:edit', '2026-07-26 10:55:43', 'Active'),
(46, 28, 4, 'action management:delete', '2026-07-26 10:55:43', 'Active'),
(47, 29, 1, 'permission builder:view', '2026-07-26 10:55:43', 'Active'),
(48, 29, 3, 'permission builder:edit', '2026-07-26 10:55:43', 'Active'),
(49, 30, 1, 'role permission matrix:view', '2026-07-26 10:55:43', 'Active'),
(50, 30, 3, 'role permission matrix:edit', '2026-07-26 10:55:43', 'Active'),
(51, 31, 1, 'dept_mgmt_1', '2026-07-26 11:27:51', 'Active'),
(52, 31, 2, 'dept_mgmt_2', '2026-07-26 11:27:51', 'Active'),
(53, 31, 3, 'dept_mgmt_3', '2026-07-26 11:27:51', 'Active'),
(54, 31, 4, 'dept_mgmt_4', '2026-07-26 11:27:51', 'Active'),
(55, 32, 1, 'citizen_directory_1', '2026-07-26 11:29:49', 'Active'),
(56, 32, 2, 'citizen_directory_2', '2026-07-26 11:29:49', 'Active'),
(57, 32, 3, 'citizen_directory_3', '2026-07-26 11:29:49', 'Active'),
(58, 32, 4, 'citizen_directory_4', '2026-07-26 11:29:49', 'Active'),
(59, 32, 5, 'citizen_directory_5', '2026-07-26 11:29:49', 'Active'),
(60, 32, 6, 'citizen_directory_6', '2026-07-26 11:29:49', 'Active'),
(61, 33, 1, 'citizen_account_1', '2026-07-26 11:29:49', 'Active'),
(62, 33, 2, 'citizen_account_2', '2026-07-26 11:29:49', 'Active'),
(63, 33, 3, 'citizen_account_3', '2026-07-26 11:29:49', 'Active'),
(64, 33, 4, 'citizen_account_4', '2026-07-26 11:29:49', 'Active'),
(65, 34, 1, 'user_activities_1', '2026-07-26 11:29:49', 'Active'),
(66, 34, 5, 'user_activities_5', '2026-07-26 11:29:49', 'Active'),
(67, 35, 1, 'login_history_1', '2026-07-26 11:29:49', 'Active'),
(68, 35, 5, 'login_history_5', '2026-07-26 11:29:49', 'Active'),
(69, 36, 1, 'data_changes_1', '2026-07-26 11:29:49', 'Active'),
(70, 36, 5, 'data_changes_5', '2026-07-26 11:29:49', 'Active'),
(71, 37, 1, 'res_37_act_1', '2026-07-28 00:02:16', 'Active'),
(72, 37, 2, 'res_37_act_2', '2026-07-28 00:02:16', 'Active'),
(73, 37, 3, 'res_37_act_3', '2026-07-28 00:02:16', 'Active'),
(74, 37, 4, 'res_37_act_4', '2026-07-28 00:02:16', 'Active'),
(75, 37, 5, 'res_37_act_5', '2026-07-28 00:02:16', 'Active'),
(76, 37, 6, 'res_37_act_6', '2026-07-28 00:02:16', 'Active'),
(77, 39, 1, 'res_39_act_1', '2026-07-31 06:50:33', 'Active'),
(78, 39, 2, 'res_39_act_2', '2026-07-31 06:51:06', 'Active'),
(79, 39, 3, 'res_39_act_3', '2026-07-31 06:51:06', 'Active'),
(80, 39, 4, 'res_39_act_4', '2026-07-31 06:51:06', 'Active'),
(81, 39, 5, 'res_39_act_5', '2026-07-31 06:51:06', 'Active'),
(82, 39, 6, 'res_39_act_6', '2026-07-31 06:51:06', 'Active'),
(83, 39, 7, 'res_39_act_7', '2026-07-31 06:51:06', 'Active'),
(84, 39, 8, 'res_39_act_8', '2026-07-31 06:51:06', 'Active'),
(85, 39, 9, 'res_39_act_9', '2026-07-31 06:51:06', 'Active'),
(86, 37, 7, 'res_37_act_7', '2026-07-31 07:02:02', 'Active'),
(87, 37, 8, 'res_37_act_8', '2026-07-31 07:02:02', 'Active'),
(88, 37, 9, 'res_37_act_9', '2026-07-31 07:02:02', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `department_id`, `position_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'IT Administrator', 'Manages IT infrastructure and security', 'Active', '2026-07-25 13:51:44', '2026-07-27 00:43:43'),
(4, 4, 'Schoolarship Administrator', NULL, 'Active', '2026-07-26 06:54:42', '2026-07-26 08:35:28'),
(5, 4, 'Scholarship Coordinator', NULL, 'Active', '2026-07-26 10:13:09', '2026-07-26 10:13:09'),
(6, 8, 'Health Sanitation Administrator', NULL, 'Active', '2026-07-31 06:17:31', '2026-07-31 06:17:31');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `resource_name` varchar(100) NOT NULL,
  `resource_route` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Archived') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `module_id`, `resource_name`, `resource_route`, `description`, `status`, `created_at`, `updated_at`) VALUES
(6, 4, 'Users Account', '/pages/usermanagement/create-account.php', 'test', 'Active', '2026-07-26 01:54:37', '2026-07-28 06:24:27'),
(21, 4, 'Account Status', 'account-status.php', 'test', 'Active', '2026-07-26 04:18:58', '2026-07-26 04:18:58'),
(25, 5, 'Roles', '/pages/rolespermission/roles-management.php', 'Manage system access roles, prefix codes, and position assignments.', 'Active', '2026-07-26 10:55:43', '2026-07-26 10:55:43'),
(26, 5, 'Module Management', '/pages/rolespermission/module-management.php', 'Manage operational system modules and functional categories.', 'Active', '2026-07-26 10:55:43', '2026-07-26 10:55:43'),
(27, 5, 'Resource Management', '/pages/rolespermission/resource-management.php', 'Register system resources, page routes, and API endpoints.', 'Active', '2026-07-26 10:55:43', '2026-07-26 10:55:43'),
(28, 5, 'Action Management', '/pages/rolespermission/action-management.php', 'Manage system operation action types (View, Create, Edit, Delete, Export, Approve).', 'Active', '2026-07-26 10:55:43', '2026-07-26 10:55:43'),
(29, 5, 'Permission Builder', '/pages/rolespermission/permissions.php', 'Configure granular action permissions and module capabilities per role.', 'Active', '2026-07-26 10:55:43', '2026-07-26 10:55:43'),
(30, 5, 'Role Permission Matrix', '/pages/rolespermission/access-control.php', 'Review and audit comprehensive system access permissions matrix across all roles.', 'Active', '2026-07-26 10:55:43', '2026-07-26 10:55:43'),
(31, 6, 'Department Management', '/pages/department/departments.php', 'City-wide department structure, office units, and organizational positions', 'Active', '2026-07-26 05:27:51', '2026-07-26 05:27:51'),
(32, 7, 'Citizen Directory', '/pages/citizen/citizen-directory.php', 'City-wide citizen profile registry, verification status, and biometric identity records', 'Active', '2026-07-26 05:29:49', '2026-07-26 05:29:49'),
(33, 7, 'Citizen Account', '/pages/citizen/citizen-account.php', 'Citizen online portal account credentials, KYC verification, and status management', 'Active', '2026-07-26 05:29:49', '2026-07-26 05:29:49'),
(34, 8, 'User Activities', '/pages/audit/user-activities.php', 'Real-time system transaction logs, operational event streams, and user action trails', 'Active', '2026-07-26 05:29:49', '2026-07-26 05:29:49'),
(35, 8, 'Login History', '/pages/audit/login-history.php', 'Authentication logs, sign-in IP addresses, session durations, and security attempt trails', 'Active', '2026-07-26 05:29:49', '2026-07-26 05:29:49'),
(36, 8, 'Data Changes', '/pages/audit/data-changes.php', 'Field-level data mutation audit trails, state diffs, and database record change logs', 'Active', '2026-07-26 05:29:49', '2026-07-26 05:29:49'),
(37, 11, 'Scholarship Applicant', 'scholarship-applicant.php', 'test', 'Archived', '2026-07-28 00:02:16', '2026-07-31 07:03:34'),
(39, 13, 'Health Services Registry', 'health-services-registry.php', 'Sanitation and health service records registry', 'Active', '2026-07-31 06:50:33', '2026-07-31 06:50:33');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_prefix` varchar(10) NOT NULL,
  `is_global_access` tinyint(1) DEFAULT 0,
  `is_superadmin` tinyint(1) NOT NULL DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Archived') DEFAULT 'Active',
  `is_system_role` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_prefix`, `is_global_access`, `is_superadmin`, `description`, `status`, `is_system_role`, `created_at`, `updated_at`, `department_id`) VALUES
(1, 'Super Administrator', 'SA', 1, 1, 'Full system administration access', 'Active', 1, '2026-07-25 13:51:44', '2026-07-28 06:24:27', 1),
(4, 'Eudcation Scholarship Adminmistrator', 'ESA', 0, 0, 'test', 'Active', 0, '2026-07-26 00:34:59', '2026-07-26 09:59:07', 4),
(5, 'Scholarship Coordinator', 'SC', 0, 0, 'test', 'Active', 0, '2026-07-26 02:26:25', '2026-07-31 07:37:25', 4),
(6, 'Health Sanitation Administrator', 'HSA', 0, 0, 'test', 'Active', 0, '2026-07-26 02:29:40', '2026-07-31 06:43:56', 8),
(7, 'Scholarship Screening Committee', 'SSC', 0, 0, 'test', 'Active', 0, '2026-07-31 04:07:22', '2026-07-31 04:07:22', 4),
(8, 'Section Head', 'SH', 0, 0, 'head', 'Active', 0, '2026-07-31 04:17:14', '2026-07-31 04:17:14', 4),
(9, 'Administrative Assistant', 'AA', 0, 0, 'test', 'Active', 0, '2026-07-31 04:29:00', '2026-07-31 04:29:00', 4),
(10, 'Payroll Officer', 'PO', 0, 0, 'test', 'Active', 0, '2026-07-31 04:54:47', '2026-07-31 04:54:47', 4),
(11, 'Budget Officer', 'BO', 0, 0, 'test', 'Active', 0, '2026-07-31 05:02:52', '2026-07-31 05:02:52', 4),
(12, 'Doctor', 'DOCT', 0, 0, 'test', 'Active', 0, '2026-07-31 06:27:21', '2026-07-31 07:45:17', 8);

-- --------------------------------------------------------

--
-- Table structure for table `role_department_access`
--

CREATE TABLE `role_department_access` (
  `access_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_department_access`
--

INSERT INTO `role_department_access` (`access_id`, `role_id`, `department_id`, `created_at`) VALUES
(2, 4, 4, '2026-07-26 02:09:41'),
(4, 12, 8, '2026-07-31 06:43:49'),
(5, 6, 8, '2026-07-31 06:43:56'),
(6, 5, 4, '2026-07-31 07:37:25');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_permission_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_permission_id`, `role_id`, `permission_id`, `granted_by`, `granted_at`) VALUES
(331, 4, 1, 1, '2026-07-31 05:03:56'),
(332, 4, 2, 1, '2026-07-31 05:03:56'),
(333, 4, 3, 1, '2026-07-31 05:03:56'),
(334, 4, 4, 1, '2026-07-31 05:03:56'),
(335, 4, 5, 1, '2026-07-31 05:03:56'),
(336, 4, 13, 1, '2026-07-31 05:03:56'),
(337, 4, 15, 1, '2026-07-31 05:03:56'),
(338, 4, 31, 1, '2026-07-31 05:03:56'),
(339, 4, 32, 1, '2026-07-31 05:03:56'),
(340, 4, 33, 1, '2026-07-31 05:03:56'),
(341, 4, 34, 1, '2026-07-31 05:03:56'),
(342, 4, 35, 1, '2026-07-31 05:03:56'),
(343, 4, 36, 1, '2026-07-31 05:03:56'),
(344, 4, 37, 1, '2026-07-31 05:03:56'),
(345, 4, 38, 1, '2026-07-31 05:03:56'),
(346, 4, 39, 1, '2026-07-31 05:03:56'),
(347, 4, 40, 1, '2026-07-31 05:03:56'),
(348, 4, 41, 1, '2026-07-31 05:03:56'),
(349, 4, 42, 1, '2026-07-31 05:03:56'),
(350, 4, 43, 1, '2026-07-31 05:03:56'),
(351, 4, 44, 1, '2026-07-31 05:03:56'),
(352, 4, 45, 1, '2026-07-31 05:03:56'),
(353, 4, 46, 1, '2026-07-31 05:03:56'),
(354, 4, 47, 1, '2026-07-31 05:03:56'),
(355, 4, 48, 1, '2026-07-31 05:03:56'),
(356, 4, 49, 1, '2026-07-31 05:03:56'),
(357, 4, 50, 1, '2026-07-31 05:03:56'),
(358, 4, 51, 1, '2026-07-31 05:03:56'),
(359, 4, 52, 1, '2026-07-31 05:03:56'),
(360, 4, 53, 1, '2026-07-31 05:03:56'),
(361, 4, 54, 1, '2026-07-31 05:03:56'),
(362, 4, 55, 1, '2026-07-31 05:03:56'),
(363, 4, 56, 1, '2026-07-31 05:03:56'),
(364, 4, 57, 1, '2026-07-31 05:03:56'),
(365, 4, 58, 1, '2026-07-31 05:03:56'),
(366, 4, 59, 1, '2026-07-31 05:03:56'),
(367, 4, 60, 1, '2026-07-31 05:03:56'),
(368, 4, 61, 1, '2026-07-31 05:03:56'),
(369, 4, 62, 1, '2026-07-31 05:03:56'),
(370, 4, 63, 1, '2026-07-31 05:03:56'),
(371, 4, 64, 1, '2026-07-31 05:03:56'),
(372, 4, 65, 1, '2026-07-31 05:03:56'),
(373, 4, 67, 1, '2026-07-31 05:03:56'),
(374, 4, 69, 1, '2026-07-31 05:03:56'),
(375, 4, 71, 1, '2026-07-31 05:03:56'),
(376, 4, 72, 1, '2026-07-31 05:03:56'),
(377, 4, 73, 1, '2026-07-31 05:03:56'),
(378, 4, 74, 1, '2026-07-31 05:03:56'),
(379, 4, 75, 1, '2026-07-31 05:03:56'),
(380, 4, 76, 1, '2026-07-31 05:03:56'),
(412, 4, 77, 3, '2026-07-31 06:51:06'),
(413, 5, 77, 3, '2026-07-31 06:51:06'),
(414, 7, 77, 3, '2026-07-31 06:51:06'),
(415, 8, 77, 3, '2026-07-31 06:51:06'),
(416, 9, 77, 3, '2026-07-31 06:51:06'),
(417, 10, 77, 3, '2026-07-31 06:51:06'),
(418, 11, 77, 3, '2026-07-31 06:51:06'),
(421, 4, 78, 3, '2026-07-31 06:51:06'),
(422, 5, 78, 3, '2026-07-31 06:51:06'),
(423, 7, 78, 3, '2026-07-31 06:51:06'),
(424, 8, 78, 3, '2026-07-31 06:51:06'),
(425, 9, 78, 3, '2026-07-31 06:51:06'),
(426, 10, 78, 3, '2026-07-31 06:51:06'),
(427, 11, 78, 3, '2026-07-31 06:51:06'),
(430, 4, 79, 3, '2026-07-31 06:51:06'),
(431, 5, 79, 3, '2026-07-31 06:51:06'),
(432, 7, 79, 3, '2026-07-31 06:51:06'),
(433, 8, 79, 3, '2026-07-31 06:51:06'),
(434, 9, 79, 3, '2026-07-31 06:51:06'),
(435, 10, 79, 3, '2026-07-31 06:51:06'),
(436, 11, 79, 3, '2026-07-31 06:51:06'),
(439, 4, 80, 3, '2026-07-31 06:51:06'),
(440, 5, 80, 3, '2026-07-31 06:51:06'),
(441, 7, 80, 3, '2026-07-31 06:51:06'),
(442, 8, 80, 3, '2026-07-31 06:51:06'),
(443, 9, 80, 3, '2026-07-31 06:51:06'),
(444, 10, 80, 3, '2026-07-31 06:51:06'),
(445, 11, 80, 3, '2026-07-31 06:51:06'),
(448, 4, 81, 3, '2026-07-31 06:51:06'),
(449, 5, 81, 3, '2026-07-31 06:51:06'),
(450, 7, 81, 3, '2026-07-31 06:51:06'),
(451, 8, 81, 3, '2026-07-31 06:51:06'),
(452, 9, 81, 3, '2026-07-31 06:51:06'),
(453, 10, 81, 3, '2026-07-31 06:51:06'),
(454, 11, 81, 3, '2026-07-31 06:51:06'),
(457, 4, 82, 3, '2026-07-31 06:51:06'),
(458, 5, 82, 3, '2026-07-31 06:51:06'),
(459, 7, 82, 3, '2026-07-31 06:51:06'),
(460, 8, 82, 3, '2026-07-31 06:51:06'),
(461, 9, 82, 3, '2026-07-31 06:51:06'),
(462, 10, 82, 3, '2026-07-31 06:51:06'),
(463, 11, 82, 3, '2026-07-31 06:51:06'),
(466, 4, 83, 3, '2026-07-31 06:51:06'),
(467, 5, 83, 3, '2026-07-31 06:51:06'),
(468, 7, 83, 3, '2026-07-31 06:51:06'),
(469, 8, 83, 3, '2026-07-31 06:51:06'),
(470, 9, 83, 3, '2026-07-31 06:51:06'),
(471, 10, 83, 3, '2026-07-31 06:51:06'),
(472, 11, 83, 3, '2026-07-31 06:51:06'),
(475, 4, 84, 3, '2026-07-31 06:51:06'),
(476, 5, 84, 3, '2026-07-31 06:51:06'),
(477, 7, 84, 3, '2026-07-31 06:51:06'),
(478, 8, 84, 3, '2026-07-31 06:51:06'),
(479, 9, 84, 3, '2026-07-31 06:51:06'),
(480, 10, 84, 3, '2026-07-31 06:51:06'),
(481, 11, 84, 3, '2026-07-31 06:51:06'),
(484, 4, 85, 3, '2026-07-31 06:51:06'),
(485, 5, 85, 3, '2026-07-31 06:51:06'),
(486, 7, 85, 3, '2026-07-31 06:51:06'),
(487, 8, 85, 3, '2026-07-31 06:51:06'),
(488, 9, 85, 3, '2026-07-31 06:51:06'),
(489, 10, 85, 3, '2026-07-31 06:51:06'),
(490, 11, 85, 3, '2026-07-31 06:51:06'),
(894, 1, 1, 1, '2026-07-31 08:01:02'),
(895, 1, 2, 1, '2026-07-31 08:01:02'),
(896, 1, 3, 1, '2026-07-31 08:01:02'),
(897, 1, 4, 1, '2026-07-31 08:01:02'),
(898, 1, 5, 1, '2026-07-31 08:01:02'),
(899, 1, 6, 1, '2026-07-31 08:01:02'),
(900, 1, 13, 1, '2026-07-31 08:01:02'),
(901, 1, 14, 1, '2026-07-31 08:01:02'),
(902, 1, 15, 1, '2026-07-31 08:01:02'),
(903, 1, 16, 1, '2026-07-31 08:01:02'),
(904, 1, 17, 1, '2026-07-31 08:01:02'),
(905, 1, 18, 1, '2026-07-31 08:01:02'),
(906, 1, 31, 1, '2026-07-31 08:01:02'),
(907, 1, 32, 1, '2026-07-31 08:01:02'),
(908, 1, 33, 1, '2026-07-31 08:01:02'),
(909, 1, 34, 1, '2026-07-31 08:01:02'),
(910, 1, 35, 1, '2026-07-31 08:01:02'),
(911, 1, 36, 1, '2026-07-31 08:01:02'),
(912, 1, 37, 1, '2026-07-31 08:01:02'),
(913, 1, 38, 1, '2026-07-31 08:01:02'),
(914, 1, 39, 1, '2026-07-31 08:01:02'),
(915, 1, 40, 1, '2026-07-31 08:01:02'),
(916, 1, 41, 1, '2026-07-31 08:01:02'),
(917, 1, 42, 1, '2026-07-31 08:01:02'),
(918, 1, 43, 1, '2026-07-31 08:01:02'),
(919, 1, 44, 1, '2026-07-31 08:01:02'),
(920, 1, 45, 1, '2026-07-31 08:01:02'),
(921, 1, 46, 1, '2026-07-31 08:01:02'),
(922, 1, 47, 1, '2026-07-31 08:01:02'),
(923, 1, 48, 1, '2026-07-31 08:01:02'),
(924, 1, 49, 1, '2026-07-31 08:01:02'),
(925, 1, 50, 1, '2026-07-31 08:01:02'),
(926, 1, 51, 1, '2026-07-31 08:01:02'),
(927, 1, 52, 1, '2026-07-31 08:01:02'),
(928, 1, 53, 1, '2026-07-31 08:01:02'),
(929, 1, 54, 1, '2026-07-31 08:01:02'),
(930, 1, 55, 1, '2026-07-31 08:01:02'),
(931, 1, 56, 1, '2026-07-31 08:01:02'),
(932, 1, 57, 1, '2026-07-31 08:01:02'),
(933, 1, 58, 1, '2026-07-31 08:01:02'),
(934, 1, 59, 1, '2026-07-31 08:01:02'),
(935, 1, 60, 1, '2026-07-31 08:01:02'),
(936, 1, 61, 1, '2026-07-31 08:01:02'),
(937, 1, 62, 1, '2026-07-31 08:01:02'),
(938, 1, 63, 1, '2026-07-31 08:01:02'),
(939, 1, 64, 1, '2026-07-31 08:01:02'),
(940, 1, 65, 1, '2026-07-31 08:01:02'),
(941, 1, 66, 1, '2026-07-31 08:01:02'),
(942, 1, 67, 1, '2026-07-31 08:01:02'),
(943, 1, 68, 1, '2026-07-31 08:01:02'),
(944, 1, 69, 1, '2026-07-31 08:01:02'),
(945, 1, 70, 1, '2026-07-31 08:01:02'),
(946, 1, 71, 1, '2026-07-31 08:01:02'),
(947, 1, 72, 1, '2026-07-31 08:01:02'),
(948, 1, 73, 1, '2026-07-31 08:01:02'),
(949, 1, 74, 1, '2026-07-31 08:01:02'),
(950, 1, 75, 1, '2026-07-31 08:01:02'),
(951, 1, 76, 1, '2026-07-31 08:01:02'),
(952, 1, 86, 1, '2026-07-31 08:01:02'),
(953, 1, 87, 1, '2026-07-31 08:01:02'),
(954, 1, 88, 1, '2026-07-31 08:01:02'),
(955, 1, 77, 1, '2026-07-31 08:01:02'),
(956, 1, 78, 1, '2026-07-31 08:01:02'),
(957, 1, 79, 1, '2026-07-31 08:01:02'),
(958, 1, 80, 1, '2026-07-31 08:01:02'),
(959, 1, 81, 1, '2026-07-31 08:01:02'),
(960, 1, 82, 1, '2026-07-31 08:01:02'),
(961, 1, 83, 1, '2026-07-31 08:01:02'),
(962, 1, 84, 1, '2026-07-31 08:01:02'),
(963, 1, 85, 1, '2026-07-31 08:01:02'),
(964, 6, 1, 1, '2026-07-31 08:01:25'),
(965, 6, 2, 1, '2026-07-31 08:01:25'),
(966, 6, 3, 1, '2026-07-31 08:01:25'),
(967, 6, 13, 1, '2026-07-31 08:01:25'),
(968, 6, 15, 1, '2026-07-31 08:01:25'),
(969, 6, 31, 1, '2026-07-31 08:01:25'),
(970, 6, 32, 1, '2026-07-31 08:01:25'),
(971, 6, 33, 1, '2026-07-31 08:01:25'),
(972, 6, 34, 1, '2026-07-31 08:01:25'),
(973, 6, 35, 1, '2026-07-31 08:01:25'),
(974, 6, 36, 1, '2026-07-31 08:01:25'),
(975, 6, 37, 1, '2026-07-31 08:01:25'),
(976, 6, 38, 1, '2026-07-31 08:01:25'),
(977, 6, 39, 1, '2026-07-31 08:01:25'),
(978, 6, 40, 1, '2026-07-31 08:01:25'),
(979, 6, 41, 1, '2026-07-31 08:01:25'),
(980, 6, 42, 1, '2026-07-31 08:01:25'),
(981, 6, 43, 1, '2026-07-31 08:01:25'),
(982, 6, 44, 1, '2026-07-31 08:01:25'),
(983, 6, 45, 1, '2026-07-31 08:01:25'),
(984, 6, 46, 1, '2026-07-31 08:01:25'),
(985, 6, 47, 1, '2026-07-31 08:01:25'),
(986, 6, 48, 1, '2026-07-31 08:01:25'),
(987, 6, 51, 1, '2026-07-31 08:01:25'),
(988, 6, 55, 1, '2026-07-31 08:01:25'),
(989, 6, 61, 1, '2026-07-31 08:01:25'),
(990, 6, 65, 1, '2026-07-31 08:01:25'),
(991, 6, 67, 1, '2026-07-31 08:01:25'),
(992, 6, 69, 1, '2026-07-31 08:01:25'),
(993, 6, 77, 1, '2026-07-31 08:01:25'),
(994, 6, 78, 1, '2026-07-31 08:01:25'),
(995, 6, 79, 1, '2026-07-31 08:01:25'),
(996, 6, 80, 1, '2026-07-31 08:01:25'),
(997, 6, 81, 1, '2026-07-31 08:01:25'),
(998, 6, 82, 1, '2026-07-31 08:01:25'),
(999, 6, 83, 1, '2026-07-31 08:01:25'),
(1000, 6, 84, 1, '2026-07-31 08:01:25'),
(1001, 6, 85, 1, '2026-07-31 08:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `role_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default-avatar.png',
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Pending','Active','Inactive','Locked','Archived') DEFAULT 'Pending',
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_first_login` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `mobile_verified` tinyint(1) DEFAULT 0,
  `password_changed_at` datetime DEFAULT NULL,
  `last_password_reset` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `employee_id`, `role_id`, `position_id`, `first_name`, `middle_name`, `last_name`, `mobile_number`, `profile_picture`, `email`, `password`, `status`, `failed_attempts`, `last_login`, `created_at`, `updated_at`, `is_first_login`, `email_verified`, `mobile_verified`, `password_changed_at`, `last_password_reset`) VALUES
(1, 'SA-2026-001', 1, 1, 'Joshua', 'Rivero', 'Suruiz', '09123456789', 'uploads/avatars/avatar_1_1785134623.jpg', 'suruizandrie@gmail.com', '$2y$10$uPJCtqjnT8hE6Yxc7neDYO5rVCXq.Y/vsXELLjz/ywYSrBMJsdPSS', 'Active', 0, '2026-07-31 14:19:37', '2026-07-25 13:51:44', '2026-07-31 06:19:37', 0, 1, 1, '2026-07-26 08:05:47', NULL),
(3, 'ESA-2026-001', 4, 4, 'John Laurence', NULL, 'Gilbuena', '09123467981', 'uploads/avatars/avatar_3_1785076528.jpg', 'suruiz.joshuabcp@gmail.com', '$2y$10$/E/vrqGcvUm0jfXuE07DN.QuKYwsJ2F38nwQkXp60.8nrtFtdJxqm', 'Active', 0, '2026-07-31 12:05:02', '2026-07-26 01:04:33', '2026-07-31 04:05:02', 0, 1, 1, '2026-07-26 11:07:41', NULL),
(4, 'SC-2026-001', 5, 5, 'Mae', NULL, 'Basco', '09987654321', 'default-avatar.png', 'basco@gmail.com', '$2y$10$rpOzDX9zUQYj7As1pnkZc.Hpswl85UykD3AjapcDY2VMRZiUK.I3u', 'Active', 0, NULL, '2026-07-26 04:13:09', '2026-07-28 00:39:26', 1, 1, 1, NULL, NULL),
(5, 'HSA-2026-001', 6, 6, 'Andrie', NULL, 'Suruiz', '09205941912', 'default-avatar.png', 'suruizjoshua72@gmail.com', '$2y$10$KEEha0FBHyWUtQ/o8tnCbOT0zHezauRlIBy4SZeQ9DK.D2w1OpGZS', 'Active', 0, '2026-07-31 14:18:42', '2026-07-31 06:17:31', '2026-07-31 06:18:42', 1, 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_otps`
--

CREATE TABLE `user_otps` (
  `otp_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` char(6) NOT NULL,
  `purpose` enum('Login','Password Reset','Email Verification') NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_otps`
--

INSERT INTO `user_otps` (`otp_id`, `user_id`, `otp_code`, `purpose`, `expires_at`, `verified_at`, `is_used`, `created_at`, `attempts`) VALUES
(1, 1, '261871', 'Login', '2026-07-25 16:08:04', '2026-07-25 16:03:33', 1, '2026-07-25 14:03:04', 0),
(2, 1, '135101', 'Login', '2026-07-25 16:29:44', '2026-07-25 16:25:19', 1, '2026-07-25 14:24:44', 0),
(3, 1, '967390', 'Login', '2026-07-25 16:47:48', '2026-07-25 16:43:11', 1, '2026-07-25 14:42:48', 0),
(4, 1, '127318', 'Login', '2026-07-25 19:09:24', '2026-07-25 19:04:50', 1, '2026-07-25 17:04:24', 1),
(5, 1, '367974', 'Login', '2026-07-26 04:26:57', '2026-07-26 04:22:13', 1, '2026-07-26 02:21:57', 1),
(6, 1, '891920', 'Login', '2026-07-26 06:55:23', '2026-07-26 06:50:44', 1, '2026-07-26 04:50:23', 1),
(7, 1, '200275', 'Login', '2026-07-26 07:01:25', '2026-07-26 06:56:44', 1, '2026-07-26 04:56:25', 1),
(8, 1, '556809', 'Login', '2026-07-26 08:09:42', '2026-07-26 08:05:13', 1, '2026-07-26 06:04:42', 1),
(9, 1, '686990', 'Login', '2026-07-26 08:20:29', '2026-07-26 08:15:41', 1, '2026-07-26 06:15:29', 1),
(10, 1, '487671', 'Login', '2026-07-26 08:36:01', '2026-07-26 08:31:46', 1, '2026-07-26 06:31:01', 1),
(11, 1, '629740', 'Login', '2026-07-26 08:55:25', '2026-07-26 08:51:07', 1, '2026-07-26 06:50:25', 1),
(12, 1, '339369', 'Login', '2026-07-26 09:07:53', '2026-07-26 09:03:28', 1, '2026-07-26 07:02:53', 1),
(13, 3, '536511', 'Login', '2026-07-26 09:10:52', '2026-07-26 09:06:16', 1, '2026-07-26 07:05:52', 1),
(14, 1, '828574', 'Login', '2026-07-26 09:24:02', '2026-07-26 09:19:21', 1, '2026-07-26 07:19:02', 1),
(15, 1, '647637', 'Login', '2026-07-26 09:59:56', '2026-07-26 09:55:07', 1, '2026-07-26 07:54:56', 1),
(16, 1, '750945', 'Login', '2026-07-26 10:08:56', '2026-07-26 10:04:08', 1, '2026-07-26 08:03:56', 1),
(17, 3, '828547', 'Login', '2026-07-26 10:11:50', '2026-07-26 10:07:10', 1, '2026-07-26 08:06:50', 1),
(18, 1, '514517', 'Login', '2026-07-26 10:29:49', '2026-07-26 10:25:09', 1, '2026-07-26 08:24:49', 1),
(19, 1, '718552', 'Login', '2026-07-26 11:08:19', '2026-07-26 11:03:32', 1, '2026-07-26 09:03:19', 1),
(20, 1, '415110', 'Login', '2026-07-26 11:09:05', '2026-07-26 11:04:25', 1, '2026-07-26 09:04:05', 1),
(21, 3, '528672', 'Login', '2026-07-26 11:11:45', '2026-07-26 11:07:06', 1, '2026-07-26 09:06:45', 1),
(22, 3, '226548', 'Login', '2026-07-26 11:40:26', '2026-07-26 11:35:46', 1, '2026-07-26 09:35:26', 1),
(23, 1, '543169', 'Login', '2026-07-26 11:55:17', '2026-07-26 11:50:36', 1, '2026-07-26 09:50:17', 1),
(24, 3, '327834', 'Login', '2026-07-26 12:05:43', '2026-07-26 12:01:02', 1, '2026-07-26 10:00:43', 1),
(25, 3, '938169', 'Login', '2026-07-26 12:16:03', '2026-07-26 12:11:15', 1, '2026-07-26 10:11:03', 1),
(26, 1, '421978', 'Login', '2026-07-26 12:18:52', NULL, 0, '2026-07-26 10:13:52', 1),
(27, 1, '118113', 'Login', '2026-07-26 12:19:23', '2026-07-26 12:15:38', 1, '2026-07-26 10:14:23', 1),
(28, 3, '866867', 'Login', '2026-07-26 12:28:59', '2026-07-26 12:24:22', 1, '2026-07-26 10:23:59', 1),
(29, 3, '171936', 'Login', '2026-07-26 12:35:34', '2026-07-26 12:31:00', 1, '2026-07-26 10:30:34', 1),
(30, 1, '391615', 'Login', '2026-07-26 12:35:45', '2026-07-26 12:31:07', 1, '2026-07-26 10:30:45', 1),
(31, 1, '656998', 'Login', '2026-07-26 12:48:07', '2026-07-26 12:43:22', 1, '2026-07-26 10:43:07', 1),
(32, 1, '361462', 'Login', '2026-07-26 13:03:54', '2026-07-26 12:59:05', 1, '2026-07-26 10:58:54', 1),
(33, 3, '770962', 'Login', '2026-07-26 13:04:57', '2026-07-26 13:00:10', 1, '2026-07-26 10:59:57', 1),
(34, 3, '271858', 'Login', '2026-07-26 13:13:57', '2026-07-26 13:09:29', 1, '2026-07-26 11:08:57', 1),
(35, 1, '445455', 'Login', '2026-07-26 13:31:38', '2026-07-26 13:26:54', 1, '2026-07-26 11:26:38', 1),
(36, 3, '548445', 'Login', '2026-07-26 13:37:30', '2026-07-26 13:32:44', 1, '2026-07-26 11:32:30', 1),
(37, 3, '203085', 'Login', '2026-07-26 15:46:23', NULL, 0, '2026-07-26 13:41:23', 0),
(38, 3, '500195', 'Login', '2026-07-26 16:24:27', '2026-07-26 16:19:44', 1, '2026-07-26 14:19:27', 1),
(39, 3, '250156', 'Login', '2026-07-26 16:39:40', '2026-07-26 16:34:51', 1, '2026-07-26 14:34:40', 1),
(40, 1, '912156', 'Login', '2026-07-26 20:32:58', NULL, 0, '2026-07-26 18:27:58', 0),
(41, 1, '409819', 'Login', '2026-07-27 04:57:13', '2026-07-27 04:52:41', 1, '2026-07-27 02:52:13', 1),
(42, 1, '746587', 'Login', '2026-07-27 06:01:00', '2026-07-27 05:56:55', 1, '2026-07-27 03:56:00', 1),
(43, 1, '633861', 'Login', '2026-07-27 08:06:40', '2026-07-27 08:02:01', 1, '2026-07-27 06:01:40', 1),
(44, 1, '114921', 'Login', '2026-07-27 15:38:18', '2026-07-27 15:33:40', 1, '2026-07-27 13:33:18', 1),
(45, 3, '370586', 'Login', '2026-07-28 07:27:52', '2026-07-28 07:23:24', 1, '2026-07-28 05:22:52', 1),
(46, 3, '536311', 'Login', '2026-07-28 08:03:46', '2026-07-28 07:59:11', 1, '2026-07-28 05:58:46', 1),
(47, 1, '119070', 'Login', '2026-07-28 08:05:11', '2026-07-28 08:00:35', 1, '2026-07-28 06:00:11', 1),
(48, 1, '193813', 'Login', '2026-07-28 08:39:09', '2026-07-28 08:34:36', 1, '2026-07-28 06:34:09', 1),
(49, 3, '541787', 'Login', '2026-07-28 10:04:51', NULL, 0, '2026-07-28 07:59:51', 0),
(50, 3, '926293', 'Login', '2026-07-30 05:57:06', '2026-07-30 05:52:54', 1, '2026-07-30 03:52:06', 1),
(51, 1, '939252', 'Login', '2026-07-30 12:27:10', '2026-07-30 12:22:37', 1, '2026-07-30 10:22:10', 1),
(52, 1, '980357', 'Login', '2026-07-30 12:27:58', '2026-07-30 12:23:16', 1, '2026-07-30 10:22:58', 1),
(53, 1, '391477', 'Login', '2026-07-30 12:39:09', '2026-07-30 12:34:35', 1, '2026-07-30 10:34:09', 1),
(54, 1, '233671', 'Login', '2026-07-30 14:02:48', '2026-07-30 13:58:33', 1, '2026-07-30 11:57:48', 1),
(55, 1, '422558', 'Login', '2026-07-30 20:54:17', '2026-07-30 20:49:58', 1, '2026-07-30 12:49:17', 1),
(56, 1, '277040', 'Login', '2026-07-30 22:31:32', '2026-07-30 22:27:28', 1, '2026-07-30 14:26:32', 1),
(57, 1, '274456', 'Login', '2026-07-30 23:20:25', NULL, 0, '2026-07-30 15:15:25', 0),
(58, 1, '905854', 'Login', '2026-07-30 23:38:16', '2026-07-30 23:33:55', 1, '2026-07-30 15:33:16', 1),
(59, 1, '632127', 'Login', '2026-07-31 00:14:00', '2026-07-31 00:09:32', 1, '2026-07-30 16:09:00', 1),
(60, 1, '455029', 'Login', '2026-07-31 00:52:32', '2026-07-31 00:48:36', 1, '2026-07-30 16:47:32', 2),
(61, 3, '102482', 'Login', '2026-07-31 01:24:40', NULL, 0, '2026-07-30 17:19:40', 0),
(62, 3, '109825', 'Login', '2026-07-31 01:30:51', '2026-07-31 01:27:01', 1, '2026-07-30 17:25:51', 1),
(63, 1, '209518', 'Login', '2026-07-31 12:09:04', '2026-07-31 12:04:28', 1, '2026-07-31 04:04:04', 1),
(64, 3, '116800', 'Login', '2026-07-31 12:09:52', '2026-07-31 12:05:02', 1, '2026-07-31 04:04:52', 1),
(65, 1, '160233', 'Login', '2026-07-31 12:13:38', '2026-07-31 12:09:20', 1, '2026-07-31 04:08:38', 1),
(66, 1, '974950', 'Login', '2026-07-31 14:17:47', '2026-07-31 14:13:12', 1, '2026-07-31 06:12:47', 1),
(67, 5, '958170', 'Login', '2026-07-31 14:23:17', '2026-07-31 14:18:42', 1, '2026-07-31 06:18:17', 1),
(68, 1, '806704', 'Login', '2026-07-31 14:24:07', '2026-07-31 14:19:37', 1, '2026-07-31 06:19:07', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_token_hash` char(64) DEFAULT NULL,
  `refresh_token_hash` char(64) DEFAULT NULL,
  `login_ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `access_token_hash`, `refresh_token_hash`, `login_ip`, `user_agent`, `expires_at`, `created_at`) VALUES
(42, 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 15:23:24', '2026-07-28 05:23:24'),
(44, 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 16:00:35', '2026-07-28 06:00:35'),
(46, 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 13:52:54', '2026-07-30 03:52:54'),
(53, 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 07:33:55', '2026-07-30 15:33:55'),
(55, 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 08:48:36', '2026-07-30 16:48:36'),
(56, 3, NULL, NULL, '::1', 'PostmanRuntime/7.39.1', '2026-07-31 09:27:01', '2026-07-30 17:27:01'),
(61, 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-31 22:18:42', '2026-07-31 06:18:42'),
(62, 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 22:19:37', '2026-07-31 06:19:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`action_id`),
  ADD UNIQUE KEY `action_name` (`action_name`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `actor_user_id` (`actor_user_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `citizen_login_history`
--
ALTER TABLE `citizen_login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `fk_history_session` (`session_id`),
  ADD KEY `idx_login_history` (`citizen_user_id`,`login_time`);

--
-- Indexes for table `citizen_otps`
--
ALTER TABLE `citizen_otps`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `idx_otp_lookup` (`citizen_user_id`,`otp_code`,`purpose`,`is_used`,`expires_at`);

--
-- Indexes for table `citizen_password_resets`
--
ALTER TABLE `citizen_password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`),
  ADD KEY `fk_reset_user` (`citizen_user_id`),
  ADD KEY `idx_reset_token` (`reset_token_hash`,`expires_at`,`used_at`);

--
-- Indexes for table `citizen_sessions`
--
ALTER TABLE `citizen_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `refresh_token_hash` (`refresh_token_hash`),
  ADD KEY `idx_refresh` (`refresh_token_hash`,`is_revoked`),
  ADD KEY `idx_user_sessions` (`citizen_user_id`,`is_revoked`);

--
-- Indexes for table `citizen_users`
--
ALTER TABLE `citizen_users`
  ADD PRIMARY KEY (`citizen_user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile_number` (`mobile_number`),
  ADD KEY `idx_name` (`last_name`,`first_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_code` (`department_code`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD KEY `fk_dept_head_user` (`department_head_user_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`module_id`),
  ADD UNIQUE KEY `module_name` (`module_name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notification_audit` (`audit_id`),
  ADD KEY `idx_notification_action` (`action_id`),
  ADD KEY `idx_notification_actor` (`actor_user_id`),
  ADD KEY `idx_notification_recipient` (`recipient_user_id`),
  ADD KEY `idx_notification_department` (`department_id`),
  ADD KEY `idx_notification_status` (`notification_status`),
  ADD KEY `idx_notification_created` (`created_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_key` (`permission_key`),
  ADD UNIQUE KEY `resource_id` (`resource_id`,`action_id`),
  ADD KEY `action_id` (`action_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`),
  ADD UNIQUE KEY `department_id` (`department_id`,`position_name`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD UNIQUE KEY `resource_route` (`resource_route`),
  ADD UNIQUE KEY `module_id` (`module_id`,`resource_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD UNIQUE KEY `role_prefix` (`role_prefix`),
  ADD KEY `fk_roles_department` (`department_id`);

--
-- Indexes for table `role_department_access`
--
ALTER TABLE `role_department_access`
  ADD PRIMARY KEY (`access_id`),
  ADD UNIQUE KEY `role_id` (`role_id`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_permission_id`),
  ADD UNIQUE KEY `role_id` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `granted_by` (`granted_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `mobile_number` (`mobile_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_role` (`role_id`),
  ADD KEY `fk_user_position` (`position_id`);

--
-- Indexes for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- AUTO_INCREMENT for table `citizen_login_history`
--
ALTER TABLE `citizen_login_history`
  MODIFY `login_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `citizen_otps`
--
ALTER TABLE `citizen_otps`
  MODIFY `otp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `citizen_password_resets`
--
ALTER TABLE `citizen_password_resets`
  MODIFY `reset_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `citizen_sessions`
--
ALTER TABLE `citizen_sessions`
  MODIFY `session_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `citizen_users`
--
ALTER TABLE `citizen_users`
  MODIFY `citizen_user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `role_department_access`
--
ALTER TABLE `role_department_access`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1002;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`session_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_ibfk_4` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_ibfk_5` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE SET NULL;

--
-- Constraints for table `citizen_login_history`
--
ALTER TABLE `citizen_login_history`
  ADD CONSTRAINT `fk_history_session` FOREIGN KEY (`session_id`) REFERENCES `citizen_sessions` (`session_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`citizen_user_id`) REFERENCES `citizen_users` (`citizen_user_id`) ON DELETE SET NULL;

--
-- Constraints for table `citizen_otps`
--
ALTER TABLE `citizen_otps`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`citizen_user_id`) REFERENCES `citizen_users` (`citizen_user_id`) ON DELETE CASCADE;

--
-- Constraints for table `citizen_password_resets`
--
ALTER TABLE `citizen_password_resets`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`citizen_user_id`) REFERENCES `citizen_users` (`citizen_user_id`) ON DELETE CASCADE;

--
-- Constraints for table `citizen_sessions`
--
ALTER TABLE `citizen_sessions`
  ADD CONSTRAINT `fk_session_user` FOREIGN KEY (`citizen_user_id`) REFERENCES `citizen_users` (`citizen_user_id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_department_head` FOREIGN KEY (`department_head_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dept_head_user` FOREIGN KEY (`department_head_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `login_history_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`session_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_action` FOREIGN KEY (`action_id`) REFERENCES `actions` (`action_id`),
  ADD CONSTRAINT `fk_notification_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_notification_audit` FOREIGN KEY (`audit_id`) REFERENCES `audit_logs` (`audit_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notification_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notification_recipient` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `actions` (`action_id`) ON DELETE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `fk_position_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `fk_roles_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `role_department_access`
--
ALTER TABLE `role_department_access`
  ADD CONSTRAINT `role_department_access_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_department_access_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD CONSTRAINT `user_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
