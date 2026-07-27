-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 05:05 AM
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
  `action_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`action_id`, `action_name`) VALUES
(6, 'Approve'),
(2, 'Create'),
(4, 'Delete'),
(3, 'Edit'),
(5, 'Export'),
(1, 'View');

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
(141, 1, NULL, NULL, NULL, NULL, '2FA Verification Success', 'users', '1', 'User completed 2FA verification and signed in.', '::1', 'POST', '/civentral/api/employee/verify-otp.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, 'Success', NULL, '2026-07-27 02:52:41');

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
(1, 'IT', 'Information Technology Department', NULL, 'IT Systems and Infrastructure', 'Active', '2026-07-25 13:51:44', '2026-07-25 13:51:44'),
(4, 'ESMS', 'Education & Scholarship', NULL, 'test', 'Active', '2026-07-26 00:17:15', '2026-07-26 00:17:15'),
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
(58, 1, 38, '2026-07-27 04:52:41', NULL, '::1', NULL, 'Success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Sa', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`module_id`, `module_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(4, 'User & Account Management', 'test', 'Active', '2026-07-26 01:35:13', '2026-07-26 01:35:13'),
(5, 'Role & Permission Management', 'test', 'Active', '2026-07-26 01:35:35', '2026-07-26 01:35:35'),
(6, 'Department Management', 'test', 'Active', '2026-07-26 01:35:58', '2026-07-26 01:35:58'),
(7, 'Citizen Management', '', 'Active', '2026-07-26 01:36:12', '2026-07-26 01:36:12'),
(8, 'Audit Logs System', '', 'Active', '2026-07-26 01:36:25', '2026-07-26 01:36:25');

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
(70, 36, 5, 'data_changes_5', '2026-07-26 11:29:49', 'Active');

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
(1, 1, 'IT Administrator', 'Manages IT infrastructure and security', 'Active', '2026-07-25 13:51:44', '2026-07-25 13:51:44'),
(4, 4, 'Schoolarship Administrator', NULL, 'Active', '2026-07-26 06:54:42', '2026-07-26 08:35:28'),
(5, 4, 'Scholarship Coordinator', NULL, 'Active', '2026-07-26 10:13:09', '2026-07-26 10:13:09');

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
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `module_id`, `resource_name`, `resource_route`, `description`, `status`, `created_at`, `updated_at`) VALUES
(6, 4, 'Users Account', '/pages/usermanagement/create-account.php', 'test', 'Active', '2026-07-26 01:54:37', '2026-07-26 01:54:37'),
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
(36, 8, 'Data Changes', '/pages/audit/data-changes.php', 'Field-level data mutation audit trails, state diffs, and database record change logs', 'Active', '2026-07-26 05:29:49', '2026-07-26 05:29:49');

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
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `is_system_role` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_prefix`, `is_global_access`, `is_superadmin`, `description`, `status`, `is_system_role`, `created_at`, `updated_at`, `department_id`) VALUES
(1, 'Super Administrator', 'SA', 1, 1, 'Full system administration access', 'Active', 1, '2026-07-25 13:51:44', '2026-07-26 09:58:48', 1),
(4, 'Eudcation Scholarship Adminmistrator', 'ESA', 0, 0, 'test', 'Active', 0, '2026-07-26 00:34:59', '2026-07-26 09:59:07', 4),
(5, 'Scholarship Coordinator', 'SC', 0, 0, 'test', 'Active', 0, '2026-07-26 02:26:25', '2026-07-26 09:59:15', 4),
(6, 'Health Sanitation Administrator', 'HSA', 0, 0, 'test', 'Active', 0, '2026-07-26 02:29:40', '2026-07-26 09:59:42', 8);

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
(2, 4, 4, '2026-07-26 02:09:41');

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
(27, 1, 1, 1, '2026-07-26 04:19:11'),
(28, 1, 2, 1, '2026-07-26 04:19:11'),
(29, 1, 3, 1, '2026-07-26 04:19:11'),
(30, 1, 4, 1, '2026-07-26 04:19:11'),
(31, 1, 5, 1, '2026-07-26 04:19:11'),
(32, 1, 6, 1, '2026-07-26 04:19:11'),
(39, 1, 13, 1, '2026-07-26 04:19:11'),
(40, 1, 14, 1, '2026-07-26 04:19:11'),
(41, 1, 15, 1, '2026-07-26 04:19:11'),
(42, 1, 16, 1, '2026-07-26 04:19:11'),
(43, 1, 17, 1, '2026-07-26 04:19:11'),
(44, 1, 18, 1, '2026-07-26 04:19:11'),
(102, 1, 31, NULL, '2026-07-26 10:55:43'),
(103, 1, 32, NULL, '2026-07-26 10:55:43'),
(104, 1, 33, NULL, '2026-07-26 10:55:43'),
(105, 1, 34, NULL, '2026-07-26 10:55:43'),
(106, 1, 35, NULL, '2026-07-26 10:55:43'),
(107, 1, 36, NULL, '2026-07-26 10:55:43'),
(108, 1, 37, NULL, '2026-07-26 10:55:43'),
(109, 1, 38, NULL, '2026-07-26 10:55:43'),
(110, 1, 39, NULL, '2026-07-26 10:55:43'),
(111, 1, 40, NULL, '2026-07-26 10:55:43'),
(112, 1, 41, NULL, '2026-07-26 10:55:43'),
(113, 1, 42, NULL, '2026-07-26 10:55:43'),
(114, 1, 43, NULL, '2026-07-26 10:55:43'),
(115, 1, 44, NULL, '2026-07-26 10:55:43'),
(116, 1, 45, NULL, '2026-07-26 10:55:43'),
(117, 1, 46, NULL, '2026-07-26 10:55:43'),
(118, 1, 47, NULL, '2026-07-26 10:55:43'),
(119, 1, 48, NULL, '2026-07-26 10:55:43'),
(120, 1, 49, NULL, '2026-07-26 10:55:43'),
(121, 1, 50, NULL, '2026-07-26 10:55:43'),
(198, 1, 51, 1, '2026-07-26 05:27:51'),
(199, 1, 52, 1, '2026-07-26 05:27:51'),
(200, 1, 53, 1, '2026-07-26 05:27:51'),
(201, 1, 54, 1, '2026-07-26 05:27:51'),
(219, 1, 55, 1, '2026-07-26 05:29:49'),
(221, 1, 56, 1, '2026-07-26 05:29:49'),
(223, 1, 57, 1, '2026-07-26 05:29:49'),
(225, 1, 58, 1, '2026-07-26 05:29:49'),
(227, 1, 59, 1, '2026-07-26 05:29:49'),
(229, 1, 60, 1, '2026-07-26 05:29:49'),
(231, 1, 61, 1, '2026-07-26 05:29:49'),
(233, 1, 62, 1, '2026-07-26 05:29:49'),
(235, 1, 63, 1, '2026-07-26 05:29:49'),
(237, 1, 64, 1, '2026-07-26 05:29:49'),
(239, 1, 65, 1, '2026-07-26 05:29:49'),
(241, 1, 66, 1, '2026-07-26 05:29:49'),
(243, 1, 67, 1, '2026-07-26 05:29:49'),
(245, 1, 68, 1, '2026-07-26 05:29:49'),
(247, 1, 69, 1, '2026-07-26 05:29:49'),
(249, 1, 70, 1, '2026-07-26 05:29:49'),
(251, 4, 1, 1, '2026-07-26 05:32:03'),
(252, 4, 2, 1, '2026-07-26 05:32:03'),
(253, 4, 3, 1, '2026-07-26 05:32:03'),
(254, 4, 4, 1, '2026-07-26 05:32:03'),
(255, 4, 5, 1, '2026-07-26 05:32:03'),
(256, 4, 13, 1, '2026-07-26 05:32:03'),
(257, 4, 15, 1, '2026-07-26 05:32:03'),
(258, 4, 31, 1, '2026-07-26 05:32:03'),
(259, 4, 35, 1, '2026-07-26 05:32:03'),
(260, 4, 39, 1, '2026-07-26 05:32:03'),
(261, 4, 43, 1, '2026-07-26 05:32:03'),
(262, 4, 47, 1, '2026-07-26 05:32:03'),
(263, 4, 51, 1, '2026-07-26 05:32:03'),
(264, 4, 55, 1, '2026-07-26 05:32:03'),
(265, 4, 61, 1, '2026-07-26 05:32:03'),
(266, 4, 65, 1, '2026-07-26 05:32:03'),
(267, 4, 66, 1, '2026-07-26 05:32:03'),
(268, 4, 67, 1, '2026-07-26 05:32:03'),
(269, 4, 68, 1, '2026-07-26 05:32:03'),
(270, 4, 69, 1, '2026-07-26 05:32:03'),
(271, 4, 70, 1, '2026-07-26 05:32:03');

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
(1, 'SA-2026-001', 1, 1, 'Joshua ', 'Rivero', 'Suruiz', '09123456789', 'default-avatar.png', 'suruizandrie@gmail.com', '$2y$10$uPJCtqjnT8hE6Yxc7neDYO5rVCXq.Y/vsXELLjz/ywYSrBMJsdPSS', 'Active', 0, '2026-07-27 04:52:41', '2026-07-25 13:51:44', '2026-07-27 02:52:41', 0, 1, 1, '2026-07-26 08:05:47', NULL),
(3, 'ESA-2026-001', 4, 4, 'John Laurence', NULL, 'Gilbuena', '09123467981', 'uploads/avatars/avatar_3_1785076528.jpg', 'suruiz.joshuabcp@gmail.com', '$2y$10$/E/vrqGcvUm0jfXuE07DN.QuKYwsJ2F38nwQkXp60.8nrtFtdJxqm', 'Active', 0, '2026-07-26 16:34:51', '2026-07-26 01:04:33', '2026-07-26 08:35:28', 0, 1, 1, '2026-07-26 11:07:41', NULL),
(4, 'SC-2026-001', 5, 5, 'Mae', NULL, 'Basco', '09987654321', 'default-avatar.png', 'basco@gmail.com', '$2y$10$rpOzDX9zUQYj7As1pnkZc.Hpswl85UykD3AjapcDY2VMRZiUK.I3u', 'Active', 0, NULL, '2026-07-26 04:13:09', '2026-07-26 08:43:32', 1, 1, 1, NULL, NULL);

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
(41, 1, '409819', 'Login', '2026-07-27 04:57:13', '2026-07-27 04:52:41', 1, '2026-07-27 02:52:13', 1);

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
(38, 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 12:52:41', '2026-07-27 02:52:41');

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
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

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
  MODIFY `citizen_user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_department_access`
--
ALTER TABLE `role_department_access`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=272;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

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
