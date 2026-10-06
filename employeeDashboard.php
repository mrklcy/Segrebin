<?php
session_start();

if (!isset($_SESSION['employee_logged_in']) && (!isset($_COOKIE['employee_logged_in']) || $_COOKIE['employee_logged_in'] !== 'true')) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEGREBIN - Employee View</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
  /* Base Styles */
  :root {
    --primary-bg: #ffffff;
    --secondary-bg: #f8f9fa;
    --card-bg: #ffffff;
    --text-color: #333333;
    --text-muted: #6c757d;
    --blue: #4ade80;
    --red: #ff5a5a;
    --teal: #34d399;
    --orange: #fb6340;
    --positive: #10b981;
    --negative: #f5365c;
    --yellow: #fbbf24;
    --border-radius: 0.75rem;
    --transition: all 0.3s ease;
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
    --border-color: #e9ecef;
    --purple: #008000;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--secondary-bg);
    color: var(--text-color);
    line-height: 1.6;
  }

  a {
    text-decoration: none;
    color: inherit;
  }

  ul {
    list-style: none;
  }

  /* Layout */
  .container {
    display: flex;
    min-height: 100vh;
  }

  /* Sidebar */
  .sidebar {
    width: 250px;
    background-color: var(--primary-bg);
    border-right: 1px solid var(--border-color);
    padding: 1.5rem 1rem;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
    box-shadow: var(--shadow-sm);
  }

  .logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    padding: 0 0.5rem;
  }

  .logo img {
    width: 32px;
    height: 32px;
  }

  .logo span {
    font-weight: 700;
    font-size: 1rem;
    white-space: nowrap;
    color: var(--text-color);
  }

  .nav-menu ul {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .nav-menu li a {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius);
    transition: var(--transition);
    color: var(--text-muted);
    position: relative;
  }

  .nav-menu li a:hover,
  .nav-menu li.active a {
    color: var(--purple);
  }

  .nav-menu li.active a {
    font-weight: 600;
  }

  .nav-menu li.active a::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: var(--purple);
    border-radius: 4px 0 0 4px;
  }

  .nav-section {
    margin-top: 2rem;
  }

  .nav-section h3 {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
    padding-left: 1rem;
  }

  /* Main Content */
  .main-content {
    flex: 1;
    margin-left: 250px;
    padding: 1.5rem;
  }

  /* Header */
  .header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    position: sticky;
    top: 0;
    z-index: 99;
    background-color: var(--primary-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    animation: fadeInDown 0.5s ease-out;
  }

  .header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
  }

  .header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
  }

  .header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .search-bar {
    position: relative;
    width: 250px;
  }

  .search-bar input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    background-color: var(--primary-bg);
    color: var(--text-color);
  }

  .search-bar i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
  }

  .user-menu {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  /* User Profile Styles */
  .user-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
    cursor: pointer;
  }

  .avatar-container {
    position: relative;
  }

  .avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #4ade80;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .avatar-dot {
    width: 8px;
    height: 8px;
    background-color: #10b981;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .notification-badge {
    position: absolute;
    top: -5px;
    left: -5px;
    background-color: #4ade80;
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
  }

  .user-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--text-color);
  }

  .btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    background-color: var(--secondary-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
  }

  .mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--text-color);
    font-size: 1.5rem;
    cursor: pointer;
  }

  /* User Dropdown Menu */
  .user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 250px;
    background-color: var(--primary-bg);
    border-radius: 0.75rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
  }

  .user-dropdown.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .dropdown-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
  }

  .dropdown-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .dropdown-user-info {
    display: flex;
    flex-direction: column;
  }

  .dropdown-user-name {
    font-weight: 600;
    color: var(--text-color);
  }

  .dropdown-user-email {
    font-size: 0.8rem;
    color: var(--text-muted);
  }

  .dropdown-menu {
    padding: 0.5rem 0;
  }

  .dropdown-menu-item {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-color);
    transition: background-color 0.2s;
  }

  .dropdown-menu-item:hover {
    background-color: var(--secondary-bg);
  }

  .dropdown-menu-item i {
    width: 20px;
    color: var(--text-muted);
  }

  /* Dashboard Content */
  .dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  /* Bin Levels */
  .bin-levels {
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    margin-bottom: 2rem;
    animation: fadeInUp 0.6s ease-out;
  }

  .bin-levels h2 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .bin-location-address {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
  }

  .bin-circles {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    justify-content: space-around;
    margin-bottom: 1rem;
  }

  .bin-circle {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }

  .circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    position: relative;
    transition: transform 0.5s ease, box-shadow 0.5s ease;
  }

  .circle:hover {
    transform: scale(1.1);
    cursor: pointer;
  }

  .circle::before {
    content: '';
    position: absolute;
    top: 5px;
    left: 5px;
    right: 5px;
    bottom: 5px;
    border-radius: 50%;
    background-color: var(--card-bg);
    z-index: 1;
  }

  .circle span {
    position: relative;
    z-index: 2;
  }

  .bin-name {
    font-size: 0.875rem;
    font-weight: 600;
  }

  .bin-location {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-color);
    margin-bottom: 0.25rem;
  }

  .bin-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
  }

  .bin-action-btn {
    padding: 0.6rem 1.2rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .collect-btn {
    background-color: var(--positive);
    color: white;
  }

  .collect-btn:hover {
    background-color: #0da271;
  }

  .report-btn {
    background-color: var(--yellow);
    color: #333;
  }

  .report-btn:hover {
    background-color: #e5ac1f;
  }

  /* Animated bin level circles */
  .level-low {
    background: conic-gradient(var(--positive) 0% 25%, rgba(0, 0, 0, 0.05) 25% 100%);
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
    animation: pulse-green 2s infinite ease-in-out;
  }

  .level-medium {
    background: conic-gradient(var(--yellow) 0% 65%, rgba(0, 0, 0, 0.05) 65% 100%);
    box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
    animation: pulse-yellow 2s infinite ease-in-out;
  }

  .level-high {
    background: conic-gradient(var(--negative) 0% 90%, rgba(0, 0, 0, 0.05) 90% 100%);
    box-shadow: 0 0 15px rgba(245, 54, 92, 0.3);
    animation: pulse-red 2s infinite ease-in-out;
  }

  .level-full {
    background: var(--negative);
    box-shadow: 0 0 15px rgba(245, 54, 92, 0.5);
    animation: pulse-red-urgent 1.2s infinite ease-in-out, shake 0.8s infinite;
  }

  /* Footer */
  .footer {
    margin-top: auto;
    padding: 1.5rem;
    background-color: var(--primary-bg);
    border-top: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
  }

  .footer-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-muted);
    font-size: 0.875rem;
  }

  .footer-left .crown-icon {
    color: var(--yellow);
    font-size: 1rem;
  }

  .footer-right {
    display: flex;
    gap: 1.5rem;
  }

  .footer-link {
    color: var(--text-muted);
    font-size: 0.875rem;
    transition: color 0.2s;
  }

  .footer-link:hover {
    color: var(--text-color);
  }

  /* New animations for bin levels */
  @keyframes pulse-green {
    0%, 100% { box-shadow: 0 0 15px rgba(16, 185, 129, 0.3); }
    50% { box-shadow: 0 0 25px rgba(16, 185, 129, 0.6); }
  }

  @keyframes pulse-yellow {
    0%, 100% { box-shadow: 0 0 15px rgba(251, 191, 36, 0.3); }
    50% { box-shadow: 0 0 25px rgba(251, 191, 36, 0.6); }
  }

  @keyframes pulse-red {
    0%, 100% { box-shadow: 0 0 15px rgba(245, 54, 92, 0.3); }
    50% { box-shadow: 0 0 25px rgba(245, 54, 92, 0.6); }
  }

  @keyframes pulse-red-urgent {
    0%, 100% { box-shadow: 0 0 15px rgba(245, 54, 92, 0.5); }
    50% { box-shadow: 0 0 30px rgba(245, 54, 92, 0.8); }
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
    20%, 40%, 60%, 80% { transform: translateX(2px); }
  }

  /* Fill animation for when circles are clicked */
  .fill-animation {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    transform: scale(0);
    z-index: 0;
    animation: fill-ripple 0.8s ease-out;
  }

  @keyframes fill-ripple {
    0% {
      transform: scale(0);
      opacity: 0.5;
    }
    100% {
      transform: scale(2);
      opacity: 0;
    }
  }

  .footer {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .footer-right {
    justify-content: center;
  }

  /* Google Maps Style Bin Locations */
  .bin-locations {
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    animation: fadeInUp 0.8s ease-out;
  }

  .bin-locations h2 {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
  }

  .map-container {
    position: relative;
    height: 400px;
    background-color: #f5f5f5;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
  }

  .map-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgdmlld0JveD0iMCAwIDgwMCA2MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPHJlY3Qgd2lkdGg9IjgwMCIgaGVpZ2h0PSI2MDAiIGZpbGw9IiNmNWY1ZjUiLz4KICA8cGF0aCBkPSJNMCAwIEw4MDAgNjAwIiBzdHJva2U9IiNlMGUwZTAiIHN0cm9rZS13aWR0aD0iMSIvPgogIDxwYXRoIGQ9Ik04MDAgMCBMMCw2MDAiIHN0cm9rZT0iI2UwZTBlMCIgc3Ryb2tlLXdpZHRoPSIxIi8+CiAgPHBhdGggZD0iTTAgMzAwIEw4MDAgMzAwIiBzdHJva2U9IiNjY2NjY2MiIHN0cm9rZS13aWR0aD0iMiIvPgogIDxwYXRoIGQ9Ik00MDAgMCBMNDAwIDYwMCIgc3Ryb2tlPSIjY2NjY2NjIiBzdHJva2Utd2lkdGg9IjIiLz4KICA8cGF0aCBkPSJNMTAwIDUwIEwxMDAgNTUwIiBzdHJva2U9IiNlMGUwZTAiIHN0cm9rZS13aWR0aD0iMSIvPgogIDxwYXRoIGQ9Ik0yMDAgNTAgTDIwMCA1NTAiIHN0cm9rZT0iI2UwZTBlMCIgc3Ryb2tlLXdpZHRoPSIxIi8+CiAgPHBhdGggZD0iTTMwMCA1MCBMMzAwIDU1MCIgc3Ryb2tlPSIjZTBlMGUwIiBzdHJva2Utd2lkdGg9IjEiLz4KICA8cGF0aCBkPSJNNTAwIDUwIEw1MDAgNTUwIiBzdHJva2U9IiNlMGUwZTAiIHN0cm9rZS13aWR0aD0iMSIvPgogIDxwYXRoIGQ9Ik02MDAgNTAgTDYwMCA1NTAiIHN0cm9rZT0iI2UwZTBlMCIgc3Ryb2tlLXdpZHRoPSIxIi8+CiAgPHBhdGggZD0iTTcwMCA1MCBMNzAwIDU1MCIgc3Ryb2tlPSIjZTBlMGUwIiBzdHJva2Utd2lkdGg9IjEiLz4KICA8cGF0aCBkPSJNNTAgMTAwIEw3NTAgMTAwIiBzdHJva2U9IiNlMGUwZTAiIHN0cm9rZS13aWR0aD0iMSIvPgogIDxwYXRoIGQ9Ik01MCAyMDAgTDc1MCAyMDAiIHN0cm9rZT0iI2UwZTBlMCIgc3Ryb2tlLXdpZHRoPSIxIi8+CiAgPHBhdGggZD0iTTUwIDQwMCBMNzUwIDQwMCIgc3Ryb2tlPSIjZTBlMGUwIiBzdHJva2Utd2lkdGg9IjEiLz4KICA8cGF0aCBkPSJNNTAgNTAwIEw3NTAgNTAwIiBzdHJva2U9IiNlMGUwZTAiIHN0cm9rZS13aWR0aD0iMSIvPgogIDxjaXJjbGUgY3g9IjQwMCIgY3k9IjMwMCIgcj0iMTAwIiBzdHJva2U9IiNjY2NjY2MiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPgogIDxwYXRoIGQ9Ik0xMDAgMTUwIEwzMDAgMTUwIEwzMDAgMjUwIEwxMDAgMjUwIFoiIHN0cm9rZT0iI2NjY2NjYyIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+CiAgPHBhdGggZD0iTTUwMCAzNTAgTDcwMCAzNTAgTDcwMCA0NTAgTDUwMCA0NTAgWiIgc3Ryb2tlPSIjY2NjY2NjIiBzdHJva2Utd2lkdGg9IjIiIGZpbGw9Im5vbmUiLz4KICA8cGF0aCBkPSJNMTUwIDQwMCBMMjUwIDQwMCBMMjUwIDUwMCBMMTUwIDUwMCBaIiBzdHJva2U9IiNjY2NjY2MiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPgogIDxwYXRoIGQ9Ik01NTAgMTUwIEw2NTAgMTUwIEw2NTAgMjUwIEw1NTAgMjUwIFoiIHN0cm9rZT0iI2NjY2NjYyIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+Cjwvc3ZnPg==');
    background-size: cover;
    opacity: 0.8;
  }

  .map-roads {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
  }

  .map-road {
    position: absolute;
    background-color: #cccccc;
  }

  .map-road-h {
    height: 6px;
    transform: translateY(-3px);
  }

  .map-road-v {
    width: 6px;
    transform: translateX(-3px);
  }

  .map-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    z-index: 5;
  }

  .map-control-btn {
    width: 30px;
    height: 30px;
    border-radius: 2px;
    background-color: rgba(255, 255, 255, 0.9);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #333;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
  }

  .map-pin {
    position: absolute;
    transform: translate(-50%, -100%);
    z-index: 2;
    cursor: pointer;
  }

  .map-pin-icon {
    width: 24px;
    height: 36px;
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMzYiIHZpZXdCb3g9IjAgMCAyNCAzNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cGF0aCBkPSJNMTIgMEMxOC42MjcgMCAyNCA1LjM3MyAyNCAxMkMyNCAxOC42MjcgMTIgMzYgMTIgMzZDMTIgMzYgMCAxOC42MjcgMCAxMkMwIDUuMzczIDUuMzczIDAgMTIgMFoiIGZpbGw9IiMxMGI5ODEiLz4KICA8Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSI2IiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4=');
    background-size: contain;
    background-repeat: no-repeat;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
  }

  .map-pin-icon.warning {
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMzYiIHZpZXdCb3g9IjAgMCAyNCAzNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cGF0aCBkPSJNMTIgMEMxOC42MjcgMCAyNCA1LjM3MyAyNCAxMkMyNCAxOC42MjcgMTIgMzYgMTIgMzZDMTIgMzYgMCAxOC42MjcgMCAxMkMwIDUuMzczIDUuMzczIDAgMTIgMFoiIGZpbGw9IiNmYmJmMjQiLz4KICA8Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSI2IiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4=');
  }

  .map-pin-icon.alert {
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMzYiIHZpZXdCb3g9IjAgMCAyNCAzNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cGF0aCBkPSJNMTIgMEMxOC42MjcgMCAyNCA1LjM3MyAyNCAxMkMyNCAxOC42MjcgMTIgMzYgMTIgMzZDMTIgMzYgMCAxOC42MjcgMCAxMkMwIDUuMzczIDUuMzczIDAgMTIgMFoiIGZpbGw9IiNmNTM2NWMiLz4KICA8Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSI2IiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4=');
  }

  .map-pin-label {
    position: absolute;
    top: -40px;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.2s;
    pointer-events: none;
  }

  .map-pin:hover .map-pin-label {
    opacity: 1;
  }

  .map-info-window {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background-color: white;
    border-radius: 8px;
    padding: 12px;
    width: 250px;
    box-shadow: var(--shadow);
    color: #333;
    z-index: 10;
    border: 1px solid var(--border-color);
    display: none;
  }

  .map-info-window.active {
    display: block;
  }

  .map-info-window h3 {
    font-size: 16px;
    margin-bottom: 5px;
    color: #111;
  }

  .map-info-window p {
    font-size: 13px;
    margin-bottom: 8px;
    color: #555;
  }

  .map-info-window .status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    margin-top: 5px;
  }

  .map-info-window .status.low {
    background-color: rgba(16, 185, 129, 0.2);
    color: var(--positive);
  }

  .map-info-window .status.medium {
    background-color: rgba(251, 191, 36, 0.2);
    color: #d4b100;
  }

  .map-info-window .status.high {
    background-color: rgba(245, 54, 92, 0.2);
    color: var(--negative);
  }

  /* Animations */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive Styles */
  @media (max-width: 1200px) {
    .charts-section,
    .tables-section {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 992px) {
    .sidebar {
      transform: translateX(-100%);
      transition: transform 0.3s ease;
    }

    .sidebar.active {
      transform: translateX(0);
    }

    .main-content {
      margin-left: 0;
    }

    .mobile-menu-toggle {
      display: block;
    }

    .header-right {
      margin-left: auto;
    }

    .footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
      }
  
      .footer-right {
        justify-content: center;
      }

    .bin-circles {
      gap: 1rem;
    }

    .circle {
      width: 80px;
      height: 80px;
      font-size: 1.25rem;
    }
    
    .bin-actions {
      flex-direction: column;
      align-items: center;
    }
  }

  @media (max-width: 768px) {
    .bin-circles {
      flex-direction: column;
      align-items: center;
    }

    .search-bar {
      width: 100%;
    }

    .header-left {
      width: 100%;
      margin-bottom: 1rem;
    }

    .header-right {
      width: 100%;
    }

    .bin-circles {
      flex-direction: column;
      align-items: center;
    }

    .updates-table {
      font-size: 0.75rem;
    }

    .time-ago {
      font-size: 0.7rem;
    }
  }

  /* Logout Confirmation Modal Styles */
  .logout-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    justify-content: center;
    align-items: center;
  }

  .logout-modal.active {
    display: flex;
  }

  .logout-modal-content {
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 2rem;
    width: 90%;
    max-width: 400px;
    box-shadow: var(--shadow-lg);
    text-align: center;
    animation: fadeInUp 0.3s ease-out;
  }

  .logout-modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
  }

  .logout-modal-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
  }

  .logout-modal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
  }

  .logout-modal-btn-cancel {
    background-color: var(--secondary-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
  }

  .logout-modal-btn-cancel:hover {
    background-color: #e9ecef;
  }

  .logout-modal-btn-confirm {
    background-color: var(--negative);
    color: white;
    border: none;
  }

  .logout-modal-btn-confirm:hover {
    background-color: #e03050;
  }

  /* Report Modal Styles */
  .report-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    justify-content: center;
    align-items: center;
  }

  .report-modal.active {
    display: flex;
  }

  .report-modal-content {
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 2rem;
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
    animation: fadeInUp 0.3s ease-out;
  }

  .report-modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .report-modal-title i {
    color: var(--yellow);
  }

  .report-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .form-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-color);
  }

  .form-group select,
  .form-group textarea,
  .form-group input {
    padding: 0.75rem;
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
    background-color: var(--primary-bg);
    color: var(--text-color);
    font-family: inherit;
    resize: vertical;
  }

  .form-group textarea {
    min-height: 100px;
  }

  .severity-options {
    display: flex;
    gap: 1rem;
  }

  .severity-option {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
    cursor: pointer;
    transition: var(--transition);
  }

  .severity-option:hover {
    background-color: var(--secondary-bg);
  }

  .severity-option.selected {
    border-color: var(--yellow);
    background-color: rgba(251, 191, 36, 0.1);
  }

  .severity-option.selected.low {
    border-color: var(--positive);
    background-color: rgba(16, 185, 129, 0.1);
  }

  .severity-option.selected.high {
    border-color: var(--negative);
    background-color: rgba(245, 54, 92, 0.1);
  }

  .severity-icon {
    font-size: 1.5rem;
  }

  .severity-low {
    color: var(--positive);
  }

  .severity-medium {
    color: var(--yellow);
  }

  .severity-high {
    color: var(--negative);
  }

  .report-modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
  }

  .report-modal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
  }

  .report-modal-btn-cancel {
    background-color: var(--secondary-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
  }

  .report-modal-btn-cancel:hover {
    background-color: #e9ecef;
  }

  .report-modal-btn-submit {
    background-color: var(--yellow);
    color: #333;
    border: none;
  }

  .report-modal-btn-submit:hover {
    background-color: #e5ac1f;
  }
</style>
</head>
<body>
<div class="container">
<!-- Sidebar -->
<aside class="sidebar">
  <div class="logo">
    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3QgeD0iMyIgeT0iMyIgd2lkdGg9IjE4IiBoZWlnaHQ9IjE4IiByeD0iMiIgc3Ryb2tlPSIjMzMzMzMzIiBzdHJva2Utd2lkdGg9IjIiLz4KPHJlY3QgeD0iNyIgeT0iNyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSIxMyIgeT0iNyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSI3IiB5PSIxMyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSIxMyIgeT0iMTMiIHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiMzMzMzMzMiLz4KPC9zdmc+" alt="Logo">
    <span>SEGREBIN</span>
  </div>
  <nav class="nav-menu">
    <ul>
      <li class="active">
        <a href="index.html">
          <i class="fa-solid fa-home"></i>
          <span>Dashboard</span>
        </a>
      </li>
    </ul>

    <div class="nav-section">
      <h3>ACCOUNT PAGES</h3>
      <ul>
        <li>
          <a href="#">
            <i class="fa-solid fa-user"></i>
            <span>Profile</span>
          </a>
        </li>
        <li>
          <a href="#" id="logoutBtn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="main-content">
  <!-- Header -->
  <header class="header">
    <div class="header-left">
      <h1>Employee Dashboard</h1>
      <div class="search-bar">
        <i class="fa-solid fa-search"></i>
        <input type="text" placeholder="Type here...">
      </div>
    </div>
    <div class="header-right">
      <div class="user-menu">
        <div class="user-profile" id="userProfileTrigger">
          <div class="avatar-container">
            <div class="avatar">
              <div class="avatar-dot"></div>
            </div>
            <div class="notification-badge">3</div>
          </div>
          <div class="user-name">Aiden Max</div>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <button class="btn-icon">
          <i class="fa-solid fa-gear"></i>
        </button>
        <button class="btn-icon">
          <i class="fa-solid fa-bell"></i>
        </button>
      </div>
    </div>
    <button class="mobile-menu-toggle">
      <i class="fa-solid fa-bars"></i>
    </button>
    
    <!-- User Dropdown Menu -->
    <div class="user-dropdown" id="userDropdown">
      <div class="dropdown-header">
        <div class="dropdown-avatar">
          <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-CnYOTm2EYgnfTTQZgktcT0hHcsfTLf.png" alt="User Avatar">
        </div>
        <div class="dropdown-user-info">
          <div class="dropdown-user-name">Aiden Max</div>
          <div class="dropdown-user-email">aiden.max@example.com</div>
        </div>
      </div>
      <div class="dropdown-menu">
        <a href="#" class="dropdown-menu-item">
          <i class="fa-regular fa-user"></i>
          <span>View Profile</span>
        </a>
        <a href="#" class="dropdown-menu-item">
          <i class="fa-solid fa-gear"></i>
          <span>Account Settings</span>
        </a>
        <a href="#" class="dropdown-menu-item" id="dropdownLogoutBtn">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          <span>Logout</span>
        </a>
      </div>
    </div>
  </header>

  <!-- Dashboard Content -->
  <div class="dashboard-content">
    <!-- City Hall Bin Levels -->
    <div class="bin-levels">
      <h2>City Hall</h2>
      <div class="bin-location-address">
        <i class="fa-solid fa-location-dot"></i> 123 Government Ave, Downtown District
      </div>
      <div class="bin-circles">
        <div class="bin-circle">
          <div class="circle level-high">
            <span>85%</span>
          </div>
          <div class="bin-name">Plastic</div>
        </div>
        <div class="bin-circle">
          <div class="circle level-medium">
            <span>60%</span>
          </div>
          <div class="bin-name">Paper</div>
        </div>
        <div class="bin-circle">
          <div class="circle level-full">
            <span>100%</span>
          </div>
          <div class="bin-name">Styrofoam</div>
        </div>
      </div>
      <div class="bin-actions">
        <button class="bin-action-btn collect-btn">
          <i class="fa-solid fa-truck"></i> Collect
        </button>
        <button class="bin-action-btn report-btn" data-location="City Hall">
          <i class="fa-solid fa-flag"></i> Report
        </button>
      </div>
    </div>

    <!-- Central Park Bin Levels -->
    <div class="bin-levels">
      <h2>Central Park</h2>
      <div class="bin-location-address">
        <i class="fa-solid fa-location-dot"></i> 456 Park Avenue, Green Zone
      </div>
      <div class="bin-circles">
        <div class="bin-circle">
          <div class="circle level-low">
            <span>25%</span>
          </div>
          <div class="bin-name">Plastic</div>
        </div>
        <div class="bin-circle">
          <div class="circle level-medium">
            <span>45%</span>
          </div>
          <div class="bin-name">Paper</div>
        </div>
        <div class="bin-circle">
          <div class="circle level-high">
            <span>75%</span>
          </div>
          <div class="bin-name">Styrofoam</div>
        </div>
      </div>
      <div class="bin-actions">
        <button class="bin-action-btn collect-btn">
          <i class="fa-solid fa-truck"></i> Collect
        </button>
        <button class="bin-action-btn report-btn" data-location="Central Park">
          <i class="fa-solid fa-flag"></i> Report
        </button>
      </div>
    </div>

    <!-- Bin Locations -->
    <div class="bin-locations">
      <h2>Bin Locations</h2>
      <div class="map-container">
        <div class="map-bg"></div>
        
        <!-- Map Roads -->
        <div class="map-roads">
          <div class="map-road map-road-h" style="top: 30%; left: 0; width: 100%;"></div>
          <div class="map-road map-road-h" style="top: 60%; left: 0; width: 100%;"></div>
          <div class="map-road map-road-v" style="left: 25%; top: 0; height: 100%;"></div>
          <div class="map-road map-road-v" style="left: 50%; top: 0; height: 100%;"></div>
          <div class="map-road map-road-v" style="left: 75%; top: 0; height: 100%;"></div>
        </div>
        
        <!-- Map Controls -->
        <div class="map-controls">
          <button class="map-control-btn">+</button>
          <button class="map-control-btn">−</button>
        </div>
        
        <!-- Map Pins -->
        <div class="map-pin" style="top: 30%; left: 25%;" data-bin="Plastic" data-location="Central Park" data-level="25%" data-status="low">
          <div class="map-pin-icon"></div>
          <div class="map-pin-label">Central Park - Plastic</div>
        </div>
        
        <div class="map-pin" style="top: 45%; left: 40%;" data-bin="Paper" data-location="Central Park" data-level="45%" data-status="medium">
          <div class="map-pin-icon warning"></div>
          <div class="map-pin-label">Central Park - Paper</div>
        </div>
        
        <div class="map-pin" style="top: 60%; left: 65%;" data-bin="Styrofoam" data-location="City Hall" data-level="100%" data-status="high">
          <div class="map-pin-icon alert"></div>
          <div class="map-pin-label">City Hall - Styrofoam</div>
        </div>
        
        <!-- Map Info Window -->
        <div class="map-info-window" id="mapInfoWindow">
          <h3>City Hall</h3>
          <p><strong>Waste Type:</strong> <span id="infoLocation">Styrofoam</span></p>
          <p><strong>Fill Level:</strong> <span id="infoLevel">100%</span></p>
          <p><strong>Status:</strong> <span class="status high" id="infoStatus">Needs Attention</span></p>
        </div>
      </div>
    </div>
  </div>
</main>
</div>

 <!-- Footer -->
 <footer class="footer">
    <div class="footer-left">
        <p>© 2025, made by <a href="#">SEGRE TEAM</a><i class="fa-solid fa-dumpster"></i></p>
    </div>
    <div class="footer-right">
        <a href="#">SEGRE TEAM</a>
        <a href="#">About Us</a>
        <a href="#">Blog</a>
        <a href="#">License</a>
    </div>
</footer>
</div>
</main>
</div>

<!-- Logout Confirmation Modal -->
<div class="logout-modal" id="logoutModal">
  <div class="logout-modal-content">
    <h2 class="logout-modal-title">Are you sure?</h2>
    <p>Do you really want to logout from your account?</p>
    <div class="logout-modal-buttons">
      <button class="logout-modal-btn logout-modal-btn-cancel" id="cancelLogout">Cancel</button>
      <button class="logout-modal-btn logout-modal-btn-confirm" id="confirmLogout">Yes, Logout</button>
    </div>
  </div>
</div>

<!-- Report Issue Modal -->
<div class="report-modal" id="reportModal">
  <div class="report-modal-content">
    <h2 class="report-modal-title">
      <i class="fa-solid fa-triangle-exclamation"></i>
      Report Issue at <span id="reportLocation">City Hall</span>
    </h2>
    <form class="report-form" id="reportForm">
      <div class="form-group">
        <label for="issueType">Issue Type</label>
        <select id="issueType" required>
          <option value="">Select issue type</option>
          <option value="damage">Bin Damage</option>
          <option value="malfunction">Bin Malfunction</option>
          <option value="overflow">Bin Overflow</option>
          <option value="contamination">Waste Contamination</option>
          <option value="access">Access Problem</option>
          <option value="other">Other</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="wasteType">Waste Type</label>
        <select id="wasteType" required>
          <option value="">Select waste type</option>
          <option value="plastic">Plastic</option>
          <option value="paper">Paper</option>
          <option value="styrofoam">Styrofoam</option>
        </select>
      </div>
      
      <div class="form-group">
        <label>Severity Level</label>
        <div class="severity-options">
          <div class="severity-option" data-severity="low">
            <i class="fa-solid fa-circle-info severity-icon severity-low"></i>
            <span>Low</span>
          </div>
          <div class="severity-option" data-severity="medium">
            <i class="fa-solid fa-triangle-exclamation severity-icon severity-medium"></i>
            <span>Medium</span>
          </div>
          <div class="severity-option" data-severity="high">
            <i class="fa-solid fa-circle-exclamation severity-icon severity-high"></i>
            <span>High</span>
          </div>
        </div>
        <input type="hidden" id="severityLevel" value="" required>
      </div>
      
      <div class="form-group">
        <label for="issueDescription">Description</label>
        <textarea id="issueDescription" placeholder="Please describe the issue in detail..." required></textarea>
      </div>
      
      <div class="report-modal-buttons">
        <button type="button" class="report-modal-btn report-modal-btn-cancel" id="cancelReport">Cancel</button>
        <button type="submit" class="report-modal-btn report-modal-btn-submit">Submit Report</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
  const sidebar = document.querySelector('.sidebar');

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', function() {
      sidebar.classList.toggle('active');
    });
  }

  // User dropdown toggle
  const userProfileTrigger = document.getElementById('userProfileTrigger');
  const userDropdown = document.getElementById('userDropdown');

  if (userProfileTrigger && userDropdown) {
    userProfileTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdown.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userProfileTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('active');
      }
    });
  }

  // Add click event listeners to bin level circles for ripple effect
  const binCircles = document.querySelectorAll('.circle');
  binCircles.forEach(circle => {
    circle.addEventListener('click', function(e) {
      // Create ripple element
      const ripple = document.createElement('div');
      ripple.classList.add('fill-animation');
      this.appendChild(ripple);
      
      // Remove ripple after animation completes
      setTimeout(() => {
        ripple.remove();
      }, 800);
      
      // Show bin info (could be expanded to show a modal with more details)
      const binName = this.closest('.bin-circle').querySelector('.bin-name').textContent;
      const binLocation = this.closest('.bin-levels').querySelector('h2').textContent;
      const fillLevel = this.querySelector('span').textContent;
      
      console.log(`Location: ${binLocation}, Waste Type: ${binName}, Fill Level: ${fillLevel}`);
    });
  });

  // Animate bin level percentages when they come into view
  function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      const value = Math.floor(progress * (end - start) + start);
      obj.innerHTML = value + '%';
      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    };
    window.requestAnimationFrame(step);
  }

  // Animate bin level percentages when they come into view
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const span = entry.target.querySelector('span');
        const endValue = parseInt(span.textContent);
        span.textContent = '0%';
        animateValue(span, 0, endValue, 1500);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  binCircles.forEach(circle => {
    observer.observe(circle);
  });

  // Map pin click functionality
  const mapPins = document.querySelectorAll('.map-pin');
  const mapInfoWindow = document.getElementById('mapInfoWindow');
  const infoLocation = document.getElementById('infoLocation');
  const infoLevel = document.getElementById('infoLevel');
  const infoStatus = document.getElementById('infoStatus');

  mapPins.forEach(pin => {
    pin.addEventListener('click', function() {
      // Get data from pin attributes
      const binType = this.getAttribute('data-bin');
      const location = this.getAttribute('data-location');
      const level = this.getAttribute('data-level');
      const status = this.getAttribute('data-status');
      
      // Update info window content
      mapInfoWindow.querySelector('h3').textContent = location;
      infoLocation.textContent = binType;
      infoLevel.textContent = level;
      
      // Update status class and text
      infoStatus.className = `status ${status}`;
      if (status === 'low') {
        infoStatus.textContent = 'Normal';
      } else if (status === 'medium') {
        infoStatus.textContent = 'Attention Soon';
      } else if (status === 'high') {
        infoStatus.textContent = 'Needs Attention';
      }
      
      // Show the info window
      mapInfoWindow.classList.add('active');
    });
  });

  // Logout functionality
  const logoutBtn = document.getElementById('logoutBtn');
  const dropdownLogoutBtn = document.getElementById('dropdownLogoutBtn');
  const logoutModal = document.getElementById('logoutModal');
  const cancelLogoutBtn = document.getElementById('cancelLogout');
  const confirmLogoutBtn = document.getElementById('confirmLogout');

  // Show modal when logout button is clicked
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      logoutModal.classList.add('active');
    });
  }

  // Also show modal when logout is clicked from dropdown
  if (dropdownLogoutBtn) {
    dropdownLogoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      logoutModal.classList.add('active');
      userDropdown.classList.remove('active'); // Close the dropdown
    });
  }

  // Hide modal when cancel button is clicked
  if (cancelLogoutBtn) {
    cancelLogoutBtn.addEventListener('click', function() {
      logoutModal.classList.remove('active');
    });
  }

  // Handle logout confirmation
  if (confirmLogoutBtn) {
    confirmLogoutBtn.addEventListener('click', function() {
      // In a real application, this would redirect to logout endpoint
      window.location.href = "login.html";
    });
  }

  // Close modal when clicking outside
  window.addEventListener('click', function(e) {
    if (e.target === logoutModal) {
      logoutModal.classList.remove('active');
    }
  });

  // Report Modal Functionality
  const reportBtns = document.querySelectorAll('.report-btn');
  const reportModal = document.getElementById('reportModal');
  const reportLocation = document.getElementById('reportLocation');
  const cancelReportBtn = document.getElementById('cancelReport');
  const reportForm = document.getElementById('reportForm');
  const severityOptions = document.querySelectorAll('.severity-option');
  const severityLevelInput = document.getElementById('severityLevel');

  // Show report modal when report button is clicked
  reportBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const location = this.getAttribute('data-location');
      reportLocation.textContent = location;
      reportModal.classList.add('active');
    });
  });

  // Hide report modal when cancel button is clicked
  if (cancelReportBtn) {
    cancelReportBtn.addEventListener('click', function() {
      reportModal.classList.remove('active');
      reportForm.reset();
      // Reset severity selection
      severityOptions.forEach(option => {
        option.classList.remove('selected', 'low', 'medium', 'high');
      });
      severityLevelInput.value = '';
    });
  }

  // Handle severity option selection
  severityOptions.forEach(option => {
    option.addEventListener('click', function() {
      // Remove selected class from all options
      severityOptions.forEach(opt => {
        opt.classList.remove('selected', 'low', 'medium', 'high');
      });
      
      // Add selected class to clicked option
      const severity = this.getAttribute('data-severity');
      this.classList.add('selected', severity);
      severityLevelInput.value = severity;
    });
  });

  // Handle report form submission
  if (reportForm) {
    reportForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form values
      const location = reportLocation.textContent;
      const issueType = document.getElementById('issueType').value;
      const wasteType = document.getElementById('wasteType').value;
      const severity = severityLevelInput.value;
      const description = document.getElementById('issueDescription').value;
      
      // Validate form
      if (!issueType || !wasteType || !severity || !description) {
        alert('Please fill in all required fields');
        return;
      }
      
      // In a real application, this would send the report to a server
      console.log('Report submitted:', {
        location,
        issueType,
        wasteType,
        severity,
        description
      });
      
      // Show success message
      alert(`Report for ${location} submitted successfully!`);
      
      // Reset and close form
      reportForm.reset();
      severityOptions.forEach(option => {
        option.classList.remove('selected', 'low', 'medium', 'high');
      });
      severityLevelInput.value = '';
      reportModal.classList.remove('active');
    });
  }

  // Close report modal when clicking outside
  window.addEventListener('click', function(e) {
    if (e.target === reportModal) {
      reportModal.classList.remove('active');
      reportForm.reset();
      // Reset severity selection
      severityOptions.forEach(option => {
        option.classList.remove('selected', 'low', 'medium', 'high');
      });
      severityLevelInput.value = '';
    }
  });

  // Collect button functionality
  const collectBtns = document.querySelectorAll('.collect-btn');
  collectBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const location = this.closest('.bin-levels').querySelector('h2').textContent;
      alert(`Collection scheduled for ${location}`);
    });
  });
});
</script>
</body>
</html>
</merged    btn.addEventListener('click', function() {
      const location = this.closest('.bin-levels').querySelector('h2').textContent;
      alert(`Collection scheduled for ${location}`);
    });
  });
});
</script>
</body>
</html>
</merged
      alert(`Collection scheduled for ${location}`);
    });
  });
});
</script>
</body>
</html>